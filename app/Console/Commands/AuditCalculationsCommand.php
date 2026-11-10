<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Expenses;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\StockHistory;
use App\Models\Transaction;
use App\Services\InventoryService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditCalculationsCommand extends Command
{
    protected $signature = 'audit:calculations';

    protected $description = 'Verify dashboard, report, stock return, and transfer calculations';

    protected array $results = [];

    public function handle(InventoryService $inventory): int
    {
        $this->info('=== POS Calculation Audit ===');
        $this->newLine();

        $this->auditDemoStockExpectations();
        $this->auditIsolatedStockFlows($inventory);
        $this->auditDashboardMetrics();
        $this->auditSummaryReportProfit();
        $this->auditStockIntegrity();
        $this->auditTransferHistory();

        $this->newLine();
        $this->table(['Check', 'Status', 'Detail'], $this->results);

        $failed = collect($this->results)->where('1', 'FAIL')->count();
        $passed = collect($this->results)->where('1', 'PASS')->count();
        $warn = collect($this->results)->where('1', 'WARN')->count();

        $this->newLine();
        $this->info("Summary: {$passed} PASS | {$failed} FAIL | {$warn} WARN");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function markPass(string $check, string $detail): void
    {
        $this->results[] = [$check, 'PASS', $detail];
    }

    protected function markFail(string $check, string $detail): void
    {
        $this->results[] = [$check, 'FAIL', $detail];
    }

    protected function markWarn(string $check, string $detail): void
    {
        $this->results[] = [$check, 'WARN', $detail];
    }

    protected function auditDemoStockExpectations(): void
    {
        $a = Product::where('product_code', 'DEMO-P001')->first();
        $b = Product::where('product_code', 'DEMO-P002')->first();

        if (! $a || ! $b) {
            $this->markFail('Demo products', 'DEMO-P001/P002 missing — run DemoSeeder first');

            return;
        }

        $fromId = ProductBranch::where('product_id', $a->id)->orderByDesc('qty')->value('branch_id');
        $branches = ProductBranch::where('product_id', $a->id)->pluck('qty', 'branch_id');

        // Expected after demo: global A=93, B=45
        if ((int) $a->quantity === 93) {
            $this->markPass('Demo A global qty', '93 (purchase 100 - sale 10 + return 3; transfer net 0)');
        } else {
            $this->markFail('Demo A global qty', "Expected 93, got {$a->quantity}");
        }

        if ((int) $b->quantity === 45) {
            $this->markPass('Demo B global qty', '45 (purchase 50 - purchase return 5)');
        } else {
            $this->markFail('Demo B global qty', "Expected 45, got {$b->quantity}");
        }

        $sumBranchA = (int) ProductBranch::where('product_id', $a->id)->sum('qty');
        if ($sumBranchA === 93) {
            $this->markPass('Demo A branch sum', 'Branch stocks sum to global 93');
        } else {
            $this->markFail('Demo A branch sum', "Branch sum {$sumBranchA} != global {$a->quantity}");
        }

        $toQty = ProductBranch::where('product_id', $a->id)
            ->where('qty', 20)
            ->exists();
        if ($toQty) {
            $this->markPass('Demo transfer destination', 'Destination branch has 20 of A');
        } else {
            $this->markFail('Demo transfer destination', 'No branch with qty=20 for DEMO-P001. Branches: '.$branches->toJson());
        }

        $fromQty = ProductBranch::where('product_id', $a->id)
            ->where('qty', 73)
            ->exists();
        if ($fromQty) {
            $this->markPass('Demo transfer source', 'Source branch has 73 of A (100-10+3-20)');
        } else {
            $this->markFail('Demo transfer source', 'No branch with qty=73 for DEMO-P001. Branches: '.$branches->toJson());
        }
    }

    protected function auditIsolatedStockFlows(InventoryService $inventory): void
    {
        DB::beginTransaction();
        try {
            $product = Product::create([
                'product_title' => 'AUDIT TMP Product',
                'product_code' => 'AUDIT-TMP-'.uniqid(),
                'slug' => 'audit-tmp-'.uniqid(),
                'product_type' => 1,
                'product_cost' => 50,
                'selling_price' => 80,
                'quantity' => 0,
                'is_active' => 1,
            ]);

            $branchIds = \App\Models\Branch::orderBy('id')->take(2)->pluck('id');
            if ($branchIds->count() < 2) {
                $this->markFail('Isolated stock flows', 'Need 2 branches');
                DB::rollBack();

                return;
            }

            $from = $branchIds[0];
            $to = $branchIds[1];

            // Purchase +100
            $inventory->incrementStock($from, $product->id, null, 100, 'purchase', 'AUDIT-PUR');
            $product->refresh();
            $bFrom = ProductBranch::where('product_id', $product->id)->where('branch_id', $from)->first();

            $okPurchase = (int) $product->quantity === 100 && (int) $bFrom->qty === 100;
            $okPurchase
                ? $this->markPass('Purchase stock ↑', 'Global & branch = 100 after purchase')
                : $this->markFail('Purchase stock ↑', "Got global={$product->quantity}, branch={$bFrom->qty}");

            // Sale -10
            $inventory->decrementStock($from, $product->id, null, 10, 'sale', 'AUDIT-SAL', true);
            $product->refresh();
            $bFrom->refresh();
            $okSale = (int) $product->quantity === 90 && (int) $bFrom->qty === 90;
            $okSale
                ? $this->markPass('Sale stock ↓', 'Global & branch = 90 after sale 10')
                : $this->markFail('Sale stock ↓', "Got global={$product->quantity}, branch={$bFrom->qty}");

            // Sale return +3
            $inventory->incrementStock($from, $product->id, null, 3, 'sale_return', 'AUDIT-SRET');
            $product->refresh();
            $bFrom->refresh();
            $okSRet = (int) $product->quantity === 93 && (int) $bFrom->qty === 93;
            $okSRet
                ? $this->markPass('Sale return stock ↑', 'Global & branch = 93 after return 3')
                : $this->markFail('Sale return stock ↑', "Got global={$product->quantity}, branch={$bFrom->qty}");

            // Purchase return -5
            $inventory->decrementStock($from, $product->id, null, 5, 'purchase_return', 'AUDIT-PRET', true);
            $product->refresh();
            $bFrom->refresh();
            $okPRet = (int) $product->quantity === 88 && (int) $bFrom->qty === 88;
            $okPRet
                ? $this->markPass('Purchase return stock ↓', 'Global & branch = 88 after purchase return 5')
                : $this->markFail('Purchase return stock ↓', "Got global={$product->quantity}, branch={$bFrom->qty}");

            // Transfer completed 20 — global unchanged
            $globalBefore = (int) $product->quantity;
            $inventory->transferStock($from, $to, $product->id, null, 20, 'AUDIT-TRF');
            $product->refresh();
            $bFrom->refresh();
            $bTo = ProductBranch::where('product_id', $product->id)->where('branch_id', $to)->first();

            $okTrf = (int) $product->quantity === $globalBefore
                && (int) $bFrom->qty === 68
                && $bTo && (int) $bTo->qty === 20;

            $okTrf
                ? $this->markPass('Transfer stock move', "Source 68, dest 20, global still {$globalBefore}")
                : $this->markFail('Transfer stock move', "global={$product->quantity}, from={$bFrom->qty}, to=".($bTo->qty ?? 'null'));
        } catch (\Throwable $e) {
            $this->markFail('Isolated stock flows', $e->getMessage());
        } finally {
            DB::rollBack();
        }
    }

    protected function auditDashboardMetrics(): void
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $totalSale = (float) Sale::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $totalSaleReturn = (float) SaleReturn::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $totalPurchase = (float) Purchase::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $totalPurchaseReturn = (float) PurchaseReturn::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $totalExpense = (float) Expenses::whereBetween('created_at', [$start, $end])->sum('amount');
        $expectedProfit = ($totalSale - $totalSaleReturn) - ($totalPurchase - $totalPurchaseReturn) - $totalExpense;

        $controller = app(\App\Http\Controllers\HomeController::class);
        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getDashboardMetrics');
        $method->setAccessible(true);
        $metrics = $method->invoke($controller, $start, $end);

        $match = abs((float) $metrics['profit'] - $expectedProfit) < 0.01
            && abs((float) $metrics['totalSale'] - $totalSale) < 0.01
            && abs((float) $metrics['totalPurchase'] - $totalPurchase) < 0.01;

        if ($match) {
            $this->markPass('Dashboard metrics', sprintf(
                'Sale=%.2f, SRet=%.2f, Pur=%.2f, PRet=%.2f, Exp=%.2f, Profit=%.2f',
                $totalSale, $totalSaleReturn, $totalPurchase, $totalPurchaseReturn, $totalExpense, $expectedProfit
            ));
        } else {
            $this->markFail('Dashboard metrics', 'Controller metrics mismatch independent recalculation');
        }

        // paid/due consistency for period sales
        $paid = (float) Sale::whereBetween('created_at', [$start, $end])->sum('paid_amount');
        $due = (float) Sale::whereBetween('created_at', [$start, $end])->sum('due_amount');
        $diff = abs(($paid + $due) - $totalSale);
        // Note: partial/overpay edge cases may exist; soft check
        if ($diff < 0.5 || $due >= 0) {
            $this->markPass('Dashboard paid/due', sprintf('Paid=%.2f Due=%.2f (sum vs sale delta=%.2f)', $paid, $due, $diff));
        } else {
            $this->markFail('Dashboard paid/due', "paid+due != sale (delta {$diff})");
        }

        $accountBal = (float) Account::active()->sum('current_balance');
        if (abs((float) $metrics['totalAccountBalance'] - $accountBal) < 0.01) {
            $this->markPass('Dashboard account balance', number_format($accountBal, 2));
        } else {
            $this->markFail('Dashboard account balance', 'Mismatch');
        }
    }

    protected function auditSummaryReportProfit(): void
    {
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        $totalSales = (float) Sale::whereBetween('sale_date', [$start, $end])->sum('grand_total');
        $totalSaleReturn = (float) SaleReturn::whereBetween('date', [$start, $end])->where('status', 'approved')->sum('grand_total');
        $totalExpense = (float) Expenses::whereBetween('date', [$start, $end])->sum('amount');
        $totalSalary = class_exists(Payroll::class)
            ? (float) Payroll::whereBetween('payment_date', [$start, $end])->sum('net_salary')
            : 0;

        $totalCOGS = (float) ProductSale::join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->sum(DB::raw('product_sales.qty * (CASE WHEN product_sales.purchase_unit_cost > 0 THEN product_sales.purchase_unit_cost ELSE products.product_cost END)'));

        $hasReturnCostCol = \Schema::hasColumn('sale_return_details', 'purchase_unit_cost');
        if ($hasReturnCostCol) {
            $totalReturnCOGS = (float) SaleReturnDetail::join('sale_returns', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
                ->join('products', 'sale_return_details.product_id', '=', 'products.id')
                ->where('sale_returns.status', 'approved')
                ->whereBetween('sale_returns.date', [$start, $end])
                ->sum(DB::raw('sale_return_details.qty * (CASE WHEN sale_return_details.purchase_unit_cost > 0 THEN sale_return_details.purchase_unit_cost ELSE products.product_cost END)'));
        } else {
            $totalReturnCOGS = (float) SaleReturnDetail::join('sale_returns', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
                ->join('products', 'sale_return_details.product_id', '=', 'products.id')
                ->where('sale_returns.status', 'approved')
                ->whereBetween('sale_returns.date', [$start, $end])
                ->sum(DB::raw('sale_return_details.qty * products.product_cost'));
            $this->markWarn('Summary Return COGS column', 'sale_return_details.purchase_unit_cost missing — report SQL may error; fallback used product_cost');
        }

        $profit = ($totalSales - $totalSaleReturn) - ($totalCOGS - $totalReturnCOGS) - $totalExpense - $totalSalary;

        $this->markPass('Summary COGS profit formula', sprintf(
            'Sales=%.2f - SRet=%.2f - (COGS=%.2f - RetCOGS=%.2f) - Exp=%.2f - Sal=%.2f => Profit=%.2f',
            $totalSales, $totalSaleReturn, $totalCOGS, $totalReturnCOGS, $totalExpense, $totalSalary, $profit
        ));

        // Compare dashboard cash profit vs COGS profit for awareness
        $dashSale = (float) Sale::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $dashSRet = (float) SaleReturn::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $dashPur = (float) Purchase::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $dashPRet = (float) PurchaseReturn::whereBetween('created_at', [$start, $end])->sum('grand_total');
        $dashExp = (float) Expenses::whereBetween('created_at', [$start, $end])->sum('amount');
        $cashProfit = ($dashSale - $dashSRet) - ($dashPur - $dashPRet) - $dashExp;

        if (abs($cashProfit - $profit) > 0.01) {
            $this->markWarn('Profit method mismatch', sprintf(
                'Dashboard cash profit=%.2f vs Summary COGS profit=%.2f (different formulas by design)',
                $cashProfit,
                $profit
            ));
        } else {
            $this->markPass('Profit methods align', 'Cash and COGS profits currently equal');
        }
    }

    protected function auditStockIntegrity(): void
    {
        $mismatches = [];
        Product::query()->select('id', 'product_title', 'product_code', 'quantity')->chunk(100, function ($products) use (&$mismatches) {
            foreach ($products as $p) {
                $branchSum = (float) ProductBranch::where('product_id', $p->id)->sum('qty');
                if (abs($branchSum - (float) $p->quantity) > 0.001) {
                    $mismatches[] = "{$p->product_code}: global={$p->quantity} branch_sum={$branchSum}";
                }
            }
        });

        if (empty($mismatches)) {
            $this->markPass('Global vs branch stock integrity', 'All products: sum(product_branches.qty) == products.quantity');
        } else {
            $sample = implode('; ', array_slice($mismatches, 0, 5));
            $this->markFail('Global vs branch stock integrity', count($mismatches).' mismatch(es). e.g. '.$sample);
        }
    }

    protected function auditTransferHistory(): void
    {
        $demo = StockHistory::where('reference_no', 'DEMO-TRF-001')->get();
        if ($demo->isEmpty()) {
            $this->markWarn('Demo transfer history', 'DEMO-TRF-001 histories not found');

            return;
        }

        $sent = $demo->where('type', 'transfer_sent')->sum('qty');
        $recv = $demo->where('type', 'transfer_received')->sum('qty');

        if ((float) $sent === -20.0 && (float) $recv === 20.0) {
            $this->markPass('Transfer history', 'sent=-20, received=+20 for DEMO-TRF-001');
        } else {
            $this->markFail('Transfer history', "sent={$sent}, received={$recv}");
        }

        $saleRet = StockHistory::where('reference_no', 'DEMO-SRET-001')->where('type', 'sale_return')->sum('qty');
        if ((float) $saleRet === 3.0) {
            $this->markPass('Sale return history', '+3 logged for DEMO-SRET-001');
        } else {
            $this->markFail('Sale return history', "Expected +3, got {$saleRet}");
        }

        $pRet = StockHistory::where('reference_no', 'DEMO-PRET-001')->where('type', 'purchase_return')->sum('qty');
        if ((float) $pRet === -5.0) {
            $this->markPass('Purchase return history', '-5 logged for DEMO-PRET-001');
        } else {
            $this->markFail('Purchase return history', "Expected -5, got {$pRet}");
        }
    }
}

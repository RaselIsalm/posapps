<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expenses;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagementReportsController extends ApiController
{
    private const PERMISSIONS = [
        'summary' => 'view_summary_report', 'sales' => 'view_sales_report', 'purchases' => 'view_purchases_report',
        'customer-ledger' => 'view_customer_ledger_report', 'supplier-ledger' => 'view_supplier_ledger_report',
        'inventory' => 'view_inventory_report', 'employees' => 'view_employees_report', 'branches' => 'view_branch_report',
    ];

    public function show(Request $request, string $report): JsonResponse
    {
        abort_unless(isset(self::PERMISSIONS[$report]), 404);
        $this->requirePermission(self::PERMISSIONS[$report]);
        [$from, $to] = $this->dateRange($request);
        $branchId = $this->branchFilter($request);
        $data = match ($report) {
            'summary' => $this->summary($request, $from, $to, $branchId),
            'sales' => $this->sales($request, $from, $to, $branchId),
            'purchases' => $this->purchases($request, $from, $to, $branchId),
            'customer-ledger' => $this->customerLedger($request, $from, $to, $branchId),
            'supplier-ledger' => $this->supplierLedger($request, $from, $to, $branchId),
            'inventory' => $this->inventory($request, $branchId),
            'employees' => $this->employees($request, $from, $to, $branchId),
            'branches' => $this->branches($request, $from, $to, $branchId),
        };
        $data['branch_options'] = app(CatalogController::class)
            ->accessibleBranchesForUser($request->user())
            ->orderBy('name')
            ->get(['id', 'name']);
        return $this->success($data);
    }

    private function summary(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $sales = $this->scoped(Sale::query(), $request, $branchId)->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()]);
        $purchases = $this->scoped(Purchase::query(), $request, $branchId)->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        $saleReturns = $this->scoped(SaleReturn::query(), $request, $branchId)->where('status', 'approved')->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        $purchaseReturns = $this->scoped(PurchaseReturn::query(), $request, $branchId)->where('status', 'approved')->whereBetween('return_date', [$from->toDateString(), $to->toDateString()]);
        $expenses = $this->scoped(Expenses::query(), $request, $branchId)->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
        $payrolls = Payroll::query()->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])->where('status', 'paid')
            ->whereHas('employee', fn ($q) => $this->applyBranchScope($q, $request, 'branch_id'));
        $saleTotal = (float) (clone $sales)->sum('grand_total');
        $saleReturnTotal = (float) (clone $saleReturns)->sum('grand_total');
        $purchaseTotal = (float) (clone $purchases)->sum('grand_total');
        $purchaseReturnTotal = (float) (clone $purchaseReturns)->sum('grand_total');
        $expenseTotal = (float) (clone $expenses)->sum('amount');
        $salaryTotal = (float) (clone $payrolls)->sum('net_salary');
        $accessibleSales = $this->applyBranchScope(Sale::query(), $request)->select('id');
        $cogs = (float) ProductSale::query()->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereIn('sales.id', $accessibleSales)
            ->whereBetween('sales.sale_date', [$from->toDateString(), $to->toDateString()])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->sum(DB::raw('product_sales.qty * product_sales.purchase_unit_cost'));
        $available = (float) $this->scoped(ProductBranch::query(), $request, $branchId)->sum('qty');
        return ['period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()], 'branch_id' => $branchId,
            'sales' => ['total' => $saleTotal, 'tax' => (float) (clone $sales)->sum('tax_amount'), 'paid' => (float) (clone $sales)->sum('paid_amount'), 'due' => (float) (clone $sales)->sum('due_amount')],
            'sale_returns' => ['total' => $saleReturnTotal], 'purchases' => ['total' => $purchaseTotal, 'paid' => (float) (clone $purchases)->sum('paid_amount'), 'due' => (float) (clone $purchases)->sum('due_amount')],
            'purchase_returns' => ['total' => $purchaseReturnTotal], 'expenses' => ['total' => $expenseTotal], 'payroll' => ['total' => $salaryTotal],
            'profit' => $saleTotal - $saleReturnTotal - $cogs - $expenseTotal - $salaryTotal, 'available_products' => $available];
    }

    private function sales(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $query = $this->scoped(Sale::query(), $request, $branchId)->with(['customer', 'branch', 'seller'])->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
            ->when($request->filled('search'), function ($q) use ($request) { $s = trim((string) $request->query('search')); $q->where(fn ($n) => $n->where('invoice_no', 'like', "%$s%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%$s%"))); });
        $daily = (clone $query)->selectRaw('DATE(sale_date) as date, COUNT(*) as invoices, SUM(grand_total) as total, SUM(paid_amount) as paid, SUM(due_amount) as due')->groupBy(DB::raw('DATE(sale_date)'))->orderByDesc('date')->get();
        $list = $this->page($request, $query->latest('sale_date')->latest('id'), fn (Sale $sale) => ['id' => $sale->id, 'date' => $sale->sale_date, 'invoice' => $sale->invoice_no, 'customer' => $sale->customer?->name ?? 'Walk-in', 'branch' => $sale->branch?->name, 'total' => (float) $sale->grand_total, 'paid' => (float) $sale->paid_amount, 'due' => (float) $sale->due_amount, 'status' => $sale->payment_status], 'rows');
        $list['daily'] = $daily->map(fn ($r) => ['date' => $r->date, 'invoices' => (int) $r->invoices, 'total' => (float) $r->total, 'paid' => (float) $r->paid, 'due' => (float) $r->due]);
        $list['totals'] = ['sales' => (float) (clone $query)->sum('grand_total'), 'paid' => (float) (clone $query)->sum('paid_amount'), 'due' => (float) (clone $query)->sum('due_amount'), 'invoices' => (int) (clone $query)->count()];
        return $list;
    }

    private function purchases(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $query = $this->scoped(Purchase::query(), $request, $branchId)->with(['supplier', 'branch', 'details.product'])->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($request->filled('supplier_id'), fn ($q) => $q->where('supplier_id', $request->integer('supplier_id')));
        $rows = $this->page($request, $query->latest('date')->latest('id'), fn (Purchase $p) => ['id' => $p->id, 'date' => $p->date, 'reference' => $p->invoice_no, 'supplier_id' => $p->supplier_id, 'supplier' => $p->supplier?->name, 'branch' => $p->branch?->name, 'total' => (float) $p->grand_total, 'paid' => (float) $p->paid_amount, 'due' => (float) $p->due_amount], 'rows');
        $rows['totals'] = ['total' => (float) (clone $query)->sum('grand_total'), 'paid' => (float) (clone $query)->sum('paid_amount'), 'due' => (float) (clone $query)->sum('due_amount'), 'returns' => (float) $this->scoped(PurchaseReturn::query(), $request, $branchId)->where('status', 'approved')->whereBetween('return_date', [$from->toDateString(), $to->toDateString()])->sum('grand_total')];
        $rows['by_supplier'] = (clone $query)->select('supplier_id')->selectRaw('COUNT(*) as count, SUM(grand_total) as total')->with('supplier:id,name')->groupBy('supplier_id')->get()->map(fn ($p) => ['supplier_id' => $p->supplier_id, 'supplier' => $p->supplier?->name, 'count' => (int) $p->count, 'total' => (float) $p->total]);
        $rows['by_day'] = (clone $query)->selectRaw('DATE(date) as date, COUNT(*) as count, SUM(grand_total) as total')->groupBy(DB::raw('DATE(date)'))->orderByDesc('date')->get()->map(fn ($p) => ['date' => $p->date, 'count' => (int) $p->count, 'total' => (float) $p->total]);
        $accessiblePurchases = $this->applyBranchScope(Purchase::query(), $request)->select('id');
        $rows['by_product'] = DB::table('purchase_details')->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')->join('products', 'purchase_details.product_id', '=', 'products.id')
            ->whereIn('purchases.id', $accessiblePurchases)->whereBetween('purchases.date', [$from->toDateString(), $to->toDateString()])->when($branchId, fn ($q) => $q->where('purchases.branch_id', $branchId))
            ->select('products.id as product_id', 'products.product_title as product')->selectRaw('SUM(purchase_details.qty) as quantity, SUM(purchase_details.total) as total')->groupBy('products.id', 'products.product_title')->orderByDesc('total')->limit(100)->get()->map(fn ($p) => ['product_id' => $p->product_id, 'product' => $p->product, 'quantity' => (float) $p->quantity, 'total' => (float) $p->total]);
        $accessiblePurchases = $this->applyBranchScope(Purchase::query(), $request)->select('supplier_id');
        $rows['suppliers'] = Supplier::whereIn('id', $accessiblePurchases)->orderBy('name')->get(['id', 'name', 'phone']);
        return $rows;
    }

    private function customerLedger(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $customer = $request->query('customer_id');
        $accessibleCustomers = $this->applyBranchScope(Sale::query(), $request)
            ->whereNotNull('customer_id')->select('customer_id');
        $customers = Customer::whereIn('id', $accessibleCustomers)->orderBy('name')->get(['id', 'name', 'phone']);
        $customers->prepend(['id' => 'walk_in', 'name' => 'Walk-in customer', 'phone' => null]);
        if (! $customer) return $this->ledgerData(collect(), 0, ['customers' => $customers]);
        $isWalkIn = $customer === 'walk_in';
        $sales = fn () => $this->scoped(Sale::query(), $request, $branchId)->when($isWalkIn, fn ($q) => $q->whereNull('customer_id'), fn ($q) => $q->where('customer_id', $customer))->whereIn('delivery_status', ['delivered', 'completed', 'pending']);
        $saleIds = (clone $sales())->select('id');
        $opening = (float) (clone $sales())->whereDate('sale_date', '<', $from->toDateString())->sum('grand_total')
            - (float) Payment::whereIn('sale_id', (clone $saleIds))->whereDate('payment_date', '<', $from->toDateString())->sum('amount')
            - (float) $this->scoped(SaleReturn::query(), $request, $branchId)->when($isWalkIn, fn ($q) => $q->whereNull('customer_id'), fn ($q) => $q->where('customer_id', $customer))->where('status', 'approved')->whereDate('date', '<', $from->toDateString())->sum('grand_total');
        $entries = collect();
        foreach ((clone $sales())->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])->get() as $sale) $entries->push(['date' => $sale->sale_date, 'description' => 'Sale invoice', 'reference' => $sale->invoice_no, 'debit' => (float) $sale->grand_total, 'credit' => 0]);
        foreach (Payment::whereIn('sale_id', (clone $saleIds))->whereBetween('payment_date', [$from->toDateString(), $to->toDateString()])->get() as $payment) $entries->push(['date' => $payment->payment_date, 'description' => 'Payment received', 'reference' => $payment->transaction_id, 'debit' => 0, 'credit' => (float) $payment->amount]);
        foreach ($this->scoped(SaleReturn::query(), $request, $branchId)->when($isWalkIn, fn ($q) => $q->whereNull('customer_id'), fn ($q) => $q->where('customer_id', $customer))->where('status', 'approved')->whereBetween('date', [$from->toDateString(), $to->toDateString()])->get() as $return) $entries->push(['date' => $return->date, 'description' => 'Sale return', 'reference' => $return->reference_no, 'debit' => 0, 'credit' => (float) $return->grand_total]);
        return $this->ledgerData($entries, $opening, ['customers' => $customers]);
    }

    private function supplierLedger(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $supplierId = $request->integer('supplier_id');
        $accessibleSuppliers = $this->applyBranchScope(Purchase::query(), $request)->select('supplier_id');
        $suppliers = Supplier::whereIn('id', $accessibleSuppliers)->orderBy('name')->get(['id', 'name', 'phone']);
        if (! $supplierId) return $this->ledgerData(collect(), 0, ['suppliers' => $suppliers]);
        $purchases = fn () => $this->scoped(Purchase::query(), $request, $branchId)->where('supplier_id', $supplierId);
        $opening = (float) (clone $purchases())->whereDate('date', '<', $from->toDateString())->sum('grand_total') - (float) (clone $purchases())->whereDate('date', '<', $from->toDateString())->sum('paid_amount')
            - (float) $this->scoped(PurchaseReturn::query(), $request, $branchId)->where('supplier_id', $supplierId)->where('status', 'approved')->whereDate('return_date', '<', $from->toDateString())->sum('grand_total');
        $entries = collect();
        foreach ((clone $purchases())->whereBetween('date', [$from->toDateString(), $to->toDateString()])->get() as $purchase) $entries->push(['date' => $purchase->date, 'description' => 'Purchase invoice', 'reference' => $purchase->invoice_no, 'debit' => (float) $purchase->grand_total, 'credit' => (float) $purchase->paid_amount]);
        foreach ($this->scoped(PurchaseReturn::query(), $request, $branchId)->where('supplier_id', $supplierId)->where('status', 'approved')->whereBetween('return_date', [$from->toDateString(), $to->toDateString()])->get() as $return) $entries->push(['date' => $return->return_date, 'description' => 'Purchase return', 'reference' => $return->reference_no, 'debit' => 0, 'credit' => (float) $return->grand_total]);
        return $this->ledgerData($entries, $opening, ['suppliers' => $suppliers]);
    }

    private function inventory(Request $request, ?int $branchId): array
    {
        $query = $this->scoped(ProductBranch::query(), $request, $branchId)->with(['product.category', 'product.brand', 'branch'])->whereHas('product', fn ($q) => $q->where('is_active', 1))
            ->when($request->filled('category_id'), fn ($q) => $q->whereHas('product', fn ($p) => $p->where('category_id', $request->integer('category_id'))))
            ->when($request->filled('search'), fn ($q) => $q->whereHas('product', fn ($p) => $p->where('product_title', 'like', '%'.trim((string) $request->query('search')).'%')->orWhere('product_code', 'like', '%'.trim((string) $request->query('search')).'%')));
        $list = $this->page($request, $query->orderBy('product_id'), fn (ProductBranch $row) => ['id' => $row->id, 'product_id' => $row->product_id, 'product' => $row->product?->product_title, 'code' => $row->product?->product_code, 'category' => $row->product?->category?->name, 'branch' => $row->branch?->name, 'quantity' => (float) $row->qty, 'cost' => (float) $row->product?->product_cost, 'price' => (float) $row->sell_price, 'asset_value' => (float) $row->qty * (float) $row->product?->product_cost, 'low_stock' => (float) $row->qty <= (float) $row->product?->alert_quantity], 'rows');
        $list['categories'] = \App\Models\Category::orderBy('name')->get(['id', 'name']); return $list;
    }

    private function employees(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $employees = $this->applyBranchScope(Employee::query()->with('branch'), $request)->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->query('search')).'%'));
        $accessibleSales = $this->applyBranchScope(Sale::query(), $request)->select('id');
        $rows = $this->page($request, $employees->orderBy('name'), function (Employee $employee) use ($from, $to, $branchId, $accessibleSales) {
            $sales = Sale::whereIn('id', $accessibleSales)->where('seller_id', $employee->id)->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()])->whereIn('delivery_status', ['delivered', 'completed', 'pending'])->when($branchId, fn ($q) => $q->where('branch_id', $branchId));
            $returns = SaleReturn::whereIn('sale_id', (clone $sales)->select('id'))->where('status', 'approved')->sum('grand_total');
            $commission = ProductSale::query()->join('sales', 'product_sales.sale_id', '=', 'sales.id')->join('products', 'product_sales.product_id', '=', 'products.id')->whereIn('sales.id', $accessibleSales)->where('sales.seller_id', $employee->id)->whereBetween('sales.sale_date', [$from->toDateString(), $to->toDateString()])->whereIn('sales.delivery_status', ['delivered', 'completed', 'pending'])->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))->get(['product_sales.qty', 'products.max_comission'])->sum(fn ($line) => (float) $line->qty * min((float) $employee->max_commission, (float) $line->max_comission));
            $total = (float) (clone $sales)->sum('grand_total');
            return ['id' => $employee->id, 'name' => $employee->name, 'phone' => $employee->phone, 'branch' => $employee->branch?->name, 'invoices' => (int) (clone $sales)->count(), 'sales' => $total, 'returns' => (float) $returns, 'net_sales' => $total - (float) $returns, 'commission' => (float) $commission];
        }, 'rows');
        $rows['period'] = ['from' => $from->toDateString(), 'to' => $to->toDateString()]; return $rows;
    }

    private function branches(Request $request, Carbon $from, Carbon $to, ?int $branchId): array
    {
        $branches = app(CatalogController::class)->accessibleBranchesForUser($request->user())->when($branchId, fn ($q) => $q->whereKey($branchId));
        $rows = $branches->orderBy('name')->get()->map(function (Branch $branch) use ($request, $from, $to) {
            $sales = Sale::where('branch_id', $branch->id)->whereBetween('sale_date', [$from->toDateString(), $to->toDateString()]);
            $purchases = Purchase::where('branch_id', $branch->id)->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
            $expenses = Expenses::where('branch_id', $branch->id)->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
            $returns = SaleReturn::where('branch_id', $branch->id)->where('status', 'approved')->whereBetween('date', [$from->toDateString(), $to->toDateString()]);
            $s = (float) (clone $sales)->sum('grand_total'); $r = (float) (clone $returns)->sum('grand_total'); $e = (float) (clone $expenses)->sum('amount');
            return ['id' => $branch->id, 'name' => $branch->name, 'sales' => $s, 'purchases' => (float) (clone $purchases)->sum('grand_total'), 'expenses' => $e, 'returns' => $r, 'net' => $s - $r - $e];
        })->values();
        return ['period' => ['from' => $from->toDateString(), 'to' => $to->toDateString()], 'branches' => $rows, 'totals' => ['sales' => (float) $rows->sum('sales'), 'purchases' => (float) $rows->sum('purchases'), 'expenses' => (float) $rows->sum('expenses'), 'returns' => (float) $rows->sum('returns'), 'net' => (float) $rows->sum('net')]];
    }

    private function ledgerData($entries, float $opening, array $options): array
    {
        $entries = $entries->sortBy('date')->values(); $balance = $opening; $debit = 0.0; $credit = 0.0;
        $rows = [['date' => null, 'description' => 'Opening balance', 'reference' => null, 'debit' => 0, 'credit' => 0, 'balance' => $balance]];
        foreach ($entries as $entry) { $balance += $entry['debit'] - $entry['credit']; $debit += $entry['debit']; $credit += $entry['credit']; $rows[] = $entry + ['balance' => $balance]; }
        return ['rows' => $rows, 'opening_balance' => $opening, 'totals' => ['debit' => $debit, 'credit' => $credit, 'closing_balance' => $balance]] + $options;
    }

    private function dateRange(Request $request): array
    {
        $validated = $request->validate(['from_date' => ['nullable', 'date'], 'to_date' => ['nullable', 'date', 'after_or_equal:from_date'], 'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date']]);
        $from = $validated['from_date'] ?? $validated['start_date'] ?? now()->startOfMonth()->toDateString();
        $to = $validated['to_date'] ?? $validated['end_date'] ?? now()->endOfMonth()->toDateString();
        return [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()];
    }

    private function branchFilter(Request $request): ?int
    {
        $id = $this->requestedBranchId($request);
        if ($id !== null) abort_unless(app(CatalogController::class)->accessibleBranchesForUser($request->user())->whereKey($id)->exists(), 403, 'You cannot access this branch.');
        return $id;
    }

    private function scoped(Builder $query, Request $request, ?int $branchId): Builder
    {
        if ($branchId) return $query->where('branch_id', $branchId);
        return $this->applyBranchScope($query, $request);
    }
}

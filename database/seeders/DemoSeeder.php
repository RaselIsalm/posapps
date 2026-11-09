<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Supplier;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SaleService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds a controlled DEMO dataset with known quantities/amounts
 * so dashboard, reports, returns, and transfers can be verified.
 *
 * Safe to re-run: skips if DEMO-P001 product already exists.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (Product::where('product_code', 'DEMO-P001')->exists()) {
            $this->command?->warn('Demo data already exists (DEMO-P001). Skipping DemoSeeder.');

            return;
        }

        DB::transaction(function () {
            $inventory = app(InventoryService::class);
            $saleService = app(SaleService::class);
            $demoManager = User::query()->orderBy('id')->firstOrFail();

            $unit = Unit::first() ?? Unit::create([
                'name' => 'Piece',
                'short_code' => 'pc',
                'is_active' => 1,
            ]);

            $category = Category::first() ?? Category::create([
                'name' => 'Demo Category',
                'slug' => 'demo-category',
                'status' => 1,
            ]);

            $brand = Brand::first() ?? Brand::create([
                'name' => 'Demo Brand',
                'slug' => 'demo-brand',
                'status' => 1,
            ]);

            $account = Account::active()->first() ?? Account::create([
                'name' => 'Demo Cash',
                'account_number' => 'DEMO-CASH',
                'initial_balance' => 100000,
                'current_balance' => 100000,
                'type' => 'cash',
                'is_active' => 1,
                'is_default' => 0,
            ]);

            $branches = Branch::orderBy('id')->take(2)->get();
            if ($branches->count() < 2) {
                while ($branches->count() < 2) {
                    $n = $branches->count() + 1;
                    $branches->push(Branch::create([
                        'name' => 'Demo Branch '.$n,
                        'manager_id' => $demoManager->id,
                        'phone' => '0170000000'.$n,
                        'status' => 1,
                        'account_id' => $account->id,
                        'created_by' => $demoManager->id,
                    ]));
                }
            }

            if ($branches->count() < 2) {
                throw new \RuntimeException('Need at least 2 branches for demo transfer. Create another branch first.');
            }

            $fromBranch = $branches[0];
            $toBranch = $branches[1];

            $customer = Customer::firstOrCreate(
                ['phone' => '01990000001'],
                [
                    'name' => 'Demo Customer',
                    'email' => 'demo.customer@example.com',
                    'code' => 'DEMO-CUS-001',
                    'balance' => 0,
                    'reward_points' => 0,
                    'status' => 1,
                ]
            );

            $supplier = Supplier::firstOrCreate(
                ['phone' => '01990000002'],
                [
                    'name' => 'Demo Supplier',
                    'company_name' => 'Demo Supply Co',
                    'email' => 'demo.supplier@example.com',
                    'balance' => 0,
                    'is_active' => 1,
                ]
            );

            $expenseCategory = ExpensesCategory::first() ?? ExpensesCategory::create([
                'code' => 'DEMO-EXP-001',
                'name' => 'Demo Expense',
                'status' => 1,
            ]);

            // Product A: cost 100, sell 150 — used for purchase/sale/return/transfer
            $productA = Product::create([
                'product_title' => 'Demo Product Alpha',
                'product_code' => 'DEMO-P001',
                'slug' => 'demo-product-alpha-'.Str::random(4),
                'product_type' => 1,
                'product_cost' => 100,
                'selling_price' => 150,
                'wholesale_price' => 120,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'product_unit' => $unit->id,
                'purchase_unit' => $unit->id,
                'selling_unit' => $unit->id,
                'quantity' => 0,
                'alert_quantity' => 5,
                'is_active' => 1,
                'is_published' => 1,
            ]);

            // Product B: cost 200, sell 300
            $productB = Product::create([
                'product_title' => 'Demo Product Beta',
                'product_code' => 'DEMO-P002',
                'slug' => 'demo-product-beta-'.Str::random(4),
                'product_type' => 1,
                'product_cost' => 200,
                'selling_price' => 300,
                'wholesale_price' => 250,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'product_unit' => $unit->id,
                'purchase_unit' => $unit->id,
                'selling_unit' => $unit->id,
                'quantity' => 0,
                'alert_quantity' => 5,
                'is_active' => 1,
                'is_published' => 1,
            ]);

            // --- Purchase received: A=100, B=50 into fromBranch ---
            $purchaseQtyA = 100;
            $purchaseQtyB = 50;
            $purchaseTotal = ($purchaseQtyA * 100) + ($purchaseQtyB * 200); // 20000

            $purchase = Purchase::create([
                'invoice_no' => 'DEMO-PUR-001',
                'branch_id' => $fromBranch->id,
                'supplier_id' => $supplier->id,
                'date' => now()->toDateString(),
                'status' => 'received',
                'payment_method' => $account->id,
                'order_tax' => 0,
                'order_discount' => 0,
                'shipping_cost' => 0,
                'currency' => 'BDT',
                'exchange_rate' => 1,
                'total_qty' => $purchaseQtyA + $purchaseQtyB,
                'grand_total' => $purchaseTotal,
                'paid_amount' => $purchaseTotal,
                'due_amount' => 0,
                'note' => 'Demo purchase',
            ]);

            foreach ([[$productA, $purchaseQtyA, 100], [$productB, $purchaseQtyB, 200]] as [$p, $qty, $cost]) {
                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $p->id,
                    'variant_id' => null,
                    'qty' => $qty,
                    'net_unit_cost' => $cost,
                    'discount' => 0,
                    'tax' => 0,
                    'total' => $qty * $cost,
                ]);
                $inventory->incrementStock($fromBranch->id, $p->id, null, $qty, 'purchase', $purchase->invoice_no);
            }

            // --- Sale: A=10 @150 = 1500 ---
            $sale = $saleService->createSale([
                'customer_id' => $customer->id,
                'branch_id' => $fromBranch->id,
                'sale_date' => now()->toDateString(),
                'discount_type' => 'fixed',
                'discount_value' => 0,
                'discount_amount' => 0,
                'group_discount_amount' => 0,
                'tax_percent' => 0,
                'tax_amount' => 0,
                'items' => [
                    [
                        'id' => $productA->id,
                        'variant_id' => null,
                        'qty' => 10,
                        'price' => 150,
                    ],
                ],
                'payments' => [
                    [
                        'method' => $account->id,
                        'amount' => 1500,
                    ],
                ],
            ]);

            Sale::where('id', $sale->id)->update(['invoice_no' => 'DEMO-SAL-001']);

            // --- Sale return approve: return 3 of A → stock +3 ---
            $returnQty = 3;
            $returnTotal = $returnQty * 150;
            $saleReturn = SaleReturn::create([
                'reference_no' => 'DEMO-SRET-001',
                'sale_id' => $sale->id,
                'customer_id' => $customer->id,
                'branch_id' => $fromBranch->id,
                'date' => now()->toDateString(),
                'grand_total' => $returnTotal,
                'status' => 'pending',
                'created_by' => 1,
            ]);

            SaleReturnDetail::create([
                'sale_return_id' => $saleReturn->id,
                'product_id' => $productA->id,
                'variant_id' => null,
                'qty' => $returnQty,
                'unit_price' => 150,
                'total' => $returnTotal,
            ]);

            $inventory->incrementStock(
                $fromBranch->id,
                $productA->id,
                null,
                $returnQty,
                'sale_return',
                $saleReturn->reference_no
            );
            $saleReturn->update(['status' => 'approved']);

            // --- Purchase return approve: return 5 of B → stock -5 ---
            $pReturnQty = 5;
            $pReturnTotal = $pReturnQty * 200;
            $purchaseReturn = PurchaseReturn::create([
                'reference_no' => 'DEMO-PRET-001',
                'purchase_id' => $purchase->id,
                'supplier_id' => $supplier->id,
                'branch_id' => $fromBranch->id,
                'return_date' => now()->toDateString(),
                'total_qty' => $pReturnQty,
                'grand_total' => $pReturnTotal,
                'status' => 'approved',
                'payment_status' => 'pending',
                'created_by' => 1,
            ]);

            PurchaseReturnDetail::create([
                'purchase_return_id' => $purchaseReturn->id,
                'product_id' => $productB->id,
                'variant_id' => null,
                'qty' => $pReturnQty,
                'unit_price' => 200,
                'total' => $pReturnTotal,
            ]);

            $inventory->decrementStock(
                $fromBranch->id,
                $productB->id,
                null,
                $pReturnQty,
                'purchase_return',
                $purchaseReturn->reference_no,
                true
            );

            // --- Transfer completed: A=20 from → to (global qty unchanged) ---
            $transferQty = 20;
            $transfer = Transfer::create([
                'reference_no' => 'DEMO-TRF-001',
                'user_id' => 1,
                'from_branch_id' => $fromBranch->id,
                'to_branch_id' => $toBranch->id,
                'item_count' => 1,
                'total_qty' => $transferQty,
                'shipping_cost' => 0,
                'grand_total' => $transferQty * 100,
                'status' => 'completed',
                'note' => 'Demo transfer',
            ]);

            TransferDetail::create([
                'transfer_id' => $transfer->id,
                'product_id' => $productA->id,
                'variant_id' => null,
                'qty' => $transferQty,
                'net_unit_cost' => 100,
                'tax' => 0,
                'subtotal' => $transferQty * 100,
            ]);

            $inventory->transferStock(
                $fromBranch->id,
                $toBranch->id,
                $productA->id,
                null,
                $transferQty,
                $transfer->reference_no
            );

            // --- Expense for dashboard profit ---
            Expenses::create([
                'date' => now()->toDateString(),
                'category_id' => $expenseCategory->id,
                'branch_id' => $fromBranch->id,
                'account_id' => $account->id,
                'amount' => 500,
                'note' => 'Demo expense',
                'status' => 1,
            ]);

            $this->command?->info('DemoSeeder completed.');
            $this->command?->table(
                ['Key', 'Expected'],
                [
                    ['Product A global qty', '93 (100 - 10 sale + 3 return; transfer does not change global)'],
                    ['Product A from-branch', '73 (100 - 10 + 3 - 20 transfer)'],
                    ['Product A to-branch', '20'],
                    ['Product B global/from', '45 (50 - 5 purchase return)'],
                    ['Demo sale total', '1500'],
                    ['Demo sale return', '450'],
                    ['Demo purchase', '20000'],
                    ['Demo purchase return', '1000'],
                    ['Demo expense', '500'],
                ]
            );
        });
    }
}

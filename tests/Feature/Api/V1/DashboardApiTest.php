<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expenses;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-08 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_dashboard_totals_financial_groups_recent_rows_and_best_sellers_match_website_formulas(): void
    {
        $user = $this->dashboardUser(canViewAllBranches: true);
        $customer = Customer::create(['name' => 'Dashboard Customer', 'phone' => '555-1001']);
        $supplier = Supplier::create(['name' => 'Dashboard Supplier', 'phone' => '555-2001']);
        $account = $this->account('Main Cash', 'ACC-1', 1000);
        $inactiveAccount = $this->account('Inactive Cash', 'ACC-2', 500);
        $inactiveAccount->update(['is_active' => false]);
        $employee = Employee::create([
            'name' => 'Dashboard Employee',
            'email' => 'dashboard-employee@example.test',
            'status' => true,
        ]);
        $product = Product::create([
            'product_title' => 'Dashboard Product',
            'product_code' => 'DASH-1',
            'slug' => 'dashboard-product',
            'product_type' => 1,
            'product_cost' => 20,
            'selling_price' => 50,
        ]);

        $sale = Sale::create([
            'invoice_no' => 'DASH-SALE-1',
            'customer_id' => $customer->id,
            'sale_date' => today()->toDateString(),
            'sub_total' => 100,
            'grand_total' => 100,
            'paid_amount' => 60,
            'due_amount' => 40,
            'payment_status' => 'partial',
        ]);
        ProductSale::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'qty' => 2,
            'unit_price' => 50,
            'purchase_unit_cost' => 20,
            'total_price' => 100,
        ]);
        SaleReturn::create([
            'reference_no' => 'DASH-SR-1',
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'date' => today()->toDateString(),
            'grand_total' => 5,
        ]);

        $purchase = Purchase::create([
            'branch_id' => null,
            'supplier_id' => $supplier->id,
            'invoice_no' => 'DASH-PUR-1',
            'date' => today()->toDateString(),
            'grand_total' => 40,
            'paid_amount' => 20,
            'due_amount' => 20,
        ]);
        PurchaseReturn::create([
            'reference_no' => 'DASH-PR-1',
            'purchase_id' => $purchase->id,
            'supplier_id' => $supplier->id,
            'return_date' => today()->toDateString(),
            'grand_total' => 3,
        ]);
        Expenses::create([
            'date' => today()->toDateString(),
            'amount' => 10,
            'note' => 'Dashboard expense',
        ]);
        Payroll::create([
            'employee_id' => $employee->id,
            'salary_month' => 'November',
            'salary_year' => 2026,
            'net_salary' => 15,
            'status' => 'pending',
        ]);
        Payroll::create([
            'employee_id' => null,
            'salary_month' => 'November',
            'salary_year' => 2026,
            'net_salary' => 5,
            'status' => 'pending',
        ]);
        Transaction::create([
            'account_id' => $account->id,
            'type' => 'credit',
            'amount' => 7,
            'balance' => 1007,
            'date' => today()->toDateString(),
            'note' => 'Cash in',
        ]);
        Transaction::create([
            'account_id' => $account->id,
            'type' => 'debit',
            'amount' => 2,
            'balance' => 1005,
            'date' => today()->toDateString(),
            'note' => 'Cash out',
        ]);
        Transaction::create([
            'account_id' => null,
            'type' => 'credit',
            'amount' => 3,
            'balance' => 3,
            'date' => today()->toDateString(),
            'note' => 'Cash without linked account',
        ]);
        Transaction::create([
            'account_id' => $inactiveAccount->id,
            'type' => 'credit',
            'amount' => 4,
            'balance' => 504,
            'date' => today()->toDateString(),
            'note' => 'Cash linked to inactive account',
        ]);
        Quotation::create([
            'reference_no' => 'DASH-QUO-1',
            'customer_id' => $customer->id,
            'date' => today()->toDateString(),
            'grand_total' => 75,
        ]);

        $data = $this->dashboard($user)->assertOk()->json('data');

        $expected = [
            'total_customers' => 1,
            'total_suppliers' => 1,
            'total_sale' => 100.0,
            'sale_return' => 5.0,
            'total_purchase' => 40.0,
            'purchase_return' => 3.0,
            'total_paid' => 60.0,
            'total_due' => 40.0,
            'total_due_receive' => 0.0,
            'total_purchase_paid' => 20.0,
            'total_purchase_due' => 20.0,
            'total_expense' => 10.0,
            'profit' => 48.0,
            'total_salary' => 20.0,
            'receivable_customer_due' => 40.0,
            'total_account_balance' => 1000.0,
            'supplier_due' => 20.0,
            'customer_advance' => 0.0,
            'total_cash_in' => 14.0,
            'total_cash_out' => 2.0,
        ];

        foreach ($expected as $key => $value) {
            $this->assertEquals($value, $data[$key], "Dashboard metric {$key} should match website formula.");
        }

        $this->assertEquals($data['total_sale'], $data['sale']);
        $this->assertEquals($data['total_purchase'], $data['purchase']);
        $this->assertEquals($data['total_purchase_due'], $data['purchase_due']);
        $this->assertEquals(40.0, $data['assets']['receivable_customer_due']);
        $this->assertEquals(1000.0, $data['assets']['total_account_balance']);
        $this->assertEquals(20.0, $data['liabilities']['supplier_due']);
        $this->assertEquals(0.0, $data['liabilities']['customer_advance']);
        $this->assertEquals(14.0, $data['cash']['total_cash_in']);
        $this->assertEquals(2.0, $data['cash']['total_cash_out']);
        $this->assertCount(1, $data['recent_sales']);
        $this->assertCount(1, $data['recent_purchases']);
        $this->assertCount(1, $data['recent_quotations']);
        $this->assertCount(4, $data['recent_transactions']);
        $this->assertSame('DASH-SALE-1', $data['recent_sales'][0]['reference']);
        $this->assertSame('DASH-PUR-1', $data['recent_purchases'][0]['reference']);
        $this->assertSame('DASH-QUO-1', $data['recent_quotations'][0]['reference']);
        $this->assertSame($product->id, $data['best_seller_month'][0]['product_id']);
        $this->assertEquals(2.0, $data['best_seller_month'][0]['quantity']);
        $this->assertEquals(2.0, $data['best_seller_year_qty'][0]['quantity']);
        $this->assertEquals(100.0, $data['best_seller_year_revenue'][0]['revenue']);
        $this->assertCount(6, $data['purchase_and_sale_chart']);
        $this->assertSame(11, $data['purchase_and_sale_chart'][5]['month']);
        $this->assertSame(2026, $data['purchase_and_sale_chart'][5]['year']);
    }

    public function test_website_date_filters_and_legacy_app_filter_aliases_select_the_same_created_at_ranges(): void
    {
        $user = $this->dashboardUser(canViewAllBranches: true);
        $ranges = [
            ['DASH-TODAY', now(), 1],
            ['DASH-7DAYS', now()->subDays(7), 2],
            ['DASH-8DAYS', now()->subDays(8), 4],
            ['DASH-LASTYEAR', now()->subYear(), 8],
        ];
        foreach ($ranges as [$invoice, $createdAt, $amount]) {
            $sale = new Sale([
                'invoice_no' => $invoice,
                'sale_date' => $createdAt->toDateString(),
                'sub_total' => $amount,
                'grand_total' => $amount,
            ]);
            $sale->created_at = $createdAt;
            $sale->updated_at = $createdAt;
            $sale->save();
        }

        $expectedTotals = [
            'today' => 1.0,
            'daily' => 1.0,
            'last_7_days' => 3.0,
            'weekly' => 3.0,
            'this_month' => 3.0,
            'monthly' => 3.0,
            'this_year' => 7.0,
            'yearly' => 7.0,
        ];
        foreach ($expectedTotals as $filter => $expected) {
            $data = $this->dashboard($user, $filter)->assertOk()->json('data');
            $this->assertEquals($expected, $data['total_sale'], "Filter {$filter} returned the wrong total.");
        }
    }

    public function test_employee_dashboard_metrics_recent_data_and_balances_are_limited_to_assigned_branch(): void
    {
        $user = $this->dashboardUser();
        $otherManager = User::factory()->create(['is_active' => true]);
        $assignedAccount = $this->account('Assigned Account', 'BR-A', 100);
        $otherAccount = $this->account('Other Account', 'BR-B', 900);
        $assignedBranch = Branch::create([
            'name' => 'Assigned Branch',
            'manager_id' => $user->id,
            'account_id' => $assignedAccount->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $otherBranch = Branch::create([
            'name' => 'Other Branch',
            'manager_id' => $otherManager->id,
            'account_id' => $otherAccount->id,
            'created_by' => $otherManager->id,
            'status' => true,
        ]);
        $employee = Employee::create([
            'name' => 'Branch Employee',
            'email' => 'branch-dashboard@example.test',
            'branch_id' => $assignedBranch->id,
            'user_id' => $user->id,
            'status' => true,
        ]);
        $otherEmployee = Employee::create([
            'name' => 'Other Branch Employee',
            'email' => 'other-branch-dashboard@example.test',
            'branch_id' => $otherBranch->id,
            'status' => true,
        ]);
        $assignedCustomer = Customer::create(['name' => 'Assigned Customer', 'phone' => '555-3001']);
        $otherCustomer = Customer::create(['name' => 'Other Customer', 'phone' => '555-3002']);
        $assignedSupplier = Supplier::create(['name' => 'Assigned Supplier', 'phone' => '555-4001']);
        $otherSupplier = Supplier::create(['name' => 'Other Supplier', 'phone' => '555-4002']);

        Sale::create([
            'invoice_no' => 'BRANCH-SALE-A',
            'customer_id' => $assignedCustomer->id,
            'branch_id' => $assignedBranch->id,
            'sale_date' => today()->toDateString(),
            'sub_total' => 30,
            'grand_total' => 30,
            'paid_amount' => 10,
            'due_amount' => 20,
        ]);
        Sale::create([
            'invoice_no' => 'BRANCH-SALE-B',
            'customer_id' => $otherCustomer->id,
            'branch_id' => $otherBranch->id,
            'sale_date' => today()->toDateString(),
            'sub_total' => 500,
            'grand_total' => 500,
            'paid_amount' => 500,
        ]);
        Purchase::create([
            'branch_id' => $assignedBranch->id,
            'supplier_id' => $assignedSupplier->id,
            'invoice_no' => 'BRANCH-PUR-A',
            'date' => today()->toDateString(),
            'grand_total' => 12,
            'paid_amount' => 2,
            'due_amount' => 10,
        ]);
        Purchase::create([
            'branch_id' => $otherBranch->id,
            'supplier_id' => $otherSupplier->id,
            'invoice_no' => 'BRANCH-PUR-B',
            'date' => today()->toDateString(),
            'grand_total' => 90,
            'paid_amount' => 90,
        ]);
        Payroll::create([
            'employee_id' => $employee->id,
            'salary_month' => 'November',
            'salary_year' => 2026,
            'net_salary' => 7,
        ]);
        Payroll::create([
            'employee_id' => $otherEmployee->id,
            'salary_month' => 'November',
            'salary_year' => 2026,
            'net_salary' => 70,
        ]);
        Transaction::create([
            'account_id' => $assignedAccount->id,
            'type' => 'credit',
            'amount' => 5,
            'balance' => 105,
            'date' => today()->toDateString(),
        ]);
        Transaction::create([
            'account_id' => $otherAccount->id,
            'type' => 'credit',
            'amount' => 50,
            'balance' => 950,
            'date' => today()->toDateString(),
        ]);

        $data = $this->dashboard($user)->assertOk()->json('data');

        $this->assertEquals(1, $data['total_customers']);
        $this->assertEquals(1, $data['total_suppliers']);
        $this->assertEquals(30.0, $data['total_sale']);
        $this->assertEquals(12.0, $data['total_purchase']);
        $this->assertEquals(7.0, $data['total_salary']);
        $this->assertEquals(100.0, $data['total_account_balance']);
        $this->assertEquals(5.0, $data['total_cash_in']);
        $this->assertEquals(20.0, $data['receivable_customer_due']);
        $this->assertEquals(10.0, $data['supplier_due']);
        $this->assertCount(1, $data['recent_sales']);
        $this->assertSame('BRANCH-SALE-A', $data['recent_sales'][0]['reference']);
    }

    private function dashboardUser(bool $canViewAllBranches = false): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('view_dashboard', 'web'));
        if ($canViewAllBranches) {
            $user->givePermissionTo(Permission::findOrCreate('view_branches', 'web'));
        }

        return $user;
    }

    private function dashboard(User $user, string $filter = 'this_month')
    {
        $token = $user->createToken('dashboard-test')->plainTextToken;

        return $this->withToken($token)->getJson('/api/v1/dashboard?type='.$filter);
    }

    private function account(string $name, string $accountNumber, float $balance): Account
    {
        return Account::create([
            'name' => $name,
            'account_number' => $accountNumber,
            'current_balance' => $balance,
            'type' => 'cash',
            'is_active' => true,
        ]);
    }
}

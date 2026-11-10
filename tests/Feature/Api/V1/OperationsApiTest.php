<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Sale;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OperationsApiTest extends TestCase
{
    use RefreshDatabase;

    private function allow(User $user, string ...$names): string
    {
        foreach ($names as $name) $user->givePermissionTo(Permission::findOrCreate($name, 'web'));
        return $user->createToken('mobile')->plainTextToken;
    }

    private function account(): Account
    {
        return Account::create(['name' => 'Ops cash '.uniqid(), 'account_number' => null, 'initial_balance' => 0, 'current_balance' => 0, 'type' => 'cash', 'is_active' => true, 'is_default' => false]);
    }

    private function branch(User $manager): Branch
    {
        return Branch::create(['name' => 'Branch '.uniqid(), 'manager_id' => $manager->id, 'account_id' => $this->account()->id, 'created_by' => $manager->id, 'status' => true]);
    }

    public function test_inventory_and_stock_history_are_limited_to_assigned_branch(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $other = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_inventory');
        $ownBranch = $this->branch($user);
        $otherBranch = $this->branch($other);
        $product = Product::create(['product_title' => 'Inventory item', 'product_code' => 'INV-'.uniqid(), 'slug' => 'inventory-'.uniqid(), 'product_type' => 1, 'product_cost' => 4, 'selling_price' => 10, 'quantity' => 13, 'is_active' => true, 'created_by' => $user->id]);
        ProductBranch::create(['branch_id' => $ownBranch->id, 'product_id' => $product->id, 'qty' => 3, 'sell_price' => 10]);
        ProductBranch::create(['branch_id' => $otherBranch->id, 'product_id' => $product->id, 'qty' => 10, 'sell_price' => 10]);

        $this->withToken($token)->getJson('/api/v1/inventory')
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.inventory.0.branch_id', $ownBranch->id);
        $this->withToken($token)->getJson('/api/v1/inventory?branch_id='.$otherBranch->id)->assertForbidden();
    }

    public function test_quotation_crud_uses_server_totals_and_exposes_web_shared_record(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_quotations', 'create_quotations', 'update_quotations', 'delete_quotations', 'create_quick_sale', 'view_sale_list', 'update_sale_list');
        $branch = $this->branch($user);
        $account = $branch->account_id;
        $product = Product::create(['product_title' => 'Quotation item', 'product_code' => 'QUO-'.uniqid(), 'slug' => 'quotation-'.uniqid(), 'product_type' => 1, 'product_cost' => 4, 'selling_price' => 20, 'quantity' => 10, 'is_active' => true, 'created_by' => $user->id]);
        ProductBranch::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'qty' => 10, 'sell_price' => 20]);

        $response = $this->withToken($token)->postJson('/api/v1/quotations', [
            'date' => today()->toDateString(), 'branch_id' => $branch->id, 'account_id' => $account,
            'order_tax_rate' => 10, 'order_discount' => 2, 'shipping_cost' => 3,
            'items' => [['product_id' => $product->id, 'qty' => 2, 'unit_price' => 50, 'discount' => 5, 'tax_rate' => 5]],
        ])->assertCreated()->assertJsonPath('data.quotation.grand_total', 110.73);

        $id = $response->json('data.quotation.id');
        $this->assertDatabaseHas('quotations', ['id' => $id, 'branch_id' => $branch->id, 'grand_total' => 110.73]);
        $this->assertDatabaseHas('quotation_products', ['quotation_id' => $id, 'product_id' => $product->id, 'total' => 99.75]);
        $this->withToken($token)->getJson('/api/v1/quotations/'.$id)->assertOk()->assertJsonPath('data.quotation.items.0.product', 'Quotation item');
        $this->withToken($token)->getJson('/api/v1/quotations/'.$id.'/convert?type=sale')->assertOk()
            ->assertJsonPath('data.quotation_id', $id)
            ->assertJsonPath('data.grand_total', 110.73)
            ->assertJsonPath('data.shipping_cost', 3);
        $saleResponse = $this->withToken($token)->postJson('/api/v1/pos/store', [
            'quotation_id' => $id, 'branch_id' => $branch->id,
            'items' => [['id' => $product->id, 'qty' => 2]],
            'payments' => [['method' => (string) $account, 'amount' => 10]],
            'sale_date' => today()->toDateString(), 'discount_type' => 'fixed', 'discount_value' => 0,
        ])->assertCreated()->assertJsonPath('data.grand_total', 110.73)->assertJsonPath('data.due_amount', 100.73);
        $saleId = $saleResponse->json('data.sale_id');
        $this->assertDatabaseHas('sales', [
            'id' => $saleId, 'sub_total' => 100, 'discount_amount' => 7,
            'tax_amount' => 14.73, 'shipping_cost' => 3, 'grand_total' => 110.73,
        ]);
        $this->assertDatabaseHas('product_sales', [
            'sale_id' => $saleId, 'product_id' => $product->id,
            'unit_price' => 50, 'discount_amount' => 5, 'tax_amount' => 4.75,
        ]);
        $this->assertSame(8.0, (float) ProductBranch::where('product_id', $product->id)->value('qty'));
        $this->assertDatabaseHas('quotations', ['id' => $id, 'status' => 'sale_generated']);
        $this->withToken($token)->getJson('/api/v1/sales/'.$saleId)->assertOk()
            ->assertJsonPath('data.sale.shipping_cost', 3)
            ->assertJsonPath('data.sale.grand_total', 110.73);
        $this->withToken($token)->putJson('/api/v1/sales/'.$saleId, [
            'items' => [['id' => $product->id, 'qty' => 2]],
            'discount_type' => 'fixed', 'discount_value' => 0, 'tax_percent' => 0,
            'sale_date' => today()->toDateString(),
        ])->assertOk()->assertJsonPath('data.sale.shipping_cost', 3)
            ->assertJsonPath('data.sale.grand_total', 43);
        $this->withToken($token)->postJson('/api/v1/pos/store', [
            'quotation_id' => $id, 'branch_id' => $branch->id,
            'items' => [['id' => $product->id, 'qty' => 2]], 'payments' => [],
            'sale_date' => today()->toDateString(), 'discount_type' => 'fixed', 'discount_value' => 0,
        ])->assertConflict();
    }

    public function test_customer_group_list_and_unauthorized_branches_are_safe(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_customer_groups', 'create_customer_groups');
        CustomerGroup::create(['name' => 'Mobile group', 'prefix_code' => 'MG', 'discount' => 5, 'type' => 'percentage', 'status' => true]);
        $this->withToken($token)->getJson('/api/v1/customer-groups')->assertOk()->assertJsonPath('data.customer_groups.0.customers_count', 0);
        $this->withToken($token)->getJson('/api/v1/transfers')->assertForbidden();
    }

    public function test_account_options_and_statements_are_limited_to_accessible_branch_accounts(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $other = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_accounts', 'create_quick_sale');
        $ownBranch = $this->branch($user);
        $otherBranch = $this->branch($other);
        $ownAccount = Account::findOrFail($ownBranch->account_id);
        $otherAccount = Account::findOrFail($otherBranch->account_id);
        Transaction::create(['account_id' => $ownAccount->id, 'type' => 'credit', 'amount' => 25, 'balance' => 25, 'reason' => 'own', 'date' => today(), 'created_by' => $user->id]);
        Transaction::create(['account_id' => $otherAccount->id, 'type' => 'credit', 'amount' => 99, 'balance' => 99, 'reason' => 'other', 'date' => today(), 'created_by' => $other->id]);

        $this->withToken($token)->getJson('/api/v1/operations/options')
            ->assertOk()
            ->assertJsonPath('data.accounts.0.id', $ownAccount->id);
        $this->withToken($token)->getJson('/api/v1/accounts/statement?account_id='.$ownAccount->id)
            ->assertOk()->assertJsonPath('data.transactions.0.amount', 25);
        $this->withToken($token)->getJson('/api/v1/accounts/statement?account_id='.$otherAccount->id)
            ->assertNotFound();
    }

    public function test_stock_adjustment_updates_branch_global_stock_and_history(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_inventory', 'update_inventory');
        $branch = $this->branch($user);
        $product = Product::create(['product_title' => 'Adjustable item', 'product_code' => 'ADJ-'.uniqid(), 'slug' => 'adjustable-'.uniqid(), 'product_type' => 1, 'product_cost' => 4, 'selling_price' => 10, 'quantity' => 8, 'is_active' => true, 'created_by' => $user->id]);
        $stock = ProductBranch::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'qty' => 8, 'sell_price' => 10]);

        $this->withToken($token)->postJson('/api/v1/inventory/adjust', [
            'product_branch_id' => $stock->id, 'type' => 'addition', 'amount' => 3, 'reason' => 'Local QA adjustment',
        ])->assertOk()->assertJsonPath('data.inventory.qty', 11)->assertJsonPath('data.history.note', 'Local QA adjustment');

        $this->assertSame(11.0, (float) $stock->fresh()->qty);
        $this->assertSame(11.0, (float) $product->fresh()->quantity);
        $this->assertDatabaseHas('stock_histories', ['product_id' => $product->id, 'branch_id' => $branch->id, 'qty' => 3, 'type' => 'adjustment']);

        $this->withToken($token)->postJson('/api/v1/inventory/adjust', [
            'product_branch_id' => $stock->id, 'type' => 'subtraction', 'amount' => 20, 'reason' => 'Must reject negative stock',
        ])->assertUnprocessable();
        $this->assertSame(11.0, (float) $stock->fresh()->qty);
    }

    public function test_transfer_lifecycle_moves_stock_once_between_accessible_branches(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole(Role::findOrCreate('Super-Admin', 'web'));
        $token = $this->allow($user, 'view_transfer', 'create_transfer', 'update_transfer');
        $from = $this->branch($user);
        $to = $this->branch($user);
        $product = Product::create(['product_title' => 'Transfer item', 'product_code' => 'TR-'.uniqid(), 'slug' => 'transfer-'.uniqid(), 'product_type' => 1, 'product_cost' => 5, 'selling_price' => 12, 'quantity' => 10, 'is_active' => true, 'created_by' => $user->id]);
        $sourceStock = ProductBranch::create(['branch_id' => $from->id, 'product_id' => $product->id, 'qty' => 10, 'sell_price' => 12]);
        $destinationStock = ProductBranch::create(['branch_id' => $to->id, 'product_id' => $product->id, 'qty' => 0, 'sell_price' => 12]);

        $created = $this->withToken($token)->postJson('/api/v1/transfers', [
            'from_branch_id' => $from->id, 'to_branch_id' => $to->id, 'status' => 'request',
            'items' => [['product_id' => $product->id, 'qty' => 4, 'unit_cost' => 5]],
        ])->assertCreated()->assertJsonPath('data.transfer.status', 'request');
        $transferId = $created->json('data.transfer.id');
        $this->assertSame(10.0, (float) $sourceStock->fresh()->qty);
        $this->assertSame(0.0, (float) $destinationStock->fresh()->qty);

        $this->withToken($token)->patchJson('/api/v1/transfers/'.$transferId.'/status', ['status' => 'sent'])
            ->assertOk()->assertJsonPath('data.transfer.status', 'sent');
        $this->assertSame(6.0, (float) $sourceStock->fresh()->qty);
        $this->assertSame(0.0, (float) $destinationStock->fresh()->qty);

        $this->withToken($token)->patchJson('/api/v1/transfers/'.$transferId.'/status', ['status' => 'completed'])
            ->assertOk()->assertJsonPath('data.transfer.status', 'completed');
        $this->assertSame(6.0, (float) $sourceStock->fresh()->qty);
        $this->assertSame(4.0, (float) $destinationStock->fresh()->qty);
        $this->assertSame(10.0, (float) $product->fresh()->quantity);
        $this->assertSame(2, StockHistory::where('reference_no', Transfer::findOrFail($transferId)->reference_no)->count());
    }

    public function test_purchase_payment_and_purchase_return_approval_update_shared_balances_and_stock(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_purchases', 'create_purchases', 'update_purchases', 'view_purchase_return', 'create_purchase_return', 'update_purchase_return');
        $branch = $this->branch($user);
        $account = Account::findOrFail($branch->account_id);
        $account->update(['current_balance' => 1000]);
        $supplier = Supplier::create(['name' => 'Local supplier', 'phone' => 'SUP-'.uniqid(), 'balance' => 0, 'previous_due' => 0, 'created_by' => $user->id, 'is_active' => true]);
        $product = Product::create(['product_title' => 'Purchased item', 'product_code' => 'PUR-'.uniqid(), 'slug' => 'purchased-'.uniqid(), 'product_type' => 1, 'product_cost' => 10, 'selling_price' => 20, 'quantity' => 0, 'is_active' => true, 'created_by' => $user->id]);
        $stock = ProductBranch::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'qty' => 0, 'sell_price' => 20]);

        $created = $this->withToken($token)->postJson('/api/v1/purchases', [
            'branch_id' => $branch->id, 'supplier_id' => $supplier->id, 'date' => today()->toDateString(),
            'status' => 'received', 'paid_amount' => 0,
            'items' => [['product_id' => $product->id, 'qty' => 5, 'unit_cost' => 10]],
        ])->assertCreated()->assertJsonPath('data.purchase.grand_total', 50)->assertJsonPath('data.purchase.due_amount', 50);
        $purchaseId = $created->json('data.purchase.id');
        $this->assertSame(5.0, (float) $stock->fresh()->qty);
        $this->assertSame(50.0, (float) $supplier->fresh()->balance);

        $this->withToken($token)->postJson('/api/v1/purchases/'.$purchaseId.'/payments', [
            'account_id' => $account->id, 'amount' => 20, 'date' => today()->toDateString(),
        ])->assertOk()->assertJsonPath('data.purchase.paid_amount', 20)->assertJsonPath('data.purchase.due_amount', 30);
        $this->assertSame(30.0, (float) $supplier->fresh()->balance);
        $this->assertSame(980.0, (float) $account->fresh()->current_balance);

        $returned = $this->withToken($token)->postJson('/api/v1/purchase-returns', [
            'purchase_id' => $purchaseId,
            'items' => [['product_id' => $product->id, 'qty' => 2]],
        ])->assertCreated()->assertJsonPath('data.purchase_return.status', 'pending');
        $returnId = $returned->json('data.purchase_return.id');
        $this->withToken($token)->postJson('/api/v1/purchase-returns/'.$returnId.'/approve')
            ->assertOk()->assertJsonPath('data.purchase_return.status', 'approved');

        $this->assertSame(3.0, (float) $stock->fresh()->qty);
        $this->assertSame(3.0, (float) $product->fresh()->quantity);
        $this->assertSame(10.0, (float) Purchase::findOrFail($purchaseId)->due_amount);
        $this->assertSame(10.0, (float) $supplier->fresh()->balance);
    }

    public function test_sale_return_approval_restores_stock_and_caps_return_quantity(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->allow($user, 'view_sale_return', 'create_sale_return', 'update_sale_return');
        $branch = $this->branch($user);
        $product = Product::create(['product_title' => 'Returned item', 'product_code' => 'RET-'.uniqid(), 'slug' => 'returned-'.uniqid(), 'product_type' => 1, 'product_cost' => 5, 'selling_price' => 20, 'quantity' => 3, 'is_active' => true, 'created_by' => $user->id]);
        $stock = ProductBranch::create(['branch_id' => $branch->id, 'product_id' => $product->id, 'qty' => 3, 'sell_price' => 20]);
        $sale = Sale::create(['invoice_no' => 'RET-SALE-'.uniqid(), 'branch_id' => $branch->id, 'sale_date' => today()->toDateString(), 'sub_total' => 100, 'discount_type' => 'fixed', 'discount_value' => 0, 'discount_amount' => 0, 'group_discount_amount' => 0, 'coupon_discount_amount' => 0, 'reward_points_earned' => 0, 'reward_points_used' => 0, 'point_discount_amount' => 0, 'tax_percent' => 0, 'tax_amount' => 0, 'grand_total' => 100, 'paid_amount' => 0, 'due_amount' => 100, 'payment_status' => 'due', 'delivery_status' => 'pending']);
        ProductSale::create(['sale_id' => $sale->id, 'product_id' => $product->id, 'qty' => 2, 'unit_price' => 50, 'purchase_unit_cost' => 5, 'total_price' => 100]);

        $this->withToken($token)->postJson('/api/v1/sale-returns', [
            'sale_id' => $sale->id, 'items' => [['product_id' => $product->id, 'qty' => 3]],
        ])->assertUnprocessable();

        $created = $this->withToken($token)->postJson('/api/v1/sale-returns', [
            'sale_id' => $sale->id, 'items' => [['product_id' => $product->id, 'qty' => 1]],
        ])->assertCreated()->assertJsonPath('data.sale_return.grand_total', 50)->assertJsonPath('data.sale_return.status', 'pending');
        $returnId = $created->json('data.sale_return.id');
        $this->withToken($token)->postJson('/api/v1/sale-returns/'.$returnId.'/approve')
            ->assertOk()->assertJsonPath('data.sale_return.status', 'approved');

        $this->assertSame(4.0, (float) $stock->fresh()->qty);
        $this->assertSame(4.0, (float) $product->fresh()->quantity);
        $this->assertSame(50.0, (float) $sale->fresh()->due_amount);
    }

    public function test_people_account_and_expense_changes_round_trip_without_losing_default_account(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $this->allow(
            $user,
            'view_customer_groups', 'create_customer_groups', 'update_customer_groups',
            'view_customers', 'create_customers', 'update_customers',
            'view_suppliers', 'create_suppliers', 'update_suppliers',
            'view_accounts', 'create_accounts', 'update_accounts',
            'view_expenses', 'create_expenses', 'delete_expenses',
            'view_expenses_categories', 'create_expenses_categories', 'update_expenses_categories'
        );
        $branch = $this->branch($user);
        $account = Account::findOrFail($branch->account_id);
        $account->update(['current_balance' => 1000]);

        $group = $this->withToken($token)->postJson('/api/v1/customer-groups', [
            'name' => 'Local group '.uniqid(), 'prefix_code' => 'LG', 'discount' => 5, 'type' => 'percentage',
        ])->assertCreated()->json('data.customer_group.id');
        $customer = $this->withToken($token)->postJson('/api/v1/customers', [
            'name' => 'Local customer', 'phone' => 'C-'.uniqid(), 'customer_group_id' => $group,
        ])->assertCreated()->assertJsonPath('data.customer.reward_points', 0)->json('data.customer.id');
        $this->withToken($token)->putJson('/api/v1/customers/'.$customer, [
            'name' => 'Updated local customer', 'phone' => 'C-'.uniqid(), 'customer_group_id' => $group,
        ])->assertOk()->assertJsonPath('data.customer.name', 'Updated local customer');
        $this->withToken($token)->getJson('/api/v1/customers?search=Updated%20local')
            ->assertOk()->assertJsonPath('data.total', 1);

        $supplier = $this->withToken($token)->postJson('/api/v1/suppliers', [
            'name' => 'Local supplier', 'phone' => 'S-'.uniqid(), 'company_name' => 'Local Co',
        ])->assertCreated()->json('data.supplier.id');
        $this->withToken($token)->putJson('/api/v1/suppliers/'.$supplier, [
            'name' => 'Updated local supplier', 'phone' => 'S-'.uniqid(), 'company_name' => 'Local Co',
        ])->assertOk()->assertJsonPath('data.supplier.name', 'Updated local supplier');

        Account::setAsDefault($account->id);
        $this->withToken($token)->putJson('/api/v1/accounts/'.$account->id, [
            'name' => $account->name, 'account_number' => $account->account_number, 'type' => 'cash',
            'initial_balance' => 0, 'note' => 'Updated without changing default',
        ])->assertOk()->assertJsonPath('data.account.is_default', true);
        $this->assertDatabaseHas('accounts', ['id' => $account->id, 'is_default' => 1]);

        $category = $this->withToken($token)->postJson('/api/v1/expense-categories', [
            'name' => 'Local category '.uniqid(), 'code' => 'LOCAL',
        ])->assertCreated()->json('data.category.id');
        $expenseId = $this->withToken($token)->postJson('/api/v1/expenses', [
            'date' => today()->toDateString(), 'category_id' => $category, 'branch_id' => $branch->id,
            'account_id' => $account->id, 'amount' => 25, 'note' => 'Local API expense',
        ])->assertCreated()->json('data.expense.id');
        $this->assertSame(975.0, (float) $account->fresh()->current_balance);
        $this->assertDatabaseHas('expenses', ['id' => $expenseId, 'amount' => 25, 'branch_id' => $branch->id]);

        $this->withToken($token)->deleteJson('/api/v1/expenses/'.$expenseId)->assertOk();
        $this->assertSame(1000.0, (float) $account->fresh()->current_balance);
        $this->assertDatabaseMissing('expenses', ['id' => $expenseId]);
        $this->withToken($token)->getJson('/api/v1/expense-categories?search=Local')
            ->assertOk()->assertJsonPath('data.total', 1);
    }
}

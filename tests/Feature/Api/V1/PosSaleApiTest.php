<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductImei;
use App\Models\ProductSale;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PosSaleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_server_branch_price_and_updates_shared_stock_and_ledger(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $permission = Permission::findOrCreate('create_quick_sale', 'web');
        $user->givePermissionTo($permission);
        $account = Account::create([
            'name' => 'Register cash',
            'account_number' => 'POS-100',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => true,
        ]);
        $branch = Branch::create([
            'name' => 'Main shop',
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $product = Product::create([
            'product_title' => 'Server priced item',
            'product_code' => 'SERVER-PRICE-1',
            'slug' => 'server-priced-item',
            'product_type' => 1,
            'product_cost' => 4,
            'selling_price' => 10,
            'quantity' => 10,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        $stock = ProductBranch::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'qty' => 10,
            'sell_price' => 15,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $branch->id,
            'product_ids' => [$product->id],
            'qty' => [2],
            'price' => [0.01],
            'paid_amount' => 31.5,
            'payment_method' => $account->id,
            'type' => 'Sales',
        ])->assertCreated()
            ->assertJsonPath('data.sale_id', fn ($value) => is_int($value));

        $sale = Sale::query()->firstOrFail();
        $line = ProductSale::query()->where('sale_id', $sale->id)->firstOrFail();
        $this->assertSame($branch->id, (int) $sale->branch_id);
        $this->assertSame(1.5, (float) $sale->tax_amount);
        $this->assertSame(31.5, (float) $sale->grand_total);
        $this->assertSame(31.5, (float) $sale->paid_amount);
        $this->assertSame(15.0, (float) $line->unit_price);
        $this->assertSame(8.0, (float) $stock->fresh()->qty);
        $this->assertSame(31.5, (float) $account->fresh()->current_balance);

        $user->givePermissionTo(Permission::findOrCreate('view_sale_list', 'web'));
        Auth::forgetGuards();
        $this->withToken($token)->getJson('/api/v1/sales?start_date='.today()->format('d-m-Y').'&end_date='.today()->format('d-m-Y').'&warehouse_id='.$branch->id)
            ->assertOk()
            ->assertJsonPath('data.sales.0.id', $sale->id);
    }

    public function test_employee_sale_uses_assigned_branch_when_client_requests_another(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $permission = Permission::findOrCreate('create_quick_sale', 'web');
        $user->givePermissionTo($permission);
        $account = Account::create([
            'name' => 'Assigned cash',
            'account_number' => 'POS-201',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => true,
        ]);
        $assignedBranch = Branch::create([
            'name' => 'Assigned branch',
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $otherBranch = Branch::create([
            'name' => 'Unassigned branch',
            'manager_id' => User::factory()->create()->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $product = Product::create([
            'product_title' => 'Branch restricted item',
            'product_code' => 'BRANCH-LOCK-1',
            'slug' => 'branch-restricted-item',
            'product_type' => 1,
            'product_cost' => 4,
            'selling_price' => 10,
            'quantity' => 10,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        ProductBranch::create([
            'branch_id' => $assignedBranch->id,
            'product_id' => $product->id,
            'qty' => 2,
            'sell_price' => 10,
        ]);
        ProductBranch::create([
            'branch_id' => $otherBranch->id,
            'product_id' => $product->id,
            'qty' => 10,
            'sell_price' => 15,
        ]);
        Employee::create([
            'name' => $user->name,
            'email' => 'employee-'.uniqid().'@example.test',
            'user_id' => $user->id,
            'branch_id' => $assignedBranch->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $otherBranch->id,
            'product_ids' => [$product->id],
            'qty' => [1],
            'payment_method' => $account->id,
            'type' => 'Sales',
        ])->assertCreated();

        $sale = Sale::query()->firstOrFail();
        $this->assertSame($assignedBranch->id, (int) $sale->branch_id);
    }

    public function test_drafts_are_private_to_the_creator_and_can_be_deleted(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('create_quick_sale', 'web'));
        $user->givePermissionTo(Permission::findOrCreate('view_quick_sale', 'web'));
        $account = Account::create([
            'name' => 'Draft cash',
            'account_number' => 'POS-301',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => true,
        ]);
        $branch = Branch::create([
            'name' => 'Draft branch',
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $product = Product::create([
            'product_title' => 'Draft item',
            'product_code' => 'DRAFT-ITEM',
            'slug' => 'draft-item',
            'product_type' => 1,
            'product_cost' => 4,
            'selling_price' => 10,
            'quantity' => 10,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        ProductBranch::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'qty' => 8,
            'sell_price' => 12,
        ]);
        $coupon = Coupon::create([
            'code' => 'DRAFT2',
            'type' => 'fixed',
            'amount' => 2,
            'min_purchase' => 0,
            'max_discount' => null,
            'start_date' => today()->subDay()->toDateString(),
            'end_date' => today()->addDay()->toDateString(),
            'usage_limit' => null,
            'used_count' => 0,
            'status' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $draftId = $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $branch->id,
            'product_ids' => [$product->id],
            'qty' => [2],
            'coupon_id' => $coupon->id,
            'type' => 'Draft',
        ])->assertCreated()
            ->assertJsonPath('data.draft_id', fn ($value) => is_int($value))
            ->json('data.draft_id');

        $this->withToken($token)->getJson('/api/v1/drafts')->assertOk()
            ->assertJsonPath('data.drafts.0.branch_id', $branch->id)
            ->assertJsonPath('data.drafts.0.products.0.price', 12)
            ->assertJsonPath('data.drafts.0.coupon_code', 'DRAFT2')
            ->assertJsonPath('data.drafts.0.coupon_type', 'fixed')
            ->assertJsonPath('data.drafts.0.coupon_amount', 2);

        $otherUser = User::factory()->create(['is_active' => true]);
        $otherUser->givePermissionTo(Permission::findOrCreate('view_quick_sale', 'web'));
        $otherToken = $otherUser->createToken('mobile')->plainTextToken;
        Auth::forgetGuards();
        $this->withToken($otherToken)->getJson('/api/v1/drafts')->assertOk()
            ->assertJsonCount(0, 'data.drafts');
        Auth::forgetGuards();
        $this->withToken($otherToken)->deleteJson('/api/v1/draft/delete/'.$draftId)->assertNotFound();

        Auth::forgetGuards();
        $this->withToken($token)->deleteJson('/api/v1/draft/delete/'.$draftId)->assertOk();
        $this->assertDatabaseMissing('mobile_pos_drafts', ['id' => $draftId]);
    }

    public function test_checkout_applies_fixed_customer_discount_coupon_and_configured_tax(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('create_quick_sale', 'web'));
        $account = Account::create([
            'name' => 'Discount cash',
            'account_number' => 'POS-401',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => true,
        ]);
        $branch = Branch::create([
            'name' => 'Discount branch',
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $product = Product::create([
            'product_title' => 'Discount item',
            'product_code' => 'DISCOUNT-1',
            'slug' => 'discount-item',
            'product_type' => 1,
            'product_cost' => 4,
            'selling_price' => 15,
            'quantity' => 10,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        ProductBranch::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'qty' => 10,
            'sell_price' => 15,
        ]);
        $group = CustomerGroup::create([
            'name' => 'Fixed discount',
            'prefix_code' => 'FIX',
            'discount' => 3,
            'type' => 'fixed',
            'status' => true,
        ]);
        $customer = Customer::create([
            'name' => 'Test customer',
            'phone' => '555-0401',
            'customer_group_id' => $group->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $coupon = Coupon::create([
            'code' => 'TENPERCENT',
            'type' => 'percent',
            'amount' => 10,
            'min_purchase' => 0,
            'max_discount' => null,
            'start_date' => today()->subDay()->toDateString(),
            'end_date' => today()->addDay()->toDateString(),
            'usage_limit' => null,
            'used_count' => 0,
            'status' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'product_ids' => [$product->id],
            'qty' => [2],
            'coupon_id' => $coupon->id,
            'payment_method' => $account->id,
            'type' => 'Sales',
        ])->assertCreated();

        $sale = Sale::query()->firstOrFail();
        $this->assertSame(3.0, (float) $sale->group_discount_amount);
        $this->assertSame(3.0, (float) $sale->coupon_discount_amount);
        $this->assertSame(1.2, (float) $sale->tax_amount);
        $this->assertSame(25.2, (float) $sale->grand_total);
        $this->assertSame(25.2, (float) $sale->paid_amount);
        $response->assertJsonPath('data.grand_total', 25.2);
    }

    public function test_imei_checkout_and_sale_edit_only_assign_available_branch_units(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('create_quick_sale', 'web'));
        $user->givePermissionTo(Permission::findOrCreate('view_sale_list', 'web'));
        $user->givePermissionTo(Permission::findOrCreate('update_sale_list', 'web'));
        $account = Account::create([
            'name' => 'IMEI cash',
            'account_number' => 'IMEI-POS-1',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => true,
        ]);
        $branch = Branch::create([
            'name' => 'IMEI branch',
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $product = Product::create([
            'product_title' => 'Tracked phone',
            'product_code' => 'IMEI-PHONE-1',
            'slug' => 'imei-phone-1',
            'has_imei' => true,
            'product_type' => 1,
            'product_cost' => 5,
            'selling_price' => 10,
            'quantity' => 2,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        ProductBranch::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'qty' => 2,
            'sell_price' => 10,
        ]);
        $firstImei = ProductImei::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'imei_number' => 'LOCAL-IMEI-0001',
            'status' => 'in_stock',
            'is_sold' => false,
        ]);
        $secondImei = ProductImei::create([
            'product_id' => $product->id,
            'branch_id' => $branch->id,
            'imei_number' => 'LOCAL-IMEI-0002',
            'status' => 'in_stock',
            'is_sold' => false,
        ]);
        $token = $user->createToken('mobile-imei')->plainTextToken;

        $created = $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $branch->id,
            'items' => [[
                'id' => $product->id,
                'qty' => 1,
                'imei_numbers' => [$firstImei->imei_number],
            ]],
            'payments' => [['method' => (string) $account->id, 'amount' => 10]],
            'type' => 'Sales',
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'tax_percent' => 0,
        ])->assertCreated();
        $saleId = $created->json('data.sale_id');

        $this->assertDatabaseHas('product_imeis', [
            'id' => $firstImei->id,
            'is_sold' => 1,
            'status' => 'sold',
            'sale_id' => $saleId,
        ]);
        $this->assertDatabaseHas('product_imeis', [
            'id' => $secondImei->id,
            'is_sold' => 0,
            'status' => 'in_stock',
            'sale_id' => null,
        ]);

        $this->withToken($token)->putJson('/api/v1/sales/'.$saleId, [
            'items' => [[
                'id' => $product->id,
                'qty' => 1,
                'imei_numbers' => [$secondImei->imei_number],
            ]],
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'tax_percent' => 0,
        ])->assertOk();

        $this->assertDatabaseHas('product_imeis', [
            'id' => $firstImei->id,
            'is_sold' => 0,
            'status' => 'in_stock',
            'sale_id' => null,
        ]);
        $this->assertDatabaseHas('product_imeis', [
            'id' => $secondImei->id,
            'is_sold' => 1,
            'status' => 'sold',
            'sale_id' => $saleId,
        ]);

        $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $branch->id,
            'items' => [[
                'id' => $product->id,
                'qty' => 1,
                'imei_numbers' => [$secondImei->imei_number],
            ]],
            'payments' => [['method' => (string) $account->id, 'amount' => 10]],
            'type' => 'Sales',
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'tax_percent' => 0,
        ])->assertUnprocessable()->assertJsonFragment(['message' => 'IMEI LOCAL-IMEI-0002 is no longer available.']);

        $this->withToken($token)->postJson('/api/v1/pos/store', [
            'branch_id' => $branch->id,
            'items' => [['id' => $product->id, 'qty' => 1]],
            'payments' => [['method' => (string) $account->id, 'amount' => 10]],
            'type' => 'Sales',
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'tax_percent' => 0,
        ])->assertUnprocessable()->assertJsonFragment(['message' => 'Select exactly one available IMEI for each Tracked phone unit.']);

        $this->assertSame(1, Sale::query()->count());
    }
}

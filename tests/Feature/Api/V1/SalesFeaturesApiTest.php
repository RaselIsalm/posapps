<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Courier;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SalesFeaturesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_list_and_detail_stay_inside_the_employee_branch(): void
    {
        [$user, $branch] = $this->employeeWithBranch('sales-branch');
        $foreignUser = User::factory()->create(['is_active' => true]);
        $foreignBranch = $this->branch($foreignUser);
        $ownSale = $this->sale($branch, 'OWN-SALE-001');
        $foreignSale = $this->sale($foreignBranch, 'FOREIGN-SALE-001');
        $user->givePermissionTo(Permission::findOrCreate('view_sale_list', 'web'));
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/sales?per_page=100')
            ->assertOk()
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.sales.0.id', $ownSale->id);

        $this->withToken($token)->getJson('/api/v1/sales/'.$foreignSale->id)
            ->assertNotFound();
    }

    public function test_delivery_creation_and_status_updates_are_local_mock_operations(): void
    {
        Http::fake();
        [$user, $branch] = $this->employeeWithBranch('delivery-branch');
        foreach (['create_delivery', 'view_delivery', 'update_delivery'] as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }
        $sale = $this->sale($branch, 'DELIVERY-SALE-001');
        $courier = Courier::create([
            'name' => 'Mock courier',
            'code' => 'MOCK-'.uniqid(),
            'has_api' => true,
            'api_config' => ['token' => 'must-not-be-used'],
            'default_charge' => 25,
            'is_active' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/deliveries/sales')
            ->assertOk()
            ->assertJsonPath('data.sales.0.id', $sale->id);

        $deliveryId = $this->withToken($token)->postJson('/api/v1/deliveries', [
            'sale_id' => $sale->id,
            'courier_id' => $courier->id,
            'recipient_name' => 'Local test recipient',
            'recipient_phone' => '0000000000',
            'address' => 'Local test address',
            'cod_amount' => 100,
            'delivery_charge' => 25,
        ])->assertCreated()
            ->assertJsonPath('message', 'Local delivery record created. No courier service was contacted.')
            ->json('data.delivery.id');

        $this->withToken($token)->patchJson('/api/v1/deliveries/'.$deliveryId.'/status', [
            'status' => 'in_transit',
        ])->assertOk()->assertJsonPath('data.delivery.status', 'in_transit');

        $this->assertSame('in_transit', $sale->fresh()->delivery_status);
        $this->assertSame('local_mock', Delivery::findOrFail($deliveryId)->courier_response['mode']);
        Http::assertNothingSent();
    }

    private function employeeWithBranch(string $prefix): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $branch = $this->branch($user, $prefix);
        Employee::create([
            'name' => $user->name,
            'email' => $prefix.'-'.uniqid().'@example.test',
            'user_id' => $user->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'status' => true,
        ]);

        return [$user, $branch];
    }

    private function branch(User $manager, string $prefix = 'branch'): Branch
    {
        $account = Account::create([
            'name' => $prefix.' account',
            'account_number' => $prefix.'-'.uniqid(),
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => false,
        ]);

        return Branch::create([
            'name' => $prefix,
            'manager_id' => $manager->id,
            'account_id' => $account->id,
            'created_by' => $manager->id,
            'status' => true,
        ]);
    }

    private function sale(Branch $branch, string $invoice): Sale
    {
        return Sale::create([
            'invoice_no' => $invoice,
            'branch_id' => $branch->id,
            'sale_date' => today()->toDateString(),
            'sub_total' => 100,
            'discount_type' => 'fixed',
            'discount_value' => 0,
            'discount_amount' => 0,
            'group_discount_amount' => 0,
            'coupon_discount_amount' => 0,
            'reward_points_earned' => 0,
            'reward_points_used' => 0,
            'point_discount_amount' => 0,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'grand_total' => 100,
            'paid_amount' => 0,
            'due_amount' => 100,
            'payment_status' => 'due',
            'delivery_status' => 'pending',
        ]);
    }
}

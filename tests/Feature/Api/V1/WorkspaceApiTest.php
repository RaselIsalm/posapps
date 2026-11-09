<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class WorkspaceApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_account_search_cannot_escape_the_users_branch_scope(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->givePermissionTo(Permission::findOrCreate('view_accounts', 'web'));
        $otherManager = User::factory()->create(['is_active' => true]);

        $ownAccount = Account::create([
            'name' => 'Assigned cash account',
            'account_number' => 'OWN-ACCOUNT-001',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => false,
        ]);
        $foreignAccount = Account::create([
            'name' => 'Foreign cash account',
            'account_number' => 'FOREIGN-ACCOUNT-999',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => false,
        ]);

        Branch::create([
            'name' => 'Manager assigned branch',
            'manager_id' => $manager->id,
            'account_id' => $ownAccount->id,
            'created_by' => $manager->id,
            'status' => true,
        ]);
        Branch::create([
            'name' => 'Foreign branch',
            'manager_id' => $otherManager->id,
            'account_id' => $foreignAccount->id,
            'created_by' => $otherManager->id,
            'status' => true,
        ]);

        $token = $manager->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/accounts?search=FOREIGN-ACCOUNT-999')
            ->assertOk()
            ->assertJsonCount(0, 'data.accounts');

        $this->withToken($token)->getJson('/api/v1/accounts')
            ->assertOk()
            ->assertJsonCount(1, 'data.accounts')
            ->assertJsonPath('data.accounts.0.id', $ownAccount->id);
    }

    public function test_purchase_pages_have_a_stable_order_and_return_the_full_total(): void
    {
        $admin = User::factory()->create(['is_active' => true]);
        $admin->givePermissionTo(Permission::findOrCreate('view_purchases', 'web'));
        $admin->assignRole(Role::findOrCreate('Super-Admin', 'web'));

        foreach (['PUR-TEST-001', 'PUR-TEST-002', 'PUR-TEST-003'] as $invoice) {
            Purchase::create([
                'invoice_no' => $invoice,
                'date' => '2026-10-20',
                'grand_total' => 100,
                'paid_amount' => 0,
                'due_amount' => 100,
            ]);
        }

        $token = $admin->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/purchase?page=1&per_page=2')
            ->assertOk()
            ->assertJsonPath('data.total', 3)
            ->assertJsonPath('data.current_page', 1)
            ->assertJsonPath('data.last_page', 2)
            ->assertJsonPath('data.purchases.0.reference_no', 'PUR-TEST-003')
            ->assertJsonPath('data.purchases.1.reference_no', 'PUR-TEST-002');

        $this->withToken($token)->getJson('/api/v1/purchase?page=2&per_page=2')
            ->assertOk()
            ->assertJsonPath('data.total', 3)
            ->assertJsonPath('data.purchases.0.reference_no', 'PUR-TEST-001');
    }
}

<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_read_session_and_revoke_token(): void
    {
        $user = User::factory()->create([
            'username' => 'mobile-owner',
            'email' => 'owner@example.test',
            'password' => Hash::make('secret-password'),
            'is_active' => true,
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile-owner',
            'password' => 'secret-password',
        ])->assertOk()
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonStructure(['data' => ['access' => ['token'], 'user' => ['id', 'name', 'email', 'permissions', 'branches']]]);

        $token = $login->json('data.access.token');
        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk()
            ->assertJsonPath('data.user.email', 'owner@example.test');

        $this->withToken($token)->postJson('/api/v1/auth/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'one-pos-mobile',
        ]);
    }

    public function test_invalid_credentials_and_inactive_users_cannot_login(): void
    {
        User::factory()->create([
            'email' => 'inactive@example.test',
            'password' => Hash::make('secret-password'),
            'is_active' => false,
        ]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.test',
            'password' => 'secret-password',
        ])->assertUnauthorized();

        $this->postJson('/api/v1/auth/login', [
            'email' => 'inactive@example.test',
            'password' => 'incorrect',
        ])->assertUnauthorized();
    }

    public function test_expired_and_newly_inactive_sessions_are_rejected(): void
    {
        $expiredUser = User::factory()->create(['is_active' => true]);
        $expiredToken = $expiredUser->createToken('mobile', ['*'], now()->subMinute())->plainTextToken;
        $this->withToken($expiredToken)->getJson('/api/v1/auth/me')->assertUnauthorized();

        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('mobile')->plainTextToken;
        $user->forceFill(['is_active' => false])->save();

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertUnauthorized();
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id, 'name' => 'mobile']);
    }

    public function test_dashboard_requires_the_website_permission(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/dashboard')->assertForbidden();
    }

    public function test_profile_and_password_can_be_updated_through_the_api(): void
    {
        $user = User::factory()->create([
            'email' => 'profile@example.test',
            'password' => Hash::make('secret-password'),
            'is_active' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/v1/profile/update', [
            'name' => 'Updated Mobile User',
            'phone' => '555-0100',
            'designation' => 'Store Manager',
        ])->assertOk()
            ->assertJsonPath('data.user.name', 'Updated Mobile User')
            ->assertJsonPath('data.user.phone', '555-0100')
            ->assertJsonPath('data.user.designation', 'Store Manager');

        $this->withToken($token)->putJson('/api/v1/change/password', [
            'current_password' => 'secret-password',
            'password' => 'new-secret-password',
            'password_confirmation' => 'new-secret-password',
        ])->assertOk();

        $this->assertTrue(Hash::check('new-secret-password', $user->fresh()->password));
    }

    public function test_profile_photo_upload_is_saved_and_returned_as_an_absolute_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('users/old-avatar.png', 'old-image');
        $user = User::factory()->create(['is_active' => true, 'avatar' => 'old-avatar.png']);
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)->post('/api/v1/profile/update', [
            'name' => 'Photo User',
            'avatar' => UploadedFile::fake()->image('profile.png'),
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('data.user.name', 'Photo User');

        $photoUrl = $response->json('data.user.profile_photo');
        $this->assertStringStartsWith(rtrim((string) config('app.url'), '/').'/storage/users/', $photoUrl);
        $storedPath = 'users/'.basename(parse_url($photoUrl, PHP_URL_PATH));
        Storage::disk('public')->assertExists($storedPath);
        Storage::disk('public')->assertMissing('users/old-avatar.png');
        $this->assertSame(basename($storedPath), $user->fresh()->avatar);

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk()
            ->assertJsonPath('data.user.profile_photo', $photoUrl)
            ->assertJsonPath('data.user.designation', $user->designation);

        $this->withToken($token)->postJson('/api/v1/profile/update', [
            'remove_avatar' => true,
        ])->assertOk()->assertJsonPath('data.user.profile_photo', null);
        Storage::disk('public')->assertMissing($storedPath);
        $this->assertNull($user->fresh()->avatar);
    }

    public function test_website_profile_photo_is_immediately_shared_with_mobile_auth_response(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'name' => 'Website Profile User',
            'email' => 'website-profile@example.test',
            'is_active' => true,
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $this->actingAs($user)->put('/profile/update', [
            'name' => $user->name,
            'email' => $user->email,
            'phone' => '555-0134',
            'designation' => 'Shop Owner',
            'avatar' => UploadedFile::fake()->image('website-profile.png'),
        ])->assertRedirect(route('profile.edit'));

        $avatar = $user->fresh()->avatar;
        $this->assertNotEmpty($avatar);
        Storage::disk('public')->assertExists('users/'.$avatar);

        $this->withToken($token)->getJson('/api/v1/auth/me')->assertOk()
            ->assertJsonPath('data.user.name', $user->name)
            ->assertJsonPath('data.user.phone', '555-0134')
            ->assertJsonPath('data.user.designation', 'Shop Owner')
            ->assertJsonPath('data.user.profile_photo', url('/storage/users/'.$avatar));
    }

    public function test_employee_branch_assignment_cannot_be_overridden_by_the_app(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $account = Account::create([
            'name' => 'Branch cash',
            'account_number' => 'TEST-001',
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
            'name' => 'Other branch',
            'manager_id' => $user->id,
            'account_id' => $account->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        Employee::create([
            'name' => $user->name,
            'email' => 'employee-'.uniqid().'@example.test',
            'user_id' => $user->id,
            'branch_id' => $assignedBranch->id,
            'created_by' => $user->id,
            'status' => true,
        ]);
        $permission = Permission::findOrCreate('view_quick_sale', 'web');
        $user->givePermissionTo($permission);

        $product = Product::create([
            'product_title' => 'Assigned branch item',
            'product_code' => 'ASSIGNED-ITEM',
            'slug' => 'assigned-branch-item',
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
            'qty' => 3,
            'sell_price' => 15,
        ]);

        $otherProduct = Product::create([
            'product_title' => 'Other branch item',
            'product_code' => 'OTHER-ITEM',
            'slug' => 'other-branch-item',
            'product_type' => 1,
            'product_cost' => 4,
            'selling_price' => 20,
            'quantity' => 10,
            'is_active' => true,
            'created_by' => $user->id,
        ]);
        ProductBranch::create([
            'branch_id' => $otherBranch->id,
            'product_id' => $otherProduct->id,
            'qty' => 9,
            'sell_price' => 99,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/pos/branches')
            ->assertOk()
            ->assertJsonPath('data.locked_branch_id', $assignedBranch->id)
            ->assertJsonCount(1, 'data.branches');

        $this->withToken($token)->getJson('/api/v1/product/search?branch_id='.$otherBranch->id.'&search=ASSIGNED-ITEM')
            ->assertOk()
            ->assertJsonPath('data.products.0.id', $product->id)
            ->assertJsonPath('data.products.0.stock', 3)
            ->assertJsonPath('data.products.0.price', 15);
    }

    public function test_branch_manager_only_sees_assigned_pos_branches(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->givePermissionTo(Permission::findOrCreate('view_quick_sale', 'web'));
        $account = Account::create([
            'name' => 'Manager cash',
            'account_number' => 'TEST-MANAGER',
            'initial_balance' => 0,
            'current_balance' => 0,
            'type' => 'cash',
            'is_active' => true,
            'is_default' => true,
        ]);
        $assignedBranch = Branch::create([
            'name' => 'Managed branch',
            'manager_id' => $manager->id,
            'account_id' => $account->id,
            'created_by' => $manager->id,
            'status' => true,
        ]);
        Branch::create([
            'name' => 'Other manager branch',
            'manager_id' => User::factory()->create()->id,
            'account_id' => $account->id,
            'created_by' => $manager->id,
            'status' => true,
        ]);
        $token = $manager->createToken('mobile')->plainTextToken;

        $this->withToken($token)->getJson('/api/v1/pos/branches')->assertOk()
            ->assertJsonCount(1, 'data.branches')
            ->assertJsonPath('data.branches.0.id', $assignedBranch->id);
    }

    public function test_mobile_product_creation_writes_the_website_catalog(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->givePermissionTo(Permission::findOrCreate('create_products', 'web'));
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/v1/product/store', [
            'name' => 'Mobile created product',
            'code' => 'MOBILE-CATALOG-1',
            'type' => 'Standard',
            'cost' => 4,
            'price' => 10,
            'alert_quantity' => 7,
            'image' => ' ',
        ]);
        $response->assertCreated();

        $this->assertDatabaseHas('products', [
            'id' => $response->json('data.id'),
            'product_title' => 'Mobile created product',
            'product_code' => 'MOBILE-CATALOG-1',
            'alert_quantity' => 7,
        ]);
    }
}

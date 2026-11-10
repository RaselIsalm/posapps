<?php

namespace Tests\Feature;

use App\Models\AccountDeletionRequest;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\AccountDeletionVerification;
use App\Notifications\AccountDeletionRequested;
use App\Services\AccountDeletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_privacy_policy_and_public_deletion_request_pages_are_available(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('Privacy Policy');
        $this->get('/account-deletion')->assertOk()->assertSee('Request account deletion');
    }

    public function test_public_deletion_request_requires_email_verification_before_admin_review(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'store.user@example.test']);

        $this->post('/account-deletion', ['email' => $user->email])
            ->assertRedirect(route('account-deletion.create'))
            ->assertSessionHas('status');

        $request = AccountDeletionRequest::query()->sole();
        $this->assertSame(AccountDeletionRequest::STATUS_VERIFICATION_PENDING, $request->status);
        $this->assertNotSame($request->token_hash, '');

        $verification = null;
        Notification::assertSentOnDemand(AccountDeletionVerification::class, function ($notification) use (&$verification) {
            $verification = $notification;

            return true;
        });

        $path = parse_url($verification->confirmationUrl, PHP_URL_PATH);
        $this->get($path)->assertOk()->assertSee('Confirm account deletion request');
        $this->assertSame(
            AccountDeletionRequest::STATUS_VERIFICATION_PENDING,
            $request->fresh()->status,
            'Opening the link must not verify it automatically.'
        );

        $this->post($path)
            ->assertRedirect(route('account-deletion.create'))
            ->assertSessionHas('status');
        $this->assertSame(AccountDeletionRequest::STATUS_PENDING, $request->fresh()->status);
        $this->assertNotNull($request->fresh()->verified_at);
    }

    public function test_authenticated_profile_deletion_request_is_password_verified_and_notifies_super_admin(): void
    {
        Notification::fake();
        $admin = User::factory()->create();
        $admin->assignRole(Role::create(['name' => 'Super-Admin', 'guard_name' => 'web']));
        $user = User::factory()->create([
            'email' => 'profile.user@example.test',
            'password' => Hash::make('correct-password'),
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['password' => 'correct-password'])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'delete-requested');

        $request = AccountDeletionRequest::query()->sole();
        $this->assertSame(AccountDeletionRequest::STATUS_PENDING, $request->status);
        $this->assertNotNull($request->verified_at);
        Notification::assertSentTo($admin, AccountDeletionRequested::class);
    }

    public function test_authenticated_user_is_redirected_to_dashboard_when_opening_login(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get('/login')->assertRedirect(route('dashboard'));
    }

    public function test_unknown_account_request_returns_same_response_without_sending_email(): void
    {
        Notification::fake();

        $this->post('/account-deletion', ['email' => 'unknown@example.test'])
            ->assertRedirect(route('account-deletion.create'))
            ->assertSessionHas('status');

        $this->assertDatabaseCount('account_deletion_requests', 0);
        Notification::assertNothingSent();
    }

    public function test_admin_approval_removes_profile_data_and_deidentifies_retained_employee_records(): void
    {
        Notification::fake();
        $adminRole = Role::create(['name' => 'Super-Admin', 'guard_name' => 'web']);
        $admin = User::factory()->create();
        $admin->assignRole($adminRole);

        $user = User::factory()->create([
            'name' => 'Store Staff',
            'email' => 'staff@example.test',
            'phone' => '01700000000',
            'username' => 'store-staff',
            'is_active' => true,
            'designation' => 'Cashier',
        ]);
        $employee = Employee::create([
            'name' => 'Store Staff',
            'email' => 'staff@example.test',
            'phone' => '01700000000',
            'salary' => 25000,
            'max_commission' => 5,
            'sale_target' => 100000,
            'joining_date' => '2024-01-01',
            'branch_id' => null,
            'department_id' => null,
            'user_id' => $user->id,
            'status' => true,
        ]);

        $request = AccountDeletionRequest::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'status' => AccountDeletionRequest::STATUS_PENDING,
            'verified_at' => now(),
        ]);

        app(AccountDeletionService::class)->approve($request, $admin);

        $this->assertSame('Former user', $user->fresh()->name);
        $this->assertSame('deleted-user-'.$user->id.'@deleted.invalid', $user->fresh()->email);
        $this->assertFalse($user->fresh()->is_active);
        $this->assertNull($user->fresh()->phone);
        $this->assertSame('Former staff member', $employee->fresh()->name);
        $this->assertSame('former-staff-'.$employee->id.'@deleted.invalid', $employee->fresh()->email);
        $this->assertNull($employee->fresh()->phone);
        $this->assertNull($employee->fresh()->salary);
        $this->assertSame(AccountDeletionRequest::STATUS_COMPLETED, $request->fresh()->status);
        $this->assertNull($request->fresh()->email);
    }
}

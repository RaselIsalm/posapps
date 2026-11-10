<?php

namespace Tests\Feature\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Leads;
use App\Models\Payroll;
use App\Models\SmsLog;
use App\Models\Unit;
use App\Models\Transaction;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ManagementModulesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crm_lead_source_status_notes_and_customer_conversion_round_trip(): void
    {
        $user = $this->userWithPermissions([
            'view_leads', 'create_leads', 'update_leads', 'view_lead_source', 'create_lead_source',
            'view_lead_status', 'create_lead_status', 'view_lead_notes', 'create_lead_notes',
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $sourceId = $this->withToken($token)->postJson('/api/v1/crm/lead-sources', [
            'name' => 'Mobile Campaign', 'status' => true,
        ])->assertCreated()->json('data.item.id');
        $statusId = $this->withToken($token)->postJson('/api/v1/crm/lead-statuses', [
            'name' => 'Contacted', 'progress' => 25, 'status' => true,
        ])->assertCreated()->json('data.item.id');

        $leadId = $this->withToken($token)->postJson('/api/v1/crm/leads', [
            'name' => 'Mobile Lead', 'email' => 'mobile-lead@example.test', 'number' => '555-0101',
            'source_id' => $sourceId, 'status_id' => $statusId, 'status' => true,
        ])->assertCreated()->assertJsonPath('data.item.source_id', $sourceId)->json('data.item.id');

        $this->withToken($token)->getJson('/api/v1/crm/leads?search=Mobile%20Lead')
            ->assertOk()->assertJsonPath('data.total', 1);
        $this->withToken($token)->postJson("/api/v1/crm/leads/{$leadId}/notes", [
            'notes' => 'Called from mobile.',
        ])->assertCreated();

        $this->withToken($token)->postJson("/api/v1/crm/leads/{$leadId}/convert", [])
            ->assertOk()->assertJsonPath('data.customer.phone', '555-0101');
        $this->assertNotNull(Leads::findOrFail($leadId)->customer_id);
        $this->assertDatabaseHas('customers', ['phone' => '555-0101', 'name' => 'Mobile Lead']);
        $this->assertDatabaseHas('lead_notes', ['lead_id' => $leadId, 'notes' => 'Called from mobile.']);
        $this->withToken($token)->postJson("/api/v1/crm/leads/{$leadId}/convert", [])->assertStatus(409);
    }

    public function test_hrm_blocks_deleting_used_departments_and_payroll_updates_account_transactions(): void
    {
        $user = $this->userWithPermissions([
            'view_departments', 'delete_departments', 'view_payrolls', 'create_payrolls',
            'update_payrolls', 'delete_payrolls',
        ]);
        $token = $user->createToken('mobile')->plainTextToken;
        $department = Department::create([
            'name' => 'Mobile Operations', 'code' => 'MOB-OPS', 'status' => true,
            'created_by' => $user->id,
        ]);
        $account = Account::create([
            'name' => 'Payroll Cash', 'account_number' => 'MOBILE-PAYROLL-01',
            'initial_balance' => 500, 'current_balance' => 500, 'type' => 'cash',
            'is_active' => true, 'is_default' => false, 'created_by' => $user->id,
        ]);
        $branch = Branch::create([
            'name' => 'Mobile Main Branch', 'manager_id' => $user->id, 'account_id' => $account->id,
            'created_by' => $user->id, 'status' => true,
        ]);
        $employee = Employee::create([
            'name' => 'Mobile Employee', 'email' => 'mobile-employee@example.test',
            'phone' => '555-0102', 'salary' => 120, 'department_id' => $department->id,
            'branch_id' => $branch->id, 'joining_date' => '2026-01-01', 'status' => true,
            'created_by' => $user->id,
        ]);
        $otherManager = User::factory()->create(['is_active' => true]);
        $otherBranch = Branch::create([
            'name' => 'Other Branch', 'manager_id' => $otherManager->id, 'account_id' => $account->id,
            'created_by' => $otherManager->id, 'status' => true,
        ]);
        $otherEmployee = Employee::create([
            'name' => 'Other Branch Employee', 'email' => 'other-branch-employee@example.test',
            'phone' => '555-0103', 'salary' => 250, 'department_id' => $department->id,
            'branch_id' => $otherBranch->id, 'joining_date' => '2026-01-01', 'status' => true,
            'created_by' => $otherManager->id,
        ]);

        $this->withToken($token)->deleteJson("/api/v1/hrm/departments/{$department->id}")
            ->assertStatus(409);

        $this->withToken($token)->postJson('/api/v1/hrm/payrolls/employee-data', [
            'employee_id' => $otherEmployee->id, 'month' => 'November', 'year' => 2026,
        ])->assertForbidden();

        $payload = [
            'employee_id' => $employee->id, 'salary_month' => 'November', 'salary_year' => 2026,
            'basic_salary' => 120, 'total_allowance' => 0, 'total_deduction' => 0,
            'payment_method' => (string) $account->id, 'payment_date' => '2026-11-01',
            'status' => 'paid',
        ];
        $payrollId = $this->withToken($token)->postJson('/api/v1/hrm/payrolls', $payload)
            ->assertCreated()->json('data.item.id');
        $this->assertSame(380.0, (float) $account->fresh()->current_balance);
        $this->assertDatabaseHas('transactions', [
            'reference_type' => Payroll::class, 'reference_id' => $payrollId, 'type' => 'debit', 'amount' => 120,
        ]);

        $payload['basic_salary'] = 480;
        $this->withToken($token)->putJson("/api/v1/hrm/payrolls/{$payrollId}", $payload)->assertOk();
        $this->assertSame(20.0, (float) $account->fresh()->current_balance);
        $this->assertDatabaseHas('payrolls', ['id' => $payrollId, 'net_salary' => 480]);

        $this->withToken($token)->deleteJson("/api/v1/hrm/payrolls/{$payrollId}")->assertOk();
        $this->assertSame(500.0, (float) $account->fresh()->current_balance);
        $this->assertDatabaseMissing('transactions', ['reference_type' => Payroll::class, 'reference_id' => $payrollId]);

        $foreignPayroll = $payload;
        $foreignPayroll['employee_id'] = $otherEmployee->id;
        $foreignPayroll['status'] = 'pending';
        $this->withToken($token)->postJson('/api/v1/hrm/payrolls', $foreignPayroll)->assertForbidden();
    }

    public function test_reports_allow_initial_ledger_load_and_backup_api_does_not_reveal_secrets(): void
    {
        $user = $this->userWithPermissions(['view_customer_ledger_report', 'manage_general_settings']);
        $token = $user->createToken('mobile')->plainTextToken;
        BackupService::storeSecret('backup_remote_password', 'never-return-this-secret');

        $this->withToken($token)->getJson('/api/v1/management-reports/customer-ledger')
            ->assertOk()->assertJsonPath('data.opening_balance', 0)->assertJsonStructure(['data' => ['customers', 'branch_options', 'totals']]);
        $response = $this->withToken($token)->getJson('/api/v1/system/backups')->assertOk();
        $this->assertStringNotContainsString('never-return-this-secret', $response->getContent());
        $this->withToken($token)->getJson('/api/v1/crm/leads')->assertForbidden();
    }

    public function test_all_management_report_pages_and_sms_template_log_round_trip(): void
    {
        $user = $this->userWithPermissions([
            'view_summary_report', 'view_sales_report', 'view_purchases_report',
            'view_customer_ledger_report', 'view_supplier_ledger_report', 'view_inventory_report',
            'view_employees_report', 'view_branch_report', 'view_sms_log', 'view_sms_template',
            'create_sms_template', 'update_sms_template', 'delete_sms_template', 'manage_api_integrations',
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        foreach ([
            'summary', 'sales', 'purchases', 'customer-ledger', 'supplier-ledger',
            'inventory', 'employees', 'branches',
        ] as $report) {
            $this->withToken($token)->getJson("/api/v1/management-reports/{$report}")->assertOk();
        }

        SmsLog::create(['phone_number' => '555-0199', 'message' => 'test message', 'status' => 'sent']);
        $this->withToken($token)->getJson('/api/v1/communication/sms-logs?search=555-0199')
            ->assertOk()->assertJsonPath('data.total', 1)->assertJsonPath('data.sms-logs.0.phone', '555-0199');

        $templateId = $this->withToken($token)->postJson('/api/v1/communication/sms-templates', [
            'template_name' => 'Payment Reminder', 'message_body' => 'Your due is {amount}.', 'is_active' => true,
        ])->assertCreated()->assertJsonPath('data.item.template_name', 'payment_reminder')->json('data.item.id');
        $this->withToken($token)->putJson("/api/v1/communication/sms-templates/{$templateId}", [
            'template_name' => 'Payment Reminder', 'message_body' => 'Updated reminder.', 'is_active' => true,
        ])->assertOk()->assertJsonPath('data.item.message_body', 'Updated reminder.');
        $this->withToken($token)->postJson('/api/v1/communication/sms-templates', [
            'template_name' => '', 'message_body' => '',
        ])->assertUnprocessable();
        $this->withToken($token)->deleteJson("/api/v1/communication/sms-templates/{$templateId}")->assertOk();

        $settingId = $this->withToken($token)->postJson('/api/v1/communication/sms-settings', [
            'provider_name' => 'Test Gateway', 'api_url' => 'https://sms.example.test/send',
            'params' => [['key' => 'api_key', 'value' => 'do-not-return-this']],
            'is_active' => true,
        ])->assertCreated()->json('data.item.id');
        $this->assertStringNotContainsString('do-not-return-this', $this->getJson("/api/v1/communication/sms-settings/{$settingId}")->getContent());
        $this->withToken($token)->putJson("/api/v1/communication/sms-settings/{$settingId}", [
            'provider_name' => 'Test Gateway', 'api_url' => 'https://sms.example.test/send',
            'params' => [['key' => 'api_key', 'value' => '••••••••']], 'is_active' => true,
        ])->assertOk();
        $this->assertDatabaseHas('sms_setting_params', ['sms_setting_id' => $settingId, 'param_key' => 'api_key', 'param_value' => 'do-not-return-this']);
    }

    public function test_administration_permissions_unit_relationship_and_color_settings(): void
    {
        $user = $this->userWithPermissions([
            'manage_roles', 'view_units', 'create_units', 'update_units', 'delete_units',
            'manage_general_settings', 'view_leads',
        ]);
        $token = $user->createToken('mobile')->plainTextToken;

        $roleId = $this->withToken($token)->postJson('/api/v1/system/roles', [
            'name' => 'Mobile Lead Manager', 'description' => 'CRM access',
            'permissions' => ['view_leads'],
        ])->assertCreated()->assertJsonPath('data.item.permissions.0', 'view_leads')->json('data.item.id');
        $this->withToken($token)->putJson("/api/v1/system/roles/{$roleId}", [
            'name' => 'Mobile Lead Manager', 'permissions' => [],
        ])->assertOk()->assertJsonPath('data.item.permissions', []);

        $baseId = $this->withToken($token)->postJson('/api/v1/system/units', [
            'name' => 'Mobile Base Unit', 'short_code' => 'MBU', 'conversion_rate' => 1,
            'operator' => '*', 'is_active' => true,
        ])->assertCreated()->json('data.item.id');
        Unit::query()->findOrFail($baseId);
        $this->withToken($token)->postJson('/api/v1/system/units', [
            'name' => 'Mobile Child Unit', 'short_code' => 'MCU', 'base_unit_id' => $baseId,
            'conversion_rate' => 12, 'operator' => '*', 'is_active' => true,
        ])->assertCreated();
        $this->withToken($token)->deleteJson("/api/v1/system/units/{$baseId}")->assertStatus(409);

        $this->withToken($token)->putJson('/api/v1/system/settings/color', [
            'primary_color' => '#308e87', 'primary_hover_color' => '#256e69',
            'link_color' => '#308e87', 'toast_success_color' => '#198754',
            'primary_light_color' => '#eaf4f3',
        ])->assertOk()->assertJsonPath('data.values.primary_color', '#308e87');
        $this->withToken($token)->getJson('/api/v1/system/settings/color')
            ->assertOk()
            ->assertJsonPath('data.values.primary_color', '#308e87')
            ->assertJsonPath('data.values.primary_hover_color', '#256e69')
            ->assertJsonPath('data.values.toast_success_color', '#198754');
        $this->withToken($token)->putJson('/api/v1/system/settings/color', [
            'primary_color' => 'teal',
        ])->assertUnprocessable();

        $this->withToken($token)->deleteJson("/api/v1/system/roles/{$roleId}")->assertOk();
    }

    /** @param array<int, string> $permissions */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create(['is_active' => true]);
        foreach ($permissions as $permission) {
            $user->givePermissionTo(Permission::findOrCreate($permission, 'web'));
        }

        return $user;
    }
}

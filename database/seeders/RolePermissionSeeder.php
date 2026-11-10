<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $permissions = [
            'view_dashboard',
            'manage_general_settings',
            'manage_roles',
            'manage_users',
            'view_branches',
            'create_branches',
            'update_branches',
            'delete_branches',
            'view_categories',
            'create_categories',
            'update_categories',
            'delete_categories',
            'view_brands',
            'create_brands',
            'update_brands',
            'delete_brands',
            'view_units',
            'create_units',
            'update_units',
            'delete_units',
            'view_currencies',
            'create_currencies',
            'update_currencies',
            'delete_currencies',
            'view_products',
            'create_products',
            'update_products',
            'delete_products',
            'view_customer_groups',
            'create_customer_groups',
            'update_customer_groups',
            'delete_customer_groups',
            'view_customers',
            'create_customers',
            'update_customers',
            'delete_customers',
            'view_suppliers',
            'create_suppliers',
            'update_suppliers',
            'delete_suppliers',
            'view_accounts',
            'create_accounts',
            'update_accounts',
            'delete_accounts',
            'view_expenses',
            'create_expenses',
            'update_expenses',
            'delete_expenses',
            'view_expenses_categories',
            'create_expenses_categories',
            'update_expenses_categories',
            'delete_expenses_categories',
            'view_employees',
            'create_employees',
            'update_employees',
            'delete_employees',
            'view_employees_attendances',
            'create_employees_attendances',
            'update_employees_attendances',
            'delete_employees_attendances',
            'view_departments',
            'create_departments',
            'update_departments',
            'delete_departments',
            'view_payrolls',
            'create_payrolls',
            'update_payrolls',
            'delete_payrolls',
            'view_lead_source',
            'create_lead_source',
            'update_lead_source',
            'delete_lead_source',
            'view_lead_status',
            'create_lead_status',
            'update_lead_status',
            'delete_lead_status',
            'view_leads',
            'create_leads',
            'update_leads',
            'delete_leads',
            'view_lead_notes',
            'create_lead_notes',
            'update_lead_notes',
            'delete_lead_notes',
            'view_quick_sale',
            'create_quick_sale',
            'update_quick_sale',
            'delete_quick_sale',
            'view_sale_list',
            'update_sale_list',
            'delete_sale_list',
            'view_purchases',
            'create_purchases',
            'update_purchases',
            'delete_purchases',
            'view_inventory',
            'update_inventory',
            'delete_inventory',
            'view_sale_return',
            'create_sale_return',
            'update_sale_return',
            'delete_sale_return',
            'view_purchase_return',
            'create_purchase_return',
            'update_purchase_return',
            'delete_purchase_return',
            'view_transfer',
            'create_transfer',
            'update_transfer',
            'delete_transfer',
            'view_gift_card',
            'create_gift_card',
            'delete_gift_card',
            'update_gift_card',
            'view_coupons',
            'create_coupons',
            'delete_coupons',
            'update_coupons',
            'view_delivery',
            'create_delivery',
            'delete_delivery',
            'update_delivery',
            'view_reports',
            'view_sales_report',
            'view_purchases_report',
            'view_inventory_report',
            'view_summary_report',
            'view_customer_ledger_report',
            'view_supplier_ledger_report',
            'view_employees_report',
            'view_branch_report',
            'view_reward_points',
            'update_reward_points',
            'view_activity_log',
            'view_quotations',
            'create_quotations',
            'update_quotations',
            'delete_quotations',
            'view_quotation_items',
            'create_quotation_items',
            'update_quotation_items',
            'delete_quotation_items',
            'view_print_barcode',
            'view_sms_log',
            'view_sms_template',
            'create_sms_template',
            'update_sms_template',
            'delete_sms_template',
            'manage_api_integrations',
            'balance_sheet',
        ];

        foreach ($permissions as $permission) {
            if (! Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
            }
        }

        $roles = [
            'Super-Admin',
            'Admin',
            'Editor',
            'Demo-Guest',
        ];

        foreach ($roles as $role) {
            if (! Role::where('name', $role)->exists()) {
                Role::create(['name' => $role]);
            }
        }

        $role = Role::where('name', 'Super-Admin')->first();
        $role->givePermissionTo(Permission::all()->pluck('name'));

        $role = Role::where('name', 'Admin')->first();
        $role->givePermissionTo(Permission::all()->pluck('name'));

        $demoRole = Role::where('name', 'Demo-Guest')->first();
        $demoRole->syncPermissions(Permission::query()->where('name', 'like', 'view_%')->pluck('name'));
    }
}

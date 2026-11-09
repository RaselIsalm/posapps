<?php

return [
    [
        'id' => 'dashboard-menu',
        'title' => 'file.dashboard',
        'icon' => '<i class="fa-solid fa-house"></i>',
        'permission' => null,
        'sub_menu' => [
            [
                'title' => 'Dashboard',
                'route' => 'dashboard',
                'permission' => null,
            ],
        ],
    ],

    [
        'id' => 'quick-sale-menu',
        'title' => 'Sale',
        'icon' => '<i class="fa-solid fa-cart-shopping"></i>',
        'sub_menu' => [
            [
                'title' => 'Quick Sale',
                'route' => 'quick-sale.index',
                'permission' => ['view_quick_sale', 'create_quick_sale'],
            ],
            [
                'title' => 'Sale List',
                'route' => 'sale-list.index',
                'permission' => ['view_sale_list'],
            ],
            [
                'title' => 'Gift Card',
                'route' => 'gift-card.index',
                'permission' => ['view_gift_card'],
            ],
            [
                'title' => 'Coupons',
                'route' => 'coupons.index',
                'permission' => ['view_coupons'],
            ],
            [
                'title' => 'Reward Points',
                'route' => 'reward-points.index',
                'permission' => ['view_reward_points'],
            ],
            [
                'title' => 'Delivery',
                'route' => 'delivery.index',
                'permission' => ['view_delivery'],
            ],
        ],
    ],

    [
        'id' => 'quotations-menu',
        'title' => 'Quotations',
        'icon' => '<i class="fa-solid fa-file-invoice"></i>',
        'sub_menu' => [
            [
                'title' => 'Add Quotations',
                'route' => 'quotations.create',
                'permission' => ['create_quotations'],
            ],
            [
                'title' => 'Quotations List',
                'route' => 'quotations.index',
                'permission' => ['view_quotations'],
            ],
        ],
    ],

    [
        'id' => 'products-menu',
        'title' => 'Products',
        'icon' => '<i class="fa-solid fa-cube"></i>',
        'sub_menu' => [
            [
                'title' => 'Product List',
                'route' => 'products.index',
                'permission' => ['view_products'],
            ],
            [
                'title' => 'Create Product',
                'route' => 'products.create',
                'permission' => ['create_products'],
            ],
            [
                'title' => 'Bulk Product Import',
                'route' => 'products.import',
                'permission' => ['create_products'],
            ],
            [
                'title' => 'Categories',
                'route' => 'categories.index',
                'permission' => ['view_categories'],
            ],
            [
                'title' => 'Brands',
                'route' => 'brands.index',
                'permission' => ['view_brands'],
            ],
            [
                'title' => 'Print Barcode',
                'route' => 'barcode.index',
                'permission' => ['view_print_barcode'],
            ],
        ],
    ],

    [
        'id' => 'inventory-menu',
        'title' => 'Stock',
        'icon' => '<i class="fa-solid fa-boxes-stacked"></i>',
        'sub_menu' => [
            [
                'title' => 'Inventory List',
                'route' => 'inventory.index',
                'permission' => ['view_inventory'],
            ],
            [
                'title' => 'Stock History',
                'route' => 'stock-history.index',
                'permission' => ['view_inventory'],
            ],
        ],
    ],

    [
        'id' => 'transfers-menu',
        'title' => 'Transfers',
        'icon' => '<i class="fa-solid fa-right-left"></i>',
        'sub_menu' => [
            [
                'title' => 'Transfers',
                'route' => 'transfers.index',
                'permission' => ['view_transfer'],
            ],
            [
                'title' => 'Add Transfer',
                'route' => 'transfers.create',
                'permission' => ['create_transfer'],
            ],
        ],
    ],

    [
        'id' => 'purchase-menu',
        'title' => 'Purchase',
        'icon' => '<i class="fa-solid fa-bag-shopping"></i>',
        'sub_menu' => [
            [
                'title' => 'Purchase List',
                'route' => 'purchases.index',
                'permission' => ['view_purchases'],
            ],
            [
                'title' => 'Add Purchase',
                'route' => 'purchases.create',
                'permission' => ['create_purchases'],
            ],
        ],
    ],

    [
        'id' => 'return-menu',
        'title' => 'Return',
        'icon' => '<i class="fa-solid fa-rotate-left"></i>',
        'sub_menu' => [
            [
                'title' => 'Sale Return',
                'route' => 'sale-return.index',
                'permission' => ['view_sale_return'],
            ],
            [
                'title' => 'Purchase Return',
                'route' => 'purchase-return.index',
                'permission' => ['view_purchase_return'],
            ],
        ],
    ],

    [
        'id' => 'peoples-menu',
        'title' => 'Peoples',
        'icon' => '<i class="fa-solid fa-users"></i>',
        'sub_menu' => [
            [
                'title' => 'Customer Groups',
                'route' => 'customer-groups.index',
                'permission' => ['view_customer_groups'],
            ],
            [
                'title' => 'Customers',
                'route' => 'customers.index',
                'permission' => ['view_customers'],
            ],
            [
                'title' => 'Suppliers',
                'route' => 'suppliers.index',
                'permission' => ['view_suppliers'],
            ],
        ],
    ],

    [
        'id' => 'accounts-menu',
        'title' => 'Accounts',
        'icon' => '<i class="fa-solid fa-wallet"></i>',
        'sub_menu' => [
            [
                'title' => 'View Accounts',
                'route' => 'accounts.index',
                'permission' => ['view_accounts'],
            ],
            [
                'title' => 'Statement',
                'route' => 'accounts.statement',
                'permission' => ['view_accounts'],
            ],
            [
                'title' => 'Balance Sheet',
                'route' => 'accounts.balance-sheet',
                'permission' => ['balance_sheet'],
            ],
            [
                'title' => 'View Expenses',
                'route' => 'expenses.index',
                'permission' => ['view_expenses'],
            ],
            [
                'title' => 'Expenses Categories',
                'route' => 'expenses-categories.index',
                'permission' => ['view_expenses_categories'],
            ],
        ],
    ],

    [
        'id' => 'crm-menu',
        'title' => 'CRM',
        'icon' => '<i class="fa-solid fa-handshake"></i>',
        'sub_menu' => [
            [
                'title' => 'Leads',
                'route' => 'leads.index',
                'permission' => ['view_leads'],
            ],
            [
                'title' => 'Lead Source',
                'route' => 'lead_source.index',
                'permission' => ['view_lead_source'],
            ],
            [
                'title' => 'Lead Status',
                'route' => 'lead_status.index',
                'permission' => ['view_lead_status'],
            ],
            [
                'title' => 'Lead Notes',
                'route' => 'lead_notes.index',
                'permission' => ['view_lead_notes'],
            ],
        ],
    ],

    [
        'id' => 'hrm-menu',
        'title' => 'HRM',
        'icon' => '<i class="fa-solid fa-users-gear"></i>',
        'sub_menu' => [
            [
                'title' => 'Employee',
                'route' => 'employees.index',
                'permission' => ['view_employees'],
            ],
            [
                'title' => 'Attendance',
                'route' => 'attendance.index',
                'permission' => ['view_employees_attendances'],
            ],
            [
                'title' => 'Department',
                'route' => 'departments.index',
                'permission' => ['view_departments'],
            ],
            [
                'title' => 'Payrolls',
                'route' => 'payrolls.index',
                'permission' => ['view_payrolls'],
            ],
        ],
    ],

    [
        'id' => 'reports-menu',
        'title' => 'Reports',
        'icon' => '<i class="fa-solid fa-chart-line"></i>',
        'sub_menu' => [
            [
                'title' => 'Summary Reports',
                'route' => 'reports.summary',
                'permission' => ['view_summary_report'],
            ],
            [
                'title' => 'Sales Report',
                'route' => 'reports.sales',
                'permission' => ['view_sales_report'],
            ],
            [
                'title' => 'Purchases Report',
                'route' => 'reports.purchase.index',
                'permission' => ['view_purchases_report'],
            ],
            [
                'title' => 'Customer Ledger Report',
                'route' => 'reports.customer_ledger',
                'permission' => ['view_customer_ledger_report'],
            ],
            [
                'title' => 'Supplier Ledger Report',
                'route' => 'reports.supplier_ledger',
                'permission' => ['view_supplier_ledger_report'],
            ],
            [
                'title' => 'Inventory Report',
                'route' => 'reports.inventory',
                'permission' => ['view_inventory_report'],
            ],
            [
                'title' => 'Employees Report',
                'route' => 'reports.employees',
                'permission' => ['view_employees_report'],
            ],
            [
                'title' => 'Branch Report',
                'route' => 'reports.branch.index',
                'permission' => ['view_branch_report'],
            ],
        ],
    ],

    [
        'id' => 'sms-menu',
        'title' => 'SMS',
        'icon' => '<i class="fa-solid fa-comment-sms"></i>',
        'sub_menu' => [
            [
                'title' => 'SMS Logs',
                'route' => 'sms_logs.index',
                'permission' => ['view_sms_log'],
            ],
            [
                'title' => 'SMS Templates',
                'route' => 'sms_templates.index',
                'permission' => ['view_sms_template'],
            ],
        ],
    ],

    [
        'id' => 'api-settings-menu',
        'title' => 'API Settings',
        'icon' => '<i class="fa-solid fa-code"></i>',
        'sub_menu' => [
            [
                'title' => 'SMS Settings',
                'route' => 'sms_settings.index',
                'permission' => ['manage_api_integrations'],
            ],
            [
                'title' => 'Courier Settings',
                'route' => 'courier_settings.index',
                'permission' => ['manage_api_integrations'],
            ],
            [
                'title' => 'Fraud Check API',
                'route' => 'fraud-check-setting.index',
                'permission' => ['manage_api_integrations'],
            ],
        ],
    ],

    [
        'id' => 'settings-menu',
        'title' => 'Settings',
        'icon' => '<i class="fa-solid fa-gear"></i>',
        'sub_menu' => [
            [
                'title' => 'General Settings',
                'route' => 'settings.general',
                'permission' => ['manage_general_settings'],
            ],
            [
                'title' => 'Color Settings',
                'route' => 'settings.colors',
                'permission' => ['manage_general_settings'],
            ],
            [
                'title' => 'Print Settings',
                'route' => 'settings.print',
                'permission' => ['manage_general_settings'],
            ],
            [
                'title' => 'Backup',
                'route' => 'settings.backup',
                'permission' => ['manage_general_settings'],
            ],
            [
                'title' => 'Terms & Conditions',
                'route' => 'settings.terms',
                'permission' => ['manage_general_settings'],
            ],
            [
                'title' => 'Mail Settings',
                'route' => 'mail-settings.index',
                'permission' => ['manage_general_settings'],
            ],
            [
                'title' => 'Branches',
                'route' => 'branches.index',
                'permission' => ['view_branches'],
            ],
            [
                'title' => 'Manage Roles',
                'route' => 'roles.index',
                'permission' => ['manage_roles'],
            ],
            [
                'title' => 'Manage Users',
                'route' => 'users.index',
                'permission' => ['manage_users'],
            ],
            [
                'title' => 'Units',
                'route' => 'units.index',
                'permission' => ['view_units'],
            ],
            [
                'title' => 'Currencies',
                'route' => 'currencies.index',
                'permission' => ['view_currencies'],
            ],
            [
                'title' => 'Activities Logs',
                'route' => 'activity-logs.index',
                'permission' => ['view_activity_log'],
            ],
        ],
    ],

    [
        'id' => 'help-center-menu',
        'title' => 'Help Center',
        'icon' => '<i class="fa-solid fa-circle-question"></i>',
        'permission' => null,
        'sub_menu' => [
            [
                'title' => 'Help & FAQ',
                'route' => 'help.index',
                'permission' => null,
            ],
        ],
    ],

];

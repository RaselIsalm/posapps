<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\BarcodeController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\BranchReportController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CourierSettingController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerGroupController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeAttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ExpensesCategoryController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\FraudCheckController;
use App\Http\Controllers\FraudCheckSettingController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\LeadNoteController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\LeadSourcesController;
use App\Http\Controllers\LeadStatusController;
use App\Http\Controllers\MailSettingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReportController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuickSaleController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RewardPointSettingController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SaleListController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SmsLogController;
use App\Http\Controllers\SmsSettingController;
use App\Http\Controllers\SmsTemplateController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransferController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here you can register the web routes for your application.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',

])->group(function () {

    Route::get('/clear', function () {
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');

        return redirect()->back()->with('success', 'Cache cleared successfully.');
    })->name('clear-cache');

    // Webhooks for Couriers (Public)
    Route::post('/webhook/steadfast', [DeliveryController::class, 'steadfastWebhook'])->name('webhook.steadfast');
    Route::post('/webhook/pathao', [DeliveryController::class, 'pathaoWebhook'])->name('webhook.pathao');

    Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');

    Route::get('/', [LandingController::class, 'index'])->name('landing');

    Route::get('/privacy', [AccountDeletionController::class, 'privacy'])->name('privacy');
    Route::get('/account-deletion', [AccountDeletionController::class, 'create'])->name('account-deletion.create');
    Route::post('/account-deletion', [AccountDeletionController::class, 'requestDeletion'])
        ->middleware('throttle:5,10')
        ->name('account-deletion.store');
    Route::get('/account-deletion/verify/{deletionRequest}/{token}', [AccountDeletionController::class, 'confirm'])
        ->whereNumber('deletionRequest')
        ->where('token', '[A-Za-z0-9]+')
        ->name('account-deletion.verify.form');
    Route::post('/account-deletion/verify/{deletionRequest}/{token}', [AccountDeletionController::class, 'verify'])
        ->middleware('throttle:10,10')
        ->whereNumber('deletionRequest')
        ->where('token', '[A-Za-z0-9]+')
        ->name('account-deletion.verify');

    Route::middleware(['common', 'auth'])->group(function () {
        Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard/data', [HomeController::class, 'dashboardData'])->name('dashboard.data');
        Route::get('/onboarding', [HomeController::class, 'onboarding'])->name('onboarding');
        Route::get('/help', [\App\Http\Controllers\HelpGuideController::class, 'index'])->name('help.index');

        // settings
        Route::prefix('settings')->controller(SettingController::class)->middleware('permission:manage_general_settings')->group(function () {
            Route::get('/general-settings', 'index')->name('settings.general');
            Route::put('/general-settings/update', 'updateGeneralSettings')->name('settings.generalUpdate');

            Route::get('/color-settings', 'colorSettings')->name('settings.colors');
            Route::put('/color-settings/update', 'updateColorSettings')->name('settings.colorsUpdate');
            Route::post('/color-settings/reset', 'resetColorSettings')->name('settings.colorsReset');
            
            Route::get('/terms-and-conditions', 'termsAndConditions')->name('settings.terms');
            Route::put('/terms-and-conditions/update', 'updateTermsAndConditions')->name('settings.termsUpdate');

            Route::get('/print-settings', 'printSettings')->name('settings.print');
            Route::put('/print-settings/update', 'updatePrintSettings')->name('settings.printUpdate');
        });

        Route::prefix('settings')->controller(\App\Http\Controllers\BackupController::class)->middleware('permission:manage_general_settings')->group(function () {
            Route::get('/backup', 'index')->name('settings.backup');
            Route::put('/backup', 'update')->name('settings.backupUpdate');
            Route::put('/backup/remote-test', 'testRemote')->name('settings.backupRemoteTest');
            Route::post('/backup/run', 'run')->name('settings.backupRun');
            Route::post('/backup/password', 'regeneratePassword')->name('settings.backupPassword');
            Route::get('/backup/download/{backup}', 'download')->name('settings.backupDownload');
            Route::delete('/backup/{backup}', 'destroy')->name('settings.backupDelete');
            Route::post('/backup/restore/{backup}', 'restore')->name('settings.backupRestore');
        });

        // Mail Settings
        Route::prefix('mail-settings')->controller(MailSettingController::class)->middleware('permission:manage_general_settings')->group(function () {
            Route::get('/', 'index')->name('mail-settings.index');
            Route::post('/store', 'store')->name('mail-settings.store');
            Route::post('/test', 'testMail')->name('mail-settings.test');
        });

        Route::prefix('users')->controller(UserController::class)->middleware('permission:manage_users')->group(function () {
            Route::get('/deletion-requests', [AccountDeletionController::class, 'index'])
                ->middleware('role:Super-Admin')
                ->name('users.deletionRequests.index');
            Route::post('/deletion-requests/{deletionRequest}/approve', [AccountDeletionController::class, 'approve'])
                ->middleware('role:Super-Admin')
                ->name('users.deletionRequests.approve');
            Route::post('/deletion-requests/{deletionRequest}/reject', [AccountDeletionController::class, 'reject'])
                ->middleware('role:Super-Admin')
                ->name('users.deletionRequests.reject');
            Route::get('/', 'index')->name('users.index');
            Route::get('/create', 'create')->name('users.create');
            Route::post('/store', 'store')->name('users.store');
            Route::get('/edit/{id}', 'edit')->name('users.edit');
            Route::put('/update/{id}', 'update')->name('users.update');
            Route::get('/removal-preview/{id}', 'removalPreview')->name('users.removalPreview');
            Route::post('/activate/{id}', 'activate')->name('users.activate');
            Route::delete('/delete/{id}', 'destroy')->name('users.destroy');
        });

        // roles
        Route::prefix('roles')->controller(RolePermissionController::class)->middleware('permission:manage_roles')->group(function () {
            Route::get('/', 'index')->name('roles.index');
            Route::get('/create', 'create')->name('roles.create');
            Route::post('/store', 'store')->name('roles.store');
            Route::get('/edit/{id}', 'edit')->name('roles.edit');
            Route::put('/update/{id}', 'update')->name('roles.update');
            Route::delete('/delete/{id}', 'destroy')->name('roles.destroy');
            Route::get('manage-permissions/{id}', 'managePermissions')->name('roles.managePermissions');
            Route::put('update-permissions/{id}', 'updatePermissions')->name('roles.updatePermissions');
        });

        // branch
        Route::prefix('branches')->controller(BranchController::class)->group(function () {
            Route::get('/', 'index')->name('branches.index')->middleware('permission:view_branches');
            Route::middleware('permission:create_branches')->group(function () {
                Route::get('/create', 'create')->name('branches.create');
                Route::post('/store', 'store')->name('branches.store');
            });
            Route::middleware('permission:update_branches')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('branches.edit');
                Route::put('/update/{id}', 'update')->name('branches.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('branches.destroy')->middleware('permission:delete_branches');
        });

        // categories
        Route::prefix('categories')->controller(CategoryController::class)->group(function () {
            Route::get('/', 'index')->name('categories.index')->middleware('permission:view_categories');
            Route::middleware('permission:create_categories')->group(function () {
                Route::get('/create', 'create')->name('categories.create');
                Route::post('/store', 'store')->name('categories.store');
            });
            Route::middleware('permission:update_categories')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('categories.edit');
                Route::put('/update/{id}', 'update')->name('categories.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('categories.destroy')->middleware('permission:delete_categories');
        });

        // brands
        Route::prefix('brands')->controller(BrandController::class)->group(function () {
            Route::get('/', 'index')->name('brands.index')->middleware('permission:view_brands');
            Route::middleware('permission:create_brands')->group(function () {
                Route::get('/create', 'create')->name('brands.create');
                Route::post('/store', 'store')->name('brands.store');
            });
            Route::middleware('permission:update_brands')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('brands.edit');
                Route::put('/update/{id}', 'update')->name('brands.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('brands.destroy')->middleware('permission:delete_brands');
        });

        // units
        Route::prefix('units')->controller(UnitController::class)->group(function () {
            Route::get('/', 'index')->name('units.index')->middleware('permission:view_units');
            Route::middleware('permission:create_units')->group(function () {
                Route::get('/create', 'create')->name('units.create');
                Route::post('/store', 'store')->name('units.store');
            });
            Route::middleware('permission:update_units')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('units.edit');
                Route::put('/update/{id}', 'update')->name('units.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('units.destroy')->middleware('permission:delete_units');
            Route::get('/get-child-units/{id}', 'getChildUnits')->name('units.getChildUnits');
        });

        // currencies
        Route::prefix('currencies')->controller(CurrencyController::class)->group(function () {
            Route::get('/', 'index')->name('currencies.index')->middleware('permission:view_currencies');
            Route::middleware('permission:create_currencies')->group(function () {
                Route::get('/create', 'create')->name('currencies.create');
                Route::post('/store', 'store')->name('currencies.store');
            });
            Route::middleware('permission:update_currencies')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('currencies.edit');
                Route::put('/update/{id}', 'update')->name('currencies.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('currencies.destroy')->middleware('permission:delete_currencies');
        });

        // Customer Group
        Route::prefix('customer-groups')->controller(CustomerGroupController::class)->group(function () {
            Route::get('/', 'index')->name('customer-groups.index')->middleware('permission:view_customer_groups');
            Route::middleware('permission:create_customer_groups')->group(function () {
                Route::get('/create', 'create')->name('customer-groups.create');
                Route::post('/store', 'store')->name('customer-groups.store');
            });
            Route::middleware('permission:update_customer_groups')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('customer-groups.edit');
                Route::put('/update/{id}', 'update')->name('customer-groups.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('customer-groups.destroy')->middleware('permission:delete_customer_groups');
        });

        // Customers
        Route::prefix('customers')->controller(CustomerController::class)->group(function () {
            Route::get('/', 'index')->name('customers.index')->middleware('permission:view_customers');
            Route::middleware('permission:create_customers')->group(function () {
                Route::get('/create', 'create')->name('customers.create');
                Route::post('/store', 'store')->name('customers.store');
            });
            Route::middleware('permission:update_customers')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('customers.edit');
                Route::put('/update/{id}', 'update')->name('customers.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('customers.destroy')->middleware('permission:delete_customers');
        });

        // Suppliers
        Route::prefix('suppliers')->controller(SupplierController::class)->group(function () {
            Route::get('/', 'index')->name('suppliers.index')->middleware('permission:view_suppliers');
            Route::middleware('permission:create_suppliers')->group(function () {
                Route::get('/create', 'create')->name('suppliers.create');
                Route::post('/store', 'store')->name('suppliers.store');
            });
            Route::middleware('permission:update_suppliers')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('suppliers.edit');
                Route::put('/update/{id}', 'update')->name('suppliers.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('suppliers.destroy')->middleware('permission:delete_suppliers');
        });

        // Account
        Route::prefix('accounts')->controller(AccountController::class)->group(function () {
            Route::middleware('permission:view_accounts')->group(function () {
                Route::get('/', 'index')->name('accounts.index');
                Route::get('/statement', 'statement')->name('accounts.statement');
                Route::get('/balance-sheet', 'balanceSheet')->name('accounts.balance-sheet');
            });
            Route::middleware('permission:create_accounts')->group(function () {
                Route::get('/create', 'create')->name('accounts.create');
                Route::post('/store', 'store')->name('accounts.store');
            });
            Route::middleware('permission:update_accounts')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('accounts.edit');
                Route::put('/update/{id}', 'update')->name('accounts.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('accounts.destroy')->middleware('permission:delete_accounts');
            Route::put('make-default/{id}', 'makeDefault')->name('accounts.makeDefault')->middleware('permission:update_accounts');
        });

        // Notifications
        Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
            Route::get('/', 'index')->name('notifications.index');
            Route::get('/unread', 'getUnreadNotifications')->name('notifications.unread');
            Route::post('/mark-read/{id}', 'markAsRead')->name('notifications.markRead');
            Route::post('/mark-all-read', 'markAllAsRead')->name('notifications.markAllRead');
        });

        // Employee
        Route::prefix('employees')->controller(EmployeeController::class)->group(function () {
            Route::get('/', 'index')->name('employees.index')->middleware('permission:view_employees');
            Route::middleware('permission:create_employees')->group(function () {
                Route::get('/create', 'create')->name('employees.create');
                Route::post('/store', 'store')->name('employees.store');
            });
            Route::middleware('permission:update_employees')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('employees.edit');
                Route::put('/update/{id}', 'update')->name('employees.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('employees.destroy')->middleware('permission:delete_employees');
        });

        // Employee Attendance
        Route::prefix('attendance')->controller(EmployeeAttendanceController::class)->group(function () {
            Route::get('/', 'index')->name('attendance.index')->middleware('permission:view_employees_attendances');
            Route::middleware('permission:create_employees_attendances')->group(function () {
                Route::get('/create', 'create')->name('attendance.create');
                Route::post('/store', 'store')->name('attendance.store');
            });
            Route::middleware('permission:update_employees_attendances')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('attendance.edit');
                Route::put('/update/{id}', 'update')->name('attendance.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('attendance.destroy')->middleware('permission:delete_employees_attendances');
        });

        // Department
        Route::prefix('departments')->controller(DepartmentController::class)->group(function () {
            Route::get('/', 'index')->name('departments.index')->middleware('permission:view_departments');
            Route::middleware('permission:create_departments')->group(function () {
                Route::get('/create', 'create')->name('departments.create');
                Route::post('/store', 'store')->name('departments.store');
            });
            Route::middleware('permission:update_departments')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('departments.edit');
                Route::put('/update/{id}', 'update')->name('departments.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('departments.destroy')->middleware('permission:delete_departments');
        });

        // Expenses
        Route::prefix('expenses')->controller(ExpensesController::class)->group(function () {
            Route::get('/', 'index')->name('expenses.index')->middleware('permission:view_expenses');
            Route::middleware('permission:create_expenses')->group(function () {
                Route::get('/create', 'create')->name('expenses.create');
                Route::post('/store', 'store')->name('expenses.store');
            });
            Route::middleware('permission:update_expenses')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('expenses.edit');
                Route::put('/update/{id}', 'update')->name('expenses.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('expenses.destroy')->middleware('permission:delete_expenses');
        });

        // Expenses Category
        Route::prefix('expenses-categories')->controller(ExpensesCategoryController::class)->group(function () {
            Route::get('/', 'index')->name('expenses-categories.index')->middleware('permission:view_expenses_categories');
            Route::middleware('permission:create_expenses_categories')->group(function () {
                Route::get('/create', 'create')->name('expenses-categories.create');
                Route::post('/store', 'store')->name('expenses-categories.store');
            });
            Route::middleware('permission:update_expenses_categories')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('expenses-categories.edit');
                Route::put('/update/{id}', 'update')->name('expenses-categories.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('expenses-categories.destroy')->middleware('permission:delete_expenses_categories');
        });

        // Payrolls
        Route::prefix('payrolls')->controller(PayrollController::class)->group(function () {
            Route::get('/', 'index')->name('payrolls.index')->middleware('permission:view_payrolls');
            Route::middleware('permission:create_payrolls')->group(function () {
                Route::get('/create', 'create')->name('payrolls.create');
                Route::post('/store', 'store')->name('payrolls.store');
                Route::post('/get-employee-data', 'getEmployeeData')->name('payrolls.getEmployeeData');
            });
            Route::middleware('permission:update_payrolls')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('payrolls.edit');
                Route::put('/update/{id}', 'update')->name('payrolls.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('payrolls.destroy')->middleware('permission:delete_payrolls');
        });

        // Lead_source
        Route::prefix('lead-source')->controller(LeadSourcesController::class)->group(function () {
            Route::get('/', 'index')->name('lead_source.index')->middleware('permission:view_lead_source');
            Route::middleware('permission:create_lead_source')->group(function () {
                Route::get('/create', 'create')->name('lead_source.create');
                Route::post('/store', 'store')->name('lead_source.store');
            });
            Route::middleware('permission:update_lead_source')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('lead_source.edit');
                Route::put('/update/{id}', 'update')->name('lead_source.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('lead_source.destroy')->middleware('permission:delete_lead_source');
        });

        // Lead_status
        Route::prefix('lead-status')->controller(LeadStatusController::class)->group(function () {
            Route::get('/', 'index')->name('lead_status.index')->middleware('permission:view_lead_status');
            Route::middleware('permission:create_lead_status')->group(function () {
                Route::get('/create', 'create')->name('lead_status.create');
                Route::post('/store', 'store')->name('lead_status.store');
            });
            Route::middleware('permission:update_lead_status')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('lead_status.edit');
                Route::put('/update/{id}', 'update')->name('lead_status.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('lead_status.destroy')->middleware('permission:delete_lead_status');
        });

        // Leads
        Route::prefix('leads')->controller(LeadsController::class)->group(function () {
            Route::get('/', 'index')->name('leads.index')->middleware('permission:view_leads');
            Route::middleware('permission:create_leads')->group(function () {
                Route::get('/create', 'create')->name('leads.create');
                Route::post('/store', 'store')->name('leads.store');
            });
            Route::middleware('permission:create_lead_notes')->group(function () {
                Route::get('/note/{id}', 'createNote')->name('leads.note.create');
                Route::post('/note/store', 'storeNote')->name('lead_notes.store');
            });
            Route::middleware('permission:update_leads')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('leads.edit');
                Route::put('/update/{id}', 'update')->name('leads.update');
                Route::post('/convert/{id}', 'convert')->name('leads.convert');
            });
            Route::delete('/delete/{id}', 'destroy')->name('leads.destroy')->middleware('permission:delete_leads');
        });

        // Lead Notes
        Route::prefix('lead-notes')->controller(LeadNoteController::class)->group(function () {
            Route::get('/', 'index')->name('lead_notes.index')->middleware('permission:view_lead_notes');
            Route::middleware('permission:update_lead_notes')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('lead_notes.edit');
                Route::put('/update/{id}', 'update')->name('lead_notes.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('lead_notes.destroy')->middleware('permission:delete_lead_notes');
        });

        // Quick Sale
        Route::prefix('quick-sale')->controller(QuickSaleController::class)->group(function () {
            Route::get('/', 'index')->name('quick-sale.index')->middleware('permission:view_quick_sale|create_quick_sale');
            Route::post('/gift-card/store', 'storeGiftCard')->name('quick-sale.gift-card.store')->middleware('permission:create_gift_card');
            Route::post('/check-coupon', 'checkCoupon')->name('quick-sale.check-coupon');
            Route::post('/check-gift-card', 'checkGiftCard')->name('quick-sale.check-gift-card');
            Route::middleware('permission:create_quick_sale')->group(function () {
                Route::post('/store', 'store')->name('quick-sale.store');
                Route::get('/print-thermal/{id}', 'printThermal')->name('quick-sale.print-thermal');
                Route::get('/print-a4/{id}', 'printA4')->name('quick-sale.print-a4');
            });
        });

        // Gift Card
        Route::prefix('gift-card')->controller(GiftCardController::class)->group(function () {
            Route::middleware('permission:view_gift_card')->group(function () {
                Route::get('/', 'index')->name('gift-card.index');
                Route::get('/show/{id}', 'show')->name('gift-card.show');
                Route::get('/history/{id}', 'history')->name('gift-card.history');
            });
            Route::middleware('permission:update_gift_card')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('gift-card.edit');
                Route::put('/update/{id}', 'update')->name('gift-card.update');
                Route::get('/recharge/{id}', 'rechargeForm')->name('gift-card.recharge-form');
                Route::post('/recharge/{id}', 'recharge')->name('gift-card.recharge');
            });
            Route::delete('/delete/{id}', 'destroy')->name('gift-card.destroy')->middleware('permission:delete_gift_card');
        });

        // Reward Point Setting
        Route::prefix('reward-points')->controller(RewardPointSettingController::class)->group(function () {
            Route::get('/', 'index')->name('reward-points.index')->middleware('permission:view_reward_points');
            Route::post('/update', 'update')->name('reward-points.update')->middleware('permission:update_reward_points');
        });

        // Coupons
        Route::prefix('coupons')->controller(CouponController::class)->group(function () {
            Route::middleware('permission:view_coupons')->group(function () {
                Route::get('/', 'index')->name('coupons.index');
                Route::get('/show/{id}', 'show')->name('coupons.show');
            });
            Route::middleware('permission:create_coupons')->group(function () {
                Route::get('/create', 'create')->name('coupons.create');
                Route::post('/store', 'store')->name('coupons.store');
            });
            Route::middleware('permission:update_coupons')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('coupons.edit');
                Route::put('/update/{id}', 'update')->name('coupons.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('coupons.destroy')->middleware('permission:delete_coupons');
        });

        // Sale List
        Route::prefix('sale-list')->controller(SaleListController::class)->group(function () {
            Route::get('/', 'index')->name('sale-list.index')->middleware('permission:view_sale_list');
            Route::get('edit/{id}', 'edit')->name('sale-list.edit')->middleware('permission:update_sale_list');
            Route::get('show/{id}', 'show')->name('sale-list.show')->middleware('permission:view_sale_list');
            Route::put('{id}', 'update')->name('sale-list.update')->middleware('permission:update_sale_list');
            Route::post('add-payment', 'addPayment')->name('sale-list.add-payment')->middleware('permission:update_sale_list');
            Route::post('{id}/mark-delivered', 'markDelivered')->name('sale-list.markDelivered')->middleware('permission:update_sale_list');
            Route::delete('delete/{id}', 'destroy')->name('sale-list.destroy')->middleware('permission:delete_sale_list');
        });

        // Sale return
        Route::prefix('sale-return')->controller(SaleReturnController::class)->group(function () {
            Route::get('/', 'index')->name('sale-return.index')->middleware('permission:view_sale_return');
            Route::post('/approve/{id}', 'approve')->name('sale-return.approve')->middleware('permission:update_sale_return');
            Route::middleware('permission:create_sale_return')->group(function () {
                Route::get('/create', 'create')->name('sale-return.create');
                Route::post('/store', 'store')->name('sale-return.store');
            });
            Route::get('/show/{id}', 'show')->name('sale-return.show')->middleware('permission:view_sale_return');
            Route::middleware('permission:update_sale_return')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('sale-return.edit');
                Route::put('/update/{id}', 'update')->name('sale-return.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('sale-return.destroy')->middleware('permission:delete_sale_return');
        });

        // Purchase Return
        Route::prefix('purchase-return')->controller(PurchaseReturnController::class)->group(function () {
            Route::get('/', 'index')->name('purchase-return.index')->middleware('permission:view_purchase_return');
            Route::middleware('permission:create_purchase_return')->group(function () {
                Route::get('/create', 'create')->name('purchase-return.create');
                Route::post('/store', 'store')->name('purchase-return.store');
            });
            Route::middleware('permission:view_purchase_return')->group(function () {
                Route::get('/show/{id}', 'show')->name('purchase-return.show');
            });
            Route::middleware('permission:update_purchase_return')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('purchase-return.edit');
                Route::put('/update/{id}', 'update')->name('purchase-return.update');
                Route::post('/approve/{id}', 'approve')->name('purchase-return.approve');
            });
            Route::delete('/delete/{id}', 'destroy')->name('purchase-return.destroy')->middleware('permission:delete_purchase_return');
        });

        // Transfers
        Route::prefix('transfers')->controller(TransferController::class)->group(function () {
            Route::get('/', 'index')->name('transfers.index')->middleware('permission:view_transfer');
            Route::middleware('permission:create_transfer')->group(function () {
                Route::get('/create', 'create')->name('transfers.create');
                Route::post('/store', 'store')->name('transfers.store');
                Route::get('/get-product', 'getProduct')->name('transfers.get_product');
            });
            Route::middleware('permission:view_transfer')->group(function () {
                Route::get('/show/{id}', 'show')->name('transfers.show');
            });
            Route::middleware('permission:update_transfer')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('transfers.edit');
                Route::put('/update/{id}', 'update')->name('transfers.update');
                Route::post('/{id}/update-status', 'updateStatus')->name('transfers.update_status');
            });
            Route::delete('/delete/{id}', 'destroy')->name('transfers.destroy')->middleware('permission:delete_transfer');
        });

        // Purchase
        Route::prefix('purchases')->controller(PurchaseController::class)->group(function () {
            Route::get('/product-search', 'searchProduct')->name('purchases.product_search');
            Route::post('/purchase/add-payment', [PurchaseController::class, 'addPayment'])->name('purchases.add_payment')->middleware('permission:update_purchases');
            Route::get('/', 'index')->name('purchases.index')->middleware('permission:view_purchases');
            Route::get('/show/{id}', [PurchaseController::class, 'show'])->name('purchases.show')->middleware('permission:view_purchases');
            Route::middleware('permission:create_purchases')->group(function () {
                Route::get('/create', 'create')->name('purchases.create');
                Route::post('/store', 'store')->name('purchases.store');
            });
            Route::middleware('permission:update_purchases')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('purchases.edit');
                Route::put('/update/{id}', 'update')->name('purchases.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('purchases.destroy')->middleware('permission:delete_purchases');
        });

        // Inventory
        Route::prefix('inventory')->controller(InventoryController::class)->group(function () {
            Route::middleware('permission:view_inventory')->group(function () {
                Route::get('/', 'index')->name('inventory.index');
                Route::get('/show/{id}', 'show')->name('inventory.show');
                Route::post('/adjust-stock', 'adjustStock')->name('inventory.adjust');
                Route::get('/stock-history', 'stockHistory')->name('stock-history.index');
                Route::get('/stock-history/{id}', 'stockHistoryShow')->name('stock-history.show');
            });
        });

        // Products
        Route::prefix('products')->controller(ProductController::class)->group(function () {
            Route::middleware('permission:view_products')->group(function () {
                Route::get('/', 'index')->name('products.index');
                Route::get('/show/{id}', [ProductController::class, 'show'])->name('products.show');
            });
            Route::middleware('permission:create_products')->group(function () {
                Route::get('/create', 'create')->name('products.create');
                Route::post('/store', 'store')->name('products.store');
                Route::post('/upload-quill-image', 'uploadQuillImage')->name('products.upload-quill-image');
                Route::get('/search-for-combo', 'searchForCombo')->name('products.searchForCombo');
            });
            Route::middleware('permission:create_products')->controller(ProductImportController::class)->group(function () {
                Route::get('/import', 'index')->name('products.import');
                Route::get('/import/sample', 'downloadSample')->name('products.import.sample');
                Route::post('/import', 'import')->name('products.import.store');
            });
            Route::middleware('permission:update_products')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('products.edit');
                Route::put('/update/{id}', 'update')->name('products.update');
                Route::delete('/media/{id}', 'deleteMedia')->name('products.deleteMedia');
            });
            Route::middleware('permission:delete_products')->group(function () {
                Route::delete('/delete/{id}', 'destroy')->name('products.destroy');
                Route::post('/bulk-delete', 'bulkDelete')->name('products.bulkDelete');
            });
        });

        // Barcode
        Route::prefix('barcode')->controller(BarcodeController::class)->group(function () {
            Route::middleware('permission:view_print_barcode')->group(function () {
                Route::get('/', 'index')->name('barcode.index');
                Route::get('/search-product', 'searchProduct')->name('barcode.search_product');
                Route::post('/generate', 'generate')->name('barcode.generate');
            });
        });

        // Delivery
        Route::prefix('delivery')->controller(DeliveryController::class)->group(function () {
            Route::middleware('permission:view_delivery')->group(function () {
                Route::get('/', 'index')->name('delivery.index');
                Route::get('/get-sale-info/{id}', 'getSaleInfo')->name('delivery.get-sale-info');
                Route::get('/invoice/{id}', 'getDeliveryInvoice')->name('delivery.invoice');
            });
            Route::middleware('permission:create_delivery')->group(function () {
                Route::post('/store', 'store')->name('delivery.store');
            });
            Route::middleware('permission:update_delivery')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('delivery.edit');
                Route::put('/update', 'update')->name('delivery.update');
                Route::post('/update-status', 'updateStatus')->name('delivery.update-status');
                Route::post('/sync-steadfast-status', 'syncSteadfastStatus')->name('delivery.sync-steadfast');
                Route::post('/steadfast-balance', 'getSteadfastBalance')->name('delivery.steadfast-balance');
            });
            Route::middleware('permission:delete_delivery')->group(function () {
                Route::delete('/delete/{id}', 'destroy')->name('delivery.destroy');
            });
        });

        // Fraud Check API Settings
        Route::prefix('fraud-check-setting')->controller(FraudCheckSettingController::class)->group(function () {
            Route::middleware('permission:manage_api_integrations')->group(function () {
                Route::get('/', 'index')->name('fraud-check-setting.index');
                Route::post('/store', 'store')->name('fraud-check-setting.store');
            });
        });

        // Fraud Check
        Route::prefix('fraud-check')->controller(FraudCheckController::class)->group(function () {
            Route::post('/check', 'check')->name('fraud-check.ajax.check');
            Route::post('/test', 'test')->name('fraud-check.ajax.test')->middleware('permission:manage_api_integrations');
        });
        // Activity Log
        Route::prefix('activity-logs')->controller(ActivityLogController::class)->group(function () {
            Route::middleware('permission:view_activity_log')->group(function () {
                Route::get('/', 'index')->name('activity-logs.index');
                Route::get('/show/{id}', 'show')->name('activity-logs.show');
            });
        });

        // Quotation
        Route::prefix('quotations')->controller(QuotationController::class)->group(function () {
            Route::middleware('permission:view_quotations')->group(function () {
                Route::get('/', 'index')->name('quotations.index');
                Route::get('/show/{id}', 'show')->name('quotations.show');
                Route::get('/print/{id}', 'print')->name('quotations.print');
                Route::get('/gen-invoice/{id}', 'genInvoice')->name('quotations.gen-invoice');
                Route::get('/gen-purchase/{id}', 'genPurchase')->name('quotations.gen-purchase');
            });
            Route::middleware('permission:create_quotations')->group(function () {
                Route::get('/create', 'create')->name('quotations.create');
                Route::post('/store', 'store')->name('quotations.store');
                Route::get('/product-search', 'productSearch')->name('quotations.product_search');
            });
            Route::middleware('permission:update_quotations')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('quotations.edit');
                Route::put('/update/{id}', 'update')->name('quotations.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('quotations.destroy')->middleware('permission:delete_quotations');
        });

        // API Setting aliases (legacy/bookmark URLs)
        Route::middleware('permission:manage_api_integrations')->group(function () {
            Route::redirect('/couriers', '/api-setting/courier-settings');
            Route::redirect('/courier-settings', '/api-setting/courier-settings');
        });

        // API Setting
        Route::prefix('api-setting')->group(function () {
            Route::controller(SmsSettingController::class)->prefix('sms-settings')->middleware('permission:manage_api_integrations')->group(function () {
                Route::get('/', 'index')->name('sms_settings.index');
                Route::get('/create', 'create')->name('sms_settings.create');
                Route::post('/store', 'store')->name('sms_settings.store');
                Route::get('/edit/{id}', 'edit')->name('sms_settings.edit');
                Route::put('/update/{id}', 'update')->name('sms_settings.update');
                Route::delete('/delete/{id}', 'destroy')->name('sms_settings.destroy');
                Route::get('/balance/{id}', 'checkBalance')->name('sms_settings.balance');
                Route::post('/test/{id}', 'testSms')->name('sms_settings.test');
            });

            Route::controller(CourierSettingController::class)->prefix('courier-settings')->middleware('permission:manage_api_integrations')->group(function () {
                Route::get('/', 'index')->name('courier_settings.index');
                Route::get('/create', 'create')->name('courier_settings.create');
                Route::post('/store', 'store')->name('courier_settings.store');
                Route::get('/edit/{id}', 'edit')->name('courier_settings.edit');
                Route::put('/update/{id}', 'update')->name('courier_settings.update');
                Route::delete('/delete/{id}', 'destroy')->name('courier_settings.destroy');
            });
        });

        // SMS Log
        Route::prefix('sms-log')->controller(SmsLogController::class)->group(function () {
            Route::middleware('permission:view_sms_log')->group(function () {
                Route::get('/', 'index')->name('sms_logs.index');
            });
        });

        // SMS Template
        Route::prefix('sms-template')->controller(SmsTemplateController::class)->group(function () {
            Route::middleware('permission:view_sms_template')->group(function () {
                Route::get('/', 'index')->name('sms_templates.index');
            });
            Route::middleware('permission:create_sms_template')->group(function () {
                Route::get('/create', 'create')->name('sms_templates.create');
                Route::post('/store', 'store')->name('sms_templates.store');
            });
            Route::middleware('permission:update_sms_template')->group(function () {
                Route::get('/edit/{id}', 'edit')->name('sms_templates.edit');
                Route::put('/update/{id}', 'update')->name('sms_templates.update');
            });
            Route::delete('/delete/{id}', 'destroy')->name('sms_templates.destroy')->middleware('permission:delete_sms_template');
        });

        // Profile
        Route::prefix('profile')->controller(ProfileController::class)->group(function () {
            Route::get('/', 'edit')->name('profile.edit');
            Route::match(['put', 'patch'], '/update', 'update')->name('profile.update');
            Route::put('/password', 'requestPasswordOtp')->name('profile.password.update');
            Route::post('/otp/verify', 'verifyOtp')->name('profile.otp.verify');
            Route::post('/otp/resend', 'resendOtp')->name('profile.otp.resend');
            Route::delete('/', 'destroy')->name('profile.destroy');
        });

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::controller(ReportController::class)->group(function () {
                Route::get('/sales', 'salesReport')->name('sales')->middleware('permission:view_sales_report');
                Route::get('/summary', 'summaryReport')->name('summary')->middleware('permission:view_summary_report');
                Route::get('/customer-ledger', 'customerLedger')->name('customer_ledger')->middleware('permission:view_customer_ledger_report');
                Route::get('/supplier-ledger', 'supplierLedger')->name('supplier_ledger')->middleware('permission:view_supplier_ledger_report');
                Route::get('/inventory', 'inventoryReport')->name('inventory')->middleware('permission:view_inventory_report');
                Route::middleware('permission:view_employees_report')->group(function () {
                    Route::get('/employees', 'employeesReport')->name('employees');
                    Route::get('/employee-details/{id}', 'getEmployeeSaleDetails')->name('employee.details');
                });
            });

            // Branch Reports
            Route::prefix('branch')->name('branch.')->controller(BranchReportController::class)->group(function () {
                Route::middleware('permission:view_branch_report')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/summary', 'getBranchSummary')->name('summary');
                    Route::get('/sales', 'getBranchSales')->name('sales');
                    Route::get('/purchases', 'getBranchPurchases')->name('purchases');
                    Route::get('/expenses', 'getBranchExpenses')->name('expenses');
                    Route::get('/returns', 'getBranchReturns')->name('returns');
                });
            });

            // Purchase Reports
            Route::prefix('purchase')->name('purchase.')->controller(PurchaseReportController::class)->group(function () {
                Route::middleware('permission:view_purchases_report')->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/summary-data', 'getSummary')->name('summary');
                    Route::get('/product-data', 'productWise')->name('product');
                    Route::get('/supplier-data', 'supplierWise')->name('supplier');
                    Route::get('/daily-data', 'dailyData')->name('daily');
                });
            });
        });
    });
    require __DIR__.'/auth.php';
});

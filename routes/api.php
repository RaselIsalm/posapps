<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CatalogController;
use App\Http\Controllers\Api\V1\CommunicationController;
use App\Http\Controllers\Api\V1\CrmController;
use App\Http\Controllers\Api\V1\HrmController;
use App\Http\Controllers\Api\V1\ManagementReportsController;
use App\Http\Controllers\Api\V1\PosController;
use App\Http\Controllers\Api\V1\OperationsController;
use App\Http\Controllers\Api\V1\QuotationApiController;
use App\Http\Controllers\Api\V1\SalesFeaturesController;
use App\Http\Controllers\Api\V1\SystemController;
use App\Http\Controllers\Api\V1\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::get('/test', fn () => response()->json(['message' => 'API is working']));
Route::get('/admin/test', fn () => response()->json(['message' => 'API is working']));

Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::middleware(['auth:sanctum', 'active.user'])->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/profile/update', [AuthController::class, 'updateProfile']);
        Route::put('/change/password', [AuthController::class, 'updatePassword']);

        Route::get('/general-settings', [WorkspaceController::class, 'generalSettings']);
        Route::get('/dashboard', [WorkspaceController::class, 'dashboard']);
        Route::get('/reports', [WorkspaceController::class, 'reports']);

        Route::get('/product/search', [CatalogController::class, 'productSearch']);
        Route::get('/product/{id}/imeis', [CatalogController::class, 'availableImeis']);
        Route::get('/products', [CatalogController::class, 'products']);
        Route::get('/product/details/{id}', [CatalogController::class, 'productDetails']);
        Route::get('/add/product/info', [CatalogController::class, 'productInfo']);
        Route::post('/product/store', [CatalogController::class, 'storeProduct']);
        Route::put('/product/update/{id}', [CatalogController::class, 'updateProduct']);
        Route::get('/products/import/sample', [CatalogController::class, 'productImportSample']);
        Route::post('/products/import', [CatalogController::class, 'importProducts']);
        Route::post('/products/barcodes', [CatalogController::class, 'generateBarcodes']);
        Route::delete('/product/delete/{id}', [CatalogController::class, 'deleteProduct']);

        Route::get('/categories', [CatalogController::class, 'categories']);
        Route::get('/categories/parent-category', [CatalogController::class, 'parentCategories']);
        Route::post('/categories/store', [CatalogController::class, 'storeCategory']);
        Route::match(['post', 'put'], '/categories/update/{id}', [CatalogController::class, 'updateCategory']);
        Route::delete('/categories/delete/{id}', [CatalogController::class, 'deleteCategory']);
        Route::get('/brands', [CatalogController::class, 'brands']);
        Route::post('/brands/store', [CatalogController::class, 'storeBrand']);
        Route::match(['post', 'put'], '/brands/update/{id}', [CatalogController::class, 'updateBrand']);
        Route::delete('/brands/delete/{id}', [CatalogController::class, 'deleteBrand']);

        Route::get('/warehouses', [CatalogController::class, 'warehouses']);
        Route::get('/pos/branches', [CatalogController::class, 'branchesForPos']);
        Route::post('/warehouses/store', [CatalogController::class, 'storeWarehouse']);
        Route::put('/warehouses/update/{id}', [CatalogController::class, 'updateWarehouse']);
        Route::delete('/warehouses/delete/{id}', [CatalogController::class, 'deleteWarehouse']);

        Route::get('/customer/search', [PosController::class, 'customers']);
        Route::get('/customer/groups', [PosController::class, 'customerGroups']);
        Route::post('/customer/store', [PosController::class, 'storeCustomer']);
        Route::post('/apply/promo/code', [PosController::class, 'validateCoupon']);
        Route::get('/payment-gateway-list', [PosController::class, 'paymentOptions']);
        Route::post('/pos/store', [PosController::class, 'store']);
        Route::get('/drafts', [PosController::class, 'drafts']);
        Route::delete('/draft/delete/{id}', [PosController::class, 'deleteDraft']);
        Route::get('/sale/payment/success/{id}', [PosController::class, 'paymentSuccess']);
        Route::get('/invoice/{id}', [PosController::class, 'invoice']);

        Route::get('/purchase', [WorkspaceController::class, 'purchases']);
        Route::get('/purchase/pdf', [WorkspaceController::class, 'purchasesPdf']);
        Route::get('/purchase/export', [WorkspaceController::class, 'purchasesExport']);
        Route::get('/sales', [WorkspaceController::class, 'sales']);
        Route::get('/sales/{id}', [SalesFeaturesController::class, 'sale']);
        Route::put('/sales/{id}', [SalesFeaturesController::class, 'updateSale']);
        Route::post('/sales/{id}/payments', [SalesFeaturesController::class, 'addSalePayment']);
        Route::post('/sales/{id}/void', [SalesFeaturesController::class, 'voidSale']);
        Route::post('/sales/{id}/mark-delivered', [SalesFeaturesController::class, 'markSaleDelivered']);
        Route::get('/sale/pdf', [WorkspaceController::class, 'salesPdf']);
        Route::get('/sale/export', [WorkspaceController::class, 'salesExport']);
        Route::get('/expense', [WorkspaceController::class, 'expenses']);
        Route::get('/expenses', [OperationsController::class, 'expenses']);
        Route::get('/expenses/options', [OperationsController::class, 'expenseOptions']);
        Route::post('/expenses', [OperationsController::class, 'saveExpense']);
        Route::put('/expenses/{id}', [OperationsController::class, 'saveExpense']);
        Route::delete('/expenses/{id}', [OperationsController::class, 'deleteExpense']);
        Route::get('/expense-categories', [OperationsController::class, 'expenseCategories']);
        Route::post('/expense-categories', [OperationsController::class, 'saveExpenseCategory']);
        Route::put('/expense-categories/{id}', [OperationsController::class, 'saveExpenseCategory']);
        Route::delete('/expense-categories/{id}', [OperationsController::class, 'deleteExpenseCategory']);
        Route::get('/accounts', [WorkspaceController::class, 'accounts']);
        Route::get('/admin/accounts', [WorkspaceController::class, 'accounts']);
        Route::post('/accounts', [OperationsController::class, 'saveAccount']);
        Route::put('/accounts/{id}', [OperationsController::class, 'saveAccount']);
        Route::delete('/accounts/{id}', [OperationsController::class, 'deleteAccount']);
        Route::put('/accounts/{id}/default', [OperationsController::class, 'makeDefaultAccount']);
        Route::get('/accounts/statement', [OperationsController::class, 'statement']);
        Route::get('/balance-sheet', [OperationsController::class, 'balanceSheet']);
        Route::get('/balance', [WorkspaceController::class, 'balance']);
        Route::post('/balance/transfer', [WorkspaceController::class, 'addBalance']);

        Route::get('/inventory', [OperationsController::class, 'inventory']);
        Route::get('/operations/options', [OperationsController::class, 'options']);
        Route::get('/inventory/{id}', [OperationsController::class, 'inventoryDetail']);
        Route::post('/inventory/adjust', [OperationsController::class, 'adjustStock']);
        Route::get('/stock-history', [OperationsController::class, 'stockHistory']);
        Route::get('/stock-history/{id}', [OperationsController::class, 'stockHistoryDetail']);

        Route::get('/transfers', [OperationsController::class, 'transfers']);
        Route::get('/transfers/{id}', [OperationsController::class, 'transfer']);
        Route::post('/transfers', [OperationsController::class, 'storeTransfer']);
        Route::put('/transfers/{id}', [OperationsController::class, 'updateTransfer']);
        Route::patch('/transfers/{id}/status', [OperationsController::class, 'updateTransferStatus']);
        Route::delete('/transfers/{id}', [OperationsController::class, 'deleteTransfer']);

        Route::get('/purchases', [OperationsController::class, 'purchases']);
        Route::get('/purchases/{id}', [OperationsController::class, 'purchase']);
        Route::post('/purchases', [OperationsController::class, 'storePurchase']);
        Route::put('/purchases/{id}', [OperationsController::class, 'updatePurchase']);
        Route::post('/purchases/{id}/payments', [OperationsController::class, 'addPurchasePayment']);
        Route::delete('/purchases/{id}', [OperationsController::class, 'deletePurchase']);

        Route::get('/sale-returns', [OperationsController::class, 'saleReturns']);
        Route::get('/sale-returns/{id}', [OperationsController::class, 'saleReturn']);
        Route::post('/sale-returns', [OperationsController::class, 'storeSaleReturn']);
        Route::put('/sale-returns/{id}', [OperationsController::class, 'updateSaleReturn']);
        Route::post('/sale-returns/{id}/approve', [OperationsController::class, 'approveSaleReturn']);
        Route::delete('/sale-returns/{id}', [OperationsController::class, 'deleteSaleReturn']);
        Route::get('/purchase-returns', [OperationsController::class, 'purchaseReturns']);
        Route::get('/purchase-returns/{id}', [OperationsController::class, 'purchaseReturn']);
        Route::post('/purchase-returns', [OperationsController::class, 'storePurchaseReturn']);
        Route::put('/purchase-returns/{id}', [OperationsController::class, 'updatePurchaseReturn']);
        Route::post('/purchase-returns/{id}/approve', [OperationsController::class, 'approvePurchaseReturn']);
        Route::delete('/purchase-returns/{id}', [OperationsController::class, 'deletePurchaseReturn']);

        Route::get('/customer-groups', [OperationsController::class, 'customerGroups']);
        Route::post('/customer-groups', [OperationsController::class, 'saveCustomerGroup']);
        Route::put('/customer-groups/{id}', [OperationsController::class, 'saveCustomerGroup']);
        Route::delete('/customer-groups/{id}', [OperationsController::class, 'deleteCustomerGroup']);
        Route::get('/customers', [OperationsController::class, 'customers']);
        Route::post('/customers', [OperationsController::class, 'saveCustomer']);
        Route::put('/customers/{id}', [OperationsController::class, 'saveCustomer']);
        Route::delete('/customers/{id}', [OperationsController::class, 'deleteCustomer']);
        Route::get('/suppliers', [OperationsController::class, 'suppliers']);
        Route::post('/suppliers', [OperationsController::class, 'saveSupplier']);
        Route::put('/suppliers/{id}', [OperationsController::class, 'saveSupplier']);
        Route::delete('/suppliers/{id}', [OperationsController::class, 'deleteSupplier']);

        Route::get('/quotations/options', [QuotationApiController::class, 'options']);
        Route::get('/quotations', [QuotationApiController::class, 'index']);
        Route::get('/quotations/{id}', [QuotationApiController::class, 'show']);
        Route::post('/quotations', [QuotationApiController::class, 'store']);
        Route::put('/quotations/{id}', [QuotationApiController::class, 'update']);
        Route::delete('/quotations/{id}', [QuotationApiController::class, 'destroy']);
        Route::get('/quotations/{id}/convert', [QuotationApiController::class, 'conversionData']);

        Route::get('/gift-cards', [SalesFeaturesController::class, 'giftCards']);
        Route::post('/gift-cards', [SalesFeaturesController::class, 'storeGiftCard']);
        Route::get('/gift-cards/{id}', [SalesFeaturesController::class, 'giftCard']);
        Route::put('/gift-cards/{id}', [SalesFeaturesController::class, 'updateGiftCard']);
        Route::post('/gift-cards/{id}/recharge', [SalesFeaturesController::class, 'rechargeGiftCard']);
        Route::delete('/gift-cards/{id}', [SalesFeaturesController::class, 'deleteGiftCard']);

        Route::get('/coupons', [SalesFeaturesController::class, 'coupons']);
        Route::post('/coupons', [SalesFeaturesController::class, 'storeCoupon']);
        Route::put('/coupons/{id}', [SalesFeaturesController::class, 'updateCoupon']);
        Route::delete('/coupons/{id}', [SalesFeaturesController::class, 'deleteCoupon']);

        Route::get('/reward-points', [SalesFeaturesController::class, 'rewardSettings']);
        Route::put('/reward-points', [SalesFeaturesController::class, 'updateRewardSettings']);
        Route::get('/customers/{id}/rewards', [SalesFeaturesController::class, 'customerRewards']);

        Route::get('/couriers', [SalesFeaturesController::class, 'couriers']);
        Route::get('/deliveries/sales', [SalesFeaturesController::class, 'deliverySales']);
        Route::get('/deliveries', [SalesFeaturesController::class, 'deliveries']);
        Route::post('/deliveries', [SalesFeaturesController::class, 'storeDelivery']);
        Route::put('/deliveries/{id}', [SalesFeaturesController::class, 'updateDelivery']);
        Route::patch('/deliveries/{id}/status', [SalesFeaturesController::class, 'updateDeliveryStatus']);
        Route::delete('/deliveries/{id}', [SalesFeaturesController::class, 'deleteDelivery']);

        // Mobile CRM pages. Permissions are checked within the resource controller.
        Route::prefix('crm')->group(function () {
            Route::get('/{resource}/options', [CrmController::class, 'options']);
            Route::get('/{resource}', [CrmController::class, 'index']);
            Route::post('/{resource}', [CrmController::class, 'store']);
            Route::get('/{resource}/{id}', [CrmController::class, 'show'])->whereNumber('id');
            Route::match(['put', 'post'], '/{resource}/{id}', [CrmController::class, 'update'])->whereNumber('id');
            Route::delete('/{resource}/{id}', [CrmController::class, 'destroy'])->whereNumber('id');
            Route::post('/leads/{id}/notes', [CrmController::class, 'addNote']);
            Route::post('/leads/{id}/convert', [CrmController::class, 'convertLead']);
        });

        // Employee, attendance, department and payroll pages.
        Route::prefix('hrm')->group(function () {
            Route::post('/payrolls/employee-data', [HrmController::class, 'payrollEmployeeData']);
            Route::get('/{resource}/options', [HrmController::class, 'options']);
            Route::get('/{resource}', [HrmController::class, 'index']);
            Route::post('/{resource}', [HrmController::class, 'store']);
            Route::get('/{resource}/{id}', [HrmController::class, 'show'])->whereNumber('id');
            Route::match(['put', 'post'], '/{resource}/{id}', [HrmController::class, 'update'])->whereNumber('id');
            Route::delete('/{resource}/{id}', [HrmController::class, 'destroy'])->whereNumber('id');
        });

        Route::get('/management-reports/{report}', [ManagementReportsController::class, 'show']);

        // SMS logs/templates and external service configuration.
        Route::prefix('communication')->group(function () {
            Route::get('/{resource}', [CommunicationController::class, 'index']);
            Route::post('/{resource}', [CommunicationController::class, 'store']);
            Route::get('/{resource}/{id}', [CommunicationController::class, 'show'])->whereNumber('id');
            Route::match(['put', 'post'], '/{resource}/{id}', [CommunicationController::class, 'update'])->whereNumber('id');
            Route::delete('/{resource}/{id}', [CommunicationController::class, 'destroy'])->whereNumber('id');
            Route::post('/sms-settings/{id}/test', [CommunicationController::class, 'testSms']);
            Route::get('/sms-settings/{id}/balance', [CommunicationController::class, 'smsBalance']);
            Route::post('/fraud-check/test', [CommunicationController::class, 'testFraudCheck']);
        });
        

        // Website configuration and administration, exposed as JSON for the mobile app.
        Route::prefix('system')->group(function () {
            Route::get('/settings/{section}', [SystemController::class, 'settings']);
            Route::match(['put', 'post'], '/settings/{section}', [SystemController::class, 'updateSettings']);
            Route::post('/settings/mail/test', [SystemController::class, 'testMail']);
            Route::post('/settings/color/reset', [SystemController::class, 'resetColorSettings']);
            Route::get('/backups', [SystemController::class, 'backups']);
            Route::put('/backups/settings', [SystemController::class, 'updateBackupSettings']);
            Route::post('/backups/test-remote', [SystemController::class, 'testBackupRemote']);
            Route::post('/backups/run', [SystemController::class, 'runBackup']);
            Route::get('/backups/{id}/download', [SystemController::class, 'downloadBackup']);
            Route::delete('/backups/{id}', [SystemController::class, 'deleteBackup']);
            Route::post('/backups/{id}/restore', [SystemController::class, 'restoreBackup']);
            Route::get('/users/{id}/removal-preview', [SystemController::class, 'userRemovalPreview']);
            Route::post('/users/{id}/activate', [SystemController::class, 'activateUser']);
            Route::get('/{resource}', [SystemController::class, 'index']);
            Route::post('/{resource}', [SystemController::class, 'store']);
            Route::get('/{resource}/{id}', [SystemController::class, 'show'])->whereNumber('id');
            Route::match(['put', 'post'], '/{resource}/{id}', [SystemController::class, 'update'])->whereNumber('id');
            Route::delete('/{resource}/{id}', [SystemController::class, 'destroy'])->whereNumber('id');
        });
    });
});

<?php

namespace App\Http\Controllers;

use App\Helpers\InvoiceHelper;
use App\DataTables\SaleDataTables;
use App\Http\Requests\AddPaymentRequest;
use App\Http\Requests\UpdateSaleRequest;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Services\SaleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class SaleListController extends Controller
{
    protected SaleService $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    /**
     * Display the sale list with DataTable.
     */
    public function index(SaleDataTables $dataTable)
    {
        $accounts = Account::where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return $dataTable->render('backend.sale_list.index', compact('accounts', 'branches'));
    }

    /**
     * Show sale invoice details via AJAX modal (global invoice design).
     * Optional query overrides are not used; controllers pass array overrides via InvoiceHelper.
     */
    public function show($id)
    {
        if (request()->ajax()) {
            $sale = Sale::withoutGlobalScope('not_voided')
                ->with(['customer', 'branch', 'productSales.product', 'payments', 'seller'])
                ->findOrFail($id);

            $invoice = InvoiceHelper::fromSale($sale);

            return view('backend.components.invoice.content', compact('invoice'));
        }

        return abort(404);
    }

    /**
     * Show the edit form for a sale.
     */
    public function edit($id)
    {
        $sale = Sale::with(['customer', 'productSales.product', 'payments'])->findOrFail($id);

        if ($sale->isVoided()) {
            return redirect()->route('sale-list.index')->with('error', __('Cannot edit a voided sale.'));
        }

        $branches = Branch::all();

        // Prefer the sale's customer first, then a limited list for Select2
        $customers = Customer::select('id', 'name', 'phone')
            ->when($sale->customer_id, function ($q) use ($sale) {
                $q->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$sale->customer_id]);
            })
            ->limit(200)
            ->get();

        $products = Product::select('id', 'product_title', 'product_code', 'selling_price')->get();
        $sellers = Employee::where('status', 1)->orderBy('name')->get(['id', 'name', 'phone']);
        $departments = Department::where('status', 1)->orderBy('name')->get(['id', 'name']);
        $branchManagers = User::orderBy('name')->get();
        $accounts = Account::where('is_active', 1)->orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        $currentPaymentAccountId = null;
        $firstPaymentMethod = optional($sale->payments->first())->payment_method;
        if ($firstPaymentMethod) {
            $currentPaymentAccountId = optional(
                $accounts->first(function ($account) use ($firstPaymentMethod) {
                    return strcasecmp((string) $account->name, (string) $firstPaymentMethod) === 0;
                })
            )->id;
        }

        return view('backend.sale_list.edit', compact(
            'sale',
            'customers',
            'branches',
            'products',
            'sellers',
            'departments',
            'branchManagers',
            'accounts',
            'employeeRoles',
            'currentPaymentAccountId'
        ));
    }

    /**
     * Mark a counter / fulfilled sale as delivered (no courier needed).
     */
    public function markDelivered($id)
    {
        if (! auth()->user()?->can('update_sale_list')) {
            abort(403, 'Unauthorized action.');
        }

        $sale = Sale::findOrFail($id);
        $status = strtolower((string) $sale->delivery_status);

        if (! in_array($status, ['pending', 'shipped', 'completed'], true)) {
            return redirect()->back()->with('error', 'This sale is already completed for delivery.');
        }

        $sale->delivery_status = 'delivered';
        $sale->save();

        return redirect()->back()->with('success', 'Sale marked as delivered.');
    }

    /**
     * Add a due payment to an existing sale.
     * Uses AddPaymentRequest for validation + authorization.
     */
    public function addPayment(AddPaymentRequest $request)
    {
        try {
            $this->saleService->addPayment($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Payment added successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add payment to sale', [
                'sale_id' => $request->sale_id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->getSafeErrorMessage($e),
            ], 500);
        }
    }

    /**
     * Update an existing sale.
     * Uses UpdateSaleRequest for validation + authorization.
     */
    public function update(UpdateSaleRequest $request, $id)
    {
        try {
            $sale = Sale::findOrFail($id);

            $this->saleService->updateSale($id, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Sale updated successfully!',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update sale', [
                'sale_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $this->getSafeErrorMessage($e),
            ], 500);
        }
    }

    /**
     * Void a sale (keep audit record) and fully reverse stock + money.
     */
    public function destroy($id)
    {
        if (! auth()->user()?->can('delete_sale_list')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            Sale::withoutGlobalScope('not_voided')->findOrFail($id);

            $this->saleService->voidSale((int) $id);

            return redirect()->back()->with('success', __('Sale voided. Stock and account payments were reversed. The invoice was kept for audit.'));
        } catch (\Exception $e) {
            Log::error('Failed to void sale', [
                'sale_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', $this->getSafeErrorMessage($e));
        }
    }

    /**
     * Return a user-safe error message.
     * Shows business logic errors directly, hides system errors.
     */
    private function getSafeErrorMessage(\Exception $e): string
    {
        // Known business exceptions — safe to show to users
        $safePatterns = [
            'Paying amount cannot be greater',
            'Cannot delete this sale',
            'Cannot void this sale',
            'already voided',
            'Insufficient stock',
            'Insufficient reward points',
            'Minimum purchase',
            'Invalid Gift Card',
        ];

        foreach ($safePatterns as $pattern) {
            if (str_contains($e->getMessage(), $pattern)) {
                return $e->getMessage();
            }
        }

        // Unknown/system errors — hide details from users
        return 'An unexpected error occurred. Please try again or contact support.';
    }
}

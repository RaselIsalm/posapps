<?php

namespace App\Http\Controllers;

use App\DataTables\PurchasesDataTables;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PurchaseController extends Controller
{
    protected $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function index(PurchasesDataTables $dataTable)
    {
        $accounts = Account::where('is_active', 1)->get();
        $branches = Branch::orderBy('name')->get(['id', 'name']);

        return $dataTable->render('backend.purchase.index', compact('accounts', 'branches'));
    }

    public function searchProduct(Request $request)
    {
        $query = $request->get('search');

        if ($query == '') {
            return response()->json([]);
        }

        $products = Product::with(['productVariants', 'unit'])
            ->where('product_title', 'LIKE', "%{$query}%")
            ->orWhere('product_code', 'LIKE', "%{$query}%")
            ->limit(10)
            ->get();

        $results = [];

        foreach ($products as $product) {
            if ($product->productVariants->count() > 0) {
                foreach ($product->productVariants as $variant) {
                    $results[] = [
                        'id' => $product->id,
                        'product_title' => $product->product_title.' - '.($variant->name ?? $variant->variant_code ?? ''),
                        'product_code' => $variant->variant_code ?? $product->product_code,
                        'product_cost' => $variant->costFromBase($product->product_cost),
                        'variant_id' => $variant->id,
                        'has_imei' => $product->has_imei,
                        'tax_method' => $product->tax_method,
                        'tax' => $product->tax ?? 0,
                        'unit' => $product->unit,
                    ];
                }
            } else {
                $results[] = [
                    'id' => $product->id,
                    'product_title' => $product->product_title,
                    'product_code' => $product->product_code,
                    'product_cost' => $product->product_cost,
                    'variant_id' => null,
                    'has_imei' => $product->has_imei,
                    'tax_method' => $product->tax_method,
                    'tax' => $product->tax ?? 0,
                    'unit' => $product->unit,
                ];
            }
        }

        return response()->json($results);
    }

    public function create(Request $request)
    {
        $branches = Branch::where('status', 1)->get();
        $currencies = \App\Models\Currency::active()->get();
        $suppliers = Supplier::where('is_active', 1)->get();
        $invoice_no = $this->generateInvoiceNo();
        $accounts = Account::where('is_active', 1)->orderBy('name')->get();

        $quotation = null;
        if (request()->has('quotation_id')) {
            $quotation = Quotation::with(['items.product.unit', 'items.variant'])->findOrFail($request->quotation_id);
        }

        $employees = User::all();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.purchase.create', compact('branches', 'suppliers', 'invoice_no', 'accounts', 'quotation', 'employees', 'currencies', 'employeeRoles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'qty' => 'required|array',
            'net_unit_cost' => 'required|array',
            'status' => 'required|in:ordered,pending,received',
            'document' => 'nullable|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
            'expire_date.*' => 'nullable|date|after_or_equal:today',
        ], [
            'expire_date.*.after_or_equal' => 'Expire date cannot be in the past.',
        ]);

        try {
            $documentName = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $documentName = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/purchases'), $documentName);
            }

            $data = $request->all();
            $data['invoice_no'] = $this->generateInvoiceNo();

            $purchase = $this->purchaseService->storePurchase($data, $documentName);

            if ($request->ajax() || $request->wantsJson()) {
                session()->flash('success', 'Purchase created successfully! Invoice: '.$purchase->invoice_no);
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase created successfully! Invoice: '.$purchase->invoice_no,
                    'redirect' => route('purchases.index')
                ]);
            }

            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully! Invoice: '.$purchase->invoice_no);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: '.$e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $purchase = Purchase::with(['supplier', 'branch', 'details.product', 'details.variant', 'creator'])
            ->findOrFail($id);

        $invoice = \App\Helpers\InvoiceHelper::fromPurchase($purchase);

        return view('backend.components.invoice.content', compact('invoice'))->render();
    }

    public function edit($id)
    {
        $purchase = Purchase::with('details.product', 'details.variant')->findOrFail($id);
        $branches = Branch::where('status', 1)->get();
        $currencies = \App\Models\Currency::active()->get();
        $suppliers = Supplier::where('is_active', 1)->get();
        $accounts = Account::where('is_active', 1)->orderBy('name')->get();
        $employees = User::all();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.purchase.edit', compact('purchase', 'branches', 'suppliers', 'accounts', 'currencies', 'employees', 'employeeRoles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'date' => 'required|date',
            'product_id' => 'required|array',
            'qty' => 'required|array',
            'net_unit_cost' => 'required|array',
            'status' => 'required|in:ordered,pending,received',
            'expire_date.*' => 'nullable|date|after_or_equal:today',
        ], [
            'expire_date.*.after_or_equal' => 'Expire date cannot be in the past.',
        ]);

        try {
            $documentName = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $documentName = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/purchases'), $documentName);
            }

            $purchase = $this->purchaseService->updatePurchase($id, $request->all(), $documentName);

            return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->purchaseService->deletePurchase($id);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Purchase deleted & amount refunded successfully!'
                ]);
            }

            return redirect()->back()->with('success', 'Purchase deleted & amount refunded successfully!');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: '.$e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function addPayment(Request $request)
    {
        // Server-side authorization check (Double-security)
        if (! auth()->user()?->can('update_purchases')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access: You do not have permission to add payments.',
            ], 403);
        }

        $request->validate([
            'purchase_id' => 'required',
            'payment_method' => 'required',
            'amount' => 'required|numeric|min:1|max:9999999999999.99',
            'date' => 'required',
        ], [
            'amount.max' => 'Payment amount is too large. Please enter a valid amount.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be at least 1.',
        ]);

        DB::beginTransaction();
        try {
            $account = Account::find($request->payment_method);
            if ($account && $account->current_balance < $request->amount) {
                return response()->json([
                    'success' => false,
                    'message' => "Transaction Failed: Insufficient funds in your {$account->name} account. Available balance: {$account->current_balance}",
                ], 400);
            }

            $purchase = Purchase::findOrFail($request->purchase_id);
            $newPaidAmount = $purchase->paid_amount + $request->amount;

            if ($newPaidAmount > $purchase->grand_total) {
                return response()->json([
                    'success' => false,
                    'message' => 'Amount cannot exceed due amount',
                ], 400);
            }

            $purchase->paid_amount = $newPaidAmount;
            $purchase->due_amount = $purchase->grand_total - $newPaidAmount;
            $purchase->save();

            $supplier = Supplier::find($purchase->supplier_id);
            if ($supplier) {
                $supplier->decrement('balance', $request->amount);
            }

            TransactionService::createTransaction(
                $request->payment_method,
                'debit',
                $request->amount,
                'Due Payment for Purchase: '.$purchase->invoice_no,
                $request->date,
                $purchase
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'message' => 'Payment failed. Please check the amount and account balance, then try again.',
            ], 500);
        }
    }

    private function generateInvoiceNo()
    {
        $year = date('Y');
        $latestPurchase = Purchase::latest()->first();

        if ($latestPurchase) {
            $parts = explode('-', $latestPurchase->invoice_no);
            if (isset($parts[1]) && $parts[1] == $year) {
                $newNumber = intval($parts[2]) + 1;
            } else {
                $newNumber = 1;
            }
        } else {
            $newNumber = 1;
        }

        return 'PUR-'.$year.'-'.sprintf('%05d', $newNumber);
    }
}

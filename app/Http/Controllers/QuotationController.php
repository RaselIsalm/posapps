<?php

namespace App\Http\Controllers;

use App\DataTables\QuotationDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class QuotationController extends Controller
{
    /**
     * Quotation List (DataTable)
     */
    public function index(QuotationDataTable $dataTable)
    {
        return $dataTable->render('backend.quotation.index');
    }

    /**
     * Show the form for creating a new Quotation
     */
    public function create()
    {
        $branches = Branch::where('status', 1)->get();
        $customers = Customer::where('status', 1)->get();
        $suppliers = Supplier::where('is_active', 1)->get();
        $employees = Employee::where('status', 1)->get();
        $accounts = Account::where('is_active', 1)->get();
        $branchManagers = User::orderBy('name')->get();
        $departments = Department::where('status', 1)->orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        $lastQuotation = Quotation::latest('id')->first();
        $lastId = $lastQuotation ? $lastQuotation->id + 1 : 1;
        $reference_no = 'QUO-'.date('Ymd').'-'.str_pad($lastId, 4, '0', STR_PAD_LEFT);

        return view('backend.quotation.create', compact(
            'branches',
            'customers',
            'suppliers',
            'employees',
            'reference_no',
            'accounts',
            'branchManagers',
            'departments',
            'employeeRoles'
        ));
    }

    public function genInvoice($id)
    {
        return redirect()->route('quick-sale.index', ['quotation_id' => $id]);
    }

    public function genPurchase($id)
    {
        return redirect()->route('purchases.create', ['quotation_id' => $id]);
    }

    public function print($id)
    {
        $quotation = Quotation::with(['items.product', 'items.variant', 'customer', 'supplier', 'branch', 'seller'])->findOrFail($id);

        $invoice = \App\Helpers\InvoiceHelper::fromQuotation($quotation);

        return view('backend.components.invoice.a4_page', compact('invoice'));
    }

    /**
     * Store a newly created Quotation in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'branch_id' => 'required',
            'employee_id' => 'required',
            'account_id' => 'required',
            'product_id' => 'required|array',
            'qty' => 'required|array',
            'document' => 'nullable|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        DB::beginTransaction();

        try {
            $documentPath = null;
            if ($request->hasFile('document')) {
                $document = $request->file('document');
                $documentPath = time().'_'.$document->getClientOriginalName();
                $document->move(public_path('uploads/quotations'), $documentPath);
            }

            $quotation = Quotation::create([
                'reference_no' => $request->reference_no,
                'date' => $request->date,
                'expiry_date' => $request->expiry_date,
                'seller_id' => $request->employee_id,
                'customer_id' => $request->customer_id ?? null,
                'supplier_id' => $request->supplier_id ?? null,
                'branch_id' => $request->branch_id,
                'account_id' => $request->account_id,

                'item_count' => count($request->product_id),
                'total_qty' => array_sum($request->qty),
                'total_discount' => array_sum($request->discount ?? []),
                'total_tax' => array_sum($request->tax ?? []),
                'total_price' => array_sum($request->total ?? []),

                'order_tax_rate' => $request->order_tax_rate ?? 0,
                'order_tax' => $request->order_tax ?? 0,
                'order_discount' => $request->order_discount ?? 0,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'grand_total' => $request->grand_total ?? 0,

                'status' => 'pending',
                'document' => $documentPath,
                'note' => $request->note,
                'created_by' => auth()->id(),
            ]);

            if ($request->product_id) {
                foreach ($request->product_id as $key => $prod_id) {
                    QuotationProduct::create([
                        'quotation_id' => $quotation->id,
                        'product_id' => $prod_id,
                        'variant_id' => $request->variant_id[$key] ?? null,
                        'qty' => $request->qty[$key],
                        'net_unit_price' => $request->net_unit_price[$key],
                        'discount' => $request->discount[$key] ?? 0,
                        'tax_rate' => $request->tax_rate[$key] ?? 0,
                        'tax' => $request->tax[$key] ?? 0,
                        'total' => $request->total[$key],
                        'imei_number' => $request->imei_numbers[$key] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('quotations.index')->with('success', 'Quotation created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage())->withInput();
        }
    }

    /**
     * View Quotation Details
     */
    public function show($id)
    {
        $quotation = Quotation::with([
            'items.product',
            'items.variant',
            'customer',
            'supplier',
            'branch',
            'seller',
            'creator',
        ])->findOrFail($id);

        $invoice = \App\Helpers\InvoiceHelper::fromQuotation($quotation);

        return view('backend.components.invoice.content', compact('invoice'))->render();
    }

    public function edit($id)
    {
        $quotation = Quotation::with(['items.product', 'items.variant'])->findOrFail($id);
        $branches = Branch::where('status', 1)->get();
        $customers = Customer::where('status', 1)->get();
        $suppliers = Supplier::where('is_active', 1)->get();
        $employees = Employee::where('status', 1)->get();
        $accounts = Account::where('is_active', 1)->get();
        $branchManagers = User::orderBy('name')->get();
        $departments = Department::where('status', 1)->orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.quotation.edit', compact(
            'quotation',
            'branches',
            'customers',
            'suppliers',
            'employees',
            'accounts',
            'branchManagers',
            'departments',
            'employeeRoles'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'branch_id' => 'required',
            'employee_id' => 'required',
            'account_id' => 'required',
            'product_id' => 'required|array',
            'document' => 'nullable|mimes:pdf,jpg,jpeg,png,doc,docx|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $quotation = Quotation::findOrFail($id);

            $documentPath = $quotation->document;
            if ($request->hasFile('document')) {
                $document = $request->file('document');
                $documentPath = time().'_'.$document->getClientOriginalName();
                $document->move(public_path('uploads/quotations'), $documentPath);
            }

            $quotation->update([
                'date' => $request->date,
                'expiry_date' => $request->expiry_date,
                'seller_id' => $request->employee_id,
                'customer_id' => $request->customer_id,
                'supplier_id' => $request->supplier_id,
                'branch_id' => $request->branch_id,
                'account_id' => $request->account_id,
                'item_count' => count($request->product_id),
                'total_qty' => array_sum($request->qty),
                'total_discount' => array_sum($request->discount ?? []),
                'total_tax' => array_sum($request->tax ?? []),
                'total_price' => array_sum($request->total ?? []),
                'order_tax_rate' => $request->order_tax_rate,
                'order_tax' => $request->order_tax,
                'order_discount' => $request->order_discount,
                'shipping_cost' => $request->shipping_cost,
                'grand_total' => $request->grand_total,
                'note' => $request->note,
                'document' => $documentPath,
            ]);

            QuotationProduct::where('quotation_id', $id)->delete();

            foreach ($request->product_id as $key => $prod_id) {
                QuotationProduct::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $prod_id,
                    'variant_id' => $request->variant_id[$key] ?? null,
                    'qty' => $request->qty[$key],
                    'net_unit_price' => $request->net_unit_price[$key],
                    'discount' => $request->discount[$key] ?? 0,
                    'tax_rate' => $request->tax_rate[$key] ?? 0,
                    'tax' => $request->tax[$key] ?? 0,
                    'total' => $request->total[$key],
                    'imei_number' => $request->imei_numbers[$key] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('quotations.index')->with('success', 'Quotation updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    /**
     * Delete Quotation
     */
    public function destroy($id)
    {
        try {
            $quotation = Quotation::findOrFail($id);
            QuotationProduct::where('quotation_id', $id)->delete();
            $quotation->delete();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Quotation deleted successfully!']);
            }
            return redirect()->route('quotations.index')->with('success', 'Quotation deleted successfully!');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting quotation: '.$e->getMessage()], 400);
            }
            return redirect()->back()->with('error', 'Error deleting quotation: '.$e->getMessage());
        }
    }

    /**
     * AJAX Product Search (For Create Form)
     */
    public function productSearch(Request $request)
    {
        $query = $request->get('search');

        if ($query == '') {
            return response()->json([]);
        }

        $products = Product::with(['productVariants', 'unit'])
            ->where(function ($q) use ($query) {
                $q->where('product_title', 'LIKE', "%{$query}%")
                    ->orWhere('product_code', 'LIKE', "%{$query}%");
            })
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
                        'price' => $variant->sellingPriceFromBase($product->selling_price),
                        'variant_id' => $variant->id,
                        'tax_rate' => $product->tax ?? 0,
                        'has_imei' => $product->has_imei,
                        'unit' => ['name' => $product->unit->name ?? 'Piece']
                    ];
                }
            } else {
                $results[] = [
                    'id' => $product->id,
                    'product_title' => $product->product_title,
                    'product_code' => $product->product_code,
                    'price' => $product->selling_price,
                    'variant_id' => null,
                    'tax_rate' => $product->tax ?? 0,
                    'has_imei' => $product->has_imei,
                    'unit' => ['name' => $product->unit->name ?? 'Piece']
                ];
            }
        }

        return response()->json($results);
    }
}

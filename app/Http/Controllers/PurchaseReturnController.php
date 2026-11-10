<?php

namespace App\Http\Controllers;

use App\DataTables\PurchaseReturnDataTable;
use App\Models\Branch;
use App\Models\ProductImei;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseReturnController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(PurchaseReturnDataTable $dataTable)
    {
        $branches = Branch::all();

        return $dataTable->render('backend.return.purchase.index', compact('branches'));
    }

    public function create(Request $request)
    {
        if ($request->ajax() && $request->has('purchase_reference')) {
            try {
                $viewPath = 'backend.return.purchase.partials.create_form';

                $purchase = Purchase::with(['details.product', 'details.variant', 'supplier', 'branch'])
                    ->where('invoice_no', $request->purchase_reference)
                    ->first();

                if (! $purchase) {
                    return response()->json(['status' => 'error', 'message' => 'Purchase Invoice not found!']);
                }

                if (! view()->exists($viewPath)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'View file not found! Create: resources/views/'.str_replace('.', '/', $viewPath).'.blade.php',
                    ]);
                }

                return view($viewPath, compact('purchase'))->render();
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'System Error: '.$e->getMessage()]);
            }
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'purchase_id' => 'required',
            'return_items' => 'required|array',
            'qty' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $purchase = Purchase::findOrFail($request->purchase_id);

            $return = new PurchaseReturn;
            $return->reference_no = 'PR-'.date('Ymd').'-'.strtoupper(Str::random(4));
            $return->purchase_id = $purchase->id;
            $return->supplier_id = $purchase->supplier_id;
            $return->branch_id = $purchase->branch_id;
            $return->created_by = Auth::id();
            $return->return_date = date('Y-m-d');
            $return->status = 'pending';
            $return->payment_status = 'due';
            $return->return_note = $request->return_note;
            $return->staff_note = $request->staff_note;
            $return->total_qty = 0;
            $return->grand_total = 0;
            $return->save();

            $totalQty = 0;
            $grandTotal = 0;

            foreach ($request->return_items as $key => $productId) {
                $returnQty = (float) $request->qty[$key];
                $unitPrice = (float) $request->unit_price[$key];
                $variantId = $request->variant_id[$key] ?? null;
                $selectedImeis = $request->imei_number[$key] ?? null;
                $imeiString = is_array($selectedImeis) ? implode(',', $selectedImeis) : $selectedImeis;

                if ($variantId === 'null' || $variantId === '') {
                    $variantId = null;
                }

                if ($returnQty <= 0) {
                    continue;
                }

                $lineTotal = $returnQty * $unitPrice;

                PurchaseReturnDetail::create([
                    'purchase_return_id' => $return->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => $returnQty,
                    'unit_price' => $unitPrice,
                    'total' => $lineTotal,
                    'imei_number' => $imeiString,
                ]);

                $totalQty += $returnQty;
                $grandTotal += $lineTotal;
            }

            $return->update([
                'total_qty' => $totalQty,
                'grand_total' => $grandTotal,
            ]);

            activity_log('created purchase return', 'return ref: '.$return->reference_no);

            DB::commit();

            return redirect()->route('purchase-return.index')
                ->with('success', 'Return Request Submitted Successfully! Waiting for Approval.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'System Error: '.$e->getMessage());
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $return = PurchaseReturn::with(['details', 'purchase'])->findOrFail($id);

            if ($return->status == 'approved') {
                return response()->json(['success' => false, 'message' => 'Already Approved!']);
            }

            foreach ($return->details as $detail) {
                // Use Service for consistent stock management
                $this->inventoryService->decrementStock(
                    $return->branch_id,
                    $detail->product_id,
                    $detail->variant_id,
                    $detail->qty,
                    'purchase_return',
                    $return->reference_no,
                    true // Validate stock
                );

                // ৩. স্পেসিফিক IMEI আপডেট
                if (! empty($detail->imei_number)) {
                    $imeiArray = explode(',', $detail->imei_number);

                    ProductImei::whereIn('imei_number', $imeiArray)->update([
                        'status' => 'returned_to_supplier',
                        'is_sold' => 0,
                        'purchase_return_id' => $return->id,
                    ]);
                }
            }

            // ৪. অ্যাকাউন্টিং এবং ডিউ (Due) অ্যাডজাস্টমেন্ট
            $purchase = $return->purchase;
            $remainingReturnAmount = $return->grand_total;

            if ($purchase && $purchase->due_amount > 0) {
                if ($purchase->due_amount >= $remainingReturnAmount) {
                    $purchase->decrement('due_amount', $remainingReturnAmount);
                    $remainingReturnAmount = 0;
                } else {
                    $remainingReturnAmount -= $purchase->due_amount;
                    $purchase->update(['due_amount' => 0, 'payment_status' => 'paid']);
                }
            }

            // The supplier balance contains this purchase's outstanding due.
            // Reduce it by the full return amount, including the portion that
            // was applied against the purchase's due above.
            if ($return->grand_total > 0 && $return->supplier_id) {
                $supplier = Supplier::find($return->supplier_id);
                if ($supplier) {
                    $supplier->decrement('balance', $return->grand_total);
                }
            }

            $return->update([
                'status' => 'approved',
                'payment_status' => 'paid',
            ]);

            activity_log('approved purchase return', 'return ref: '.$return->reference_no);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Approved! Stock Decremented & Supplier Balance Adjusted.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }

    public function show($id)
    {
        if (request()->ajax()) {
            try {
                $return = PurchaseReturn::with(['purchase', 'supplier', 'branch', 'details.product', 'details.variant', 'user'])->findOrFail($id);

                $invoice = \App\Helpers\InvoiceHelper::fromPurchaseReturn($return);

                return view('backend.components.invoice.content', compact('invoice'))->render();
            } catch (\Exception $e) {
                return '<div class="text-danger p-3">Error loading details</div>';
            }
        }

        return abort(404);
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $return = PurchaseReturn::with(['details', 'purchase.details.product', 'purchase.details.variant'])->findOrFail($id);

            if ($return->status == 'approved') {
                return '<div class="alert alert-warning">Approved returns cannot be edited!</div>';
            }

            return view('backend.return.purchase.partials.edit_form', compact('return'))->render();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'return_items' => 'required|array',
            'qty' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $return = PurchaseReturn::findOrFail($id);

            if ($return->status == 'approved') {
                return redirect()->back()->with('error', 'Cannot update approved return!');
            }

            $return->details()->delete();

            $totalQty = 0;
            $grandTotal = 0;

            foreach ($request->return_items as $key => $productId) {
                $returnQty = (float) $request->qty[$key];
                $unitPrice = (float) $request->unit_price[$key];
                $variantId = $request->variant_id[$key] ?? null;

                $selectedImeis = $request->imei_number[$key] ?? null;
                $imeiString = is_array($selectedImeis) ? implode(',', $selectedImeis) : $selectedImeis;

                if ($variantId === 'null' || $variantId === '') {
                    $variantId = null;
                }

                if ($returnQty > 0) {
                    $lineTotal = $returnQty * $unitPrice;

                    PurchaseReturnDetail::create([
                        'purchase_return_id' => $return->id,
                        'product_id' => $productId,
                        'variant_id' => $variantId,
                        'qty' => $returnQty,
                        'unit_price' => $unitPrice,
                        'total' => $lineTotal,
                        'imei_number' => $imeiString,
                    ]);

                    $totalQty += $returnQty;
                    $grandTotal += $lineTotal;
                }
            }

            $return->update([
                'return_note' => $request->return_note,
                'staff_note' => $request->staff_note,
                'total_qty' => $totalQty,
                'grand_total' => $grandTotal,
            ]);

            activity_log('updated purchase return', 'return ref: '.$return->reference_no);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Return Request Updated Successfully!']);
            }

            return redirect()->route('purchase-return.index')->with('success', 'Return Request Updated Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Error: '.$e->getMessage()], 500);
            }

            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $return = PurchaseReturn::findOrFail($id);

            if ($return->status == 'approved') {
                return response()->json(['success' => false, 'message' => 'Cannot delete approved records!']);
            }

            $return->details()->delete();
            $return->delete();

            activity_log('deleted purchase return', 'return ref: '.$return->reference_no);

            return response()->json(['success' => true, 'message' => 'Deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }
}

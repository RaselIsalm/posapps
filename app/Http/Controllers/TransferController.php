<?php

namespace App\Http\Controllers;

use App\DataTables\TransferDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\StockHistory;
use App\Models\Transfer;
use App\Models\TransferDetail;
use App\Models\Unit;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TransferController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(TransferDataTable $dataTable)
    {
        return $dataTable->render('backend.transfers.index');
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $accounts = Account::active()->orderBy('name')->get();
        $branchManagers = User::orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.transfers.create', compact('branches', 'accounts', 'branchManagers', 'employeeRoles'));
    }

    public function getProduct(Request $request)
    {
        try {
            $search = $request->search;
            $branch_id = $request->branch_id;

            if (! $branch_id) {
                return response()->json(['error' => 'Please select "From Branch" first!']);
            }

            $products = Product::with(['productVariants', 'productBranches' => function ($q) use ($branch_id) {
                $q->where('branch_id', $branch_id);
            }])
                ->where('product_title', 'LIKE', "%$search%")
                ->orWhere('product_code', 'LIKE', "%$search%")
                ->limit(15)
                ->get();

            $data = [];

            foreach ($products as $product) {
                // Calculate Base Unit Cost
                $baseUnitCost = $product->product_cost ?? 0;
                if ($product->purchase_unit) {
                    $purchaseUnit = Unit::find($product->purchase_unit);
                    if ($purchaseUnit && $purchaseUnit->operator && $purchaseUnit->conversion_rate) {
                        if ($purchaseUnit->operator == '*') {
                            $baseUnitCost = $baseUnitCost / $purchaseUnit->conversion_rate;
                        } elseif ($purchaseUnit->operator == '/') {
                            $baseUnitCost = $baseUnitCost * $purchaseUnit->conversion_rate;
                        }
                    }
                }

                if ($product->is_variation == 0 || $product->productVariants->isEmpty()) {
                    $productBranch = $product->productBranches->where('variant_id', null)->first();
                    $stockQty = $productBranch ? $productBranch->qty : 0;

                    $data[] = [
                        'id' => $product->id,
                        'variant_id' => '',
                        'name' => $product->product_title,
                        'code' => $product->product_code,
                        'price' => $baseUnitCost,
                        'stock' => $stockQty,
                        'tax' => $product->tax ?? 0,
                    ];
                } else {
                    foreach ($product->productVariants as $variant) {
                        $variantPrice = $variant->variant_price ?? $baseUnitCost;
                        $vName = $variant->variant_name ?? $variant->item_code ?? 'Var';
                        $variantName = $product->product_title.' ('.$vName.')';

                        $vBranch = $product->productBranches->where('variant_id', $variant->id)->first();
                        $vStock = $vBranch ? $vBranch->qty : 0;

                        $data[] = [
                            'id' => $product->id,
                            'variant_id' => $variant->id,
                            'name' => $variantName,
                            'code' => $variant->item_code ?? $product->product_code,
                            'price' => $variantPrice,
                            'stock' => $vStock,
                            'tax' => $product->tax ?? 0,
                        ];
                    }
                }
            }

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage(), 'line' => $e->getLine()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required',
            'to_branch_id' => 'required|different:from_branch_id',
            'status' => 'required',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0.01',
            'net_unit_cost' => 'required|array',
            'net_unit_cost.*' => 'required|numeric|min:0',
            'tax' => 'required|array',
            'tax.*' => 'required|numeric|min:0',
            'subtotal' => 'required|array',
            'subtotal.*' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'document' => 'nullable|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $transfer = new Transfer;
            $transfer->reference_no = 'TR-'.date('Ymd').'-'.strtoupper(Str::random(4));
            $transfer->user_id = Auth::id();
            $transfer->from_branch_id = $request->from_branch_id;
            $transfer->to_branch_id = $request->to_branch_id;
            $transfer->item_count = count($request->product_id);
            $transfer->total_qty = array_sum($request->qty);
            $transfer->shipping_cost = $request->shipping_cost ?? 0;
            $transfer->grand_total = $request->grand_total;
            $transfer->status = $request->status;
            $transfer->note = $request->note;

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/transfers'), $filename);
                $transfer->document = $filename;
            }

            $transfer->save();

            foreach ($request->product_id as $key => $prodId) {
                $qty = $request->qty[$key];
                $cost = $request->net_unit_cost[$key];
                $tax = $request->tax[$key];
                $subtotal = $request->subtotal[$key];
                $batch = $request->batch_no[$key] ?? null;
                $variantId = $request->variant_id[$key] ?? null;
                if ($variantId === 'null' || $variantId === '') {
                    $variantId = null;
                }

                TransferDetail::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $prodId,
                    'variant_id' => $variantId,
                    'qty' => $qty,
                    'net_unit_cost' => $cost,
                    'tax' => $tax,
                    'subtotal' => $subtotal,
                    'batch_no' => $batch,
                ]);

                // Stock Management Logic
                if ($request->status == 'sent' || $request->status == 'completed') {
                    // Logic handled by service to ensure consistency
                    if ($request->status == 'completed') {
                        $this->inventoryService->transferStock(
                            $request->from_branch_id,
                            $request->to_branch_id,
                            $prodId,
                            $variantId,
                            $qty,
                            $transfer->reference_no
                        );
                    } else {
                        // Just deduct from sender if only 'sent'
                        $this->inventoryService->decrementStock(
                            $request->from_branch_id,
                            $prodId,
                            $variantId,
                            $qty,
                            'transfer_sent',
                            $transfer->reference_no,
                            true
                        );
                    }
                }
            }

            DB::commit();

            return redirect()->route('transfers.index')->with('success', 'Transfer created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function show($id)
    {
        if (request()->ajax()) {
            $transfer = Transfer::with(['fromBranch', 'toBranch', 'user', 'details.product', 'details.variant'])->findOrFail($id);

            $invoice = \App\Helpers\InvoiceHelper::fromTransfer($transfer);

            return view('backend.components.invoice.content', compact('invoice'))->render();
        }
    }

    public function updateStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $transfer = Transfer::with('details')->findOrFail($id);
            $newStatus = $request->status;
            $oldStatus = $transfer->status;

            if ($oldStatus == 'completed') {
                return response()->json(['success' => false, 'message' => 'Completed transfers cannot be changed!']);
            }

            if ($oldStatus == $newStatus) {
                return response()->json(['success' => true, 'message' => 'Status is already '.$newStatus]);
            }

            // State Machine Logic
            if ($oldStatus == 'request' && $newStatus == 'sent') {
                // Deduct from Sender
                foreach ($transfer->details as $detail) {
                    $this->inventoryService->decrementStock(
                        $transfer->from_branch_id,
                        $detail->product_id,
                        $detail->variant_id,
                        $detail->qty,
                        'transfer_sent',
                        $transfer->reference_no,
                        true
                    );
                }
            }

            if ($oldStatus == 'sent' && $newStatus == 'request') {
                // Restore to Sender
                foreach ($transfer->details as $detail) {
                    $this->inventoryService->incrementStock(
                        $transfer->from_branch_id,
                        $detail->product_id,
                        $detail->variant_id,
                        $detail->qty,
                        'transfer_restore',
                        $transfer->reference_no
                    );
                }
            }

            if ($oldStatus == 'sent' && $newStatus == 'completed') {
                // Add to Receiver
                foreach ($transfer->details as $detail) {
                    // Logic: Sender already deducted global during 'sent'.
                    // Now incrementing receiver will restore global to neutral.
                    $this->inventoryService->incrementStock(
                        $transfer->to_branch_id,
                        $detail->product_id,
                        $detail->variant_id,
                        $detail->qty,
                        'transfer_received',
                        $transfer->reference_no
                    );
                }
            }

            $transfer->status = $newStatus;
            $transfer->save();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transfer status updated to '.ucfirst($newStatus)]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }

    public function edit($id)
    {
        $transfer = Transfer::with(['details.product', 'details.variant'])->findOrFail($id);

        if ($transfer->status == 'completed') {
            return redirect()->route('transfers.index')->with('error', 'Completed transfers cannot be edited!');
        }

        $branches = Branch::orderBy('name')->get();
        $accounts = Account::active()->orderBy('name')->get();
        $branchManagers = User::orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return view('backend.transfers.edit', compact('transfer', 'branches', 'accounts', 'branchManagers', 'employeeRoles'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'from_branch_id' => 'required',
            'to_branch_id' => 'required|different:from_branch_id',
            'status' => 'required',
            'product_id' => 'required|array',
            'product_id.*' => 'required|integer',
            'qty' => 'required|array',
            'qty.*' => 'required|numeric|min:0.01',
            'net_unit_cost' => 'required|array',
            'net_unit_cost.*' => 'required|numeric|min:0',
            'tax' => 'required|array',
            'tax.*' => 'required|numeric|min:0',
            'subtotal' => 'required|array',
            'subtotal.*' => 'required|numeric|min:0',
            'grand_total' => 'required|numeric|min:0',
            'document' => 'nullable|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $transfer = Transfer::with('details')->findOrFail($id);

            // Security Check: Prevent updating completed transfers via API/Postman
            if ($transfer->status == 'completed') {
                return back()->with('error', 'Completed transfers cannot be edited!');
            }

            if ($transfer->status == 'sent') {
                foreach ($transfer->details as $detail) {
                    $this->inventoryService->incrementStock(
                        $transfer->from_branch_id,
                        $detail->product_id,
                        $detail->variant_id,
                        $detail->qty,
                        'transfer_restore',
                        $transfer->reference_no
                    );
                }
            }

            $transfer->details()->delete();

            $transfer->from_branch_id = $request->from_branch_id;
            $transfer->to_branch_id = $request->to_branch_id;
            $transfer->status = $request->status;
            $transfer->note = $request->note;
            $transfer->shipping_cost = $request->shipping_cost ?? 0;
            $transfer->item_count = count($request->product_id);
            $transfer->total_qty = array_sum($request->qty);
            $transfer->grand_total = $request->grand_total;

            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move(public_path('uploads/transfers'), $filename);
                $transfer->document = $filename;
            }
            $transfer->save();

            foreach ($request->product_id as $key => $prodId) {
                $qty = $request->qty[$key];
                $cost = $request->net_unit_cost[$key];
                $tax = $request->tax[$key];
                $subtotal = $request->subtotal[$key];
                $batch = $request->batch_no[$key] ?? null;
                $variantId = $request->variant_id[$key] ?? null;
                if ($variantId === 'null' || $variantId === '') {
                    $variantId = null;
                }

                TransferDetail::create([
                    'transfer_id' => $transfer->id,
                    'product_id' => $prodId,
                    'variant_id' => $variantId,
                    'qty' => $qty,
                    'net_unit_cost' => $cost,
                    'tax' => $tax,
                    'subtotal' => $subtotal,
                    'batch_no' => $batch,
                ]);

                if ($request->status == 'sent' || $request->status == 'completed') {
                    if ($request->status == 'completed') {
                        $this->inventoryService->transferStock(
                            $request->from_branch_id,
                            $request->to_branch_id,
                            $prodId,
                            $variantId,
                            $qty,
                            $transfer->reference_no
                        );
                    } else {
                        // Just deduct from sender if only 'sent'
                        $this->inventoryService->decrementStock(
                            $request->from_branch_id,
                            $prodId,
                            $variantId,
                            $qty,
                            'transfer_sent',
                            $transfer->reference_no,
                            true
                        );
                    }
                }
            }

            DB::commit();

            return redirect()->route('transfers.index')->with('success', 'Transfer updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $transfer = Transfer::with('details')->find($id);
            if (! $transfer) {
                return response()->json(['success' => false, 'message' => 'Transfer not found or already deleted! Please refresh the page.']);
            }
            if ($transfer->status == 'completed') {
                return response()->json(['success' => false, 'message' => 'Completed transfers cannot be deleted!']);
            }

            if ($transfer->status == 'sent') {
                foreach ($transfer->details as $detail) {
                    $this->inventoryService->incrementStock(
                        $transfer->from_branch_id,
                        $detail->product_id,
                        $detail->variant_id,
                        $detail->qty,
                        'transfer_restore',
                        $transfer->reference_no
                    );
                }
            }

            $transfer->details()->delete();
            $transfer->delete();

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Transfer deleted successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\DataTables\SaleReturnDataTable;
use App\Models\Account;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductImei;
use App\Models\ProductSale;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\Transaction;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleReturnController extends Controller
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(SaleReturnDataTable $dataTable)
    {
        return $dataTable->render('backend.return.sale.index');
    }

    public function create(Request $request)
    {
        if ($request->ajax() && $request->has('sale_reference')) {
            try {
                $viewPath = 'backend.return.sale.partials.create_form';

                if (! view()->exists($viewPath)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'View file not found! Please create: resources/views/'.str_replace('.', '/', $viewPath).'.blade.php',
                    ]);
                }

                $sale = Sale::with(['productSales.product', 'customer', 'branch'])
                    ->where('invoice_no', $request->sale_reference)
                    ->first();

                if (! $sale) {
                    return response()->json(['status' => 'error', 'message' => 'Invoice Number not found!']);
                }

                return view($viewPath, compact('sale'))->render();
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'System Error: '.$e->getMessage()]);
            }
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required',
            'return_items' => 'required|array',
            'qty' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $sale = Sale::findOrFail($request->sale_id);

            $saleReturn = new SaleReturn;
            $saleReturn->reference_no = 'RR-'.date('Ymd').'-'.strtoupper(Str::random(4));
            $saleReturn->sale_id = $sale->id;
            $saleReturn->customer_id = $sale->customer_id ?? null;
            $saleReturn->branch_id = $sale->branch_id;
            $saleReturn->created_by = Auth::id();
            $saleReturn->date = date('Y-m-d');
            $saleReturn->status = 'pending';
            $saleReturn->return_note = $request->return_note;
            $saleReturn->staff_note = $request->staff_note;
            $saleReturn->total_qty = 0;
            $saleReturn->grand_total = 0;
            $saleReturn->save();

            $totalQty = 0;
            $grandTotal = 0;

            foreach ($request->return_items as $key => $productId) {
                $returnQty = (float) $request->qty[$key];
                $unitPrice = (float) $request->unit_price[$key];

                $selectedImeis = $request->imei_number[$key] ?? null;
                $imeiString = is_array($selectedImeis) ? implode(',', $selectedImeis) : $selectedImeis;

                $variantId = $request->variant_id[$key] ?? null;
                if ($variantId === 'null' || $variantId === '') {
                    $variantId = null;
                }

                if ($returnQty <= 0) {
                    continue;
                }

                $lineTotal = $returnQty * $unitPrice;

                $originalSaleItem = ProductSale::where('sale_id', $sale->id)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->first();

                $originalCost = 0;
                if ($originalSaleItem && $originalSaleItem->purchase_unit_cost > 0) {
                    $originalCost = $originalSaleItem->purchase_unit_cost;
                } else {
                    $prodModel = Product::find($productId);
                    $baseCost = (float) ($prodModel->product_cost ?? 0);
                    $originalCost = $baseCost;
                    if ($variantId) {
                        $vModel = ProductVariant::find($variantId);
                        if ($vModel) {
                            $originalCost = $vModel->costFromBase($baseCost);
                        }
                    }
                }

                SaleReturnDetail::create([
                    'sale_return_id' => $saleReturn->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => $returnQty,
                    'unit_price' => $unitPrice,
                    'purchase_unit_cost' => $originalCost,
                    'total' => $lineTotal,
                    'imei_number' => $imeiString,
                ]);

                $totalQty += $returnQty;
                $grandTotal += $lineTotal;
            }

            $saleReturn->update([
                'total_qty' => $totalQty,
                'grand_total' => $grandTotal,
            ]);

            activity_log('created sale return', 'return ref: '.$saleReturn->reference_no);

            DB::commit();

            return redirect()->route('sale-return.index')->with('success', 'Return Request Added to Queue!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error: '.$e->getMessage());
        }
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $return = SaleReturn::with(['details', 'sale'])
                ->lockForUpdate()
                ->findOrFail($id);

            if ($return->status == 'approved') {
                return response()->json(['success' => false, 'message' => 'Already Approved!']);
            }

            foreach ($return->details as $detail) {
                // Use Service for consistent stock management
                $this->inventoryService->incrementStock(
                    $return->branch_id,
                    $detail->product_id,
                    $detail->variant_id,
                    $detail->qty,
                    'sale_return',
                    $return->reference_no
                );

                if (! empty($detail->imei_number)) {
                    $imeiArray = explode(',', $detail->imei_number);
                    foreach ($imeiArray as $singleImei) {
                        $imeiToReturn = ProductImei::where('imei_number', trim($singleImei))->first();
                        if ($imeiToReturn) {
                            $imeiToReturn->update([
                                'status' => 'in_stock',
                                'is_sold' => 0,
                                'sale_id' => null,
                                'branch_id' => $return->branch_id,
                            ]);
                        }
                    }
                }
            }

            // Reward Point Reversal Logic
            $sale = $return->sale;
            if ($sale && $sale->customer_id && $sale->reward_points_earned > 0) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    // Calculate points to deduct (pro-rata based on return amount)
                    $ratio = $return->grand_total / $sale->grand_total;
                    $pointsToDeduct = floor($sale->reward_points_earned * $ratio);

                    if ($pointsToDeduct > 0) {
                        $customer->decrement('reward_points', min($pointsToDeduct, $customer->reward_points));
                    }
                }
            }

            $sale = $return->sale;
            $refundAmount = $return->grand_total;

            if ($sale && $sale->due_amount > 0) {
                if ($sale->due_amount >= $refundAmount) {
                    $sale->decrement('due_amount', $refundAmount);
                    $refundAmount = 0;
                } else {
                    $refundAmount -= $sale->due_amount;
                    $sale->update(['due_amount' => 0, 'payment_status' => 'paid']);
                }
            }

            if ($refundAmount > 0 && $sale) {
                if ($sale->isVoided()) {
                    throw new \Exception('Cannot approve return for a voided sale.');
                }

                $paymentIds = $sale->payments()->pluck('id')->filter()->values()->all();
                $saleTransactions = Transaction::query()
                    ->where('type', 'credit')
                    ->where(function ($query) use ($sale, $paymentIds) {
                        $query->where(function ($saleQuery) use ($sale) {
                            $saleQuery->where('reference_type', get_class($sale))
                                ->where('reference_id', $sale->id);
                        });
                        if ($paymentIds !== []) {
                            $query->orWhere(function ($paymentQuery) use ($paymentIds) {
                                $paymentQuery->where('reference_type', \App\Models\Payment::class)
                                    ->whereIn('reference_id', $paymentIds);
                            });
                        }
                    })
                    ->where(function ($q) {
                        $q->whereNull('reason')->orWhere('reason', 'not like', 'Void sale%');
                    })
                    ->get();

                foreach ($saleTransactions as $saleTx) {
                    if ($refundAmount <= 0) {
                        break;
                    }

                    $account = Account::find($saleTx->account_id);

                    if ($account && $account->current_balance > 0) {
                        $amountToDeduct = min($account->current_balance, $refundAmount);
                        $account->decrement('current_balance', $amountToDeduct);
                        $refundAmount -= $amountToDeduct;

                        Transaction::create([
                            'account_id' => $account->id,
                            'type' => 'debit',
                            'amount' => $amountToDeduct,
                            'balance' => $account->fresh()->current_balance,
                            'reason' => 'Refund for Sale Return: '.$return->reference_no,
                            'reference_type' => 'App\\Models\\SaleReturn',
                            'reference_id' => $return->id,
                            'date' => date('Y-m-d'),
                            'note' => 'Refund for Sale Return: '.$return->reference_no,
                            'created_by' => Auth::id(),
                        ]);
                    }
                }
            }

            if ($refundAmount > 0 && $return->customer_id) {
                $customer = Customer::find($return->customer_id);
                if ($customer) {
                    $customer->increment('balance', $refundAmount);
                }
            }

            $return->update(['status' => 'approved']);

            activity_log('approved sale return', 'return ref: '.$return->reference_no);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Return Approved! Stock & Balances Adjusted.']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()]);
        }
    }

    public function show($id)
    {
        try {
            $return = SaleReturn::with(['sale', 'customer', 'branch', 'details.product', 'details.variant', 'user'])->findOrFail($id);

            $invoice = \App\Helpers\InvoiceHelper::fromSaleReturn($return);

            return view('backend.components.invoice.content', compact('invoice'))->render();
        } catch (\Exception $e) {
            return '<div class="text-center text-danger py-5">'.$e->getMessage().'</div>';
        }
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $return = SaleReturn::with(['sale', 'details.product', 'details.variant', 'customer'])->findOrFail($id);

            if ($return->status == 'approved') {
                return '<div class="alert alert-warning text-center">This return is already Approved and cannot be edited.</div>';
            }

            return view('backend.return.sale.partials.edit_form', compact('return'))->render();
        }
    }

    public function update(Request $request, $id)
    {
        $saleReturn = SaleReturn::findOrFail($id);

        if ($saleReturn->status == 'approved') {
            return back()->with('error', 'Approved returns cannot be updated!');
        }

        $request->validate([
            'return_items' => 'required|array',
            'qty' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            SaleReturnDetail::where('sale_return_id', $id)->delete();

            $totalQty = 0;
            $grandTotal = 0;

            foreach ($request->return_items as $key => $productId) {
                $newQty = $request->qty[$key];
                $unitPrice = $request->unit_price[$key];
                $variantId = $request->variant_id[$key] ?? null;

                $selectedImeis = $request->imei_number[$key] ?? null;
                $imeiString = is_array($selectedImeis) ? implode(',', $selectedImeis) : $selectedImeis;

                if ($newQty <= 0) {
                    continue;
                }
                $lineTotal = $newQty * $unitPrice;

                // Fetch cost from original sale if possible, else use current cost
                $originalSaleItem = ProductSale::where('sale_id', $saleReturn->sale_id)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->first();

                $originalCost = 0;
                if ($originalSaleItem && $originalSaleItem->purchase_unit_cost > 0) {
                    $originalCost = $originalSaleItem->purchase_unit_cost;
                } else {
                    $prodModel = Product::find($productId);
                    $baseCost = (float) ($prodModel->product_cost ?? 0);
                    $originalCost = $baseCost;
                    if ($variantId) {
                        $vModel = ProductVariant::find($variantId);
                        if ($vModel) {
                            $originalCost = $vModel->costFromBase($baseCost);
                        }
                    }
                }

                SaleReturnDetail::create([
                    'sale_return_id' => $saleReturn->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => $newQty,
                    'unit_price' => $unitPrice,
                    'purchase_unit_cost' => $originalCost,
                    'total' => $lineTotal,
                    'imei_number' => $imeiString,
                ]);

                $totalQty += $newQty;
                $grandTotal += $lineTotal;
            }

            $saleReturn->update([
                'return_note' => $request->return_note,
                'staff_note' => $request->staff_note,
                'total_qty' => $totalQty,
                'grand_total' => $grandTotal,
            ]);

            activity_log('updated sale return', 'return ref: '.$saleReturn->reference_no);

            DB::commit();

            if ($request->ajax()) {
                return response()->json(['status' => 'success', 'message' => 'Return Request Updated Successfully!']);
            }

            return redirect()->route('sale-return.index')->with('success', 'Return Request Updated Successfully!');
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
        DB::beginTransaction();
        try {
            $return = SaleReturn::with('details')->findOrFail($id);

            if ($return->status == 'approved') {
                foreach ($return->details as $detail) {
                    Product::where('id', $detail->product_id)->decrement('quantity', $detail->qty);

                    $branchStock = ProductBranch::where('branch_id', $return->branch_id)
                        ->where('product_id', $detail->product_id)
                        ->where('variant_id', $detail->variant_id)
                        ->first();
                    if ($branchStock) {
                        $branchStock->decrement('qty', $detail->qty);
                    }

                    if (! empty($detail->imei_number)) {
                        $imeiArray = explode(',', $detail->imei_number);
                        ProductImei::whereIn('imei_number', $imeiArray)->update([
                            'status' => 'sold',
                            'is_sold' => 1,
                            'sale_id' => $return->sale_id,
                        ]);
                    }
                }

                if ($return->customer_id) {
                    $customer = Customer::find($return->customer_id);
                    if ($customer) {
                        $customer->decrement('balance', $return->grand_total);
                    }
                }
            }

            $return->details()->delete();
            $return->delete();

            activity_log('deleted sale return', 'return ref: '.$return->reference_no);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Return Record Deleted & Stock Reverted!']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }
}

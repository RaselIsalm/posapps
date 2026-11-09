<?php

namespace App\Http\Controllers;

use App\DataTables\InventoryDataTable;
use App\DataTables\StockHistoryDataTable;
use App\Models\Branch;
use App\Models\ProductBranch;
use App\Models\PurchaseDetail;
use App\Models\StockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InventoryController extends Controller
{
    public function index(InventoryDataTable $dataTable)
    {
        $branches = Branch::all();
        $stats = [
            'total_items' => ProductBranch::count(),
            'low_stock' => ProductBranch::whereHas('product', function ($q) {
                $q->whereColumn('product_branches.qty', '<=', 'products.alert_quantity');
            })->count(),
            'total_value' => ProductBranch::selectRaw('SUM(qty * sell_price) as total')->value('total') ?? 0,
            'total_branches' => $branches->count(),
            'expired_items' => PurchaseDetail::where('expire_date', '<', date('Y-m-d'))
                ->distinct('product_id')
                ->count(),
        ];

        return $dataTable->render('backend.inventory.index', compact('branches', 'stats'));
    }

    public function stockHistory(StockHistoryDataTable $dataTable)
    {
        $branches = Branch::all();

        return $dataTable->render('backend.inventory.stock_history', compact('branches'));
    }

    public function stockHistoryShow($id)
    {
        $history = StockHistory::with(['product', 'branch', 'variant'])->findOrFail($id);

        $typeLabels = [
            'transfer_sent' => '<span class="badge bg-warning">Transfer Sent</span>',
            'transfer_received' => '<span class="badge bg-success">Transfer Received</span>',
            'transfer_restore' => '<span class="badge bg-info">Transfer Restored</span>',
            'sale' => '<span class="badge bg-danger">Sale</span>',
            'purchase' => '<span class="badge bg-primary">Purchase</span>',
            'purchase_deleted' => '<span class="badge bg-dark">Purchase Deleted</span>',
            'adjustment' => '<span class="badge bg-secondary">Adjustment</span>',
        ];

        $response = [
            'product_name' => $history->product->product_title ?? 'N/A',
            'product_code' => $history->product->product_code ?? 'N/A',
            'variant' => $history->variant ? ($history->variant->variant_name ?? $history->variant->item_code) : 'Single',
            'branch' => $history->branch->name ?? 'N/A',
            'qty' => ($history->qty > 0 ? '+' : '').$history->qty,
            'qty_color' => $history->qty > 0 ? 'text-success' : 'text-danger',
            'type' => $typeLabels[$history->type] ?? ucfirst($history->type),
            'reference' => $history->reference_no,
            'note' => $history->note ?? 'N/A',
            'date' => date('d M, Y h:i A', strtotime($history->created_at)),
        ];

        return response()->json($response);
    }

    public function adjustStock(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:product_branches,id',
            'type' => 'required|in:addition,subtraction',
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'required|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $stock = ProductBranch::findOrFail($request->id);
            $oldQty = $stock->qty;
            $adjustmentAmount = $request->amount;

            if ($request->type == 'subtraction') {
                if ($stock->qty < $adjustmentAmount) {
                    throw new \Exception('Insufficient stock for subtraction.');
                }
                $stock->qty -= $adjustmentAmount;
                $finalQty = -$adjustmentAmount;
            } else {
                $stock->qty += $adjustmentAmount;
                $finalQty = $adjustmentAmount;
            }

            $stock->save();

            // Update global product qty
            $product = $stock->product;
            if ($request->type == 'subtraction') {
                $product->decrement('quantity', $adjustmentAmount);
            } else {
                $product->increment('quantity', $adjustmentAmount);
            }

            // Log to StockHistory
            StockHistory::create([
                'product_id' => $stock->product_id,
                'variant_id' => $stock->variant_id,
                'branch_id' => $stock->branch_id,
                'qty' => $finalQty,
                'type' => 'adjustment',
                'reference_no' => 'ADJ-'.strtoupper(Str::random(6)),
                'note' => $request->reason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Stock adjusted successfully!',
                'new_qty' => $stock->qty,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show($id)
    {
        $stock = ProductBranch::with(['product.category', 'product.unit', 'branch', 'variant'])->findOrFail($id);

        $lastPurchaseItem = PurchaseDetail::where('product_id', $stock->product_id)
            ->whereHas('purchase', function ($q) use ($stock) {
                $q->where('branch_id', $stock->branch_id);
            })
            ->with('purchase.supplier')
            ->latest()
            ->first();

        $description = strip_tags($stock->product->description ?? '');

        $response = [
            'product_code' => $stock->product->product_code ?? 'N/A',
            'product_name' => $stock->product->product_title ?? 'N/A',
            'category' => $stock->product->category->name ?? 'Uncategorized',
            'variant' => $stock->variant->variant_name ?? 'Single',
            'description' => $description ?: 'No description available',
            'unit_price' => number_format($stock->product->product_cost ?? 0, 2),
            'selling_price' => number_format($stock->sell_price ?? 0, 2),
            'total_value' => number_format($stock->qty * $stock->sell_price, 2),
            'quantity' => $stock->qty,
            'reorder_level' => $stock->product->alert_quantity ?? 0,
            'supplier_name' => $lastPurchaseItem ? $lastPurchaseItem->purchase->supplier->name : 'No History',
            'last_restock' => $lastPurchaseItem ? date('d M, Y', strtotime($lastPurchaseItem->purchase->date)) : 'Never',
        ];

        return response()->json($response);
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\TransferController;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnDetail;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\ProductSale;
use App\Models\SaleReturn;
use App\Models\SaleReturnDetail;
use App\Models\StockHistory;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Services\InventoryService;
use App\Services\PurchaseService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Native mobile endpoints for the Website's existing operations modules. */
class OperationsController extends ApiController
{
    public function options(Request $request): JsonResponse
    {
        $data = [];
        $user = $request->user();
        $branches = $this->accessibleBranchRows($request);
        if ($user->hasAnyPermission(['view_products', 'create_products', 'view_inventory', 'create_purchases', 'create_transfer', 'create_quotations', 'view_quotations', 'create_expenses', 'create_sale_return', 'create_purchase_return'])) {
            $branchIds = $branches->pluck('id');
            $data['products'] = Product::where('is_active', 1)->whereHas('productBranches', fn ($q) => $q->whereIn('branch_id', $branchIds))->orderBy('product_title')->limit(500)->get(['id', 'product_title', 'product_code']);
            $data['branches'] = $branches;
        }
        if ($user->hasAnyPermission(['view_customers', 'create_customers', 'create_quick_sale', 'create_quotations', 'view_quotations'])) {
            $data['customers'] = Customer::where('status', 1)->orderBy('name')->limit(500)->get(['id', 'name', 'code', 'phone']);
            $data['customer_groups'] = CustomerGroup::where('status', 1)->orderBy('name')->get(['id', 'name']);
        }
        if ($user->hasAnyPermission(['view_suppliers', 'create_suppliers', 'create_purchases', 'create_purchase_return', 'create_quotations', 'view_quotations'])) {
            $data['suppliers'] = Supplier::where('is_active', 1)->orderBy('name')->limit(500)->get(['id', 'name', 'phone']);
        }
        if ($user->hasAnyPermission(['view_accounts', 'create_accounts', 'create_purchases', 'create_expenses', 'create_quotations', 'view_quotations', 'create_quick_sale'])) {
            $data['accounts'] = $this->accessibleAccountQuery($request)->orderBy('name')->limit(200)->get(['id', 'name', 'current_balance']);
        }
        if ($user->hasAnyPermission(['view_expenses_categories', 'create_expenses'])) {
            $data['expense_categories'] = ExpensesCategory::where('status', 1)->orderBy('name')->get(['id', 'name']);
        }
        if ($user->hasAnyPermission(['view_sales', 'create_sale_return'])) {
            $data['sales'] = $this->applyBranchScope(Sale::query(), $request)->latest('id')->limit(200)->get(['id', 'invoice_no', 'branch_id', 'customer_id']);
        }
        if ($user->hasAnyPermission(['view_purchases', 'create_purchase_return'])) {
            $data['purchases'] = $this->applyBranchScope(Purchase::query(), $request)->latest('id')->limit(200)->get(['id', 'invoice_no', 'branch_id', 'supplier_id']);
        }
        return $this->success($data);
    }

    public function inventory(Request $request): JsonResponse
    {
        $this->requirePermission('view_inventory');
        $query = ProductBranch::query()->with(['product.category', 'product.brand', 'branch', 'variant'])
            ->whereHas('product', fn ($q) => $q->where('is_active', 1))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->query('search')).'%';
                $q->whereHas('product', fn ($p) => $p->where('product_title', 'like', $term)->orWhere('product_code', 'like', $term));
            })
            ->when($request->boolean('low_stock'), fn ($q) => $q->whereHas('product', fn ($p) => $p->whereColumn('product_branches.qty', '<=', 'products.alert_quantity')));
        $query = $this->applyBranchScope($query, $request);
        // Keep the summary query separate from paginate(), which applies a page
        // limit/offset to its builder. Aggregate queries also need a clean
        // select/order clause to work on MySQL strict mode.
        $summaryQuery = clone $query;
        $data = $this->page($request, (clone $query)->orderBy('product_id')->orderBy('id'), fn (ProductBranch $row) => [
            'id' => (int) $row->id,
            'product_id' => (int) $row->product_id,
            'product' => $row->product?->product_title,
            'code' => $row->product?->product_code,
            'category' => $row->product?->category?->name,
            'brand' => $row->product?->brand?->name,
            'variant_id' => $row->variant_id ? (int) $row->variant_id : null,
            'variant' => $row->variant?->variant_name ?? $row->variant?->item_code,
            'branch_id' => (int) $row->branch_id,
            'branch' => $row->branch?->name,
            'quantity' => (float) $row->qty,
            'alert_quantity' => (float) ($row->product?->alert_quantity ?? 0),
            'unit_cost' => (float) ($row->product?->product_cost ?? 0),
            'selling_price' => (float) $row->sell_price,
            'stock_value' => (float) $row->qty * (float) $row->sell_price,
            'low_stock' => (float) $row->qty <= (float) ($row->product?->alert_quantity ?? 0),
        ], 'inventory');
        $data['summary'] = [
            'rows' => (int) (clone $summaryQuery)->reorder()->count(),
            'low_stock' => (int) (clone $summaryQuery)->reorder()->whereHas('product', fn ($p) => $p->whereColumn('product_branches.qty', '<=', 'products.alert_quantity'))->count(),
            'stock_value' => (float) (clone $summaryQuery)->reorder()->sum(DB::raw('qty * sell_price')),
        ];
        $data['branches'] = $this->accessibleBranchRows($request);
        return $this->success($data);
    }

    public function stockHistory(Request $request): JsonResponse
    {
        $this->requirePermission('view_inventory');
        $query = StockHistory::query()->with(['product', 'branch', 'variant'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->query('search')).'%';
                $q->where(fn ($n) => $n->where('reference_no', 'like', $term)->orWhereHas('product', fn ($p) => $p->where('product_title', 'like', $term)->orWhere('product_code', 'like', $term)));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->query('type')));
        $query = $this->applyBranchScope($query, $request);
        return $this->success($this->page($request, $query->latest('id'), fn (StockHistory $row) => [
            'id' => (int) $row->id, 'date' => $row->created_at?->toIso8601String(),
            'product_id' => (int) $row->product_id, 'product' => $row->product?->product_title,
            'code' => $row->product?->product_code, 'variant' => $row->variant?->variant_name ?? $row->variant?->item_code,
            'branch_id' => (int) $row->branch_id, 'branch' => $row->branch?->name,
            'quantity' => (float) $row->qty, 'type' => $row->type,
            'reference' => $row->reference_no, 'note' => $row->note,
        ], 'history'));
    }

    public function stockHistoryDetail(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_inventory');
        $history = StockHistory::with(['product', 'branch', 'variant'])->findOrFail($id);
        $this->assertBranchAccess($request, (int) $history->branch_id);
        return $this->success(['history' => [
            'id' => (int) $history->id,
            'date' => $history->created_at?->toIso8601String(),
            'product_id' => (int) $history->product_id,
            'product' => $history->product?->product_title,
            'code' => $history->product?->product_code,
            'variant' => $history->variant?->variant_name ?? $history->variant?->item_code,
            'branch_id' => (int) $history->branch_id,
            'branch' => $history->branch?->name,
            'quantity' => (float) $history->qty,
            'type' => $history->type,
            'reference' => $history->reference_no,
            'note' => $history->note,
        ]]);
    }

    public function inventoryDetail(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_inventory');
        $row = ProductBranch::with(['product.category', 'product.unit', 'branch', 'variant'])->findOrFail($id);
        $this->assertBranchAccess($request, $row->branch_id);
        $recent = StockHistory::with('product')->where('product_id', $row->product_id)->where('branch_id', $row->branch_id)->latest('id')->limit(10)->get();
        return $this->success(['item' => [
            'id' => (int) $row->id, 'product_id' => (int) $row->product_id, 'product' => $row->product?->product_title,
            'code' => $row->product?->product_code, 'category' => $row->product?->category?->name,
            'unit' => $row->product?->unit?->name, 'variant_id' => $row->variant_id,
            'variant' => $row->variant?->variant_name ?? $row->variant?->item_code,
            'branch_id' => (int) $row->branch_id, 'branch' => $row->branch?->name,
            'quantity' => (float) $row->qty, 'alert_quantity' => (float) ($row->product?->alert_quantity ?? 0),
            'unit_cost' => (float) ($row->product?->product_cost ?? 0), 'selling_price' => (float) $row->sell_price,
            'recent_history' => $recent->map(fn ($h) => ['date' => $h->created_at?->toIso8601String(), 'qty' => (float) $h->qty, 'type' => $h->type, 'reference' => $h->reference_no])->values(),
        ]]);
    }

    public function adjustStock(Request $request): JsonResponse
    {
        $this->requirePermission('update_inventory');
        $data = $request->validate(['product_branch_id' => ['required', 'integer', 'exists:product_branches,id'], 'type' => ['required', Rule::in(['addition', 'subtraction'])], 'amount' => ['required', 'numeric', 'gt:0'], 'reason' => ['required', 'string', 'max:255']]);
        $row = ProductBranch::findOrFail($data['product_branch_id']);
        $this->assertBranchAccess($request, $row->branch_id);
        $response = app(InventoryController::class)->adjustStock($request->merge(['id' => $row->id]));
        if ($response->getStatusCode() >= 400) return $response;
        return $this->success([
            'inventory' => $row->fresh(),
            'history' => StockHistory::where('product_id', $row->product_id)->where('branch_id', $row->branch_id)->latest('id')->first(),
        ], 'Stock adjusted.');
    }

    public function transfers(Request $request): JsonResponse
    {
        $this->requirePermission('view_transfer');
        $query = Transfer::query()->with(['fromBranch', 'toBranch', 'user'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('reference_no', 'like', '%'.trim((string) $request->query('search')).'%'));
        $this->scopeTransferBranches($query, $request);
        return $this->success($this->page($request, $query->latest('id'), fn (Transfer $t) => $this->transferData($t), 'transfers'));
    }

    public function transfer(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_transfer');
        $transfer = Transfer::with(['fromBranch', 'toBranch', 'user', 'details.product', 'details.variant'])->findOrFail($id);
        $this->assertBranchAccess($request, $transfer->from_branch_id);
        $this->assertBranchAccess($request, $transfer->to_branch_id);
        return $this->success(['transfer' => $this->transferData($transfer, true)]);
    }

    public function storeTransfer(Request $request): JsonResponse
    {
        $this->requirePermission('create_transfer');
        $data = $this->validateTransfer($request);
        $this->assertBranchAccess($request, $data['from_branch_id']);
        $this->assertBranchAccess($request, $data['to_branch_id']);
        $this->mergeTransferArrays($request, $data);
        $before = Transfer::query()->max('id') ?? 0;
        $response = app(TransferController::class)->store($request);
        if ($response->getStatusCode() >= 400) return $response;
        $transfer = Transfer::with(['fromBranch', 'toBranch', 'details.product', 'details.variant'])->where('id', '>', $before)->latest('id')->first();
        return $this->success(['transfer' => $transfer ? $this->transferData($transfer, true) : null], 'Transfer created.', 201);
    }

    public function updateTransfer(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_transfer');
        $transfer = Transfer::findOrFail($id);
        $this->assertBranchAccess($request, $transfer->from_branch_id);
        $this->assertBranchAccess($request, $transfer->to_branch_id);
        abort_if($transfer->status === 'completed', 409, 'Completed transfers cannot be edited.');
        $data = $this->validateTransfer($request);
        $this->assertBranchAccess($request, $data['from_branch_id']);
        $this->assertBranchAccess($request, $data['to_branch_id']);
        $this->mergeTransferArrays($request, $data);
        $response = app(TransferController::class)->update($request, $id);
        if ($response->getStatusCode() >= 400) return $response;
        return $this->success(['transfer' => $this->transferData(Transfer::with(['fromBranch', 'toBranch', 'details.product', 'details.variant'])->findOrFail($id), true)], 'Transfer updated.');
    }

    public function updateTransferStatus(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_transfer');
        $transfer = Transfer::findOrFail($id);
        $this->assertBranchAccess($request, $transfer->from_branch_id);
        $this->assertBranchAccess($request, $transfer->to_branch_id);
        $request->validate(['status' => ['required', Rule::in(['request', 'sent', 'completed'])]]);
        $response = app(TransferController::class)->updateStatus($request, $id);
        if ($response->getStatusCode() >= 400) return $response;
        return $this->success(['transfer' => $this->transferData(Transfer::with(['fromBranch', 'toBranch'])->findOrFail($id))], 'Transfer status updated.');
    }

    public function deleteTransfer(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_transfer');
        $transfer = Transfer::findOrFail($id);
        $this->assertBranchAccess($request, $transfer->from_branch_id);
        $this->assertBranchAccess($request, $transfer->to_branch_id);
        abort_if($transfer->status === 'completed', 409, 'Completed transfers cannot be deleted.');
        app(TransferController::class)->destroy($id);
        return $this->success([], 'Transfer deleted.');
    }

    public function purchases(Request $request): JsonResponse
    {
        return app(WorkspaceController::class)->purchases($request);
    }

    public function purchase(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_purchases');
        $purchase = $this->applyBranchScope(Purchase::query(), $request)->with(['branch', 'supplier', 'details.product', 'details.variant'])->findOrFail($id);
        return $this->success(['purchase' => $this->purchaseData($purchase, true)]);
    }

    public function storePurchase(Request $request, PurchaseService $service): JsonResponse
    {
        $this->requirePermission('create_purchases');
        $data = $this->purchaseInput($request);
        $this->assertBranchAccess($request, $data['branch_id']);
        $before = Purchase::query()->max('id') ?? 0;
        $data['invoice_no'] = $this->nextPurchaseReference();
        $data['created_by'] = $request->user()->id;
        $document = $this->storeDocument($request, 'purchases');
        $purchase = $service->storePurchase($data, $document);
        return $this->success(['purchase' => $this->purchaseData($purchase->fresh(['branch', 'supplier', 'details.product', 'details.variant']), true)], 'Purchase created.', 201);
    }

    public function updatePurchase(Request $request, int $id, PurchaseService $service): JsonResponse
    {
        $this->requirePermission('update_purchases');
        $purchase = $this->applyBranchScope(Purchase::query(), $request)->findOrFail($id);
        $data = $this->purchaseInput($request);
        $this->assertBranchAccess($request, $data['branch_id']);
        $document = $this->storeDocument($request, 'purchases');
        $service->updatePurchase($purchase->id, $data, $document);
        return $this->success(['purchase' => $this->purchaseData($purchase->fresh(['branch', 'supplier', 'details.product', 'details.variant']), true)], 'Purchase updated.');
    }

    public function deletePurchase(Request $request, int $id, PurchaseService $service): JsonResponse
    {
        $this->requirePermission('delete_purchases');
        $purchase = $this->applyBranchScope(Purchase::query(), $request)->findOrFail($id);
        $service->deletePurchase($purchase->id);
        return $this->success([], 'Purchase deleted and balances reversed.');
    }

    public function addPurchasePayment(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_purchases');
        $data = $request->validate(['account_id' => ['required', 'integer', 'exists:accounts,id'], 'amount' => ['required', 'numeric', 'gt:0'], 'date' => ['required', 'date']]);
        $purchaseId = $this->applyBranchScope(Purchase::query(), $request)->whereKey($id)->value('id');
        abort_unless($purchaseId, 404);
        DB::transaction(function () use ($purchaseId, $data, &$purchase) {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchaseId);
            $account = Account::lockForUpdate()->findOrFail($data['account_id']);
            abort_if($data['amount'] > $purchase->due_amount, 422, 'Payment cannot exceed the remaining due.');
            abort_if($account->current_balance < $data['amount'], 422, 'Insufficient account balance.');
            $purchase->increment('paid_amount', $data['amount']);
            $purchase->decrement('due_amount', $data['amount']);
            if ($purchase->supplier_id) Supplier::whereKey($purchase->supplier_id)->decrement('balance', $data['amount']);
            TransactionService::createTransaction($data['account_id'], 'debit', $data['amount'], 'Due Payment for Purchase: '.$purchase->invoice_no, $data['date'], $purchase);
        });
        return $this->success(['purchase' => $this->purchaseData($purchase->fresh(['branch', 'supplier', 'details.product', 'details.variant']), true)], 'Purchase payment saved.');
    }

    public function saleReturns(Request $request): JsonResponse
    {
        $this->requirePermission('view_sale_return');
        $query = $this->applyBranchScope(SaleReturn::query(), $request)->with(['sale', 'customer', 'branch'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('reference_no', 'like', '%'.trim((string) $request->query('search')).'%'));
        return $this->success($this->page($request, $query->latest('id'), fn (SaleReturn $r) => $this->saleReturnData($r), 'sale_returns'));
    }

    public function storeSaleReturn(Request $request): JsonResponse
    {
        $this->requirePermission('create_sale_return');
        $data = $this->validateReturnInput($request, 'sale_id');
        $sale = $this->applyBranchScope(Sale::query(), $request)->with('productSales')->findOrFail($data['sale_id']);
        $return = DB::transaction(function () use ($request, $data, $sale) {
            $return = SaleReturn::create([
                'reference_no' => 'RR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'sale_id' => $sale->id, 'customer_id' => $sale->customer_id, 'branch_id' => $sale->branch_id,
                'created_by' => $request->user()->id, 'date' => today()->toDateString(), 'status' => 'pending',
                'return_note' => $data['return_note'] ?? null, 'staff_note' => $data['staff_note'] ?? null,
            ]);
            $totalQty = 0.0;
            $grandTotal = 0.0;
            foreach ($data['items'] as $item) {
                $saleLine = ProductSale::where('sale_id', $sale->id)->where('product_id', $item['product_id'])->where('variant_id', $item['variant_id'] ?? null)->firstOrFail();
                $alreadyReturned = SaleReturnDetail::whereHas('saleReturn', fn ($q) => $q->where('sale_id', $sale->id)->whereIn('status', ['pending', 'approved']))
                    ->where('product_id', $item['product_id'])->where('variant_id', $item['variant_id'] ?? null)->sum('qty');
                abort_if((float) $item['qty'] + (float) $alreadyReturned > (float) $saleLine->qty, 422, 'Return quantity exceeds the sold quantity.');
                $unitPrice = (float) $saleLine->unit_price;
                $lineTotal = $unitPrice * (float) $item['qty'];
                SaleReturnDetail::create([
                    'sale_return_id' => $return->id, 'product_id' => $saleLine->product_id,
                    'variant_id' => $saleLine->variant_id, 'qty' => $item['qty'], 'unit_price' => $unitPrice,
                    'purchase_unit_cost' => (float) $saleLine->purchase_unit_cost, 'total' => $lineTotal,
                    'imei_number' => implode(',', $item['imei_number'] ?? []),
                ]);
                $totalQty += (float) $item['qty']; $grandTotal += $lineTotal;
            }
            $return->update(['total_qty' => $totalQty, 'grand_total' => $grandTotal]);
            return $return;
        });
        $return->load(['sale', 'customer', 'branch', 'details.product', 'details.variant']);
        return $this->success(['sale_return' => $return ? $this->saleReturnData($return, true) : null], 'Sale return submitted for approval.', 201);
    }

    public function saleReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_sale_return');
        $return = $this->applyBranchScope(SaleReturn::query(), $request)->with(['sale', 'customer', 'branch', 'details.product', 'details.variant'])->findOrFail($id);
        return $this->success(['sale_return' => $this->saleReturnData($return, true)]);
    }

    public function approveSaleReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_sale_return');
        $return = $this->applyBranchScope(SaleReturn::query(), $request)->findOrFail($id);
        abort_unless($return->status === 'pending', 409, 'This return is no longer pending.');
        $response = app(SaleReturnController::class)->approve($id);
        if ($response->getStatusCode() >= 400) return $response;
        return $this->success(['sale_return' => $this->saleReturnData($return->fresh(['sale', 'customer', 'branch', 'details.product', 'details.variant']), true)], 'Sale return approved.');
    }

    public function updateSaleReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_sale_return');
        $return = $this->applyBranchScope(SaleReturn::query(), $request)->findOrFail($id);
        abort_unless($return->status === 'pending', 409, 'Only pending returns can be edited.');
        $data = $request->validate(['return_note' => ['nullable', 'string', 'max:2000'], 'staff_note' => ['nullable', 'string', 'max:2000'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'], 'items.*.variant_id' => ['nullable', 'integer'], 'items.*.qty' => ['required', 'numeric', 'gt:0'], 'items.*.imei_number' => ['nullable', 'array']]);
        DB::transaction(function () use ($return, $data) {
            $sale = Sale::with('productSales')->findOrFail($return->sale_id);
            $return->details()->delete();
            $qtyTotal = 0.0; $grandTotal = 0.0;
            foreach ($data['items'] as $item) {
                $line = $sale->productSales->first(fn ($s) => (int) $s->product_id === (int) $item['product_id'] && (int) ($s->variant_id ?? 0) === (int) ($item['variant_id'] ?? 0));
                abort_unless($line, 422, 'Return item is not part of this sale.');
                $otherReturned = SaleReturnDetail::whereHas('saleReturn', fn ($q) => $q->where('sale_id', $sale->id)->where('id', '!=', $return->id)->whereIn('status', ['pending', 'approved']))->where('product_id', $line->product_id)->where('variant_id', $line->variant_id)->sum('qty');
                abort_if((float) $item['qty'] + (float) $otherReturned > (float) $line->qty, 422, 'Return quantity exceeds the sold quantity.');
                $lineTotal = (float) $line->unit_price * (float) $item['qty'];
                SaleReturnDetail::create(['sale_return_id' => $return->id, 'product_id' => $line->product_id, 'variant_id' => $line->variant_id, 'qty' => $item['qty'], 'unit_price' => $line->unit_price, 'purchase_unit_cost' => $line->purchase_unit_cost, 'total' => $lineTotal, 'imei_number' => implode(',', $item['imei_number'] ?? [])]);
                $qtyTotal += (float) $item['qty']; $grandTotal += $lineTotal;
            }
            $return->update(['total_qty' => $qtyTotal, 'grand_total' => $grandTotal, 'return_note' => $data['return_note'] ?? null, 'staff_note' => $data['staff_note'] ?? null]);
        });
        return $this->success(['sale_return' => $this->saleReturnData($return->fresh(['sale', 'customer', 'branch', 'details.product', 'details.variant']), true)], 'Sale return updated.');
    }

    public function deleteSaleReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_sale_return');
        $return = $this->applyBranchScope(SaleReturn::query(), $request)->findOrFail($id);
        abort_unless($return->status === 'pending', 409, 'Only pending returns can be deleted.');
        $return->delete();
        return $this->success([], 'Sale return deleted.');
    }

    public function purchaseReturns(Request $request): JsonResponse
    {
        $this->requirePermission('view_purchase_return');
        $query = $this->applyBranchScope(PurchaseReturn::query(), $request)->with(['purchase', 'supplier', 'branch'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), fn ($q) => $q->where('reference_no', 'like', '%'.trim((string) $request->query('search')).'%'));
        return $this->success($this->page($request, $query->latest('id'), fn (PurchaseReturn $r) => $this->purchaseReturnData($r), 'purchase_returns'));
    }

    public function storePurchaseReturn(Request $request): JsonResponse
    {
        $this->requirePermission('create_purchase_return');
        $data = $this->validateReturnInput($request, 'purchase_id');
        $purchase = $this->applyBranchScope(Purchase::query(), $request)->findOrFail($data['purchase_id']);
        $return = DB::transaction(function () use ($request, $data, $purchase) {
            $return = PurchaseReturn::create([
                'reference_no' => 'PR-'.now()->format('Ymd').'-'.strtoupper(Str::random(6)),
                'purchase_id' => $purchase->id, 'supplier_id' => $purchase->supplier_id, 'branch_id' => $purchase->branch_id,
                'created_by' => $request->user()->id, 'return_date' => today()->toDateString(), 'status' => 'pending', 'payment_status' => 'due',
                'return_note' => $data['return_note'] ?? null, 'staff_note' => $data['staff_note'] ?? null,
            ]);
            $totalQty = 0.0;
            $grandTotal = 0.0;
            foreach ($data['items'] as $item) {
                $purchaseLine = PurchaseDetail::where('purchase_id', $purchase->id)->where('product_id', $item['product_id'])->where('variant_id', $item['variant_id'] ?? null)->firstOrFail();
                $alreadyReturned = PurchaseReturnDetail::whereHas('return', fn ($q) => $q->where('purchase_id', $purchase->id)->whereIn('status', ['pending', 'approved']))
                    ->where('product_id', $item['product_id'])->where('variant_id', $item['variant_id'] ?? null)->sum('qty');
                abort_if((float) $item['qty'] + (float) $alreadyReturned > (float) $purchaseLine->qty, 422, 'Return quantity exceeds the purchased quantity.');
                $unitPrice = (float) $purchaseLine->net_unit_cost;
                $lineTotal = $unitPrice * (float) $item['qty'];
                PurchaseReturnDetail::create([
                    'purchase_return_id' => $return->id, 'product_id' => $purchaseLine->product_id,
                    'variant_id' => $purchaseLine->variant_id, 'qty' => $item['qty'], 'unit_price' => $unitPrice, 'total' => $lineTotal,
                ]);
                $totalQty += (float) $item['qty']; $grandTotal += $lineTotal;
            }
            $return->update(['total_qty' => $totalQty, 'grand_total' => $grandTotal]);
            return $return;
        });
        $return->load(['purchase', 'supplier', 'branch', 'details.product', 'details.variant']);
        return $this->success(['purchase_return' => $return ? $this->purchaseReturnData($return, true) : null], 'Purchase return submitted for approval.', 201);
    }

    public function purchaseReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_purchase_return');
        $return = $this->applyBranchScope(PurchaseReturn::query(), $request)->with(['purchase', 'supplier', 'branch', 'details.product', 'details.variant'])->findOrFail($id);
        return $this->success(['purchase_return' => $this->purchaseReturnData($return, true)]);
    }

    public function approvePurchaseReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_purchase_return');
        $return = $this->applyBranchScope(PurchaseReturn::query(), $request)->findOrFail($id);
        abort_unless($return->status === 'pending', 409, 'This return is no longer pending.');
        $response = app(PurchaseReturnController::class)->approve($id);
        if ($response->getStatusCode() >= 400) return $response;
        return $this->success(['purchase_return' => $this->purchaseReturnData($return->fresh(['purchase', 'supplier', 'branch', 'details.product', 'details.variant']), true)], 'Purchase return approved.');
    }

    public function updatePurchaseReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_purchase_return');
        $return = $this->applyBranchScope(PurchaseReturn::query(), $request)->findOrFail($id);
        abort_unless($return->status === 'pending', 409, 'Only pending returns can be edited.');
        $data = $request->validate(['return_note' => ['nullable', 'string', 'max:2000'], 'staff_note' => ['nullable', 'string', 'max:2000'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'], 'items.*.variant_id' => ['nullable', 'integer'], 'items.*.qty' => ['required', 'numeric', 'gt:0']]);
        DB::transaction(function () use ($return, $data) {
            $purchase = Purchase::with('details')->findOrFail($return->purchase_id);
            $return->details()->delete();
            $qtyTotal = 0.0; $grandTotal = 0.0;
            foreach ($data['items'] as $item) {
                $line = $purchase->details->first(fn ($p) => (int) $p->product_id === (int) $item['product_id'] && (int) ($p->variant_id ?? 0) === (int) ($item['variant_id'] ?? 0));
                abort_unless($line, 422, 'Return item is not part of this purchase.');
                $otherReturned = PurchaseReturnDetail::whereHas('return', fn ($q) => $q->where('purchase_id', $purchase->id)->where('id', '!=', $return->id)->whereIn('status', ['pending', 'approved']))->where('product_id', $line->product_id)->where('variant_id', $line->variant_id)->sum('qty');
                abort_if((float) $item['qty'] + (float) $otherReturned > (float) $line->qty, 422, 'Return quantity exceeds the purchased quantity.');
                $lineTotal = (float) $line->net_unit_cost * (float) $item['qty'];
                PurchaseReturnDetail::create(['purchase_return_id' => $return->id, 'product_id' => $line->product_id, 'variant_id' => $line->variant_id, 'qty' => $item['qty'], 'unit_price' => $line->net_unit_cost, 'total' => $lineTotal]);
                $qtyTotal += (float) $item['qty']; $grandTotal += $lineTotal;
            }
            $return->update(['total_qty' => $qtyTotal, 'grand_total' => $grandTotal, 'return_note' => $data['return_note'] ?? null, 'staff_note' => $data['staff_note'] ?? null]);
        });
        return $this->success(['purchase_return' => $this->purchaseReturnData($return->fresh(['purchase', 'supplier', 'branch', 'details.product', 'details.variant']), true)], 'Purchase return updated.');
    }

    public function deletePurchaseReturn(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_purchase_return');
        $return = $this->applyBranchScope(PurchaseReturn::query(), $request)->findOrFail($id);
        abort_unless($return->status === 'pending', 409, 'Only pending returns can be deleted.');
        $return->delete();
        return $this->success([], 'Purchase return deleted.');
    }

    public function customerGroups(Request $request): JsonResponse
    {
        $this->requirePermission('view_customer_groups');
        $query = CustomerGroup::query()->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->query('search')).'%'));
        return $this->success($this->page($request, $query->orderBy('name'), fn (CustomerGroup $g) => ['id' => (int) $g->id, 'name' => $g->name, 'prefix_code' => $g->prefix_code, 'description' => $g->description, 'discount' => (float) $g->discount, 'type' => $g->type, 'status' => (bool) $g->status, 'customers_count' => Customer::where('customer_group_id', $g->id)->count()], 'customer_groups'));
    }

    public function saveCustomerGroup(Request $request, ?int $id = null): JsonResponse
    {
        $this->requirePermission($id ? 'update_customer_groups' : 'create_customer_groups');
        $group = $id ? CustomerGroup::findOrFail($id) : new CustomerGroup;
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'prefix_code' => ['nullable', 'string', 'max:50'], 'description' => ['nullable', 'string', 'max:1000'], 'discount' => ['required', 'numeric', 'min:0'], 'type' => ['required', Rule::in(['fixed', 'percentage'])], 'status' => ['sometimes', 'boolean']]);
        $group->fill($data + ['status' => $request->boolean('status', true)])->save();
        return $this->success(['customer_group' => $group], $id ? 'Customer group updated.' : 'Customer group created.', $id ? 200 : 201);
    }

    public function deleteCustomerGroup(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_customer_groups');
        $group = CustomerGroup::findOrFail($id);
        abort_if(Customer::where('customer_group_id', $group->id)->exists(), 409, 'Customer group has customers assigned.');
        $group->delete();
        return $this->success([], 'Customer group deleted.');
    }

    public function customers(Request $request): JsonResponse
    {
        $this->requirePermission('view_customers');
        $query = Customer::query()->with('customerGroup')
            ->when($request->filled('search'), function ($q) use ($request) { $t = '%'.trim((string) $request->query('search')).'%'; $q->where(fn ($n) => $n->where('name', 'like', $t)->orWhere('phone', 'like', $t)->orWhere('email', 'like', $t)->orWhere('code', 'like', $t)); })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->boolean('status')));
        return $this->success($this->page($request, $query->orderBy('name'), fn (Customer $c) => $this->customerData($c), 'customers'));
    }

    public function saveCustomer(Request $request, ?int $id = null): JsonResponse
    {
        $this->requirePermission($id ? 'update_customers' : 'create_customers');
        $customer = $id ? Customer::findOrFail($id) : new Customer;
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'address' => ['nullable', 'string', 'max:2000'], 'note' => ['nullable', 'string', 'max:2000'], 'customer_group_id' => ['nullable', 'exists:customer_groups,id'], 'status' => ['sometimes', 'boolean']]);
        if (! $id) { $data['code'] = $request->input('code', 'CUS-'.strtoupper(Str::random(8))); $data['created_by'] = $request->user()->id; $data['balance'] = 0; $data['reward_points'] = 0; }
        $customer->fill($data + ['status' => $request->boolean('status', true)])->save();
        return $this->success(['customer' => $this->customerData($customer->fresh('customerGroup'))], $id ? 'Customer updated.' : 'Customer created.', $id ? 200 : 201);
    }

    public function deleteCustomer(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_customers');
        $customer = Customer::findOrFail($id);
        abort_if($customer->sales()->exists(), 409, 'Customer has sales history; deactivate instead.');
        $customer->delete();
        return $this->success([], 'Customer deleted.');
    }

    public function suppliers(Request $request): JsonResponse
    {
        $this->requirePermission('view_suppliers');
        $query = Supplier::query()->when($request->filled('search'), function ($q) use ($request) { $t = '%'.trim((string) $request->query('search')).'%'; $q->where(fn ($n) => $n->where('name', 'like', $t)->orWhere('phone', 'like', $t)->orWhere('company_name', 'like', $t)->orWhere('email', 'like', $t)); });
        return $this->success($this->page($request, $query->orderBy('name'), fn (Supplier $s) => $this->supplierData($s), 'suppliers'));
    }

    public function saveSupplier(Request $request, ?int $id = null): JsonResponse
    {
        $this->requirePermission($id ? 'update_suppliers' : 'create_suppliers');
        $supplier = $id ? Supplier::findOrFail($id) : new Supplier;
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'company_name' => ['nullable', 'string', 'max:255'], 'address' => ['nullable', 'string', 'max:2000'], 'note' => ['nullable', 'string', 'max:2000'], 'is_active' => ['sometimes', 'boolean']]);
        if (! $id) { $data['created_by'] = $request->user()->id; $data['balance'] = 0; }
        $supplier->fill($data + ['is_active' => $request->boolean('is_active', true)])->save();
        return $this->success(['supplier' => $this->supplierData($supplier)], $id ? 'Supplier updated.' : 'Supplier created.', $id ? 200 : 201);
    }

    public function deleteSupplier(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_suppliers');
        $supplier = Supplier::findOrFail($id);
        abort_if($supplier->purchases()->exists() || $supplier->purchaseReturns()->exists(), 409, 'Supplier has purchase history; deactivate instead.');
        $supplier->delete();
        return $this->success([], 'Supplier deleted.');
    }

    public function accounts(Request $request): JsonResponse
    {
        return app(WorkspaceController::class)->accounts($request);
    }

    public function saveAccount(Request $request, ?int $id = null): JsonResponse
    {
        $this->requirePermission($id ? 'update_accounts' : 'create_accounts');
        $account = $id ? Account::findOrFail($id) : new Account;
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('accounts', 'name')->ignore($id)->whereNull('deleted_at')], 'account_number' => ['nullable', 'string', 'max:255', Rule::unique('accounts', 'account_number')->ignore($id)->whereNull('deleted_at')], 'type' => ['required', Rule::in(['cash', 'bank', 'mobile_banking', 'other'])], 'initial_balance' => ['nullable', 'numeric', 'min:0'], 'note' => ['nullable', 'string', 'max:1000'], 'is_active' => ['sometimes', 'boolean'], 'allow_manual_amount' => ['sometimes', 'boolean'], 'is_default' => ['sometimes', 'boolean']]);
        $initial = (float) ($data['initial_balance'] ?? 0);
        $isDefault = $request->exists('is_default')
            ? $request->boolean('is_default')
            : (bool) ($account->is_default ?? false);
        if ($id) {
            $delta = $initial - (float) $account->initial_balance;
            $account->current_balance = (float) $account->current_balance + $delta;
        } else { $account->current_balance = $initial; $account->created_by = $request->user()->id; }
        $account->fill($data + ['is_active' => $request->boolean('is_active', true), 'allow_manual_amount' => $request->boolean('allow_manual_amount')]);
        $account->is_default = $isDefault;
        $account->save();
        if ($isDefault) Account::setAsDefault($account->id);
        elseif ($request->exists('is_default')) Account::clearDefaultIfMatches($account->id);
        return $this->success(['account' => $account->fresh()], $id ? 'Account updated.' : 'Account created.', $id ? 200 : 201);
    }

    public function deleteAccount(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_accounts');
        $account = Account::findOrFail($id);
        abort_if($account->transactions()->exists(), 409, 'Account has transaction history; deactivate instead.');
        Account::clearDefaultIfMatches($id);
        $account->forceDelete();
        return $this->success([], 'Account deleted.');
    }

    public function makeDefaultAccount(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_accounts');
        $account = Account::where('is_active', 1)->findOrFail($id);
        Account::setAsDefault($id);
        return $this->success(['account' => $account->fresh()], 'Default account updated.');
    }

    public function statement(Request $request): JsonResponse
    {
        $this->requirePermission('view_accounts');
        $data = $request->validate(['account_id' => ['required', 'integer', 'exists:accounts,id'], 'start_date' => ['nullable', 'date'], 'end_date' => ['nullable', 'date', 'after_or_equal:start_date']]);
        $account = $this->accessibleAccountQuery($request)->findOrFail($data['account_id']);
        $query = Transaction::with('creator')->where('account_id', $account->id)->when($request->filled('start_date'), fn ($q) => $q->whereDate('date', '>=', $data['start_date']))->when($request->filled('end_date'), fn ($q) => $q->whereDate('date', '<=', $data['end_date']));
        $opening = (float) $account->initial_balance;
        if ($request->filled('start_date')) {
            $prior = Transaction::where('account_id', $account->id)->whereDate('date', '<', $data['start_date'])->selectRaw("SUM(CASE WHEN type='credit' THEN amount ELSE -amount END) AS net")->value('net');
            $opening += (float) $prior;
        }
        $page = $this->page($request, $query->orderBy('date')->orderBy('id'), fn (Transaction $t) => ['id' => (int) $t->id, 'date' => (string) $t->date, 'type' => $t->type, 'amount' => (float) $t->amount, 'balance' => (float) $t->balance, 'reason' => $t->reason, 'created_by' => $t->creator?->name], 'transactions');
        $page['account'] = ['id' => (int) $account->id, 'name' => $account->name];
        $page['opening_balance'] = $opening;
        return $this->success($page);
    }

    public function balanceSheet(Request $request): JsonResponse
    {
        $this->requirePermission('view_accounts');
        $data = $request->validate(['date' => ['nullable', 'date']]);
        $date = $data['date'] ?? now()->toDateString();
        $accounts = $this->accessibleAccountQuery($request)->orderBy('name')->get();
        $balances = $accounts->map(function (Account $account) use ($date) {
            $credit = Transaction::where('account_id', $account->id)->whereDate('date', '<=', $date)->where('type', 'credit')->sum('amount');
            $debit = Transaction::where('account_id', $account->id)->whereDate('date', '<=', $date)->where('type', 'debit')->sum('amount');
            return ['id' => (int) $account->id, 'name' => $account->name, 'number' => $account->account_number, 'balance' => (float) $account->initial_balance + (float) $credit - (float) $debit];
        })->filter(fn ($a) => abs($a['balance']) > 0.00001)->values();
        $cash = (float) $balances->sum('balance');
        $customerDue = (float) $this->applyBranchScope(Sale::query(), $request)->whereDate('sale_date', '<=', $date)->sum('due_amount');
        $supplierDue = (float) $this->applyBranchScope(Purchase::query(), $request)->whereDate('date', '<=', $date)->sum('due_amount');
        return $this->success(['date' => $date, 'accounts' => $balances, 'cash_bank' => $cash, 'customer_due' => $customerDue, 'total_assets' => $cash + $customerDue, 'supplier_due' => $supplierDue, 'total_liabilities' => $supplierDue, 'net_equity' => $cash + $customerDue - $supplierDue]);
    }

    public function expenses(Request $request): JsonResponse
    {
        return app(WorkspaceController::class)->expenses($request);
    }

    public function expenseOptions(Request $request): JsonResponse
    {
        $this->requirePermission('view_expenses', 'create_expenses');
        return $this->success(['categories' => ExpensesCategory::where('status', 1)->orderBy('name')->get(['id', 'name']), 'accounts' => $this->accessibleAccountQuery($request)->orderBy('name')->get(['id', 'name', 'current_balance']), 'branches' => $this->accessibleBranchRows($request)]);
    }

    public function saveExpense(Request $request, ?int $id = null): JsonResponse
    {
        $this->requirePermission($id ? 'update_expenses' : 'create_expenses');
        $expense = $id ? $this->applyBranchScope(Expenses::query(), $request)->findOrFail($id) : new Expenses;
        $data = $request->validate(['date' => ['required', 'date'], 'category_id' => ['required', 'exists:expenses_categories,id'], 'branch_id' => ['required', 'exists:branches,id'], 'account_id' => ['required', 'exists:accounts,id'], 'amount' => ['required', 'numeric', 'gt:0'], 'note' => ['nullable', 'string', 'max:2000']]);
        $this->assertBranchAccess($request, (int) $data['branch_id']);
        $account = $this->accessibleAccountQuery($request)->lockForUpdate()->findOrFail($data['account_id']);
        $oldAmount = $id ? (float) $expense->amount : 0;
        if ($id && (int) $expense->account_id !== (int) $data['account_id']) {
            abort(422, 'Changing the account of an existing expense is not supported; delete and recreate it.');
        }
        $delta = (float) $data['amount'] - $oldAmount;
        if ($delta > 0) abort_if($account->current_balance < $delta, 422, 'Insufficient account balance.');
        DB::transaction(function () use ($expense, $data, $request, $id, $account, $delta) {
            $expense->fill($data + ['status' => 1, $id ? 'updated_by' : 'created_by' => $request->user()->id])->save();
            if ($delta > 0) TransactionService::createTransaction($data['account_id'], 'debit', $delta, 'Expense: '.$expense->note, $data['date'], $expense);
            elseif ($delta < 0) TransactionService::createTransaction($data['account_id'], 'credit', abs($delta), 'Expense adjustment: '.$expense->note, $data['date'], $expense);
        });
        return $this->success(['expense' => $expense->fresh(['category', 'branch', 'account'])], $id ? 'Expense updated.' : 'Expense created.', $id ? 200 : 201);
    }

    public function deleteExpense(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_expenses');
        $expense = $this->applyBranchScope(Expenses::query(), $request)->findOrFail($id);
        DB::transaction(function () use ($expense) {
            if ($expense->account_id) TransactionService::createTransaction($expense->account_id, 'credit', $expense->amount, 'Expense reversal: '.$expense->note, now()->toDateString(), $expense);
            $expense->delete();
        });
        return $this->success([], 'Expense deleted and account balance reversed.');
    }

    public function expenseCategories(Request $request): JsonResponse
    {
        $this->requirePermission('view_expenses_categories');
        $query = ExpensesCategory::query()->withCount('expenses')->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim((string) $request->query('search')).'%'));
        return $this->success($this->page($request, $query->orderBy('name'), fn (ExpensesCategory $c) => ['id' => (int) $c->id, 'name' => $c->name, 'code' => $c->code, 'status' => (bool) $c->status, 'expenses_count' => (int) $c->expenses_count], 'categories'));
    }

    public function saveExpenseCategory(Request $request, ?int $id = null): JsonResponse
    {
        $this->requirePermission($id ? 'update_expenses_categories' : 'create_expenses_categories');
        $category = $id ? ExpensesCategory::findOrFail($id) : new ExpensesCategory;
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('expenses_categories', 'name')->ignore($id)], 'code' => ['nullable', 'string', 'max:100'], 'status' => ['sometimes', 'boolean']]);
        $category->fill($data + ['status' => $request->boolean('status', true)])->save();
        return $this->success(['category' => $category], $id ? 'Expense category updated.' : 'Expense category created.', $id ? 200 : 201);
    }

    public function deleteExpenseCategory(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_expenses_categories');
        $category = ExpensesCategory::withCount('expenses')->findOrFail($id);
        abort_if($category->expenses_count > 0, 409, 'Category has expenses assigned; deactivate it instead.');
        $category->delete();
        return $this->success([], 'Expense category deleted.');
    }

    private function accessibleBranchRows(Request $request)
    {
        return app(CatalogController::class)->accessibleBranchesForUser($request->user())->orderBy('name')->get(['id', 'name']);
    }

    private function assertBranchAccess(Request $request, int $branchId): void
    {
        abort_unless($this->accessibleBranchRows($request)->contains('id', $branchId), 403, 'You do not have access to this branch.');
    }

    private function scopeTransferBranches($query, Request $request): void
    {
        $branches = $this->accessibleBranchRows($request)->pluck('id')->all();
        abort_if($branches === [], 403, 'No branch access is assigned to this user.');
        $query->where(fn ($q) => $q->whereIn('from_branch_id', $branches)->orWhereIn('to_branch_id', $branches));
        if ($request->filled('branch_id')) {
            $branchId = (int) $request->query('branch_id');
            abort_unless(in_array($branchId, $branches, true), 403, 'You do not have access to this branch.');
            $query->where(fn ($q) => $q->where('from_branch_id', $branchId)->orWhere('to_branch_id', $branchId));
        }
    }

    private function transferData(Transfer $t, bool $withItems = false): array
    {
        $data = ['id' => (int) $t->id, 'reference_no' => $t->reference_no, 'date' => $t->created_at?->toDateString(), 'from_branch_id' => (int) $t->from_branch_id, 'from_branch' => $t->fromBranch?->name, 'to_branch_id' => (int) $t->to_branch_id, 'to_branch' => $t->toBranch?->name, 'status' => $t->status, 'item_count' => (int) $t->item_count, 'total_qty' => (float) $t->total_qty, 'shipping_cost' => (float) $t->shipping_cost, 'grand_total' => (float) $t->grand_total, 'note' => $t->note, 'created_by' => $t->user?->name];
        if ($withItems) $data['items'] = $t->details->map(fn ($d) => ['product_id' => (int) $d->product_id, 'product' => $d->product?->product_title, 'variant_id' => $d->variant_id, 'variant' => $d->variant?->variant_name ?? $d->variant?->item_code, 'qty' => (float) $d->qty, 'unit_cost' => (float) $d->net_unit_cost, 'tax' => (float) $d->tax, 'subtotal' => (float) $d->subtotal, 'batch_no' => $d->batch_no])->values();
        return $data;
    }

    private function validateTransfer(Request $request): array
    {
        return $request->validate(['from_branch_id' => ['required', 'integer', 'exists:branches,id'], 'to_branch_id' => ['required', 'integer', 'different:from_branch_id', 'exists:branches,id'], 'status' => ['required', Rule::in(['request', 'sent', 'completed'])], 'shipping_cost' => ['nullable', 'numeric', 'min:0'], 'note' => ['nullable', 'string', 'max:2000'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'], 'items.*.variant_id' => ['nullable', 'integer'], 'items.*.qty' => ['required', 'numeric', 'gt:0'], 'items.*.unit_cost' => ['required', 'numeric', 'min:0'], 'items.*.tax' => ['nullable', 'numeric', 'min:0'], 'items.*.batch_no' => ['nullable', 'string', 'max:255']]);
    }

    private function mergeTransferArrays(Request $request, array $data): void
    {
        $items = collect($data['items']);
        $subtotals = $items->map(fn ($i) => (float) $i['qty'] * (float) $i['unit_cost'] + (float) ($i['tax'] ?? 0))->all();
        $request->merge(['product_id' => $items->pluck('product_id')->all(), 'variant_id' => $items->pluck('variant_id')->all(), 'qty' => $items->pluck('qty')->all(), 'net_unit_cost' => $items->pluck('unit_cost')->all(), 'tax' => $items->map(fn ($i) => $i['tax'] ?? 0)->all(), 'subtotal' => $subtotals, 'batch_no' => $items->pluck('batch_no')->all(), 'grand_total' => array_sum($subtotals) + (float) ($data['shipping_cost'] ?? 0)]);
    }

    private function purchaseInput(Request $request): array
    {
        $data = $request->validate(['branch_id' => ['required_without:quotation_id', 'nullable', 'integer', 'exists:branches,id'], 'supplier_id' => ['required_without:quotation_id', 'nullable', 'integer', 'exists:suppliers,id'], 'date' => ['required', 'date'], 'status' => ['required', Rule::in(['received', 'pending', 'ordered'])], 'payment_method' => ['nullable', 'integer', 'exists:accounts,id'], 'paid_amount' => ['nullable', 'numeric', 'min:0'], 'order_tax' => ['nullable', 'numeric', 'min:0'], 'order_discount' => ['nullable', 'numeric', 'min:0'], 'shipping_cost' => ['nullable', 'numeric', 'min:0'], 'currency' => ['nullable', 'string', 'max:10'], 'exchange_rate' => ['nullable', 'numeric', 'gt:0'], 'memo_no' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:2000'], 'quotation_id' => ['nullable', 'integer', 'exists:quotations,id'], 'items' => ['required_without:quotation_id', 'nullable', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'], 'items.*.variant_id' => ['nullable', 'integer'], 'items.*.qty' => ['required', 'numeric', 'gt:0'], 'items.*.unit_cost' => ['required', 'numeric', 'min:0'], 'items.*.tax' => ['nullable', 'numeric', 'min:0'], 'items.*.discount' => ['nullable', 'numeric', 'min:0'], 'items.*.batch_no' => ['nullable', 'string', 'max:255'], 'items.*.expire_date' => ['nullable', 'date'], 'items.*.imei_numbers' => ['nullable', 'string', 'max:5000']]);
        if (! empty($data['quotation_id'])) {
            $quotation = $this->applyBranchScope(Quotation::query(), $request)
                ->with('items')->findOrFail($data['quotation_id']);
            abort_unless($quotation->status === 'pending', 409, 'This quotation has already been converted.');
            abort_if($quotation->expiry_date && $quotation->expiry_date < today()->toDateString(), 409, 'This quotation has expired.');
            abort_unless($quotation->supplier_id, 422, 'This quotation has no supplier and cannot be converted to a purchase.');
            $data['branch_id'] = (int) $quotation->branch_id;
            $data['supplier_id'] = (int) $quotation->supplier_id;
            $data['items'] = $quotation->items->map(fn ($item) => [
                'product_id' => (int) $item->product_id,
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'qty' => (float) $item->qty,
                'unit_cost' => (float) $item->net_unit_price,
                'tax' => (float) $item->tax,
                'discount' => (float) $item->discount,
                'batch_no' => $item->batch_no,
            ])->all();
        }
        $items = collect($data['items']);
        $lineTotal = $items->sum(fn ($i) => ((float) $i['unit_cost'] * (float) $i['qty']) + (float) ($i['tax'] ?? 0) - (float) ($i['discount'] ?? 0));
        $data['grand_total'] = $lineTotal + (float) ($data['order_tax'] ?? 0) - (float) ($data['order_discount'] ?? 0) + (float) ($data['shipping_cost'] ?? 0);
        $data['paid_amount'] = min((float) ($data['paid_amount'] ?? 0), $data['grand_total']);
        $data['product_id'] = $items->pluck('product_id')->all();
        $data['variant_id'] = $items->pluck('variant_id')->all();
        $data['qty'] = $items->pluck('qty')->all();
        $data['net_unit_cost'] = $items->pluck('unit_cost')->all();
        $data['tax'] = $items->map(fn ($i) => $i['tax'] ?? 0)->all();
        $data['discount'] = $items->map(fn ($i) => $i['discount'] ?? 0)->all();
        $data['batch_no'] = $items->pluck('batch_no')->all();
        $data['expire_date'] = $items->pluck('expire_date')->all();
        $data['imei_numbers'] = $items->pluck('imei_numbers')->all();
        $data['exchange_rate'] = $data['exchange_rate'] ?? 1;
        $data['payment_method'] = $data['payment_method'] ?? '';
        return $data;
    }

    private function purchaseData(Purchase $p, bool $withItems = false): array
    {
        $data = ['id' => (int) $p->id, 'date' => (string) $p->date, 'reference_no' => $p->invoice_no ?: $p->memo_no, 'invoice_no' => $p->invoice_no, 'memo_no' => $p->memo_no, 'branch_id' => (int) $p->branch_id, 'branch' => $p->branch?->name, 'supplier_id' => (int) $p->supplier_id, 'supplier' => $p->supplier?->name, 'status' => $p->status, 'grand_total' => (float) $p->grand_total, 'paid_amount' => (float) $p->paid_amount, 'due_amount' => (float) $p->due_amount, 'payment_status' => (float) $p->due_amount <= 0 ? 'paid' : ((float) $p->paid_amount > 0 ? 'partial' : 'due'), 'order_tax' => (float) $p->order_tax, 'order_discount' => (float) $p->order_discount, 'shipping_cost' => (float) $p->shipping_cost, 'note' => $p->note];
        if ($withItems) $data['items'] = $p->details->map(fn ($i) => ['product_id' => (int) $i->product_id, 'product' => $i->product?->product_title, 'variant_id' => $i->variant_id, 'variant' => $i->variant?->variant_name ?? $i->variant?->item_code, 'qty' => (float) $i->qty, 'unit_cost' => (float) $i->net_unit_cost, 'tax' => (float) $i->tax, 'discount' => (float) $i->discount, 'total' => (float) $i->total, 'batch_no' => $i->batch_no, 'expire_date' => $i->expire_date])->values();
        return $data;
    }

    private function nextPurchaseReference(): string
    {
        $year = now()->format('Y');
        $seq = Purchase::whereYear('date', $year)->count() + 1;
        return 'PUR-'.$year.'-'.str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }

    private function validateReturnInput(Request $request, string $sourceKey): array
    {
        return $request->validate([$sourceKey => ['required', 'integer'], 'return_note' => ['nullable', 'string', 'max:2000'], 'staff_note' => ['nullable', 'string', 'max:2000'], 'items' => ['required', 'array', 'min:1'], 'items.*.product_id' => ['required', 'integer', 'exists:products,id'], 'items.*.variant_id' => ['nullable', 'integer'], 'items.*.qty' => ['required', 'numeric', 'gt:0'], 'items.*.unit_price' => ['nullable', 'numeric', 'min:0'], 'items.*.imei_number' => ['nullable', 'array']]);
    }

    private function mergeReturnArrays(Request $request, array $data): void
    {
        $items = collect($data['items']);
        $request->merge(['return_items' => $items->pluck('product_id')->all(), 'variant_id' => $items->pluck('variant_id')->all(), 'qty' => $items->pluck('qty')->all(), 'unit_price' => $items->pluck('unit_price')->all(), 'imei_number' => $items->pluck('imei_number')->all()]);
    }

    private function saleReturnData(SaleReturn $r, bool $withItems = false): array
    {
        $data = ['id' => (int) $r->id, 'reference_no' => $r->reference_no, 'sale_id' => (int) $r->sale_id, 'invoice_no' => $r->sale?->invoice_no, 'customer' => $r->customer?->name, 'branch_id' => (int) $r->branch_id, 'branch' => $r->branch?->name, 'date' => (string) $r->date, 'status' => $r->status, 'total_qty' => (float) $r->total_qty, 'grand_total' => (float) $r->grand_total, 'return_note' => $r->return_note, 'staff_note' => $r->staff_note];
        if ($withItems) $data['items'] = $r->details->map(fn ($i) => ['product_id' => (int) $i->product_id, 'product' => $i->product?->product_title, 'variant_id' => $i->variant_id, 'variant' => $i->variant?->variant_name ?? $i->variant?->item_code, 'qty' => (float) $i->qty, 'unit_price' => (float) $i->unit_price, 'total' => (float) $i->total])->values();
        return $data;
    }

    private function purchaseReturnData(PurchaseReturn $r, bool $withItems = false): array
    {
        $data = ['id' => (int) $r->id, 'reference_no' => $r->reference_no, 'purchase_id' => (int) $r->purchase_id, 'invoice_no' => $r->purchase?->invoice_no, 'supplier' => $r->supplier?->name, 'branch_id' => (int) $r->branch_id, 'branch' => $r->branch?->name, 'date' => (string) $r->return_date, 'status' => $r->status, 'payment_status' => $r->payment_status, 'total_qty' => (float) $r->total_qty, 'grand_total' => (float) $r->grand_total, 'return_note' => $r->return_note, 'staff_note' => $r->staff_note];
        if ($withItems) $data['items'] = $r->details->map(fn ($i) => ['product_id' => (int) $i->product_id, 'product' => $i->product?->product_title, 'variant_id' => $i->variant_id, 'variant' => $i->variant?->variant_name ?? $i->variant?->item_code, 'qty' => (float) $i->qty, 'unit_price' => (float) $i->unit_price, 'total' => (float) $i->total])->values();
        return $data;
    }

    private function customerData(Customer $c): array
    {
        return ['id' => (int) $c->id, 'name' => $c->name, 'code' => $c->code, 'phone' => $c->phone, 'email' => $c->email, 'address' => $c->address, 'group_id' => $c->customer_group_id, 'group' => $c->customerGroup?->name, 'balance' => (float) $c->balance, 'previous_due' => (float) $c->previous_due, 'reward_points' => (int) $c->reward_points, 'status' => (bool) $c->status, 'note' => $c->note];
    }

    private function supplierData(Supplier $s): array
    {
        return ['id' => (int) $s->id, 'name' => $s->name, 'company_name' => $s->company_name, 'phone' => $s->phone, 'email' => $s->email, 'address' => $s->address, 'balance' => (float) $s->balance, 'previous_due' => (float) $s->previous_due, 'is_active' => (bool) $s->is_active, 'note' => $s->note];
    }

    private function storeDocument(Request $request, string $folder): ?string
    {
        if (! $request->hasFile('document')) return null;
        $file = $request->file('document');
        $name = now()->format('YmdHis').'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/'.$folder), $name);
        return $name;
    }
}

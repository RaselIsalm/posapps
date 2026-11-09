<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class QuotationApiController extends ApiController
{
    public function options(Request $request): JsonResponse
    {
        $this->requirePermission('view_quotations', 'create_quotations');
        $catalog = app(CatalogController::class);
        $branches = $catalog->accessibleBranchesForUser($request->user())->orderBy('name')->get(['id', 'name']);
        return $this->success([
            'branches' => $branches,
            'customers' => Customer::where('status', 1)->orderBy('name')->get(['id', 'name', 'code', 'phone']),
            'suppliers' => Supplier::where('is_active', 1)->orderBy('name')->get(['id', 'name', 'phone']),
            'accounts' => $this->accessibleAccountQuery($request)->orderBy('name')->get(['id', 'name']),
            'employees' => Employee::where('status', 1)->whereIn('branch_id', $branches->pluck('id'))->orderBy('id')->get(['id', 'user_id', 'branch_id']),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $this->requirePermission('view_quotations');
        $query = $this->applyBranchScope(Quotation::query(), $request)
            ->with(['branch', 'customer', 'supplier', 'seller.user'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.trim((string) $request->query('search')).'%';
                $q->where(fn ($n) => $n->where('reference_no', 'like', $term)
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term))
                    ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', $term)));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('start_date'), fn ($q) => $q->whereDate('date', '>=', $request->query('start_date')))
            ->when($request->filled('end_date'), fn ($q) => $q->whereDate('date', '<=', $request->query('end_date')));

        $query->whereNotIn('status', ['sale_generated', 'purchase_generated']);
        return $this->success($this->page($request, $query->latest('date')->latest('id'), fn (Quotation $q) => $this->data($q), 'quotations'));
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_quotations');
        $quotation = $this->applyBranchScope(Quotation::query(), $request)
            ->with(['items.product', 'items.variant', 'branch', 'customer', 'supplier', 'seller.user', 'account'])
            ->findOrFail($id);
        return $this->success(['quotation' => $this->data($quotation, true)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requirePermission('create_quotations');
        $input = $this->validated($request);
        $this->assertBranch($request, (int) $input['branch_id']);
        $quotation = DB::transaction(function () use ($request, $input) {
            $quotation = new Quotation;
            $this->fillQuotation($quotation, $input, $request, true);
            $quotation->reference_no = 'QUO-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
            $quotation->status = 'pending';
            $quotation->created_by = $request->user()->id;
            $quotation->save();
            $this->syncItems($quotation, $input['items']);
            return $quotation;
        });
        return $this->success(['quotation' => $this->data($quotation->fresh(['items.product', 'items.variant', 'branch', 'customer', 'supplier', 'seller.user']), true)], 'Quotation created.', 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_quotations');
        $quotation = $this->applyBranchScope(Quotation::query(), $request)->findOrFail($id);
        abort_if(in_array($quotation->status, ['sale_generated', 'purchase_generated'], true), 409, 'A converted quotation cannot be edited.');
        $input = $this->validated($request);
        $this->assertBranch($request, (int) $input['branch_id']);
        DB::transaction(function () use ($quotation, $input, $request) {
            $this->fillQuotation($quotation, $input, $request, false);
            $quotation->save();
            $quotation->items()->delete();
            $this->syncItems($quotation, $input['items']);
        });
        return $this->success(['quotation' => $this->data($quotation->fresh(['items.product', 'items.variant', 'branch', 'customer', 'supplier', 'seller.user']), true)], 'Quotation updated.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_quotations');
        $quotation = $this->applyBranchScope(Quotation::query(), $request)->findOrFail($id);
        abort_if(in_array($quotation->status, ['sale_generated', 'purchase_generated'], true), 409, 'A converted quotation cannot be deleted.');
        DB::transaction(function () use ($quotation) {
            $quotation->items()->delete();
            $quotation->delete();
        });
        return $this->success([], 'Quotation deleted.');
    }

    public function conversionData(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_quotations', 'create_quick_sale', 'create_purchases');
        $quotation = $this->applyBranchScope(Quotation::query(), $request)
            ->with(['items.product', 'items.variant', 'customer', 'supplier', 'branch'])
            ->findOrFail($id);
        abort_if($quotation->status !== 'pending', 409, 'Only pending quotations can be converted.');
        abort_if($quotation->expiry_date && $quotation->expiry_date < today()->toDateString(), 409, 'This quotation has expired.');
        $type = (string) $request->query('type', 'sale');
        abort_unless(in_array($type, ['sale', 'purchase'], true), 422, 'Conversion type must be sale or purchase.');
        if ($type === 'sale') $this->requirePermission('create_quick_sale');
        if ($type === 'purchase') $this->requirePermission('create_purchases');

        return $this->success([
            'quotation_id' => (int) $quotation->id,
            'type' => $type,
            'branch_id' => (int) $quotation->branch_id,
            'payment_account_id' => (int) ($quotation->branch?->account_id ?? $quotation->account_id),
            'customer_id' => $quotation->customer_id,
            'supplier_id' => $quotation->supplier_id,
            'expiry_date' => $quotation->expiry_date ? (string) $quotation->expiry_date : null,
            'total_discount' => (float) $quotation->total_discount,
            'total_tax' => (float) $quotation->total_tax,
            'order_discount' => (float) $quotation->order_discount,
            'shipping_cost' => (float) $quotation->shipping_cost,
            'grand_total' => (float) $quotation->grand_total,
            'items' => $quotation->items->map(fn (QuotationProduct $item) => [
                'id' => (int) $item->product_id,
                'product_id' => (int) $item->product_id,
                'name' => $item->product?->product_title,
                'qty' => (float) $item->qty,
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'price' => (float) $item->net_unit_price,
                'unit_cost' => (float) ($item->product?->product_cost ?? 0),
                'discount' => (float) $item->discount,
                'tax' => (float) $item->tax,
                'imei_number' => $item->imei_number,
            ])->values(),
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:date'],
            'branch_id' => ['required', 'integer', 'exists:branches,id'],
            'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'integer', 'exists:suppliers,id'],
            'account_id' => ['required', 'integer', 'exists:accounts,id'],
            'order_tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'order_discount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:2000'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:2048'],
            'items' => ['required', 'array', 'min:1', 'max:200'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.variant_id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.batch_no' => ['nullable', 'string', 'max:255'],
            'items.*.imei_number' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function fillQuotation(Quotation $quotation, array $input, Request $request, bool $creating): void
    {
        $employee = Employee::where('user_id', $request->user()->id)->where('status', 1)->first();
        $sellerId = $employee?->id ?? ($input['employee_id'] ?? null);
        if ($sellerId) {
            $seller = Employee::whereKey($sellerId)->where('status', 1)->firstOrFail();
            abort_unless((int) $seller->branch_id === (int) $input['branch_id'], 422, 'Selected employee must belong to the quotation branch.');
        }
        $this->accessibleAccountQuery($request)->findOrFail($input['account_id']);
        $items = collect($input['items']);
        $lineTotal = 0.0;
        $discountTotal = 0.0;
        $taxTotal = 0.0;
        foreach ($items as $item) {
            $base = (float) $item['qty'] * (float) $item['unit_price'];
            $discount = (float) ($item['discount'] ?? 0);
            abort_if($discount > $base, 422, 'Line discount cannot exceed the line price.');
            $tax = round(max(0, $base - $discount) * ((float) ($item['tax_rate'] ?? 0) / 100), 2);
            $lineTotal += round($base - $discount + $tax, 2);
            $discountTotal += $discount;
            $taxTotal += $tax;
        }
        $orderTaxRate = (float) ($input['order_tax_rate'] ?? 0);
        $orderTax = round(max(0, $lineTotal) * $orderTaxRate / 100, 2);
        $orderDiscount = (float) ($input['order_discount'] ?? 0);
        abort_if($orderDiscount > $lineTotal + $orderTax, 422, 'Order discount cannot exceed the quotation subtotal.');

        $quotation->fill([
            'date' => $input['date'], 'expiry_date' => $input['expiry_date'] ?? null,
            'seller_id' => $sellerId, 'customer_id' => $input['customer_id'] ?? null,
            'supplier_id' => $input['supplier_id'] ?? null, 'branch_id' => $input['branch_id'],
            'account_id' => $input['account_id'], 'item_count' => $items->count(),
            'total_qty' => $items->sum(fn ($i) => (float) $i['qty']),
            'total_discount' => $discountTotal + $orderDiscount, 'total_tax' => $taxTotal + $orderTax,
            'total_price' => $lineTotal, 'order_tax_rate' => $orderTaxRate, 'order_tax' => $orderTax,
            'order_discount' => $orderDiscount, 'shipping_cost' => (float) ($input['shipping_cost'] ?? 0),
            'grand_total' => round(max(0, $lineTotal + $orderTax - $orderDiscount + (float) ($input['shipping_cost'] ?? 0)), 2),
            'note' => $input['note'] ?? null,
        ]);
        if ($creating || $request->hasFile('document')) {
            $quotation->document = $this->storeDocument($request);
        }
    }

    private function syncItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $variantId = $item['variant_id'] ?? null;
            if ($variantId) abort_unless($product->productVariants()->whereKey($variantId)->exists(), 422, 'Variant does not belong to the selected product.');
            $base = (float) $item['qty'] * (float) $item['unit_price'];
            $discount = (float) ($item['discount'] ?? 0);
            $taxRate = (float) ($item['tax_rate'] ?? 0);
            $tax = round(max(0, $base - $discount) * $taxRate / 100, 2);
            QuotationProduct::create([
                'quotation_id' => $quotation->id, 'product_id' => $product->id,
                'variant_id' => $variantId, 'batch_no' => $item['batch_no'] ?? null,
                'qty' => $item['qty'], 'net_unit_price' => $item['unit_price'],
                'discount' => $discount, 'tax_rate' => $taxRate, 'tax' => $tax,
                'total' => round($base - $discount + $tax, 2), 'imei_number' => $item['imei_number'] ?? null,
            ]);
        }
    }

    private function assertBranch(Request $request, int $branchId): void
    {
        $allowed = app(CatalogController::class)->accessibleBranchesForUser($request->user())->whereKey($branchId)->exists();
        abort_unless($allowed, 403, 'You do not have access to this branch.');
    }

    private function storeDocument(Request $request): ?string
    {
        if (! $request->hasFile('document')) return null;
        $file = $request->file('document');
        $name = now()->format('YmdHis').'_'.Str::random(8).'.'.$file->getClientOriginalExtension();
        $file->move(public_path('uploads/quotations'), $name);
        return $name;
    }

    private function data(Quotation $q, bool $withItems = false): array
    {
        $data = [
            'id' => (int) $q->id, 'reference_no' => $q->reference_no,
            'date' => (string) $q->date, 'expiry_date' => $q->expiry_date,
            'status' => $q->expiry_date && $q->expiry_date < today()->toDateString() && $q->status === 'pending' ? 'expired' : $q->status,
            'branch_id' => (int) $q->branch_id, 'branch' => $q->branch?->name,
            'customer_id' => $q->customer_id, 'customer' => $q->customer?->name,
            'supplier_id' => $q->supplier_id, 'supplier' => $q->supplier?->name,
            'seller_id' => $q->seller_id, 'seller' => $q->seller?->user?->name,
            'account_id' => $q->account_id, 'item_count' => (int) $q->item_count,
            'total_qty' => (float) $q->total_qty, 'total_discount' => (float) $q->total_discount,
            'total_tax' => (float) $q->total_tax, 'total_price' => (float) $q->total_price,
            'order_tax' => (float) $q->order_tax, 'order_discount' => (float) $q->order_discount,
            'shipping_cost' => (float) $q->shipping_cost, 'grand_total' => (float) $q->grand_total,
            'note' => $q->note, 'document_url' => $q->document ? asset('uploads/quotations/'.$q->document) : null,
        ];
        if ($withItems) $data['items'] = $q->items->map(fn (QuotationProduct $i) => [
            'id' => (int) $i->id, 'product_id' => (int) $i->product_id,
            'product' => $i->product?->product_title, 'product_code' => $i->product?->product_code,
            'variant_id' => $i->variant_id, 'variant' => $i->variant?->variant_name ?? $i->variant?->item_code,
            'batch_no' => $i->batch_no, 'qty' => (float) $i->qty,
            'unit_price' => (float) $i->net_unit_price, 'discount' => (float) $i->discount,
            'tax_rate' => (float) $i->tax_rate, 'tax' => (float) $i->tax,
            'total' => (float) $i->total, 'imei_number' => $i->imei_number,
        ])->values();
        return $data;
    }
}

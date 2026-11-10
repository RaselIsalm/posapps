<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ImageHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Employee;
use App\Models\GeneralSettings;
use App\Models\MobilePosDraft;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\Sale;
use App\Services\SaleService;
use App\Support\SimplePdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PosController extends ApiController
{
    public function customers(Request $request): JsonResponse
    {
        $this->requirePermission('view_customers', 'view_quick_sale', 'create_quick_sale');
        $search = trim((string) $request->query('search', ''));
        $customers = Customer::query()->with('customerGroup')->where('status', 1)
            ->when($search !== '', fn ($q) => $q->where(function ($nested) use ($search) {
                $nested->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->orderBy('name')->limit(100)->get()
            ->map(fn (Customer $customer) => $this->customerData($customer));

        return $this->success(['customers' => $customers]);
    }

    public function customerGroups(): JsonResponse
    {
        $this->requirePermission('view_customer_groups', 'view_quick_sale', 'create_quick_sale', 'create_customers');
        $groups = CustomerGroup::query()->where('status', 1)->orderBy('name')->get()
            ->map(fn (CustomerGroup $group) => [
                'id' => (int) $group->id,
                'name' => $group->name,
                'dicountPercent' => (float) ($group->discount ?? 0),
                'type' => $group->type ?: 'percentage',
            ]);

        return $this->success(['customerGroups' => $groups]);
    }

    public function storeCustomer(Request $request): JsonResponse
    {
        $this->requirePermission('create_customers', 'create_quick_sale');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50', Rule::unique('customers', 'phone')],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'customer_group_id' => ['nullable', 'integer', 'exists:customer_groups,id'],
            'group.id' => ['nullable', 'integer', 'exists:customer_groups,id'],
        ]);
        $groupId = $validated['customer_group_id'] ?? data_get($validated, 'group.id');
        $customer = Customer::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'customer_group_id' => $groupId,
            'created_by' => $request->user()->id,
            'status' => true,
        ]);

        return $this->success(['customer' => $this->customerData($customer->load('customerGroup'))], 'Customer created successfully.', 201);
    }

    public function validateCoupon(Request $request): JsonResponse
    {
        $this->requirePermission('view_quick_sale', 'create_quick_sale');
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
            'sub_total' => ['nullable', 'numeric', 'min:0'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);
        $code = Str::upper(trim($validated['coupon_code'] ?? $validated['code']));
        $subTotal = (float) ($validated['sub_total'] ?? $validated['amount'] ?? $validated['price'] ?? 0);
        $coupon = Coupon::query()->whereRaw('UPPER(code) = ?', [$code])->first();

        if (! $coupon || ! $coupon->status || $coupon->start_date > today()->toDateString() || $coupon->end_date < today()->toDateString()) {
            return response()->json(['message' => 'This coupon is invalid or inactive.'], 422);
        }
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['message' => 'This coupon has reached its usage limit.'], 422);
        }
        if ($subTotal < (float) $coupon->min_purchase) {
            return response()->json(['message' => 'The minimum purchase for this coupon has not been met.'], 422);
        }

        $discount = $coupon->type === 'percent'
            ? $subTotal * ((float) $coupon->amount / 100)
            : (float) $coupon->amount;
        if ($coupon->type === 'percent' && $coupon->max_discount) {
            $discount = min($discount, (float) $coupon->max_discount);
        }
        $discount = min($discount, $subTotal);

        return $this->success([
            'id' => (int) $coupon->id,
            'discount' => round($discount, 2),
            'discount_amount' => round($discount, 2),
            'type' => $coupon->type,
            'amount' => (float) $coupon->amount,
            'max_discount' => $coupon->max_discount === null ? null : (float) $coupon->max_discount,
            'min_purchase' => (float) $coupon->min_purchase,
        ], 'Coupon applied successfully.');
    }

    public function paymentOptions(Request $request): JsonResponse
    {
        $this->requirePermission('view_quick_sale', 'create_quick_sale');
        $branchId = $this->branchIdForSale($request, $request->integer('branch_id') ?: null);
        $branchAccountId = Branch::query()->whereKey($branchId)->value('account_id');
        $account = Account::active()->whereKey($branchAccountId)->first()
            ?? Account::active()->where('is_default', 1)->first()
            ?? Account::active()->orderBy('id')->first();
        $accounts = collect($account ? [$account] : [])
            ->map(fn (Account $account) => [
                'id' => (int) $account->id,
                'name' => $account->name,
                'logo' => '',
            ]);

        return $this->success(['paymentGatewayResources' => $accounts]);
    }

    public function store(Request $request, SaleService $saleService): JsonResponse
    {
        $this->requirePermission('create_quick_sale');
        $ids = $request->input('product_ids');
        $quantities = $request->input('qty');
        $items = $request->input('items');
        if (! is_array($items) && is_array($ids) && is_array($quantities)) {
            $items = [];
            foreach ($ids as $index => $id) {
                $items[] = ['id' => $id, 'qty' => $quantities[$index] ?? 0];
            }
        }

        $validated = validator([
            'items' => $items,
            'customer_id' => $request->input('customer_id'),
            'coupon_id' => $request->input('coupon_id'),
            'payment_method' => $request->input('payment_method'),
            'paid_amount' => $request->input('paid_amount', 0),
            'payments' => $request->input('payments'),
            'type' => $request->input('type', 'Sales'),
            'sale_id' => $request->input('sale_id'),
            'branch_id' => $request->input('branch_id'),
            'discount_type' => $request->input('discount_type', 'fixed'),
            'discount_value' => $request->input('discount_value', 0),
            'tax_percent' => $request->input('tax_percent'),
            'sale_date' => $request->input('sale_date', today()->toDateString()),
            'use_reward_points' => $request->boolean('use_reward_points'),
            'used_points' => $request->input('used_points', 0),
            'delivery_status' => $request->input('delivery_status', 'delivered'),
            'quotation_id' => $request->input('quotation_id'),
        ], [
            'items' => ['required_without:quotation_id', 'nullable', 'array', 'min:1'],
            'items.*.id' => ['required_with:items', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required_with:items', 'numeric', 'gt:0'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.imei_numbers' => ['nullable', 'array'],
            'items.*.imei_numbers.*' => ['string', 'max:100'],
            'items.*.discount_type' => ['nullable', Rule::in(['fixed', 'percent'])],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'payment_method' => ['nullable'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.method' => ['required_with:payments', 'string'],
            'payments.*.amount' => ['required_with:payments', 'numeric', 'min:0'],
            'payments.*.note' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', Rule::in(['Draft', 'Sales'])],
            'sale_id' => ['nullable', 'integer'],
            'branch_id' => ['nullable', 'integer'],
            'discount_type' => ['required', Rule::in(['fixed', 'percent'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sale_date' => ['required', 'date'],
            'use_reward_points' => ['boolean'],
            'used_points' => ['nullable', 'integer', 'min:0'],
            'delivery_status' => ['nullable', Rule::in(['pending', 'shipped', 'delivered', 'returned', 'completed'])],
            'quotation_id' => ['nullable', 'integer', 'exists:quotations,id'],
        ])->validate();

        $branchId = $this->branchIdForSale($request, $validated['branch_id'] ?? null);
        if (($validated['type'] ?? 'Sales') === 'Draft') {
            abort_if(! empty($validated['quotation_id']), 422, 'A quotation must be converted directly into a sale.');
            $draft = $this->saveDraft($request, $validated, $branchId);

            return $this->success([
                'id' => (int) $draft->id,
                'draft_id' => (int) $draft->id,
                'invoice_pdf_url' => '',
                'qr_code_path' => '',
                'redirectUrl' => '',
            ], 'Draft saved successfully.', 201);
        }

        $quotation = null;
        if (! empty($validated['quotation_id'])) {
            $quotation = Quotation::with('items')->findOrFail($validated['quotation_id']);
            abort_unless((int) $quotation->branch_id === $branchId, 403, 'Quotation belongs to a different branch.');
            abort_if($quotation->status !== 'pending', 409, 'This quotation has already been converted.');
            abort_if($quotation->expiry_date && $quotation->expiry_date < today()->toDateString(), 409, 'This quotation has expired.');
            if ($quotation->customer_id && ! empty($validated['customer_id'])) {
                abort_unless((int) $quotation->customer_id === (int) $validated['customer_id'], 422, 'Select the customer assigned to this quotation.');
            }
            $validated['customer_id'] = $quotation->customer_id;
            // Quote prices and discounts are locked terms, so a conversion cannot
            // add a second coupon or redeem rewards on top of them.
            $validated['coupon_id'] = null;
            $validated['use_reward_points'] = false;
            $validated['used_points'] = 0;
            $validated['items'] = $quotation->items->map(fn ($item) => [
                'id' => (int) $item->product_id,
                'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                'qty' => (float) $item->qty,
                'discount_type' => 'fixed',
                'discount_value' => (float) $item->discount,
            ])->all();
        }

        $employee = Employee::query()->where('user_id', $request->user()->id)->where('status', 1)->first();
        try {
            $priced = $saleService->priceItems($validated['items'], $branchId);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }
        $validatedItems = $priced['items'];
        $subTotal = (float) $priced['sub_total'];
        $lineDiscount = 0.0;
        foreach ($validatedItems as &$item) {
            $lineBase = (float) $item['price'] * (float) $item['qty'];
            $lineType = $item['discount_type'] ?? 'fixed';
            $lineValue = (float) ($item['discount_value'] ?? 0);
            $lineDiscountAmount = $lineType === 'percent' ? $lineBase * min(100, $lineValue) / 100 : min($lineBase, $lineValue);
            $item['discount_amount'] = round($lineDiscountAmount, 2);
            $lineDiscount += $item['discount_amount'];
        }
        unset($item);
        if ($quotation) {
            $subTotal = 0.0;
            $lineDiscount = 0.0;
            foreach ($quotation->items as $index => $quoteItem) {
                $item = $validatedItems[$index];
                $item['price'] = (float) $quoteItem->net_unit_price;
                $item['discount_type'] = 'fixed';
                $item['discount_value'] = (float) $quoteItem->discount;
                $item['discount_amount'] = (float) $quoteItem->discount;
                $item['tax_percent'] = (float) $quoteItem->tax_rate;
                $item['tax_amount'] = (float) $quoteItem->tax;
                $subTotal += (float) $quoteItem->net_unit_price * (float) $quoteItem->qty;
                $lineDiscount += (float) $quoteItem->discount;
                $validatedItems[$index] = $item;
            }
            $subTotal = round($subTotal, 2);
        }
        $manualDiscountType = $validated['discount_type'];
        $manualDiscountValue = (float) ($validated['discount_value'] ?? 0);
        $manualDiscountAmount = $manualDiscountType === 'percent'
            ? $subTotal * min(100, $manualDiscountValue) / 100
            : min($subTotal, $manualDiscountValue);
        $manualDiscountAmount = min($manualDiscountAmount, max(0, $subTotal - $lineDiscount));
        $discountAmount = round($lineDiscount + $manualDiscountAmount, 2);
        $groupDiscount = 0.0;
        if (! empty($validated['customer_id'])) {
            $customer = Customer::with('customerGroup')->findOrFail($validated['customer_id']);
            $group = $customer->customerGroup;
            if ($group && $group->status) {
                $groupDiscount = strtolower((string) $group->type) === 'fixed'
                    ? (float) $group->discount
                    : $subTotal * ((float) $group->discount / 100);
                $groupDiscount = min(max(0, $groupDiscount), $subTotal);
            }
        }
        $couponId = $validated['coupon_id'] ?? null;
        $couponResult = $saleService->verifyCoupon($couponId, $subTotal);
        $couponId = $couponResult['coupon_id'];
        $taxPercent = max(0, (float) ($validated['tax_percent'] ?? GeneralSettings::getGeneralSetting('default_tax') ?? 5));
        $taxableTotal = max(0, $subTotal - $discountAmount - $groupDiscount - $couponResult['coupon_discount_amount']);
        $taxAmount = $taxableTotal * ($taxPercent / 100);
        $amountDue = $taxableTotal + $taxAmount;
        if ($quotation) {
            $manualDiscountType = 'fixed';
            $manualDiscountValue = (float) $quotation->order_discount;
            $discountAmount = (float) $quotation->total_discount;
            $groupDiscount = 0.0;
            $couponId = null;
            $couponResult = ['coupon_id' => null, 'coupon_discount_amount' => 0.0];
            $taxPercent = 0.0;
            $taxAmount = (float) $quotation->total_tax;
            $amountDue = (float) $quotation->grand_total;
        }
        $paymentLines = $validated['payments'] ?? null;
        if (! is_array($paymentLines)) {
            $account = $this->paymentAccount($validated['payment_method'] ?? null, $branchId);
            $paidAmount = $request->has('paid_amount') ? (float) ($validated['paid_amount'] ?? 0) : $amountDue;
            if (! $account && $paidAmount > 0) {
                return response()->json(['message' => 'Choose an active payment account.'], 422);
            }
            $paymentLines = ($paidAmount > 0 && $account)
                ? [['method' => $account->id, 'amount' => min($paidAmount, $amountDue)]]
                : [];
        }
        foreach ($paymentLines as &$payment) {
            if ($payment['method'] !== 'Gift Card') {
                $account = $this->paymentAccount($payment['method'], $branchId);
                if (! $account) return response()->json(['message' => 'Choose an active payment account available to this branch.'], 422);
                $payment['method'] = $account->id;
            } elseif (empty($payment['note'])) {
                return response()->json(['message' => 'Enter the gift card number for this payment.'], 422);
            }
        }
        unset($payment);
        $saleData = [
            'branch_id' => $branchId,
            'seller_id' => $employee?->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'sale_date' => $validated['sale_date'],
            'items' => $validatedItems,
            'coupon_id' => $couponId,
            'discount_type' => $manualDiscountType,
            'discount_value' => $manualDiscountValue,
            'discount_amount' => $discountAmount,
            'group_discount_amount' => $groupDiscount,
            'tax_percent' => $taxPercent,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $quotation ? (float) $quotation->shipping_cost : 0,
            'payments' => $paymentLines,
            'use_reward_points' => (bool) $validated['use_reward_points'],
            'used_points' => (int) ($validated['used_points'] ?? 0),
            'delivery_status' => $validated['delivery_status'] ?? 'delivered',
            'quotation_id' => $validated['quotation_id'] ?? null,
        ];

        try {
            $sale = $saleService->createSale($saleData);
            if (! empty($validated['sale_id'])) {
                MobilePosDraft::query()->where('user_id', $request->user()->id)->whereKey($validated['sale_id'])->delete();
            }
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['message' => $exception->getMessage() ?: 'Sale could not be completed.'], 422);
        }

        return $this->success([
            'id' => (int) $sale->id,
            'draft_id' => 0,
            'invoice_pdf_url' => url('/api/v1/invoice/'.$sale->id),
            'qr_code_path' => '',
            'redirectUrl' => '',
            'sale_id' => (int) $sale->id,
            'tax_amount' => (float) $sale->tax_amount,
            'shipping_cost' => (float) ($sale->shipping_cost ?? 0),
            'grand_total' => (float) $sale->grand_total,
            'paid_amount' => (float) $sale->paid_amount,
            'due_amount' => (float) $sale->due_amount,
            'payment_status' => $sale->payment_status,
            'reward_points_earned' => (int) $sale->reward_points_earned,
            'reward_points_used' => (int) $sale->reward_points_used,
            'customer_reward_points' => ! empty($sale->customer_id) ? (int) (Customer::find($sale->customer_id)?->reward_points ?? 0) : 0,
        ], 'Sale completed successfully.', 201);
    }

    public function drafts(Request $request): JsonResponse
    {
        $this->requirePermission('view_quick_sale', 'create_quick_sale');
        $drafts = MobilePosDraft::query()->with('customer')->where('user_id', $request->user()->id)
            ->latest('updated_at')->get()->map(fn (MobilePosDraft $draft) => $this->draftData($draft));

        return $this->success(['total' => $drafts->count(), 'drafts' => $drafts], 'Drafts loaded.');
    }

    public function deleteDraft(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_quick_sale', 'create_quick_sale');
        $draft = MobilePosDraft::query()->where('user_id', $request->user()->id)->findOrFail($id);
        $draft->delete();

        return $this->success([], 'Draft deleted successfully.');
    }

    public function paymentSuccess(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_sale_list', 'create_quick_sale');
        $sale = $this->applyBranchScope(Sale::query(), $request)->with('payments')->findOrFail($id);

        return $this->success(['id' => (int) $sale->id, 'payment_status' => $sale->payment_status]);
    }

    public function invoice(Request $request, int $id)
    {
        $this->requirePermission('view_sale_list', 'create_quick_sale');
        $sale = $this->applyBranchScope(Sale::query(), $request)
            ->with(['customer', 'branch', 'productSales.product', 'payments', 'seller.user'])
            ->findOrFail($id);
        $lines = [
            'ONE POS - SALE INVOICE',
            'Invoice: '.($sale->invoice_no ?: $sale->id),
            'Date: '.($sale->sale_date?->format('Y-m-d') ?? $sale->sale_date),
            'Branch: '.($sale->branch?->name ?? ''),
            'Customer: '.($sale->customer?->name ?? 'Walk-in customer'),
            'Served by: '.($sale->seller?->user?->name ?? ''),
            '',
            'ITEMS',
        ];
        foreach ($sale->productSales as $item) {
            $lines[] = ($item->product?->product_title ?? 'Product')
                .' | Qty '.(float) $item->qty
                .' | Unit '.number_format((float) $item->unit_price, 2)
                .' | Total '.number_format((float) $item->total_price, 2);
        }
        array_push($lines,
            '',
            'Subtotal: '.number_format((float) $sale->sub_total, 2),
            'Discount: '.number_format((float) ($sale->discount_amount + $sale->coupon_discount_amount + $sale->group_discount_amount + $sale->point_discount_amount), 2),
            'Shipping: '.number_format((float) ($sale->shipping_cost ?? 0), 2),
            'Tax: '.number_format((float) $sale->tax_amount, 2),
            'Grand total: '.number_format((float) $sale->grand_total, 2),
            'Paid: '.number_format((float) $sale->paid_amount, 2),
            'Due: '.number_format((float) $sale->due_amount, 2),
            'Payment status: '.strtoupper((string) $sale->payment_status),
            '',
            'PAYMENTS'
        );
        foreach ($sale->payments as $payment) {
            $lines[] = ($payment->payment_method ?: 'Payment').' | '.number_format((float) $payment->amount, 2);
        }

        return SimplePdf::response($lines, 'invoice-'.($sale->invoice_no ?: $sale->id).'.pdf');
    }

    private function saveDraft(Request $request, array $validated, int $branchId): MobilePosDraft
    {
        $items = collect($validated['items'])->map(fn ($item) => [
            'id' => (int) $item['id'],
            'qty' => (float) $item['qty'],
            'variant_id' => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
            'imei_numbers' => $item['imei_numbers'] ?? [],
            'discount_type' => $item['discount_type'] ?? 'fixed',
            'discount_value' => (float) ($item['discount_value'] ?? 0),
        ])->all();
        $id = $validated['sale_id'] ?? null;
        if ($id) {
            $draft = MobilePosDraft::query()->where('user_id', $request->user()->id)->findOrFail($id);
            $draft->fill([
                'customer_id' => $validated['customer_id'] ?? null,
                'branch_id' => $branchId,
                'items' => $items,
                'coupon_code' => $validated['coupon_id'] ? Coupon::find($validated['coupon_id'])?->code : null,
            ])->save();

            return $draft;
        }

        return MobilePosDraft::create([
            'user_id' => $request->user()->id,
            'customer_id' => $validated['customer_id'] ?? null,
            'branch_id' => $branchId,
            'items' => $items,
            'coupon_code' => $validated['coupon_id'] ? Coupon::find($validated['coupon_id'])?->code : null,
        ]);
    }

    private function draftData(MobilePosDraft $draft): array
    {
        $productIds = collect($draft->items)->pluck('id');
        $products = Product::with(['category', 'brand', 'sellingUnit'])->whereIn('id', $productIds)->get()->keyBy('id');
        $lines = collect($draft->items)->map(function ($item) use ($products, $draft) {
            $product = $products->get($item['id']);
            if (! $product) {
                return null;
            }
            $quantity = (int) $item['qty'];
            $branchStock = $product->productBranches()->where('branch_id', $draft->branch_id)->whereNull('variant_id')->first();
            $price = (float) ($branchStock?->sell_price ?: $product->promotional_price ?: $product->selling_price);

            return [
                'id' => (int) $product->id,
                'name' => $product->product_title,
                'thumbnail' => ImageHelper::publicUrl($product->thumbnail, 'products'),
                'brand' => $product->brand?->name,
                'category' => $product->category?->name,
                'unit' => $product->sellingUnit?->name,
                'price' => $price,
                'stock' => (int) floor((float) $product->productBranches()->where('branch_id', $draft->branch_id)->sum('qty')),
                'code' => $product->product_code,
                'tax' => (float) $product->tax,
                'sub_total' => $price,
                'quantity' => $quantity,
            ];
        })->filter()->values();
        $total = (float) $lines->sum(fn ($item) => $item['price'] * $item['quantity']);
        $customer = $draft->customer?->load('customerGroup');
        $group = $customer?->customerGroup;
        $groupDiscount = $group && $group->status
            ? (strtolower((string) $group->type) === 'fixed'
                ? (float) $group->discount
                : $total * ((float) $group->discount / 100))
            : 0.0;
        $groupDiscount = min(max(0, $groupDiscount), $total);
        $coupon = $draft->coupon_code
            ? Coupon::query()->whereRaw('UPPER(code) = ?', [Str::upper($draft->coupon_code)])->first()
            : null;
        $couponIsValid = $coupon
            && $coupon->status
            && $coupon->start_date <= today()->toDateString()
            && $coupon->end_date >= today()->toDateString()
            && (! $coupon->usage_limit || $coupon->used_count < $coupon->usage_limit)
            && $total >= (float) $coupon->min_purchase;
        $couponDiscount = 0.0;
        if ($couponIsValid) {
            $couponDiscount = $coupon->type === 'percent'
                ? $total * ((float) $coupon->amount / 100)
                : (float) $coupon->amount;
            if ($coupon->type === 'percent' && $coupon->max_discount) {
                $couponDiscount = min($couponDiscount, (float) $coupon->max_discount);
            }
            $couponDiscount = min(max(0, $couponDiscount), max(0, $total - $groupDiscount));
        }

        return [
            'id' => (int) $draft->id,
            'products' => $lines,
            'total_product' => (int) $lines->sum('quantity'),
            'discount' => $groupDiscount + $couponDiscount,
            'tax' => 0.0,
            'total_price' => $total,
            'payment_method' => 'Draft',
            'branch_id' => $draft->branch_id,
            'coupon_id' => $couponIsValid ? $coupon?->id : null,
            'coupon_discount' => $couponDiscount,
            'coupon_code' => $couponIsValid ? $coupon?->code : null,
            'coupon_type' => $couponIsValid ? $coupon?->type : null,
            'coupon_amount' => $couponIsValid ? (float) $coupon?->amount : null,
            'coupon_max_discount' => $couponIsValid && $coupon?->max_discount !== null ? (float) $coupon->max_discount : null,
            'coupon_min_purchase' => $couponIsValid ? (float) $coupon?->min_purchase : null,
            'grand_total' => max(0, $total - $groupDiscount - $couponDiscount),
            'customer' => $customer ? [
                'id' => (int) $customer->id,
                'name' => $customer->name,
                'phone' => $customer->phone,
                'email' => $customer->email,
                'address' => $customer->address,
                'group' => $group ? [
                    'id' => (int) $group->id,
                    'name' => $group->name,
                    'dicountPercent' => (float) $group->discount,
                    'type' => $group->type ?: 'percentage',
                ] : null,
            ] : null,
            'created_at' => $draft->updated_at?->format('d/m/Y'),
            'time' => $draft->updated_at?->format('H:i'),
        ];
    }

    private function customerData(Customer $customer): array
    {
        $group = $customer->customerGroup;

        return [
            'id' => (int) $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'company_name' => null,
            'address' => $customer->address,
            'group' => $group ? [
                'id' => (int) $group->id,
                'name' => $group->name,
                'dicountPercent' => (float) ($group->discount ?? 0),
                'type' => $group->type ?: 'percentage',
            ] : null,
            'reward_points' => (int) ($customer->reward_points ?? 0),
            'balance' => (float) ($customer->balance ?? 0),
        ];
    }

    private function paymentAccount(mixed $value, int $branchId): ?Account
    {
        $branchAccountId = Branch::query()->whereKey($branchId)->value('account_id');
        $branchAccount = Account::active()->whereKey($branchAccountId)->first()
            ?? Account::active()->where('is_default', 1)->first()
            ?? Account::active()->orderBy('id')->first();
        if (is_numeric($value)) {
            return (int) $value === (int) ($branchAccount?->id)
                ? $branchAccount
                : null;
        }

        return $branchAccount;
    }

    private function branchIdForSale(Request $request, ?int $requested): int
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->where('status', 1)->first();
        if ($employee) {
            abort_unless($employee->branch_id, 422, 'Your employee profile has no branch assigned. Contact an administrator.');

            return (int) $employee->branch_id;
        }

        $branchId = $requested ?: ($request->filled('branch_id') ? $request->integer('branch_id') : null);
        if (! $branchId) {
            abort(422, 'Select a branch before completing a sale.');
        }
        $branchController = app(CatalogController::class);
        abort_unless($branchController->accessibleBranchesForUser($request->user())->whereKey($branchId)->exists(), 403, 'You do not have access to this branch.');

        return (int) $branchId;
    }
}

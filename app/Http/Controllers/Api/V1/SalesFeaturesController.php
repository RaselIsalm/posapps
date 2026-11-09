<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\Coupon;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use App\Models\RewardPointSetting;
use App\Models\Sale;
use App\Services\SaleService;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Native app APIs for the Website Sales workspace. Courier requests are deliberately never sent here. */
class SalesFeaturesController extends ApiController
{
    public function giftCards(Request $request): JsonResponse
    {
        $this->requirePermission('view_gift_card');
        $query = GiftCard::query()->with('customer')->when($request->filled('search'), function ($q) use ($request) {
            $term = '%'.trim((string) $request->query('search')).'%';
            $q->where(fn ($nested) => $nested->where('card_no', 'like', $term)->orWhereHas('customer', fn ($c) => $c->where('name', 'like', $term)));
        });
        $data = $this->page($request, $query->latest('id'), fn (GiftCard $card) => $this->giftCardData($card), 'gift_cards');

        return $this->success($data);
    }

    public function giftCard(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_gift_card');
        $card = GiftCard::with(['customer', 'transactions'])->findOrFail($id);

        return $this->success(['gift_card' => $this->giftCardData($card), 'transactions' => $card->transactions->map(fn ($t) => [
            'id' => (int) $t->id, 'type' => $t->transaction_type, 'amount' => (float) $t->amount,
            'date' => (string) $t->transaction_date, 'sale_id' => $t->sale_id, 'note' => $t->note ?? '',
        ])]);
    }

    public function storeGiftCard(Request $request): JsonResponse
    {
        $this->requirePermission('create_gift_card');
        $data = $request->validate([
            'card_no' => ['required', 'string', 'max:100', 'unique:gift_cards,card_no'],
            'value' => ['required', 'numeric', 'min:1'], 'price' => ['required', 'numeric', 'min:0'],
            'expiry_date' => ['required', 'date', 'after:today'], 'customer_id' => ['nullable', 'exists:customers,id'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
        ]);
        abort_if($data['price'] > 0 && empty($data['account_id']), 422, 'Choose an account for the card sale.');
        $card = DB::transaction(function () use ($data, $request) {
            $card = GiftCard::create([
                'card_no' => trim($data['card_no']), 'customer_id' => $data['customer_id'] ?? null,
                'value' => $data['value'], 'price' => $data['price'], 'balance' => $data['value'],
                'expiry_date' => $data['expiry_date'], 'status' => 'active', 'created_by' => $request->user()->id,
            ]);
            if ((float) $data['price'] > 0) {
                TransactionService::createTransaction($data['account_id'], 'credit', $data['price'], 'Gift Card Sold: '.$card->card_no, today()->toDateString(), $card);
            }
            GiftCardTransaction::create(['gift_card_id' => $card->id, 'transaction_type' => 'credit', 'amount' => $data['value'], 'transaction_date' => today()->toDateString(), 'note' => 'Card issued']);

            return $card;
        });

        return $this->success(['gift_card' => $this->giftCardData($card->load('customer'))], 'Gift card created.', 201);
    }

    public function updateGiftCard(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_gift_card');
        $card = GiftCard::findOrFail($id);
        $data = $request->validate([
            'card_no' => ['required', 'string', 'max:100', Rule::unique('gift_cards', 'card_no')->ignore($card->id)],
            'expiry_date' => ['required', 'date'], 'customer_id' => ['nullable', 'exists:customers,id'],
            'status' => ['required', Rule::in(['active', 'inactive', 'used'])],
        ]);
        $card->update($data);

        return $this->success(['gift_card' => $this->giftCardData($card->fresh('customer'))], 'Gift card updated.');
    }

    public function rechargeGiftCard(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_gift_card');
        $data = $request->validate(['amount' => ['required', 'numeric', 'min:0.01'], 'account_id' => ['required', 'exists:accounts,id'], 'new_expiry_date' => ['nullable', 'date', 'after:today'], 'note' => ['nullable', 'string', 'max:500']]);
        $card = DB::transaction(function () use ($id, $data) {
            $card = GiftCard::query()->lockForUpdate()->findOrFail($id);
            $card->increment('balance', $data['amount']);
            $card->increment('value', $data['amount']);
            if (in_array($card->status, ['used', 'inactive'], true)) $card->status = 'active';
            if (! empty($data['new_expiry_date'])) $card->expiry_date = $data['new_expiry_date'];
            $card->save();
            TransactionService::createTransaction($data['account_id'], 'credit', $data['amount'], 'Gift Card Recharge: '.$card->card_no, today()->toDateString(), $card);
            GiftCardTransaction::create(['gift_card_id' => $card->id, 'transaction_type' => 'credit', 'amount' => $data['amount'], 'transaction_date' => today()->toDateString(), 'note' => $data['note'] ?? 'Recharge']);

            return $card;
        });

        return $this->success(['gift_card' => $this->giftCardData($card->fresh('customer'))], 'Gift card recharged.');
    }

    public function deleteGiftCard(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_gift_card');
        $card = GiftCard::findOrFail($id);
        abort_if($card->transactions()->whereNotNull('sale_id')->exists(), 409, 'A gift card used in a sale cannot be deleted.');
        $card->delete();

        return $this->success([], 'Gift card deleted.');
    }

    public function coupons(Request $request): JsonResponse
    {
        $this->requirePermission('view_coupons');
        $query = Coupon::query()->when($request->filled('search'), fn ($q) => $q->where('code', 'like', '%'.trim((string) $request->query('search')).'%'));
        return $this->success($this->page($request, $query->latest('id'), fn (Coupon $coupon) => $this->couponData($coupon), 'coupons'));
    }

    public function storeCoupon(Request $request): JsonResponse
    {
        $this->requirePermission('create_coupons');
        $data = $request->validate(['code' => ['required', 'string', 'max:100', 'unique:coupons,code'], 'type' => ['required', Rule::in(['fixed', 'percent'])], 'amount' => ['required', 'numeric', 'min:0.01'], 'min_purchase' => ['nullable', 'numeric', 'min:0'], 'max_discount' => ['nullable', 'numeric', 'min:0'], 'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'usage_limit' => ['nullable', 'integer', 'min:1'], 'status' => ['required', 'boolean']]);
        $coupon = Coupon::create([...$data, 'code' => strtoupper(trim($data['code']))]);
        return $this->success(['coupon' => $this->couponData($coupon)], 'Coupon created.', 201);
    }

    public function updateCoupon(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_coupons');
        $coupon = Coupon::findOrFail($id);
        $data = $request->validate(['code' => ['required', 'string', 'max:100', Rule::unique('coupons', 'code')->ignore($coupon->id)], 'type' => ['required', Rule::in(['fixed', 'percent'])], 'amount' => ['required', 'numeric', 'min:0.01'], 'min_purchase' => ['nullable', 'numeric', 'min:0'], 'max_discount' => ['nullable', 'numeric', 'min:0'], 'start_date' => ['required', 'date'], 'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'usage_limit' => ['nullable', 'integer', 'min:1'], 'status' => ['required', 'boolean']]);
        $coupon->update([...$data, 'code' => strtoupper(trim($data['code']))]);
        return $this->success(['coupon' => $this->couponData($coupon->fresh())], 'Coupon updated.');
    }

    public function deleteCoupon(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_coupons');
        $coupon = Coupon::findOrFail($id);
        abort_if($coupon->used_count > 0, 409, 'A coupon already used in sales cannot be deleted. Disable it instead.');
        $coupon->delete();
        return $this->success([], 'Coupon deleted.');
    }

    public function rewardSettings(Request $request): JsonResponse
    {
        $this->requirePermission('view_reward_points');
        $setting = RewardPointSetting::first();
        return $this->success(['settings' => $setting ? $setting->only(['id', 'is_active', 'min_purchase_amount', 'point_earning_rate', 'min_redeem_point', 'point_redemption_rate']) : null]);
    }

    public function updateRewardSettings(Request $request): JsonResponse
    {
        $this->requirePermission('update_reward_points');
        $data = $request->validate(['is_active' => ['required', 'boolean'], 'min_purchase_amount' => ['required', 'numeric', 'min:0'], 'point_earning_rate' => ['required', 'numeric', 'min:1'], 'min_redeem_point' => ['required', 'integer', 'min:1'], 'point_redemption_rate' => ['required', 'numeric', 'min:0.01']]);
        $setting = RewardPointSetting::first();
        $setting ? $setting->update($data) : $setting = RewardPointSetting::create($data);
        return $this->success(['settings' => $setting->fresh()], 'Reward settings saved.');
    }

    public function customerRewards(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_reward_points', 'view_customers', 'view_quick_sale');
        $customer = Customer::findOrFail($id);
        return $this->success(['customer_id' => (int) $customer->id, 'customer_name' => $customer->name, 'balance' => (int) ($customer->reward_points ?? 0), 'settings' => RewardPointSetting::first()]);
    }

    public function couriers(Request $request): JsonResponse
    {
        $this->requirePermission('view_delivery');
        return $this->success(['couriers' => Courier::active()->orderBy('name')->get(['id', 'name', 'code', 'default_charge'])->map(fn ($c) => ['id' => (int) $c->id, 'name' => $c->name, 'code' => $c->code, 'default_charge' => (float) $c->default_charge])]);
    }

    public function deliverySales(Request $request): JsonResponse
    {
        $this->requirePermission('create_delivery');
        $sales = $this->applyBranchScope(Sale::query(), $request)
            ->whereIn('delivery_status', ['pending', 'shipped'])
            ->whereDoesntHave('delivery')
            ->with(['customer', 'branch'])
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(fn (Sale $sale) => [
                'id' => (int) $sale->id,
                'invoice_no' => $sale->invoice_no,
                'customer' => $sale->customer?->name ?? 'Walk-in customer',
                'branch' => $sale->branch?->name,
                'date' => $sale->sale_date?->format('Y-m-d'),
                'grand_total' => (float) $sale->grand_total,
                'due_amount' => (float) $sale->due_amount,
            ])->values();

        return $this->success(['sales' => $sales]);
    }

    public function deliveries(Request $request): JsonResponse
    {
        $this->requirePermission('view_delivery');
        $query = Delivery::query()->with(['courier', 'sale.customer.branch'])->whereHas('sale', fn ($q) => $this->applyBranchScope($q, $request))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
            ->when($request->filled('search'), function ($q) use ($request) { $term = '%'.trim((string) $request->query('search')).'%'; $q->where(fn ($n) => $n->where('invoice_no', 'like', $term)->orWhere('tracking_code', 'like', $term)->orWhere('recipient_name', 'like', $term)); });
        return $this->success($this->page($request, $query->latest('id'), fn (Delivery $d) => $this->deliveryData($d), 'deliveries'));
    }

    public function storeDelivery(Request $request): JsonResponse
    {
        $this->requirePermission('create_delivery');
        $data = $request->validate(['sale_id' => ['required', 'integer'], 'courier_id' => ['required', 'exists:couriers,id'], 'recipient_name' => ['required', 'string', 'max:255'], 'recipient_phone' => ['required', 'string', 'max:50'], 'address' => ['required', 'string', 'max:2000'], 'cod_amount' => ['required', 'numeric', 'min:0'], 'delivery_charge' => ['required', 'numeric', 'min:0'], 'tracking_code' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:2000']]);
        $sale = $this->applyBranchScope(Sale::query(), $request)->findOrFail($data['sale_id']);
        abort_if($sale->delivery()->exists(), 409, 'This sale already has a delivery.');
        $courier = Courier::active()->findOrFail($data['courier_id']);
        $delivery = DB::transaction(function () use ($data, $sale, $courier) {
            $delivery = Delivery::create([...$data, 'sale_id' => $sale->id, 'invoice_no' => $sale->invoice_no, 'order_amount' => $sale->grand_total, 'tracking_code' => $data['tracking_code'] ?? ('LOCAL-'.strtoupper(bin2hex(random_bytes(4)))), 'status' => 'pending', 'courier_response' => ['mode' => 'local_mock', 'submitted' => false]]);
            $sale->update(['delivery_status' => 'shipped']);
            return $delivery;
        });
        return $this->success(['delivery' => $this->deliveryData($delivery->load(['courier', 'sale.customer.branch']))], 'Local delivery record created. No courier service was contacted.', 201);
    }

    public function updateDeliveryStatus(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_delivery');
        $data = $request->validate(['status' => ['required', Rule::in(['pending', 'picked_up', 'in_transit', 'delivered', 'failed', 'returned', 'cancelled'])]]);
        $delivery = Delivery::query()->whereHas('sale', fn ($q) => $this->applyBranchScope($q, $request))->findOrFail($id);
        DB::transaction(function () use ($delivery, $data) { $delivery->update(['status' => $data['status']]); if ($delivery->sale) $delivery->sale->update(['delivery_status' => $data['status']]); });
        return $this->success(['delivery' => $this->deliveryData($delivery->fresh(['courier', 'sale.customer.branch']))], 'Delivery status updated locally.');
    }

    public function updateDelivery(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_delivery');
        $data = $request->validate(['courier_id' => ['required', 'exists:couriers,id'], 'recipient_name' => ['required', 'string', 'max:255'], 'recipient_phone' => ['required', 'string', 'max:50'], 'address' => ['required', 'string', 'max:2000'], 'cod_amount' => ['required', 'numeric', 'min:0'], 'delivery_charge' => ['required', 'numeric', 'min:0'], 'tracking_code' => ['nullable', 'string', 'max:255'], 'note' => ['nullable', 'string', 'max:2000']]);
        $delivery = Delivery::query()->whereHas('sale', fn ($q) => $this->applyBranchScope($q, $request))->findOrFail($id);
        Courier::active()->findOrFail($data['courier_id']);
        $delivery->update($data);
        return $this->success(['delivery' => $this->deliveryData($delivery->fresh(['courier', 'sale.customer.branch']))], 'Delivery updated locally.');
    }

    public function deleteDelivery(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('delete_delivery');
        $delivery = Delivery::query()->whereHas('sale', fn ($q) => $this->applyBranchScope($q, $request))->findOrFail($id);
        $delivery->delete();
        return $this->success([], 'Delivery record deleted.');
    }

    public function sale(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('view_sale_list');
        $sale = $this->applyBranchScope(Sale::query(), $request)->with(['customer', 'branch', 'seller.user', 'payments', 'productSales.product', 'productSales.variant', 'delivery.courier'])->findOrFail($id);
        return $this->success(['sale' => $this->saleData($sale)]);
    }

    public function updateSale(Request $request, int $id, SaleService $saleService): JsonResponse
    {
        $this->requirePermission('update_sale_list');
        $sale = $this->applyBranchScope(Sale::query(), $request)->findOrFail($id);
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1'], 'items.*.id' => ['required', 'integer', 'exists:products,id'],
            'items.*.qty' => ['required', 'numeric', 'gt:0'], 'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.imei_numbers' => ['nullable', 'array'], 'items.*.imei_numbers.*' => ['string', 'max:100'],
            'items.*.discount_type' => ['nullable', Rule::in(['fixed', 'percent'])], 'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'], 'coupon_id' => ['nullable', 'integer', 'exists:coupons,id'],
            'discount_type' => ['required', Rule::in(['fixed', 'percent'])], 'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'], 'use_reward_points' => ['nullable', 'boolean'],
            'used_points' => ['nullable', 'integer', 'min:0'], 'sale_date' => ['nullable', 'date'],
        ]);
        $branchId = (int) $sale->branch_id;
        $priced = $saleService->priceItems($data['items'], $branchId);
        $items = $priced['items'];
        $subtotal = (float) $priced['sub_total'];
        $lineDiscount = 0.0;
        foreach ($items as &$item) {
            $base = (float) $item['price'] * (float) $item['qty'];
            $value = (float) ($item['discount_value'] ?? 0);
            $item['discount_amount'] = ($item['discount_type'] ?? 'fixed') === 'percent' ? $base * min(100, $value) / 100 : min($base, $value);
            $lineDiscount += $item['discount_amount'];
        }
        unset($item);
        $manualType = $data['discount_type'];
        $manualValue = (float) ($data['discount_value'] ?? 0);
        $manualDiscount = $manualType === 'percent' ? $subtotal * min(100, $manualValue) / 100 : min($subtotal, $manualValue);
        $discountAmount = round(min($lineDiscount + $manualDiscount, $subtotal), 2);
        $groupDiscount = 0.0;
        $customer = ! empty($data['customer_id']) ? Customer::with('customerGroup')->findOrFail($data['customer_id']) : null;
        if ($customer?->customerGroup?->status) {
            $group = $customer->customerGroup;
            $groupDiscount = strtolower((string) $group->type) === 'fixed' ? (float) $group->discount : $subtotal * ((float) $group->discount / 100);
            $groupDiscount = min(max(0, $groupDiscount), max(0, $subtotal - $discountAmount));
        }
        $couponId = array_key_exists('coupon_id', $data) ? $data['coupon_id'] : $sale->coupon_id;
        $taxPercent = (float) ($data['tax_percent'] ?? $sale->tax_percent ?? 0);
        $serviceData = [
            'branch_id' => $branchId, 'seller_id' => $sale->seller_id, 'customer_id' => $data['customer_id'] ?? null,
            'sale_date' => $data['sale_date'] ?? $sale->sale_date->toDateString(), 'items' => $items, 'coupon_id' => $couponId,
            'discount_type' => $manualType, 'discount_value' => $manualValue, 'discount_amount' => $discountAmount,
            'group_discount_amount' => $groupDiscount, 'tax_percent' => $taxPercent,
            'use_reward_points' => (bool) ($data['use_reward_points'] ?? false), 'used_points' => (int) ($data['used_points'] ?? 0),
        ];
        try {
            $updated = $saleService->updateSale($sale->id, $serviceData);
        } catch (\Throwable $exception) {
            return response()->json(['message' => $exception->getMessage() ?: 'Sale could not be updated.'], 422);
        }
        return $this->success(['sale' => $this->saleData($updated->load(['customer', 'branch', 'seller.user', 'payments', 'productSales.product', 'productSales.variant', 'delivery.courier']))], 'Sale updated.');
    }

    public function addSalePayment(Request $request, int $id, SaleService $saleService): JsonResponse
    {
        $this->requirePermission('update_sale_list');
        $data = $request->validate(['amount' => ['required', 'numeric', 'gt:0'], 'payment_method' => ['required', 'integer', 'exists:accounts,id'], 'payment_date' => ['nullable', 'date'], 'note' => ['nullable', 'string', 'max:255']]);
        $sale = $this->applyBranchScope(Sale::query(), $request)->findOrFail($id);
        abort_if($sale->due_amount <= 0, 409, 'This sale has no due balance.');
        abort_if((float) $data['amount'] > (float) $sale->due_amount, 422, 'Payment exceeds the remaining due.');
        abort_unless(Account::active()->whereKey($data['payment_method'])->exists(), 422, 'Choose an active payment account.');
        $saleService->addPayment(['sale_id' => $sale->id, 'amount' => $data['amount'], 'payment_method' => $data['payment_method'], 'payment_date' => $data['payment_date'] ?? today()->toDateString(), 'note' => $data['note'] ?? null]);
        return $this->success(['sale' => $this->saleData($sale->fresh(['customer', 'branch', 'payments', 'productSales.product', 'delivery.courier']))], 'Payment added.');
    }

    public function voidSale(Request $request, int $id, SaleService $saleService): JsonResponse
    {
        $this->requirePermission('delete_sale_list');
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $this->applyBranchScope(Sale::query(), $request)->findOrFail($id);
        $saleService->voidSale($id, $data['reason'] ?? null);
        return $this->success([], 'Sale voided and business records reversed.');
    }

    public function markSaleDelivered(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_sale_list');
        $sale = $this->applyBranchScope(Sale::query(), $request)->with('delivery')->findOrFail($id);
        DB::transaction(function () use ($sale) {
            $sale->update(['delivery_status' => 'delivered']);
            $sale->delivery?->update(['status' => 'delivered']);
        });
        return $this->success(['sale_id' => (int) $sale->id, 'delivery_status' => 'delivered'], 'Sale marked delivered.');
    }

    private function giftCardData(GiftCard $card): array
    {
        return ['id' => (int) $card->id, 'card_no' => $card->card_no, 'customer_id' => $card->customer_id ? (int) $card->customer_id : null, 'customer' => $card->customer?->name, 'value' => (float) $card->value, 'price' => (float) $card->price, 'balance' => (float) $card->balance, 'expiry_date' => (string) $card->expiry_date, 'status' => $card->status, 'is_valid' => $card->isValid()];
    }

    private function couponData(Coupon $coupon): array
    {
        return ['id' => (int) $coupon->id, 'code' => $coupon->code, 'type' => $coupon->type, 'amount' => (float) $coupon->amount, 'min_purchase' => (float) $coupon->min_purchase, 'max_discount' => $coupon->max_discount === null ? null : (float) $coupon->max_discount, 'start_date' => (string) $coupon->start_date, 'end_date' => (string) $coupon->end_date, 'usage_limit' => $coupon->usage_limit, 'used_count' => (int) $coupon->used_count, 'status' => (bool) $coupon->status];
    }

    private function deliveryData(Delivery $d): array
    {
        return ['id' => (int) $d->id, 'sale_id' => (int) $d->sale_id, 'invoice_no' => $d->invoice_no, 'courier_id' => (int) $d->courier_id, 'courier' => $d->courier?->name, 'tracking_code' => $d->tracking_code, 'recipient_name' => $d->recipient_name, 'recipient_phone' => $d->recipient_phone, 'address' => $d->address, 'order_amount' => (float) $d->order_amount, 'cod_amount' => (float) $d->cod_amount, 'delivery_charge' => (float) $d->delivery_charge, 'status' => $d->status, 'note' => $d->note, 'sale_customer' => $d->sale?->customer?->name, 'branch' => $d->sale?->branch?->name, 'created_at' => $d->created_at?->toIso8601String()];
    }

    private function saleData(Sale $sale): array
    {
        return ['id' => (int) $sale->id, 'invoice_no' => $sale->invoice_no, 'date' => $sale->sale_date?->format('Y-m-d'), 'customer_id' => $sale->customer_id, 'customer' => $sale->customer?->name ?? 'Walk-in customer', 'branch_id' => $sale->branch_id, 'branch' => $sale->branch?->name, 'biller' => $sale->seller?->user?->name, 'sub_total' => (float) $sale->sub_total, 'discount' => (float) ($sale->discount_amount + $sale->coupon_discount_amount + $sale->group_discount_amount + $sale->point_discount_amount), 'discount_type' => $sale->discount_type, 'discount_value' => (float) $sale->discount_value, 'tax_percent' => (float) $sale->tax_percent, 'tax' => (float) $sale->tax_amount, 'shipping_cost' => (float) ($sale->shipping_cost ?? 0), 'grand_total' => (float) $sale->grand_total, 'paid_amount' => (float) $sale->paid_amount, 'due_amount' => (float) $sale->due_amount, 'payment_status' => $sale->payment_status, 'delivery_status' => $sale->delivery_status, 'coupon_id' => $sale->coupon_id, 'reward_points_earned' => (int) $sale->reward_points_earned, 'reward_points_used' => (int) $sale->reward_points_used, 'invoice_pdf_url' => url('/api/v1/invoice/'.$sale->id), 'payments' => $sale->payments->map(fn ($p) => ['id' => (int) $p->id, 'amount' => (float) $p->amount, 'method' => $p->payment_method, 'date' => (string) $p->payment_date])->values(), 'items' => $sale->productSales->map(fn ($item) => ['id' => (int) $item->product_id, 'name' => $item->product?->product_title, 'variant_id' => $item->variant_id, 'variant' => $item->variant?->name, 'qty' => (float) $item->qty, 'price' => (float) $item->unit_price, 'total' => (float) $item->total_price])->values(), 'delivery' => $sale->delivery ? $this->deliveryData($sale->delivery) : null];
    }
}

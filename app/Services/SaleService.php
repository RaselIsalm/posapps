<?php

namespace App\Services;

use App\Jobs\SendCustomerInvoiceMailJob;
use App\Models\Account;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductImei;
use App\Models\ProductSale;
use App\Models\ProductVariant;
use App\Models\Quotation;
use App\Models\RewardPointSetting;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SellerCommission;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;

class SaleService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Create a new sale with items, payments, rewards, and stock updates.
     */
    public function createSale(array $data)
    {
        return DB::transaction(function () use ($data) {
            $quotation = null;
            if (! empty($data['quotation_id'])) {
                $quotation = Quotation::query()->with('items')->lockForUpdate()->findOrFail($data['quotation_id']);
                if ($quotation->status !== 'pending') {
                    throw new Exception('This quotation has already been converted.');
                }
                if ($quotation->expiry_date && $quotation->expiry_date < today()->toDateString()) {
                    throw new Exception('This quotation has expired.');
                }
                if ((int) $quotation->branch_id !== (int) ($data['branch_id'] ?? 0)) {
                    throw new Exception('Quotation belongs to a different branch.');
                }
            }
            // Verify item prices against the database (never trust client-supplied prices)
            $itemsToVerify = $quotation
                ? $quotation->items->map(fn ($item) => [
                    'id' => (int) $item->product_id,
                    'variant_id' => $item->variant_id ? (int) $item->variant_id : null,
                    'qty' => (float) $item->qty,
                    'imei_numbers' => array_values(array_filter(array_map('trim', explode(',', (string) ($item->imei_number ?? ''))))),
                ])->all()
                : ($data['items'] ?? []);
            $verified = $this->verifyItemPrices($itemsToVerify, $data['branch_id'] ?? null);
            $data['items'] = $verified['items'];
            $data['sub_total'] = $verified['sub_total'];

            if ($quotation) {
                $quotedItems = [];
                $quotedSubtotal = 0.0;
                foreach ($quotation->items as $index => $quoteItem) {
                    $item = $verified['items'][$index] ?? null;
                    if (! $item) throw new Exception('A quotation item could not be loaded.');

                    $item['price'] = (float) $quoteItem->net_unit_price;
                    $item['discount_type'] = 'fixed';
                    $item['discount_value'] = (float) $quoteItem->discount;
                    $item['discount_amount'] = (float) $quoteItem->discount;
                    $item['tax_percent'] = (float) $quoteItem->tax_rate;
                    $item['tax_amount'] = (float) $quoteItem->tax;
                    $quotedSubtotal += (float) $quoteItem->net_unit_price * (float) $quoteItem->qty;
                    $quotedItems[] = $item;
                }

                // The server-side quotation is the authority for negotiated terms.
                $data['items'] = $quotedItems;
                $data['sub_total'] = round($quotedSubtotal, 2);
                $data['discount_type'] = 'fixed';
                $data['discount_value'] = (float) $quotation->order_discount;
                $data['discount_amount'] = (float) $quotation->total_discount;
                $data['group_discount_amount'] = 0;
                $data['coupon_id'] = null;
                $data['coupon_discount_amount'] = 0;
                $data['tax_percent'] = 0;
                $data['tax_amount'] = (float) $quotation->total_tax;
                $data['fixed_tax_amount'] = (float) $quotation->total_tax;
                $data['shipping_cost'] = (float) $quotation->shipping_cost;
            }

            // IMEIs are serialized inventory: verify exact product, variant,
            // branch and availability before creating any sale-side records.
            $data['items'] = $this->validateSaleImeis($data['items'], (int) $data['branch_id']);

            // Verify coupon against the database (never trust client-supplied coupon discount)
            $verifiedCoupon = $this->verifyCoupon($data['coupon_id'] ?? null, (float) $data['sub_total']);
            $data['coupon_id'] = $verifiedCoupon['coupon_id'];
            $data['coupon_discount_amount'] = $verifiedCoupon['coupon_discount_amount'];

            // Calculate Rewards & Totals
            $rewards = $this->calculateRewards($data);

            $grandTotal = $rewards['grand_total'];
            $allocated = $this->allocatePayments($data['payments'] ?? [], $grandTotal);
            $data['payments'] = $allocated['payments'];
            $totalPaid = $allocated['applied'];
            $dueAmount = $allocated['due'];

            $paymentStatus = $this->getPaymentStatus($totalPaid, $grandTotal);

            // POS / Quick Sale is counter fulfillment: goods handed over at checkout.
            // Courier orders can still pass delivery_status=pending and use Ship Order later.
            $deliveryStatus = strtolower((string) ($data['delivery_status'] ?? 'delivered'));
            if (! in_array($deliveryStatus, ['pending', 'shipped', 'delivered', 'returned', 'completed'], true)) {
                $deliveryStatus = 'delivered';
            }

            // Create Sale Record
            $sale = Sale::create([
                'customer_id' => $data['customer_id'] ?? null,
                'branch_id' => $data['branch_id'],
                'seller_id' => $data['seller_id'] ?? null,
                'sale_date' => $data['sale_date'],
                'sub_total' => $data['sub_total'],
                'discount_type' => $data['discount_type'] ?? 'percent',
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'group_discount_amount' => $data['group_discount_amount'] ?? 0,
                'coupon_id' => $data['coupon_id'] ?? null,
                'coupon_discount_amount' => $data['coupon_discount_amount'] ?? 0,
                'reward_points_earned' => $rewards['points_earned'],
                'reward_points_used' => $rewards['points_used'],
                'point_discount_amount' => $rewards['point_discount_amount'],
                'tax_percent' => $data['tax_percent'] ?? 0,
                'tax_amount' => $rewards['tax_amount'],
                'shipping_cost' => $data['shipping_cost'] ?? 0,
                'grand_total' => $grandTotal,
                'paid_amount' => $totalPaid,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'delivery_status' => $deliveryStatus,
            ]);

            // Update Customer Reward Points
            if (! empty($data['customer_id'])) {
                $customer = Customer::find($data['customer_id']);
                if ($customer) {
                    // Use atomic updates
                    if ($rewards['points_used'] > 0) {
                        $customer->decrement('reward_points', $rewards['points_used']);
                    }
                    if ($rewards['points_earned'] > 0) {
                        $customer->increment('reward_points', $rewards['points_earned']);
                    }
                }
            }

            // Update Customer Balance (Due Amount)
            if (! empty($data['customer_id']) && $dueAmount > 0) {
                Customer::where('id', $data['customer_id'])->increment('balance', $dueAmount);
            }

            // Update Coupon Usage
            if (! empty($data['coupon_id'])) {
                Coupon::where('id', $data['coupon_id'])->increment('used_count');
            }

            // Process Items (Stock & Details)
            foreach ($data['items'] as $item) {
                $productId = $item['id'];
                $variantId = $item['variant_id'] ?? null;
                $qty = $item['qty'];

                // COGS Tracking
                $costs = $this->inventoryService->getProductCosts($productId, $variantId);

                ProductSale::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'qty' => $qty,
                    'unit_price' => $item['price'],
                    'purchase_unit_cost' => $costs['total_cost'],
                    'total_price' => max(0, ($item['price'] * $qty) - (float) ($item['discount_amount'] ?? 0)),
                ]);

                // Update Stock (Branch & Global)
                $this->inventoryService->decrementStock(
                    $data['branch_id'],
                    $productId,
                    $variantId,
                    $qty,
                    'sale',
                    $sale->invoice_no ?? $sale->id,
                    true // Validate stock
                );

                if (! empty($item['imei_numbers'])) {
                    $this->markImeisSold($item, $sale->id, $data['sale_date']);
                }
            }

            // Process Payments
            foreach ($data['payments'] as $pay) {
                $applied = (float) ($pay['applied_amount'] ?? $pay['amount'] ?? 0);
                $change = (float) ($pay['change_amount'] ?? 0);
                if ($applied <= 0 && $change <= 0) {
                    continue;
                }

                if ($pay['method'] === 'Gift Card') {
                    $this->processGiftCardPayment($sale, $pay, $data['sale_date']);
                } else {
                    $this->processAccountPayment($sale, $pay, $data['sale_date']);
                }
            }

            // Seller Commission
            $this->processCommission($sale, $data);

            // Quotation Status
            if (! empty($data['quotation_id'])) {
                Quotation::where('id', $data['quotation_id'])->update(['status' => 'sale_generated']);
            }

            // Dispatch Email Job in Background
            if (! empty($data['customer_id'])) {
                SendCustomerInvoiceMailJob::dispatch($sale);
            }

            return $sale;
        });
    }

    /**
     * Update an existing sale — revert old stock, update records, apply new stock.
     */
    public function updateSale(int $saleId, array $data): Sale
    {
        return DB::transaction(function () use ($saleId, $data) {
            $sale = Sale::with(['productSales', 'payments'])->lockForUpdate()->findOrFail($saleId);
            $data['shipping_cost'] = (float) ($data['shipping_cost'] ?? $sale->shipping_cost ?? 0);
            if ($sale->returns()->where('status', 'approved')->exists()) {
                throw new Exception('This sale has an approved return and can no longer be edited.');
            }
            if ((int) $sale->branch_id !== (int) $data['branch_id'] && $sale->payments()->exists()) {
                throw new Exception('A sale with payment history cannot be moved to another branch.');
            }

            // Restore prior balance, points and coupon use before calculating the edited sale.
            if ($sale->customer_id) {
                $oldCustomer = Customer::query()->lockForUpdate()->find($sale->customer_id);
                if ($oldCustomer) {
                    if ((float) $sale->due_amount > 0) $oldCustomer->decrement('balance', (float) $sale->due_amount);
                    if ((int) $sale->reward_points_earned > 0) {
                        $oldCustomer->decrement('reward_points', min((int) $sale->reward_points_earned, (int) $oldCustomer->reward_points));
                    }
                    if ((int) $sale->reward_points_used > 0) $oldCustomer->increment('reward_points', (int) $sale->reward_points_used);
                }
            }
            if ($sale->coupon_id) Coupon::query()->whereKey($sale->coupon_id)->where('used_count', '>', 0)->decrement('used_count');

            $verified = $this->verifyItemPrices($data['items'] ?? [], (int) $data['branch_id']);
            $data['items'] = $verified['items'];
            $data['sub_total'] = $verified['sub_total'];
            // Allow this sale's current IMEIs to remain selected during edits.
            $data['items'] = $this->validateSaleImeis($data['items'], (int) $data['branch_id'], $sale->id);
            $coupon = $this->verifyCoupon($data['coupon_id'] ?? null, (float) $data['sub_total']);
            $data['coupon_id'] = $coupon['coupon_id'];
            $data['coupon_discount_amount'] = $coupon['coupon_discount_amount'];
            $rewards = $this->calculateRewards($data);
            $newGrandTotal = (float) $rewards['grand_total'];
            $paid = (float) $sale->paid_amount;
            if ($paid > $newGrandTotal + 0.009) {
                throw new Exception('The new total cannot be lower than the amount already paid.');
            }
            $newDueAmount = max(0, $newGrandTotal - $paid);

            foreach ($sale->productSales as $oldItem) {
                $this->inventoryService->incrementStock(
                    $sale->branch_id,
                    $oldItem->product_id,
                    $oldItem->variant_id,
                    $oldItem->qty,
                    'sale_edit_revert',
                    $sale->invoice_no ?? $sale->id
                );
            }
            ProductImei::query()->where('sale_id', $sale->id)->update(['is_sold' => 0, 'status' => 'in_stock', 'sale_id' => null, 'warranty_start_date' => null]);

            $newCustomerId = $data['customer_id'] ?? null;
            if ($newCustomerId) {
                $newCustomer = Customer::query()->lockForUpdate()->findOrFail($newCustomerId);
                if ($newDueAmount > 0) $newCustomer->increment('balance', $newDueAmount);
                if ($rewards['points_used'] > 0) $newCustomer->decrement('reward_points', $rewards['points_used']);
                if ($rewards['points_earned'] > 0) $newCustomer->increment('reward_points', $rewards['points_earned']);
            }
            if ($data['coupon_id']) Coupon::query()->whereKey($data['coupon_id'])->increment('used_count');

            $sale->update([
                'customer_id' => $newCustomerId,
                'branch_id' => $data['branch_id'],
                'seller_id' => array_key_exists('seller_id', $data) ? $data['seller_id'] : $sale->seller_id,
                'sale_date' => $data['sale_date'] ?? $sale->sale_date,
                'sub_total' => $data['sub_total'],
                'discount_type' => $data['discount_type'] ?? 'fixed',
                'discount_value' => $data['discount_value'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'group_discount_amount' => $data['group_discount_amount'] ?? 0,
                'coupon_id' => $data['coupon_id'],
                'coupon_discount_amount' => $data['coupon_discount_amount'],
                'reward_points_earned' => $rewards['points_earned'],
                'reward_points_used' => $rewards['points_used'],
                'point_discount_amount' => $rewards['point_discount_amount'],
                'tax_percent' => $data['tax_percent'] ?? 0,
                'tax_amount' => $rewards['tax_amount'],
                'shipping_cost' => $data['shipping_cost'],
                'grand_total' => $newGrandTotal,
                'due_amount' => $newDueAmount,
                'payment_status' => $this->getPaymentStatus($paid, $newGrandTotal),
            ]);

            $sale->productSales()->delete();
            foreach ($data['items'] as $item) {
                $productId = $item['id'];
                $variantId = $item['variant_id'] ?? null;
                $qty = $item['qty'];

                // COGS Tracking via InventoryService
                $costs = $this->inventoryService->getProductCosts($productId, $variantId);

                ProductSale::create([
                    'sale_id' => $sale->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'discount_type' => $item['discount_type'] ?? 'fixed',
                    'discount_value' => $item['discount_value'] ?? 0,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'tax_percent' => $item['tax_percent'] ?? 0,
                    'tax_amount' => $item['tax_amount'] ?? 0,
                    'qty' => $qty,
                    'unit_price' => $item['price'],
                    'purchase_unit_cost' => $costs['total_cost'],
                    'total_price' => max(0, ($qty * $item['price']) - (float) ($item['discount_amount'] ?? 0)),
                ]);

                // Decrement stock via InventoryService (with validation + stock history)
                $this->inventoryService->decrementStock(
                    $data['branch_id'],
                    $productId,
                    $variantId,
                    $qty,
                    'sale_edit',
                    $sale->invoice_no ?? $sale->id,
                    true
                );

                if (! empty($item['imei_numbers'])) {
                    $this->markImeisSold($item, $sale->id, $data['sale_date'] ?? today()->toDateString());
                }
            }
            SellerCommission::where('sale_id', $sale->id)->delete();
            $this->processCommission($sale->fresh(), $data);

            return $sale->fresh();
        });
    }

    /**
     * Void a sale (industry-standard): keep the sale record for audit, fully reverse
     * stock/money/gift cards/rewards/coupons, and write reversing ledger entries.
     */
    public function deleteSale(int $saleId, ?string $reason = null): void
    {
        $this->voidSale($saleId, $reason);
    }

    public function voidSale(int $saleId, ?string $reason = null): void
    {
        DB::transaction(function () use ($saleId, $reason) {
            $sale = Sale::withoutGlobalScope('not_voided')
                ->with(['productSales', 'payments'])
                ->lockForUpdate()
                ->findOrFail($saleId);

            if ($sale->isVoided()) {
                throw new Exception('This sale is already voided.');
            }

            $hasApprovedReturns = SaleReturn::where('sale_id', $sale->id)
                ->where('status', 'approved')
                ->exists();

            if ($hasApprovedReturns) {
                throw new Exception('Cannot void this sale! It has approved returns. Please handle the returns first.');
            }

            $invoice = $sale->invoice_no ?? (string) $sale->id;
            $voidDate = now()->toDateString();
            $voidReason = $reason ?: ('Void sale '.$invoice);

            // Customer due
            if ($sale->customer_id && $sale->due_amount > 0) {
                Customer::where('id', $sale->customer_id)->decrement('balance', $sale->due_amount);
            }

            // Reward points
            if ($sale->customer_id) {
                $customer = Customer::find($sale->customer_id);
                if ($customer) {
                    if ((float) $sale->reward_points_earned > 0) {
                        $safeEarned = min((float) $sale->reward_points_earned, max(0, (float) $customer->reward_points));
                        if ($safeEarned > 0) {
                            $customer->decrement('reward_points', $safeEarned);
                        }
                    }
                    if ((float) $sale->reward_points_used > 0) {
                        $customer->increment('reward_points', (float) $sale->reward_points_used);
                    }
                }
            }

            // Coupon usage
            if (! empty($sale->coupon_id)) {
                Coupon::where('id', $sale->coupon_id)
                    ->where('used_count', '>', 0)
                    ->decrement('used_count');
            }

            // Gift cards
            foreach ($sale->payments as $payment) {
                if (strcasecmp((string) $payment->payment_method, 'Gift Card') !== 0) {
                    continue;
                }
                $cardNo = trim((string) $payment->transaction_id);
                if ($cardNo === '' || (float) $payment->amount <= 0) {
                    continue;
                }
                $giftCard = GiftCard::lockForUpdate()->where('card_no', $cardNo)->first();
                if ($giftCard) {
                    $giftCard->increment('balance', (float) $payment->amount);
                    GiftCardTransaction::create([
                        'gift_card_id' => $giftCard->id,
                        'sale_id' => $sale->id,
                        'transaction_type' => 'credit',
                        'amount' => $payment->amount,
                        'transaction_date' => $voidDate,
                        'note' => 'Reversal for voided sale '.$invoice,
                    ]);
                }
            }

            // Stock
            foreach ($sale->productSales as $item) {
                $this->inventoryService->incrementStock(
                    $sale->branch_id,
                    $item->product_id,
                    $item->variant_id,
                    $item->qty,
                    'sale_void',
                    $invoice
                );
            }

            // IMEI
            ProductImei::where('sale_id', $sale->id)->update([
                'status' => 'in_stock',
                'is_sold' => 0,
                'sale_id' => null,
            ]);

            // Ledger: keep original credits, post reversing debits for audit trail
            $paymentIds = $sale->payments->pluck('id')->filter()->values()->all();
            $transactions = Transaction::query()
                ->where(function ($query) use ($sale, $paymentIds) {
                    $query->where(function ($saleQuery) use ($sale) {
                        $saleQuery->where('reference_type', get_class($sale))
                            ->where('reference_id', $sale->id);
                    });

                    if ($paymentIds !== []) {
                        $query->orWhere(function ($paymentQuery) use ($paymentIds) {
                            $paymentQuery->where('reference_type', Payment::class)
                                ->whereIn('reference_id', $paymentIds);
                        });
                    }
                })
                ->where('type', 'credit')
                ->where(function ($q) {
                    $q->whereNull('reason')->orWhere('reason', 'not like', 'Void sale%');
                })
                ->lockForUpdate()
                ->get();

            foreach ($transactions as $transaction) {
                if ((float) $transaction->amount <= 0 || ! $transaction->account_id) {
                    continue;
                }
                TransactionService::createTransaction(
                    $transaction->account_id,
                    'debit',
                    (float) $transaction->amount,
                    'Void sale '.$invoice,
                    $voidDate,
                    $sale
                );
            }

            // Commission rows no longer apply
            SellerCommission::where('sale_id', $sale->id)->delete();

            // Pending returns never moved stock/money; drop them with the void
            SaleReturn::where('sale_id', $sale->id)->where('status', '!=', 'approved')->delete();

            $sale->update([
                'due_amount' => 0,
                'voided_at' => now(),
                'voided_by' => auth()->id(),
                'void_reason' => $voidReason,
            ]);

            if (function_exists('activity_log')) {
                activity_log(
                    'voided sale',
                    'invoice: '.$invoice.'; by user #'.(auth()->id() ?? 'n/a').'; reason: '.$voidReason
                );
            }
        });
    }

    /**
     * Add a due payment to an existing sale.
     */
    public function addPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $sale = Sale::findOrFail($data['sale_id']);

            if ($sale->isVoided()) {
                throw new Exception('Cannot add payment to a voided sale.');
            }

            $totalPaid = $sale->payments()->sum('amount');
            $currentDue = $sale->grand_total - $totalPaid;

            if ($data['amount'] > $currentDue) {
                throw new Exception('Paying amount cannot be greater than due amount!');
            }

            $account = Account::find($data['payment_method']);

            // Create payment record
            $payment = Payment::create([
                'sale_id' => $sale->id,
                'amount' => $data['amount'],
                'payment_method' => $account ? $account->name : (string) ($data['payment_method'] ?? ''),
                'transaction_id' => $data['note'] ?? null,
                'payment_date' => $data['payment_date'],
            ]);

            // Update sale totals
            $newTotalPaid = $totalPaid + $data['amount'];
            $newDue = $sale->grand_total - $newTotalPaid;

            $sale->update([
                'paid_amount' => $newTotalPaid,
                'due_amount' => $newDue,
                'payment_status' => $this->getPaymentStatus($newTotalPaid, $sale->grand_total),
            ]);

            // Decrement customer balance
            if ($sale->customer_id) {
                Customer::where('id', $sale->customer_id)->decrement('balance', $data['amount']);
            }

            // Record financial transaction
            TransactionService::createTransaction(
                $data['payment_method'],
                'credit',
                $data['amount'],
                'Due Collection for Sale: '.$sale->invoice_no,
                $data['payment_date'],
                $sale
            );

            return $payment;
        });
    }

    // ─── Protected Helper Methods ────────────────────────────────

    /**
     * Re-fetch each item's real price from the database (never trust the
     * price sent by the client) and return the items with verified prices
     * plus the recalculated sub_total.
     */
    protected function verifyItemPrices(array $items, ?int $branchId = null): array
    {
        $verifiedItems = [];
        $subTotal = 0;

        foreach ($items as $item) {
            $productId = $item['id'];
            $variantId = $item['variant_id'] ?? null;
            $qty = $item['qty'];

            $product = Product::find($productId);
            if (! $product) {
                throw new Exception("Invalid product in cart (ID: {$productId}).");
            }

            $price = (float) ($product->promotional_price ?: $product->selling_price);

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if (! $variant || (int) $variant->product_id !== (int) $productId) {
                    throw new Exception("Invalid product variant in cart (ID: {$variantId}).");
                }
                $price = $variant->sellingPriceFromBase($product->selling_price);
            }

            if ($branchId) {
                $branchPrice = ProductBranch::query()
                    ->where('branch_id', $branchId)
                    ->where('product_id', $productId)
                    ->where('variant_id', $variantId)
                    ->value('sell_price');
                if ($branchPrice !== null && (float) $branchPrice > 0) {
                    $price = (float) $branchPrice;
                }
            }

            $item['price'] = $price;
            $subTotal += $price * $qty;
            $verifiedItems[] = $item;
        }

        return [
            'items' => $verifiedItems,
            'sub_total' => $subTotal,
        ];
    }

    /**
     * Validate serialized products against available branch inventory and attach
     * locked database IDs for the later status update.
     */
    protected function validateSaleImeis(array $items, int $branchId, ?int $existingSaleId = null): array
    {
        $allNumbers = [];
        foreach ($items as $index => $item) {
            $product = Product::query()->find($item['id'] ?? null);
            if (! $product) {
                throw new Exception('A product in the sale could not be loaded.');
            }

            $rawNumbers = is_array($item['imei_numbers'] ?? null) ? $item['imei_numbers'] : [];
            $numbers = array_values(array_filter(array_map(
                static fn ($value) => trim((string) $value),
                $rawNumbers
            ), static fn ($value) => $value !== ''));
            $qty = (float) ($item['qty'] ?? 0);

            if (! $product->has_imei && $numbers !== []) {
                throw new Exception("{$product->product_title} does not use IMEI tracking.");
            }
            if ($product->has_imei && (floor($qty) !== $qty || count($numbers) !== (int) $qty)) {
                throw new Exception("Select exactly one available IMEI for each {$product->product_title} unit.");
            }
            if (! $product->has_imei) {
                $items[$index]['imei_numbers'] = [];
                $items[$index]['_imei_ids'] = [];
                continue;
            }

            foreach ($numbers as $number) {
                if (isset($allNumbers[$number])) {
                    throw new Exception("IMEI {$number} is listed more than once in this sale.");
                }
                $allNumbers[$number] = true;
            }

            $records = ProductImei::query()
                ->whereIn('imei_number', $numbers)
                ->lockForUpdate()
                ->get()
                ->keyBy('imei_number');
            $ids = [];
            foreach ($numbers as $number) {
                $imei = $records->get($number);
                if (! $imei
                    || (int) $imei->product_id !== (int) $product->id
                    || (int) ($imei->variant_id ?? 0) !== (int) ($item['variant_id'] ?? 0)
                    || (int) ($imei->branch_id ?? 0) !== $branchId) {
                    throw new Exception("IMEI {$number} does not belong to this product, variant and branch.");
                }

                $available = ! $imei->is_sold && $imei->status === 'in_stock' && $imei->sale_id === null;
                $alreadyOnThisSale = $existingSaleId !== null
                    && (int) $imei->sale_id === $existingSaleId
                    && (bool) $imei->is_sold
                    && $imei->status === 'sold';
                if (! $available && ! $alreadyOnThisSale) {
                    throw new Exception("IMEI {$number} is no longer available.");
                }
                $ids[] = (int) $imei->id;
            }

            $items[$index]['imei_numbers'] = $numbers;
            $items[$index]['_imei_ids'] = $ids;
        }

        return $items;
    }

    protected function markImeisSold(array $item, int $saleId, string $saleDate): void
    {
        $ids = $item['_imei_ids'] ?? [];
        if ($ids === []) {
            return;
        }

        ProductImei::query()->whereIn('id', $ids)->update([
            'is_sold' => 1,
            'status' => 'sold',
            'sale_id' => $saleId,
            'warranty_start_date' => $saleDate,
        ]);
    }

    /** Return database-priced cart lines for API validation and preview. */
    public function priceItems(array $items, ?int $branchId = null): array
    {
        return $this->verifyItemPrices($items, $branchId);
    }

    /**
     * Re-fetch and re-validate the coupon from the database (never trust the
     * discount amount sent by the client) and compute the real discount.
     */
    public function verifyCoupon($couponId, $subTotal): array
    {
        if (empty($couponId)) {
            return ['coupon_id' => null, 'coupon_discount_amount' => 0];
        }

        $coupon = Coupon::find($couponId);
        $today = date('Y-m-d');

        if (! $coupon) {
            throw new Exception('Invalid coupon code!');
        }
        if (! $coupon->status) {
            throw new Exception('This coupon is inactive!');
        }
        if ($coupon->start_date > $today) {
            throw new Exception('Coupon not started yet!');
        }
        if ($coupon->end_date < $today) {
            throw new Exception('Coupon expired!');
        }
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            throw new Exception('Coupon usage limit exceeded!');
        }
        if ($subTotal < $coupon->min_purchase) {
            throw new Exception('Minimum purchase required: '.number_format($coupon->min_purchase, 2));
        }

        $discountAmount = 0;
        if ($coupon->type == 'fixed') {
            $discountAmount = $coupon->amount;
        } else {
            $discountAmount = ($subTotal * $coupon->amount) / 100;
            if ($coupon->max_discount && $discountAmount > $coupon->max_discount) {
                $discountAmount = $coupon->max_discount;
            }
        }

        if ($discountAmount > $subTotal) {
            $discountAmount = $subTotal;
        }

        return [
            'coupon_id' => $coupon->id,
            'coupon_discount_amount' => round($discountAmount, 2),
        ];
    }

    protected function calculateRewards(array $data)
    {
        $setting = RewardPointSetting::first();

        $points_earned = 0;
        $points_used = 0;
        $point_discount = 0;

        $sub_total = $data['sub_total'];
        $discounts = ($data['discount_amount'] ?? 0) + ($data['coupon_discount_amount'] ?? 0) + ($data['group_discount_amount'] ?? 0);
        $taxable = max(0, $sub_total - $discounts);
        $tax = array_key_exists('fixed_tax_amount', $data)
            ? max(0, (float) $data['fixed_tax_amount'])
            : $taxable * (($data['tax_percent'] ?? 0) / 100);
        $shippingCost = max(0, (float) ($data['shipping_cost'] ?? 0));
        $total_before_reward = $taxable + $tax + $shippingCost;

        // Point Redemption
        if ($setting && $setting->is_active && ! empty($data['use_reward_points']) && ! empty($data['customer_id'])) {
            $customer = Customer::find($data['customer_id']);
            $requested_points = $data['used_points'] ?? 0;

            if ($requested_points > 0) {
                if ($total_before_reward < $setting->min_purchase_amount) {
                    throw new Exception('Minimum purchase of '.number_format($setting->min_purchase_amount, 2).' required to use reward points.');
                }

                if (! $customer || $customer->reward_points < $requested_points) {
                    throw new Exception('Insufficient reward points. Available: '.($customer->reward_points ?? 0));
                }

                if ($customer->reward_points < $setting->min_redeem_point) {
                    throw new Exception("Minimum {$setting->min_redeem_point} points required to redeem.");
                }

                $potential_discount = $requested_points * $setting->point_redemption_rate;

                if ($potential_discount > $total_before_reward) {
                    $point_discount = $total_before_reward;
                    $points_used = $total_before_reward / $setting->point_redemption_rate;
                } else {
                    $point_discount = $potential_discount;
                    $points_used = $requested_points;
                }
            }
        }

        $grand_total = max(0, $total_before_reward - $point_discount);

        // Point Earning
        if ($setting && $setting->is_active && ! empty($data['customer_id'])) {
            if ($grand_total >= $setting->min_purchase_amount) {
                $points_earned = floor($grand_total / $setting->point_earning_rate);
            }
        }

        return [
            'points_earned' => $points_earned,
            'points_used' => $points_used,
            'point_discount_amount' => $point_discount,
            'tax_amount' => $tax,
            'grand_total' => $grand_total,
        ];
    }

    /**
     * Split each tendered payment into amount applied to the bill vs change returned.
     */
    protected function allocatePayments(array $payments, float $grandTotal): array
    {
        $remaining = max(0, $grandTotal);
        $allocated = [];
        $appliedTotal = 0.0;

        foreach ($payments as $pay) {
            $tendered = (float) ($pay['amount'] ?? 0);
            if ($tendered < 0) {
                continue;
            }

            $isGift = ($pay['method'] ?? '') === 'Gift Card';
            $applied = min($tendered, $remaining);
            $change = $isGift ? 0.0 : max(0, round($tendered - $applied, 2));

            $remaining = round($remaining - $applied, 2);
            $appliedTotal = round($appliedTotal + $applied, 2);

            $pay['amount'] = $applied;
            $pay['applied_amount'] = $applied;
            $pay['change_amount'] = $change;
            $pay['tendered_amount'] = $applied + $change;
            $allocated[] = $pay;
        }

        return [
            'payments' => $allocated,
            'applied' => $appliedTotal,
            'due' => max(0, $remaining),
        ];
    }

    protected function getPaymentStatus($paid, $total)
    {
        if ($paid >= $total) {
            return 'paid';
        }
        if ($paid > 0) {
            return 'partial';
        }

        return 'due';
    }

    protected function processGiftCardPayment($sale, $pay, $date)
    {
        $cardNo = $pay['note'];
        $giftCard = GiftCard::lockForUpdate()->where('card_no', $cardNo)->first();

        $applied = (float) ($pay['applied_amount'] ?? $pay['amount'] ?? 0);
        if ($applied <= 0) {
            return;
        }

        if (! $giftCard || ! $giftCard->isValid() || $giftCard->balance < $applied) {
            throw new Exception("Invalid Gift Card or Insufficient Balance: {$cardNo}");
        }

        $giftCard->decrement('balance', $applied);
        GiftCardTransaction::create([
            'gift_card_id' => $giftCard->id,
            'sale_id' => $sale->id,
            'transaction_type' => 'debit',
            'amount' => $applied,
            'transaction_date' => $date,
            'note' => 'Used for sale '.$sale->invoice_no,
        ]);

        Payment::create([
            'sale_id' => $sale->id,
            'amount' => $applied,
            'change_amount' => 0,
            'payment_method' => 'Gift Card',
            'transaction_id' => $cardNo,
            'payment_date' => $date,
        ]);
    }

    protected function processAccountPayment($sale, $pay, $date)
    {
        $account = Account::find($pay['method']);
        $applied = (float) ($pay['applied_amount'] ?? $pay['amount'] ?? 0);
        $change = (float) ($pay['change_amount'] ?? 0);

        if ($applied <= 0 && $change <= 0) {
            return;
        }

        $payment = Payment::create([
            'sale_id' => $sale->id,
            'amount' => $applied,
            'change_amount' => $change,
            'payment_method' => $account ? $account->name : (string) ($pay['method'] ?? ''),
            'transaction_id' => $pay['note'] ?? null,
            'payment_date' => $date,
        ]);

        if ($account && $applied > 0) {
            TransactionService::createTransaction(
                $account->id,
                'credit',
                $applied,
                'Sale Invoice: '.($sale->invoice_no ?? $sale->id),
                $date,
                $payment
            );
        }
    }

    protected function processCommission($sale, $data)
    {
        if (empty($data['seller_id'])) {
            return;
        }

        $employee = Employee::find($data['seller_id']);
        if ($employee && $employee->max_commission > 0) {
            $commissionable = $sale->grand_total;
            $earned = ($commissionable * $employee->max_commission) / 100;

            SellerCommission::create([
                'employee_id' => $employee->id,
                'sale_id' => $sale->id,
                'sale_amount' => $commissionable,
                'commission_percentage' => $employee->max_commission,
                'earned_amount' => $earned,
                'status' => 1,
            ]);
        }
    }
}

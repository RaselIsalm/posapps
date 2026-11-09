<?php

namespace App\Services;

use App\Helpers\InvoiceHelper;
use App\Models\Account;
use App\Models\ProductImei;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Quotation;
use App\Models\Supplier;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    protected $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function storePurchase(array $data, $documentName = null)
    {
        return DB::transaction(function () use ($data, $documentName) {
            if (! empty($data['quotation_id'])) {
                $quotation = Quotation::query()->lockForUpdate()->findOrFail($data['quotation_id']);
                if ($quotation->status !== 'pending') {
                    throw new Exception('This quotation has already been converted.');
                }
                if ($quotation->expiry_date && $quotation->expiry_date < now()->toDateString()) {
                    throw new Exception('This quotation has expired.');
                }
                if ((int) $quotation->branch_id !== (int) ($data['branch_id'] ?? 0)) {
                    throw new Exception('Quotation belongs to a different branch.');
                }
            }
            $ex = $data['exchange_rate'] ?? 1;
            $grandTotal = isset($data['grand_total']) ? $data['grand_total'] * $ex : 0;
            $paidAmount = ($data['paid_amount'] ?? 0) * $ex;
            $dueAmount = $grandTotal - $paidAmount;

            $purchase = Purchase::create([
                'invoice_no' => $data['invoice_no'],
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'memo_no' => $data['memo_no'] ?? null,
                'date' => $data['date'],
                'status' => $data['status'],
                'payment_method' => InvoiceHelper::resolvePaymentMethodName($data['payment_method'] ?? '') ?: null,
                'order_tax' => ($data['order_tax'] ?? 0) * $ex,
                'order_discount' => ($data['order_discount'] ?? 0) * $ex,
                'shipping_cost' => ($data['shipping_cost'] ?? 0) * $ex,
                'currency' => $data['currency'] ?? 'BDT',
                'exchange_rate' => $ex,
                'note' => $data['note'] ?? null,
                'document' => $documentName,
                'total_qty' => array_sum($data['qty']),
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'created_by' => Auth::id(),
            ]);

            // Supplier Balance Update
            if ($dueAmount > 0) {
                Supplier::where('id', $data['supplier_id'])->increment('balance', $dueAmount);
            }

            // Payment Transaction
            if ($paidAmount > 0 && ! empty($data['payment_method'])) {
                $account = Account::lockForUpdate()->find($data['payment_method']);
                if ($account && $account->current_balance < $paidAmount) {
                    throw new Exception("Insufficient balance in account: {$account->name}");
                }

                TransactionService::createTransaction(
                    $data['payment_method'],
                    'debit',
                    $paidAmount,
                    'Purchase Invoice: '.$purchase->invoice_no,
                    $data['date'],
                    $purchase
                );
            }

            // Details and Stock
            foreach ($data['product_id'] as $key => $productId) {
                $variantId = $data['variant_id'][$key] ?? null;
                $qty = $data['qty'][$key];
                $cost = $data['net_unit_cost'][$key] * $ex;
                $tax = ($data['tax'][$key] ?? 0) * $ex;
                $discount = ($data['discount'][$key] ?? 0) * $ex;
                $total = ($cost * $qty) + $tax - $discount;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => $qty,
                    'net_unit_cost' => $cost,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                    'batch_no' => $data['batch_no'][$key] ?? null,
                    'expire_date' => $data['expire_date'][$key] ?? null,
                ]);

                $this->handleImei($productId, $variantId, $data['branch_id'], $data['imei_numbers'][$key] ?? null, $purchase->id, $cost);

                if ($data['status'] == 'received') {
                    $this->inventoryService->incrementStock($data['branch_id'], $productId, $variantId, $qty, 'purchase', $purchase->invoice_no);
                }
            }

            // Update Quotation Status
            if (! empty($data['quotation_id'])) {
                Quotation::where('id', $data['quotation_id'])->update(['status' => 'purchase_generated']);
            }

            return $purchase;
        });
    }

    public function updatePurchase($id, array $data, $documentName = null)
    {
        return DB::transaction(function () use ($id, $data, $documentName) {
            $purchase = Purchase::lockForUpdate()->findOrFail($id);
            $oldStatus = $purchase->status;
            $oldBranchId = $purchase->branch_id;
            $oldSupplierId = $purchase->supplier_id;
            $oldDueAmount = $purchase->due_amount;

            // Revert Old Stock
            if ($oldStatus == 'received') {
                foreach ($purchase->details as $detail) {
                    $this->inventoryService->decrementStock($oldBranchId, $detail->product_id, $detail->variant_id, $detail->qty, 'purchase_revert', $purchase->invoice_no, true);
                }
            }

            // Clean up Old Details and IMEI
            ProductImei::where('purchase_id', $purchase->id)->delete();
            $purchase->details()->delete();

            $ex = $data['exchange_rate'] ?? 1;
            // Recalculate Totals
            $calculatedGrandTotal = 0;
            foreach ($data['product_id'] as $key => $productId) {
                $qty = $data['qty'][$key];
                $cost = $data['net_unit_cost'][$key] * $ex;
                $tax = ($data['tax'][$key] ?? 0) * $ex;
                $discount = ($data['discount'][$key] ?? 0) * $ex;
                $calculatedGrandTotal += ($cost * $qty) + $tax - $discount;
            }

            $grandTotal = isset($data['grand_total']) ? $data['grand_total'] * $ex : $calculatedGrandTotal;
            $paidAmount = isset($data['paid_amount']) ? $data['paid_amount'] * $ex : $purchase->paid_amount;
            $dueAmount = max(0, $grandTotal - $paidAmount);

            // Update Supplier Balance
            if ($oldSupplierId == $data['supplier_id']) {
                $diff = $dueAmount - $oldDueAmount;
                if ($diff > 0) {
                    Supplier::where('id', $oldSupplierId)->increment('balance', $diff);
                } elseif ($diff < 0) {
                    Supplier::where('id', $oldSupplierId)->decrement('balance', abs($diff));
                }
            } else {
                Supplier::where('id', $oldSupplierId)->decrement('balance', $oldDueAmount);
                Supplier::where('id', $data['supplier_id'])->increment('balance', $dueAmount);
            }

            // Update Purchase Record
            $purchase->update([
                'branch_id' => $data['branch_id'],
                'supplier_id' => $data['supplier_id'],
                'memo_no' => $data['memo_no'] ?? $purchase->memo_no,
                'date' => $data['date'],
                'status' => $data['status'],
                'total_qty' => array_sum($data['qty']),
                'order_tax' => isset($data['order_tax']) ? $data['order_tax'] * $ex : $purchase->order_tax,
                'order_discount' => isset($data['order_discount']) ? $data['order_discount'] * $ex : $purchase->order_discount,
                'shipping_cost' => isset($data['shipping_cost']) ? $data['shipping_cost'] * $ex : $purchase->shipping_cost,
                'currency' => $data['currency'] ?? $purchase->currency,
                'exchange_rate' => $ex,
                'payment_method' => isset($data['payment_method'])
                    ? (InvoiceHelper::resolvePaymentMethodName($data['payment_method']) ?: $purchase->payment_method)
                    : $purchase->payment_method,
                'document' => $documentName ?? $purchase->document,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'note' => $data['note'] ?? $purchase->note,
            ]);

            // Create New Details and Update Stock
            foreach ($data['product_id'] as $key => $productId) {
                $variantId = $data['variant_id'][$key] ?? null;
                $qty = $data['qty'][$key];
                $cost = $data['net_unit_cost'][$key] * $ex;
                $tax = ($data['tax'][$key] ?? 0) * $ex;
                $discount = ($data['discount'][$key] ?? 0) * $ex;
                $total = ($cost * $qty) + $tax - $discount;

                PurchaseDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'qty' => $qty,
                    'net_unit_cost' => $cost,
                    'discount' => $discount,
                    'tax' => $tax,
                    'total' => $total,
                ]);

                $this->handleImei($productId, $variantId, $data['branch_id'], $data['imei_numbers'][$key] ?? null, $purchase->id, $cost);

                if ($data['status'] == 'received') {
                    $this->inventoryService->incrementStock($data['branch_id'], $productId, $variantId, $qty, 'purchase_update', $purchase->invoice_no);
                }
            }

            return $purchase;
        });
    }

    public function deletePurchase($id)
    {
        return DB::transaction(function () use ($id) {
            $purchase = Purchase::lockForUpdate()->with('details')->findOrFail($id);

            // Supplier Balance Reversal
            if ($purchase->supplier_id && $purchase->due_amount > 0) {
                Supplier::where('id', $purchase->supplier_id)->decrement('balance', $purchase->due_amount);
            }

            // Stock Reversal with Validation
            if ($purchase->status == 'received') {
                foreach ($purchase->details as $detail) {
                    // Check if IMEI sold
                    $soldImei = ProductImei::where('purchase_id', $purchase->id)
                        ->where('product_id', $detail->product_id)
                        ->where('is_sold', 1)
                        ->exists();

                    if ($soldImei) {
                        throw new Exception('Some IMEI(s) from this purchase have been sold. Cannot delete.');
                    }

                    $this->inventoryService->decrementStock(
                        $purchase->branch_id,
                        $detail->product_id,
                        $detail->variant_id,
                        $detail->qty,
                        'purchase_deleted',
                        $purchase->invoice_no,
                        true // Validate sufficient stock
                    );
                }
            }

            // Payment Refund (Transaction Reversal)
            if ($purchase->paid_amount > 0) {
                $transactions = Transaction::where('reference_type', get_class($purchase))
                    ->where('reference_id', $purchase->id)
                    ->where('type', 'debit')
                    ->get();

                foreach ($transactions as $transaction) {
                    $account = Account::lockForUpdate()->find($transaction->account_id);
                    if ($account) {
                        $account->increment('current_balance', $transaction->amount);
                    }
                    $transaction->delete();
                }
            }

            // Cleanup
            ProductImei::where('purchase_id', $purchase->id)->delete();
            $purchase->details()->delete();
            $purchase->delete();

            return true;
        });
    }

    protected function handleImei($productId, $variantId, $branchId, $imeiString, $purchaseId, $cost)
    {
        if (empty($imeiString)) {
            return;
        }

        $imeis = explode(',', $imeiString);
        foreach ($imeis as $imei) {
            $imei = trim($imei);
            if ($imei) {
                ProductImei::create([
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'branch_id' => $branchId,
                    'imei_number' => $imei,
                    'status' => 'in_stock',
                    'purchase_id' => $purchaseId,
                    'cost_price' => $cost,
                    'created_by' => Auth::id(),
                ]);
            }
        }
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBranch;
use App\Models\ProductImei;
use App\Models\ProductVariant;
use App\Models\StockHistory;
use App\Models\Unit;
use Exception;

class InventoryService
{
    /**
     * Increment stock in branch and global product table.
     */
    public function incrementStock($branchId, $productId, $variantId, $qty, $type, $referenceNo)
    {
        $product = Product::with('comboItems')->find($productId);
        if ($product && $product->product_type == 3) {
            foreach ($product->comboItems as $comboItem) {
                $comboQty = $qty * $comboItem->quantity;
                $this->incrementStock($branchId, $comboItem->combo_item_id, $comboItem->variant_id, $comboQty, $type, $referenceNo);
            }

            return;
        }

        // sale_return / sale_void / sale_delete must mirror checkout ('sale') unit conversion
        $baseQty = in_array($type, ['sale_return', 'sale_void', 'sale_delete'], true)
            ? $this->calculateSaleBaseQty($productId, $qty)
            : $this->calculateBaseQty($productId, $qty);

        $branchStock = ProductBranch::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($branchStock) {
            $branchStock->increment('qty', $baseQty);
        } else {
            $product = Product::find($productId);
            $sellPrice = (float) ($product->selling_price ?? 0);

            if ($variantId) {
                $variant = ProductVariant::find($variantId);
                if ($variant) {
                    $sellPrice = $variant->sellingPriceFromBase($sellPrice);
                }
            }

            ProductBranch::create([
                'branch_id' => $branchId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'qty' => $baseQty,
                'sell_price' => $sellPrice,
            ]);
        }

        Product::where('id', $productId)->increment('quantity', $baseQty);

        // Log to StockHistory
        StockHistory::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'branch_id' => $branchId,
            'qty' => $baseQty,
            'type' => $type,
            'reference_no' => $referenceNo,
        ]);
    }

    /**
     * Decrement stock with optional validation.
     */
    public function decrementStock($branchId, $productId, $variantId, $qty, $type, $referenceNo, $validate = false)
    {
        $product = Product::with('comboItems')->find($productId);
        if ($product && $product->product_type == 3) {
            foreach ($product->comboItems as $comboItem) {
                $comboQty = $qty * $comboItem->quantity;
                $this->decrementStock($branchId, $comboItem->combo_item_id, $comboItem->variant_id, $comboQty, $type, $referenceNo, $validate);
            }

            return;
        }

        $baseQty = ($type === 'sale')
            ? $this->calculateSaleBaseQty($productId, $qty)
            : $this->calculateBaseQty($productId, $qty);

        $branchStock = ProductBranch::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if ($validate) {
            if (! $branchStock || $branchStock->qty < $baseQty) {
                $product = Product::find($productId);
                throw new Exception("Insufficient stock for product: {$product->product_title}");
            }
        }

        if ($branchStock) {
            $branchStock->decrement('qty', $baseQty);
        }

        Product::where('id', $productId)->decrement('quantity', $baseQty);

        // Log to StockHistory
        StockHistory::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'branch_id' => $branchId,
            'qty' => -$baseQty,
            'type' => $type,
            'reference_no' => $referenceNo,
        ]);
    }

    /**
     * Calculate base quantity based on product units.
     */
    public function calculateBaseQty($productId, $qty)
    {
        static $productCache = [];
        static $unitCache = [];

        if (! isset($productCache[$productId])) {
            $productCache[$productId] = Product::find($productId);
        }

        $product = $productCache[$productId];
        if (! $product || ! $product->purchase_unit) {
            return $qty;
        }

        $unitId = $product->purchase_unit;
        if (! isset($unitCache[$unitId])) {
            $unitCache[$unitId] = Unit::find($unitId);
        }

        $unit = $unitCache[$unitId];
        if (! $unit || ! $unit->base_unit_id) {
            return $qty;
        }

        if ($unit->operator == '*') {
            return $qty * $unit->conversion_rate;
        } elseif ($unit->operator == '/') {
            return $qty / $unit->conversion_rate;
        }

        return $qty;
    }

    /**
     * Get combined cost of product and variant.
     */
    public function getProductCosts($productId, $variantId = null)
    {
        $product = Product::find($productId);
        $baseCost = (float) ($product->product_cost ?? 0);
        $variantCost = 0;
        $totalCost = $baseCost;

        if ($variantId) {
            $variant = ProductVariant::find($variantId);
            if ($variant) {
                $variantCost = $variant->additionalCostAmount();
                $totalCost = $variant->costFromBase($baseCost);
            }
        }

        return [
            'base_cost' => $baseCost,
            'variant_cost' => $variantCost,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Calculate sale base quantity based on product units.
     */
    public function calculateSaleBaseQty($productId, $qty)
    {
        $product = Product::find($productId);
        if (! $product || ! $product->product_unit) {
            return $qty;
        }

        $unit = Unit::find($product->product_unit);
        if (! $unit || ! $unit->base_unit_id) {
            return $qty;
        }

        if ($unit->operator == '*') {
            return $qty * $unit->conversion_rate;
        } elseif ($unit->operator == '/') {
            return $qty / $unit->conversion_rate;
        }

        return $qty;
    }

    /**
     * Transfer stock between branches (Neutral for global stock)
     */
    public function transferStock($fromBranchId, $toBranchId, $productId, $variantId, $qty, $referenceNo, $imeis = [])
    {
        $baseQty = $this->calculateBaseQty($productId, $qty);

        // Deduct from Sender
        $senderStock = ProductBranch::where('branch_id', $fromBranchId)
            ->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->first();

        if (! $senderStock || $senderStock->qty < $baseQty) {
            $product = Product::find($productId);
            throw new Exception("Insufficient stock in source branch for product: {$product->product_title}");
        }
        $senderStock->decrement('qty', $baseQty);

        // Add to Receiver
        $receiverStock = ProductBranch::firstOrNew([
            'branch_id' => $toBranchId,
            'product_id' => $productId,
            'variant_id' => $variantId,
        ]);
        $receiverStock->qty = ($receiverStock->qty ?? 0) + $baseQty;

        // Ensure sell price is copied if receiver doesn't have it
        if (! $receiverStock->exists) {
            $receiverStock->sell_price = $senderStock->sell_price;
        }
        $receiverStock->save();

        // Handle IMEI transfers
        if (! empty($imeis)) {
            ProductImei::whereIn('imei_number', $imeis)->update([
                'branch_id' => $toBranchId,
            ]);
        }

        // Log History
        StockHistory::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'branch_id' => $fromBranchId,
            'qty' => -$baseQty,
            'type' => 'transfer_sent',
            'reference_no' => $referenceNo,
        ]);

        StockHistory::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'branch_id' => $toBranchId,
            'qty' => $baseQty,
            'type' => 'transfer_received',
            'reference_no' => $referenceNo,
        ]);
    }
}

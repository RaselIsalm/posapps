<?php

namespace App\Models;

class ProductVariant extends BaseModel
{
    protected $fillable = [
        'product_id',
        'variant_name',
        'variant_code',
        'additional_cost',
        'additional_price',
        'image',
    ];

    /**
     * Extra amount added on top of the product base cost (not a percent, not a replacement).
     */
    public function additionalCostAmount(): float
    {
        return (float) ($this->additional_cost ?? 0);
    }

    /**
     * Extra amount added on top of the product base selling price (not a percent, not a replacement).
     */
    public function additionalPriceAmount(): float
    {
        return (float) ($this->additional_price ?? 0);
    }

    /**
     * Final selling price for this variant: base selling price + additional price.
     */
    public function sellingPriceFromBase(float|int|string|null $baseSellingPrice): float
    {
        return (float) $baseSellingPrice + $this->additionalPriceAmount();
    }

    /**
     * Final cost for this variant: base cost + additional cost.
     */
    public function costFromBase(float|int|string|null $baseCost): float
    {
        return (float) $baseCost + $this->additionalCostAmount();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function options()
    {
        return $this->hasMany(VariantOption::class);
    }

    public function values()
    {
        return $this->hasMany(ProductVariantValue::class);
    }

    public function productBranches()
    {
        return $this->hasMany(ProductBranch::class, 'variant_id');
    }
}

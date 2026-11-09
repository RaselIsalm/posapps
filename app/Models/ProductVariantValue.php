<?php

namespace App\Models;

class ProductVariantValue extends BaseModel
{
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'variant_option_id',
        'variant_value_id'];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function variantOption()
    {
        return $this->belongsTo(VariantOption::class);
    }

    public function variantValue()
    {
        return $this->belongsTo(VariantValue::class);
    }
}

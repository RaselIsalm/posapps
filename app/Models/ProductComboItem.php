<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductComboItem extends Model
{
    protected $fillable = [
        'product_id',
        'combo_item_id',
        'variant_id',
        'quantity',
        'unit_price',
        'total_price',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function comboItem()
    {
        return $this->belongsTo(Product::class, 'combo_item_id');
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}

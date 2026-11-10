<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductImei extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'variant_id',
        'branch_id',
        'imei_number',
        'status',
        'is_sold',
        'condition',
        'purchase_id',
        'sale_id',
        'return_id',
        'sale_return_id',
        'purchase_return_id',
        'cost_price',
        'selling_price',
        'warranty_start_date',
        'warranty_end_date',
        'warranty_days',
        'notes',
        'created_by',
        'updated_by',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}

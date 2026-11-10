<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductBranch extends BaseModel
{
    use HasFactory;

    protected $table = 'product_branches';

    protected $fillable = [
        'branch_id',
        'product_id',
        'variant_id',
        'qty',
        'sell_price',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}

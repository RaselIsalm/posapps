<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockHistory extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}

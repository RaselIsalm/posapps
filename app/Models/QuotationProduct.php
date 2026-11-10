<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuotationProduct extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class GiftCardTransaction extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

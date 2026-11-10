<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleReturn extends BaseModel
{
    use HasFactory;

    protected $guarded = [];

    public function sale()
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(SaleReturnDetail::class, 'sale_return_id');
    }
}

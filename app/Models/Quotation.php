<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quotation extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id'];

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function seller()
    {
        return $this->belongsTo(Employee::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

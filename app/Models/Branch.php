<?php

namespace App\Models;

class Branch extends BaseModel
{
    protected $fillable = [
        'name',
        'manager_id',
        'account_id',
        'phone',
        'telephone',
        'bin_number',
        'email',
        'website',
        'address',
        'branch_logo',
        'status',
        'created_by',
    ];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }

    public function productStocks()
    {
        return $this->hasMany(ProductBranch::class, 'branch_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}

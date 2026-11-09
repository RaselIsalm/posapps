<?php

namespace App\Models;

class Expenses extends BaseModel
{
    protected $fillable = [
        'date',
        'category_id',
        'branch_id',
        'account_id',
        'amount',
        'note',
        'status',
        'created_by',
        'updated_by',
    ];

    public function category()
    {
        return $this->belongsTo(ExpensesCategory::class, 'category_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function account()
    {
        return $this->belongsTo(Account::class, 'account_id');
    }
}

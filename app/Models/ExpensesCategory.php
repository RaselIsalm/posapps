<?php

namespace App\Models;

class ExpensesCategory extends BaseModel
{
    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    public function expenses()
    {
        return $this->hasMany(Expenses::class, 'category_id');
    }
}

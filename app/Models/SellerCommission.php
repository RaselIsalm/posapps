<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerCommission extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'sale_id',
        'sale_amount',
        'commission_percentage',
        'earned_amount',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }
}

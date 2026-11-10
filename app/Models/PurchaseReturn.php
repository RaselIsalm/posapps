<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseReturn extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'purchase_id',
        'supplier_id',
        'branch_id',
        'created_by',
        'return_date',
        'return_note',
        'staff_note',
        'total_qty',
        'grand_total',
        'status',
        'payment_status',
        'document',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(PurchaseReturnDetail::class);
    }
}

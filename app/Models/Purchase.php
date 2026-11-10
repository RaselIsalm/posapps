<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'supplier_id',
        'invoice_no',
        'memo_no',
        'date',
        'status',
        'currency',
        'exchange_rate',
        'payment_method',
        'order_tax',
        'order_discount',
        'shipping_cost',
        'note',
        'document',
        'total_qty',
        'grand_total',
        'paid_amount',
        'due_amount',
        'created_by',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

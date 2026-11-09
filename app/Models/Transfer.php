<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transfer extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'reference_no',
        'user_id',
        'from_branch_id',
        'to_branch_id',
        'item_count',
        'total_qty',
        'shipping_cost',
        'grand_total',
        'document',
        'note',
        'status',
    ];

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(TransferDetail::class);
    }
}

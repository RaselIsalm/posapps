<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'image',
        'salary',
        'joining_date',
        'max_commission',
        'sale_target',
        'department_id',
        'branch_id',
        'user_id',
        'leaving_date',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'salary' => 'decimal:2',
        'max_commission' => 'decimal:2',
        'sale_target' => 'decimal:2',
        'joining_date' => 'date',
        'leaving_date' => 'date',
        'status' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function commissions()
    {
        return $this->hasMany(SellerCommission::class, 'employee_id');
    }

    public function sales()
    {
        return $this->hasMany(Sale::class, 'seller_id');
    }
}

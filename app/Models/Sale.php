<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends BaseModel
{
    protected $fillable = [
        'invoice_no',
        'customer_id',
        'coupon_id',
        'branch_id',
        'seller_id',
        'sale_date',
        'sub_total',
        'discount_type',
        'discount_value',
        'discount_amount',
        'group_discount_amount',
        'coupon_discount_amount',
        'reward_points_earned',
        'reward_points_used',
        'point_discount_amount',
        'tax_percent',
        'tax_amount',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'due_amount',
        'payment_status',
        'delivery_status',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected $casts = [
        'sale_date' => 'date',
        'sub_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'group_discount_amount' => 'decimal:2',
        'coupon_discount_amount' => 'decimal:2',
        'point_discount_amount' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'due_amount' => 'decimal:2',
        'voided_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        // Voided sales stay in the database for audit, but do not count in normal totals/lists.
        static::addGlobalScope('not_voided', function ($query) {
            $query->whereNull(($query->getModel()->getTable()).'.voided_at');
        });

        static::creating(function ($model) {
            if (empty($model->invoice_no)) {
                $dateStr = date('Ymd');

                // Use lockForUpdate to prevent race condition on concurrent requests
                $lastSale = self::withoutGlobalScope('not_voided')->lockForUpdate()->latest('id')->first();
                $nextId = $lastSale ? $lastSale->id + 1 : 1;

                $paddedId = str_pad($nextId, 4, '0', STR_PAD_LEFT);

                $model->invoice_no = 'INV-'.$dateStr.$paddedId;
            }
        });
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function voidedBy()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'sale_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function seller()
    {
        return $this->belongsTo(Employee::class, 'seller_id', 'id');
    }

    public function imei()
    {
        return $this->belongsTo(ProductImei::class);
    }

    public function delivery()
    {
        return $this->hasOne(Delivery::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function returns()
    {
        return $this->hasMany(SaleReturn::class);
    }
}

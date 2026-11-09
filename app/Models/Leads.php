<?php

namespace App\Models;

class Leads extends BaseModel
{
    protected $fillable = [
        'name',
        'number',
        'email',
        'address',
        'status_id',
        'source_id',
        'status',
        'next_follow_up_date',
        'customer_id',
        'created_by',
        'updated_by',
    ];

    public function leadStatus()
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    public function source()
    {
        return $this->belongsTo(LeadSources::class, 'source_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function notes()
    {
        return $this->hasMany(LeadNote::class, 'lead_id')->latest();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}

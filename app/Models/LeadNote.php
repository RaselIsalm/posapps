<?php

namespace App\Models;

class LeadNote extends BaseModel
{
    protected $fillable = [
        'lead_id',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    public function lead()
    {
        return $this->belongsTo(Leads::class, 'lead_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}

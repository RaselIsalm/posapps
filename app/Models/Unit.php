<?php

namespace App\Models;

class Unit extends BaseModel
{
    protected $fillable = ['name', 'short_code', 'base_unit_id', 'operator', 'conversion_rate', 'formula', 'is_active', 'created_by', 'updated_by'];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function baseUnit()
    {
        return $this->belongsTo(Unit::class, 'base_unit_id');
    }

    public function childUnits()
    {
        return $this->hasMany(Unit::class, 'base_unit_id');
    }
}

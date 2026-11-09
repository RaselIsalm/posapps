<?php

namespace App\Models;

class Department extends BaseModel
{
    protected $fillable = ['name', 'code', 'description', 'status', 'created_by', 'updated_by'];

    public function employees()
    {
        return $this->hasMany(Employee::class, 'department_id');
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

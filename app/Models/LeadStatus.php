<?php

namespace App\Models;

class LeadStatus extends BaseModel
{
    protected $fillable = ['name', 'progress', 'status', 'created_by', 'updated_by'];
}

<?php

namespace App\Models;

class UserActivity extends BaseModel
{
    protected $fillable = [
        'user_id',
        'action',
        'details',
        'ip_address',
        'user_agent',
    ];
}

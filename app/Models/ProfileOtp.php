<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileOtp extends Model
{
    protected $fillable = [
        'user_id',
        'purpose',
        'code',
        'payload',
        'attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileDemoGuestTicket extends Model
{
    protected $fillable = ['token_hash', 'mode', 'user_id', 'expires_at', 'consumed_at'];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }
}

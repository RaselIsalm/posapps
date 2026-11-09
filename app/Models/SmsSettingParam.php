<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsSettingParam extends Model
{
    protected $fillable = [
        'sms_setting_id',
        'param_key',
        'param_value',
    ];

    public function smsSetting()
    {
        return $this->belongsTo(SmsSetting::class);
    }
}

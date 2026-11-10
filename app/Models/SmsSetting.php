<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_name',
        'api_url',
        'balance_api_url',

        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function params()
    {
        return $this->hasMany(SmsSettingParam::class);
    }

    public function getParamsArrayAttribute(): array
    {
        return $this->params->pluck('param_value', 'param_key')->toArray();
    }

    public function getApiKeyAttribute($value)
    {
        if (!empty($value)) return $value;

        $params = $this->params_array;
        
        // Find a key that looks like an API key
        foreach ($params as $key => $paramValue) {
            $lowerKey = strtolower($key);
            if (str_contains($lowerKey, 'api_key') || str_contains($lowerKey, 'apikey') || str_contains($lowerKey, 'token') || str_contains($lowerKey, 'key') || str_contains($lowerKey, 'password') || str_contains($lowerKey, 'secret')) {
                return $paramValue;
            }
        }
        
        // Fallback: return the first parameter value if available
        return count($params) > 0 ? reset($params) : '';
    }

    public function getMaskedApiKeyAttribute()
    {
        $key = $this->api_key;
        if (empty($key)) {
            return '—';
        }
        if (strlen($key) <= 4) {
            return str_repeat('*', strlen($key));
        }
        return substr($key, 0, 4) . str_repeat('*', strlen($key) - 4);
    }

    public function getSenderIdAttribute($value)
    {
        if (!empty($value)) return $value;

        $params = $this->params_array;
        
        // Find a key that looks like a sender ID
        foreach ($params as $key => $paramValue) {
            $lowerKey = strtolower($key);
            if (str_contains($lowerKey, 'sender') || str_contains($lowerKey, 'from')) {
                return $paramValue;
            }
        }
        return null;
    }
}

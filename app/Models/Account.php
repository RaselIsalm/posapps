<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'account_number',
        'initial_balance',
        'current_balance',
        'type',
        'note',
        'is_active',
        'is_default',
        'allow_manual_amount',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'allow_manual_amount' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'account_id', 'id');
    }

    public static function setAsDefault(?int $accountId): void
    {
        static::query()->where('is_default', 1)->update(['is_default' => 0]);

        if ($accountId) {
            static::where('id', $accountId)->update(['is_default' => 1]);
            GeneralSettings::setGeneralSetting('default_payment_method', (string) $accountId);

            return;
        }

        GeneralSettings::setGeneralSetting('default_payment_method', '');
    }

    public static function clearDefaultIfMatches(int $accountId): void
    {
        $settingsDefault = (string) (GeneralSettings::getGeneralSetting('default_payment_method') ?? '');

        if ($settingsDefault === (string) $accountId) {
            GeneralSettings::setGeneralSetting('default_payment_method', '');
        }
    }
}

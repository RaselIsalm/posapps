<?php

namespace App\Helpers;

use App\Models\GeneralSettings;
use App\Models\Currency;

class CurrencyHelper
{
    /**
     * Get the default currency settings and cache it for the request lifecycle.
     */
    public static function getDefaultCurrency()
    {
        static $currency = null;
        if ($currency !== null) {
            return $currency;
        }

        $settings = GeneralSettings::where('category', 'general')->pluck('value', 'key_name')->toArray();
        $currencyCode = $settings['currency'] ?? 'BDT';
        
        $currencyModel = Currency::where('code', $currencyCode)->first();
        if ($currencyModel) {
            $rawPosition = strtolower($currencyModel->position);
            $position = in_array($rawPosition, ['2', 'suffix']) ? 'suffix' : 'prefix';
            
            $currency = [
                'symbol' => $currencyModel->symbol,
                'code' => $currencyModel->code ?: 'BDT',
                'position' => $position,
            ];
        } else {
            $currency = [
                'symbol' => '৳',
                'code' => $currencyCode ?: 'BDT',
                'position' => 'prefix',
            ];
        }

        return $currency;
    }

    public static function getSymbol()
    {
        return self::getDefaultCurrency()['symbol'];
    }

    public static function getCode()
    {
        return self::getDefaultCurrency()['code'];
    }

    public static function getPosition()
    {
        return self::getDefaultCurrency()['position'];
    }

    public static function format($amount, $decimals = 2)
    {
        if (!is_numeric($amount)) {
            $amount = 0;
        }

        $currency = self::getDefaultCurrency();
        $symbol = $currency['symbol'];
        $position = $currency['position'];
        
        $formattedAmount = number_format((float)$amount, $decimals);
        
        if ($position === 'suffix') {
            return $formattedAmount . ' ' . $symbol;
        }
        
        return $symbol . ' ' . $formattedAmount;
    }
}






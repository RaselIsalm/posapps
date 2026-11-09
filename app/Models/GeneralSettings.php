<?php

namespace App\Models;

class GeneralSettings extends BaseModel
{
    protected $fillable = ['key_name', 'value', 'category', 'description', 'is_active'];

    public function getValueAttribute($value)
    {
        return $this->isJson($value) ? json_decode($value, true) : $value;
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = is_array($value) ? json_encode($value) : $value;
    }

    protected function isJson($string)
    {
        if ($string === null || $string === '') {
            return false;
        }

        if (! is_string($string)) {
            return false;
        }

        json_decode($string);

        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function getGeneralSetting($key)
    {
        $record = static::where('category', 'general')->where('key_name', $key)->first();

        return $record?->value;
    }

    public static function setGeneralSetting($key, $value)
    {
        return static::updateOrCreate(
            ['key_name' => $key, 'category' => 'general'],
            ['value' => is_array($value) ? json_encode($value) : $value]
        );
    }
}

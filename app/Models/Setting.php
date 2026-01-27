<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    // Helper to get value with caching
    public static function getValue($key, $default = null)
    {
        return Cache::rememberForever("setting_{$key}", function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    // Helper to set value and clear cache
    public static function setValue($key, $value)
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting_{$key}");
    }
}
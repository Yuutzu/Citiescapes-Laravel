<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'updated_by'];

    public static function getValue(string $key, $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function setValue(string $key, $value, ?int $userId = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'updated_by' => $userId]
        );
        Cache::forget("setting.{$key}");
    }
}

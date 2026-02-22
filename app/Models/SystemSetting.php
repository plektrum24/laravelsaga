<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'is_public',
    ];

    protected $casts = [
        'value' => 'array',
        'is_public' => 'boolean',
    ];

    /**
     * Get a setting value
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting?->value ?? $default;
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value, ?string $description = null): void
    {
        static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
            ]
        );
    }

    /**
     * Get public settings
     */
    public static function getPublicSettings(): array
    {
        return static::where('is_public', true)
            ->get()
            ->pluck('value', 'key')
            ->toArray();
    }

    /**
     * Get all settings as array
     */
    public static function allAsArray(): array
    {
        return static::all()->pluck('value', 'key')->toArray();
    }

    /**
     * Check if setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Delete a setting
     */
    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
    }

    /**
     * Get setting with cache
     */
    public static function cached(string $key, mixed $default = null): mixed
    {
        return cache()->rememberForever("setting.{$key}", function () use ($key, $default) {
            return static::get($key, $default);
        });
    }

    /**
     * Clear settings cache
     */
    public static function clearCache(): void
    {
        cache()->forget('settings');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];
    
    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Get a setting value by key
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("setting.{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set a setting value
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        
        Cache::forget("setting.{$key}");
    }

    /**
     * Remove a setting
     */
    public static function remove(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget("setting.{$key}");
    }

    /**
     * Get Spotify related settings
     */
    public static function getSpotifyCredentials(): array
    {
        return [
            'spotify_id' => static::get('spotify_id'),
            'access_token' => static::get('spotify_access_token'),
            'refresh_token' => static::get('spotify_refresh_token'),
            'expires_at' => static::get('spotify_token_expires_at'),
        ];
    }

    /**
     * Store Spotify credentials
     */
    public static function storeSpotifyCredentials(array $credentials): void
    {
        static::set('spotify_id', $credentials['spotify_id']);
        static::set('spotify_access_token', $credentials['access_token']);
        static::set('spotify_refresh_token', $credentials['refresh_token']);
        static::set('spotify_token_expires_at', $credentials['expires_at']);
    }
}

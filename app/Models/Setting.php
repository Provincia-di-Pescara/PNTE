<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class Setting extends Model
{
    private const CACHE_KEY = 'settings.all';

    protected $fillable = ['key', 'value', 'group'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $record = self::query()->where('key', $key)->first();

        return $record?->value ?? $default;
    }

    public static function set(string $key, mixed $value, string $group = 'general'): void
    {
        self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'group' => $group],
        );

        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, mixed> */
    public static function allCached(): array
    {
        /** @var array<string, mixed> $settings */
        $settings = Cache::remember(self::CACHE_KEY, now()->addHour(), static fn (): array => self::query()
            ->select(['key', 'value'])
            ->pluck('value', 'key')
            ->toArray()
        );

        return $settings;
    }

    public static function isSetupComplete(): bool
    {
        return (bool) self::get('setup_completed');
    }
}

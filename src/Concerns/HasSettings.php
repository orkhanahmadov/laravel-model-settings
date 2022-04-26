<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Orkhanahmadov\ModelSettings\Exceptions\InvalidSettingKey;
use Orkhanahmadov\ModelSettings\Models\Setting;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSettings
{
    public function settings(): MorphMany
    {
        return $this->morphMany(Setting::class, 'model');
    }

    protected static function defaultSettings(): array
    {
        return [];
    }

    public static function isValidSettingKey(string $key): bool
    {
        return array_key_exists($key, static::defaultSettings());
    }

    public function hasSettingInDatabase(string $key): bool
    {
        return $this->settings()->where(compact('key'))->exists();
    }

    protected static function getDefaultSetting(string $key): array
    {
        throw_unless(static::isValidSettingKey($key), new InvalidSettingKey());

        return static::defaultSettings()[$key] ?? [];
    }

    public function settingNeedsUpdate(string $key, mixed $value): bool
    {
        if (Arr::get($this->getDefaultSetting($key), 'value') !== $value) {
            return true;
        }

        return $this->hasSettingInDatabase($key);
    }

    public function updateSetting(string $key, mixed $value): Setting
    {
        $defaultSetting = $this->getDefaultSetting($key);

        return $this->settings()->updateOrCreate(
            ['type' => $defaultSetting['type'], 'key' => $key],
            ['value' => $value?->value ?? $value] // todo: test
        );
    }

    public function deleteSetting(string $key): int
    {
        return $this->settings()->where(compact('key'))->delete();
    }

    public function getSetting(string $identifier): ?array
    {
        return $this->all_settings->first(fn (array $setting, string $key) => $key === $identifier);
    }

    public function getSettingValue(string $identifier, string $nestedKeys = ''): mixed
    {
        return Arr::get(
            $this->getSetting($identifier) ?? [],
            'value' . (! empty($nestedKeys) ? ".$nestedKeys" : '')
        );
    }

    public function getAllSettingsAttribute(): Collection
    {
        return Collection::make(static::defaultSettings())
            ->merge(
                $this->settings->mapWithKeys(
                    fn (Setting $setting) => [$setting->key => $setting->only(['type', 'value'])]
                )
            )
            ->map(fn (array $setting) => [
                ...$setting,
                'type' => $setting['type']->value,
            ]);
    }

    public function getMappedSettingsAttribute(): Collection
    {
        return $this->all_settings->map(fn (array $setting) => $setting['value']);
    }
}

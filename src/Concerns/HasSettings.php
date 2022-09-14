<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Orkhanahmadov\ModelSettings\Exceptions\InvalidSettingKey;
use Orkhanahmadov\ModelSettings\Models\SettingModel;
use Orkhanahmadov\ModelSettings\Setting;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSettings
{
    public static function bootHasSettings(): void
    {
        static::deleting(function (self $model) {
            $model->settings()->delete();
        });
    }

    public function settings(): MorphMany
    {
        return $this->morphMany(SettingModel::class, 'model');
    }

    protected static function defaultSettings(): array
    {
        return [];
    }

    public static function isValidSettingKey(string $key): bool
    {
        return array_key_exists($key, static::defaultSettings());
    }

    public function hasDatabaseSetting(string $key): bool
    {
        return $this->settings()->where(compact('key'))->exists();
    }

    public function settingNeedsUpdate(string $key, mixed $value): bool
    {
        if (Arr::get($this->getDefaultSetting($key), 'value') !== $value) {
            return true;
        }

        return $this->hasDatabaseSetting($key);
    }

    public function updateSetting(string $key, mixed $value): Setting
    {
        $defaultSetting = $this->getDefaultSetting($key);

        if (! $this->settingNeedsUpdate($key, $value)) {
            return Setting::fromDefault($key, $defaultSetting);
        }

        /** @var SettingModel $model */
        $model = $this->settings()->updateOrCreate(
            ['type' => $defaultSetting['type'], 'key' => $key],
            ['value' => $value?->value ?? $value]
        );

        return Setting::fromModel($model);
    }

    protected static function getDefaultSetting(string $key): array
    {
        throw_unless(static::isValidSettingKey($key), new InvalidSettingKey());

        return static::defaultSettings()[$key];
    }

    public function deleteSetting(string $key): void
    {
        throw_unless(static::isValidSettingKey($key), new InvalidSettingKey());

        $this->settings()->where(compact('key'))->delete();
    }

    public function getSetting(string $key): Setting
    {
        throw_unless(static::isValidSettingKey($key), new InvalidSettingKey());

        return $this->all_settings->first(fn (Setting $setting, string $settingKey) => $settingKey === $key);
    }

    public function getSettingValue(string $key, ?string $nestedValueKeys = null): mixed
    {
        $value = $this->getSetting($key)->value;

        if (is_null($nestedValueKeys)) {
            return $value;
        }

        return Arr::get($value, $nestedValueKeys);
    }

    public function getAllSettingsAttribute(): Collection
    {
        return Collection::make(static::defaultSettings())
            ->merge($this->settings->mapWithKeys(fn (SettingModel $setting) => [
                $setting->key => $setting->only(['type', 'value']),
            ]))
            ->map(fn (array $setting, string $key) => Setting::fromDefault($key, $setting));
    }
}

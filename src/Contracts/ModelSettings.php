<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Orkhanahmadov\ModelSettings\Setting;

interface ModelSettings
{
    public function settings(): MorphMany;

    public static function isValidSettingKey(string $key): bool;

    public function hasDatabaseSetting(string $key): bool;

    public function settingNeedsUpdate(string $key, mixed $value): bool;

    public function updateSetting(string $key, mixed $value): Setting;

    public function deleteSetting(string $key): void;

    public function getSetting(string $key): Setting;

    public function getSettingValue(string $key, ?string $nestedValueKeys = null): mixed;

    public function getAllSettingsAttribute(): Collection;
}

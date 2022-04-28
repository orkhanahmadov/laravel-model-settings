<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings;

use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Models\SettingModel;

class Setting
{
    public function __construct(
        public readonly Type $type,
        public readonly string $key,
        public readonly mixed $value,
    ) {
    }

    public static function fromDefault(string $key, array $setting): self
    {
        return new self($setting['type'], $key, $setting['value']);
    }

    public static function fromModel(SettingModel $model): self
    {
        return new self(...$model->only(['type', 'key', 'value']));
    }
}

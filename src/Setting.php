<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings;

use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Models\SettingModel;

class Setting
{
    public function __construct(
        protected Type $type,
        protected string $key,
        protected mixed $value,
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

    public function getType(): Type
    {
        return $this->type;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }
}

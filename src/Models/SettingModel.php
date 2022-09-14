<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Models;

use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\ModelSettings\Enums\Type;

class SettingModel extends Model
{
    protected $table = 'model_settings';

    protected $fillable = [
        'type',
        'key',
        'value',
    ];

    protected $casts = [
        'type' => Type::class,
    ];

    public function getCasts(): array
    {
        if (! isset($this->attributes['type'])) {
            return parent::getCasts();
        }

        return [
            ...parent::getCasts(),
            'value' => $this->attributes['type'],
        ];
    }
}

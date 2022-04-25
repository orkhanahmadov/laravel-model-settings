<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Events\SettingCreated;
use Orkhanahmadov\ModelSettings\Events\SettingDeleted;
use Orkhanahmadov\ModelSettings\Events\SettingUpdated;

class Setting extends Model
{
    use HasFactory;

    protected $table = 'model_settings';

    protected $fillable = [
        'type',
        'key',
        'value',
    ];

    protected $casts = [
        'type' => Type::class,
    ];

    protected static function booted()
    {
        static::created(fn (Setting $setting) => event(new SettingCreated($setting)));
        static::updated(fn (Setting $setting) => event(new SettingUpdated($setting)));
        static::deleted(fn (Setting $setting) => event(new SettingDeleted($setting)));
    }

    public function getCasts()
    {
        if (isset($this->attributes['type'])) {
            return array_merge(parent::getCasts(), [
                'value' => $this->attributes['type'],
            ]);
        }

        return parent::getCasts();
    }
}

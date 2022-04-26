<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Tests\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Models\Setting;
use Orkhanahmadov\ModelSettings\Tests\TestCase;
use ValueError;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    public function testIntCast(): void
    {
        $setting = new Setting();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = Type::INT;
        $setting->value = '1';
        $setting->save();

        $this->assertSame(1, $setting->value);
        $this->assertSame('1', $setting->getRawOriginal('value'));
    }

    public function testStringCast(): void
    {
        $setting = new Setting();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = Type::STRING;
        $setting->value = 1;
        $setting->save();

        $this->assertSame('1', $setting->value);
        $this->assertSame(1, $setting->getRawOriginal('value'));
    }

    public function testJsonCastFromArray(): void
    {
        $setting = new Setting();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = Type::JSON;
        $setting->value = ['first', 'second'];
        $setting->save();

        $this->assertSame(['first', 'second'], $setting->value);
        $this->assertSame('["first","second"]', $setting->getRawOriginal('value'));
    }

    public function testJsonCastFromJson(): void
    {
        $setting = new Setting();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = Type::JSON;
        $setting->value = ['first' => 'test', 'second' => 'test'];
        $setting->save();

        $this->assertSame(['first' => 'test', 'second' => 'test'], $setting->value);
        $this->assertSame(json_encode(['first' => 'test', 'second' => 'test']), $setting->getRawOriginal('value'));
    }

    public function testCastNotListedType(): void
    {
        $this->expectException(ValueError::class);

        $setting = new Setting();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = 'not found type';
        $setting->value = 'test';
        $setting->save();
    }
}

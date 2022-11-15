<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Tests\Models;

use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Tests\TestCase;
use ValueError;

class SettingModelTest extends TestCase
{
    use RefreshDatabase;

    public function testIntCast(): void
    {
        $setting = new $this->settingModel();
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
        $setting = new $this->settingModel();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = Type::STRING;
        $setting->value = 1;
        $setting->save();

        $this->assertSame('1', $setting->value);
        $this->assertSame(1, $setting->getRawOriginal('value'));
    }

    public function testBooleanCast(): void
    {
        $true = new $this->settingModel();
        $true->model_type = 'whatever';
        $true->model_id = 1;
        $true->key = 'true';
        $true->type = Type::BOOLEAN;
        $true->value = '1';
        $true->save();
        $this->assertTrue($true->value);
        $this->assertSame('1', $true->getRawOriginal('value'));

        $false = new $this->settingModel();
        $false->model_type = 'whatever';
        $false->model_id = 1;
        $false->key = 'false';
        $false->type = Type::BOOLEAN;
        $false->value = '0';
        $false->save();
        $this->assertFalse($false->value);
        $this->assertSame('0', $false->getRawOriginal('value'));
    }

    public function testDatetimeCast(): void
    {
        $setting = new $this->settingModel();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = Type::DATETIME;
        $setting->value = $datetime = CarbonImmutable::parse('2020-01-01 00:00:00');
        $setting->save();

        $this->assertTrue($datetime->isSameAs($setting->value));
        $this->assertInstanceOf(DateTimeImmutable::class, $setting->value);
        $this->assertSame('2020-01-01 00:00:00', $setting->getRawOriginal('value'));
    }

    public function testJsonCastFromArray(): void
    {
        $setting = new $this->settingModel();
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
        $setting = new $this->settingModel();
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

        $setting = new $this->settingModel();
        $setting->model_type = 'whatever';
        $setting->model_id = 1;
        $setting->key = 'whatever';
        $setting->type = 'not found type';
        $setting->value = 'test';
        $setting->save();
    }
}

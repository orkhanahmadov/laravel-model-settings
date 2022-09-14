<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Orkhanahmadov\ModelSettings\Concerns\HasSettings;
use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Exceptions\InvalidSettingKey;
use Orkhanahmadov\ModelSettings\Setting;
use Orkhanahmadov\ModelSettings\Tests\TestCase;

class HasSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected HasSettingsFakeModel $model;

    public function testMorphsManySettings(): void
    {
        $this->assertInstanceOf(MorphMany::class, $this->model->settings());
    }

    public function testIsValidSettingKey(): void
    {
        $this->assertTrue(HasSettingsFakeModel::isValidSettingKey('setting_key'));
        $this->assertFalse(HasSettingsFakeModel::isValidSettingKey('invalid_setting_key'));
    }

    public function testHasDatabaseSetting(): void
    {
        $setting = new $this->settingModel();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::INT;
        $setting->value = 1;
        $setting->save();

        $this->assertTrue($this->model->hasDatabaseSetting('setting_key'));
        $this->assertFalse($this->model->hasDatabaseSetting('setting_key_2'));
    }

    public function testSettingNeedsUpdate(): void
    {
        $setting = new $this->settingModel();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key_2';
        $setting->type = Type::INT;
        $setting->value = 1;
        $setting->save();

        $this->assertTrue($this->model->settingNeedsUpdate('setting_key', 'zyx'));
        $this->assertFalse($this->model->settingNeedsUpdate('setting_key', 'abc'));
        $this->assertTrue($this->model->settingNeedsUpdate('setting_key_2', 123));
    }

    public function testUpdateSettingReturnsDefaultSettingIfUpdateIsNotNeeded(): void
    {
        $result = $this->model->updateSetting('setting_key', 'abc');

        $this->assertInstanceOf(Setting::class, $result);
        $this->assertSame(Type::STRING, $result->type);
        $this->assertSame('setting_key', $result->key);
        $this->assertSame('abc', $result->value);
        $this->assertCount(0, $this->settingModel::get());
    }

    public function testUpdateSettingCreatesNewSettingModel(): void
    {
        $result = $this->model->updateSetting('setting_key', 'zyx');

        $this->assertInstanceOf(Setting::class, $result);
        $this->assertNotNull($model = $this->settingModel::first());
        $this->assertSame(HasSettingsFakeModel::class, $model->model_type);
        $this->assertSame(1, $model->model_id);
        $this->assertSame($model->type, $result->type);
        $this->assertSame($model->key, $result->key);
        $this->assertSame($model->value, $result->value);
        $this->assertCount(1, $this->settingModel::get());
    }

    public function testUpdateSettingUpdatesExistingSettingModel(): void
    {
        $this->model->updateSetting('setting_key', 'zyx');
        $result = $this->model->updateSetting('setting_key', 'qwe');

        $this->assertInstanceOf(Setting::class, $result);
        $this->assertNotNull($model = $this->settingModel::first());
        $this->assertSame($model->type, $result->type);
        $this->assertSame($model->key, $result->key);
        $this->assertSame($model->value, $result->value);
        $this->assertCount(1, $this->settingModel::get());
    }

    public function testDeleteSettingThrowsExceptionIfInvalidKeyIsPassed(): void
    {
        $this->expectException(InvalidSettingKey::class);

        $this->model->deleteSetting('invalid_setting_key');
    }

    public function testDeleteSettingDeletesSettingModel(): void
    {
        $setting = new $this->settingModel();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->assertNotNull($setting->fresh());
        $this->model->deleteSetting('setting_key');
        $this->assertNull($setting->fresh());
    }

    public function testGetSettingThrowsExceptionIfInvalidKeyIsPassed(): void
    {
        $this->expectException(InvalidSettingKey::class);

        $this->model->getSetting('invalid_setting_key');
    }

    public function testGetSettingReturnsMatchingSettingWithKey(): void
    {
        $this->assertInstanceOf(Setting::class, $setting = $this->model->getSetting('setting_key'));
        $this->assertSame(Type::STRING, $setting->type);
        $this->assertSame('setting_key', $setting->key);
        $this->assertSame('abc', $setting->value);
    }

    public function testGetSettingValueReturnsValueForSetting(): void
    {
        $this->assertSame('abc', $this->model->getSettingValue('setting_key'));
    }

    public function testGetSettingValueReturnsValueForNestedArray(): void
    {
        $this->assertSame('qwe', $this->model->getSettingValue('setting_key_3', 'nested'));
    }

    public function testAllSettingsAttributeMergesDefaultAndDatabaseSettings(): void
    {
        $setting = new $this->settingModel();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->assertInstanceOf(Collection::class, $allSettings = $this->model->all_settings);
        $this->assertCount(3, $allSettings);
        $this->assertSame('zyx', $allSettings->get('setting_key')->value);
        $this->assertSame(123, $allSettings->get('setting_key_2')->value);
        $this->assertSame(['nested' => 'qwe'], $allSettings->get('setting_key_3')->value);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new HasSettingsFakeModel(['id' => 1]);
    }
}

class HasSettingsFakeModel extends Model
{
    use HasSettings;

    protected $fillable = ['id'];

    protected static function defaultSettings(): array
    {
        return [
            'setting_key' => ['type' => Type::STRING, 'value' => 'abc'],
            'setting_key_2' => ['type' => Type::INT, 'value' => 123],
            'setting_key_3' => ['type' => Type::ARRAY, 'value' => ['nested' => 'qwe']],
        ];
    }
}

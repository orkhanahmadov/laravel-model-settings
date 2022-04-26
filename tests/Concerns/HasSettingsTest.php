<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Tests\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Orkhanahmadov\ModelSettings\Concerns\HasSettings;
use Orkhanahmadov\ModelSettings\Enums\Type;
use Orkhanahmadov\ModelSettings\Models\Setting;
use Orkhanahmadov\ModelSettings\Tests\TestCase;

class HasSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected HasSettingsFakeModel $model;

    public function testMorphManyToSettings(): void
    {
        $this->assertInstanceOf(MorphMany::class, $this->model->settings());
    }

    public function testIsValidSettingKeyThrowsExceptionForInvalidKey(): void
    {
        $this->assertTrue(HasSettingsFakeModel::isValidSettingKey('setting_key'));
        $this->assertFalse(HasSettingsFakeModel::isValidSettingKey('invalid_setting_key'));
    }

    public function testHasSettingInDatabase(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::INT;
        $setting->value = 1;
        $setting->save();

        $this->assertTrue($this->model->hasSettingInDatabase('setting_key'));
        $this->assertFalse($this->model->hasSettingInDatabase('setting_key_2'));
    }

    public function testSettingNeedsUpdate(): void
    {
        $setting = new Setting();
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

    public function testUpdateSettingCreatesNewSetting(): void
    {
        $this->assertNull(Setting::first());

        $this->model->updateSetting('setting_key', 'abc');

        $this->assertNotNull($setting = Setting::first());
        $this->assertSame(HasSettingsFakeModel::class, $setting->model_type);
        $this->assertSame(1, $setting->model_id);
        $this->assertSame('setting_key', $setting->key);
        $this->assertSame(Type::STRING->value, $setting->type->value);
        $this->assertSame('abc', $setting->value);
    }

    public function testGetSetting(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->assertSame(['type' => Type::STRING->value, 'value' => 'zyx'], $this->model->getSetting('setting_key'));
        $this->assertSame(['type' => Type::INT->value, 'value' => 123], $this->model->getSetting('setting_key_2'));
        $this->assertNull($this->model->getSetting('not_existing_setting_key'));
    }

    public function testGetSettingValue(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->assertSame('zyx', $this->model->getSettingValue('setting_key'));
        $this->assertSame(123, $this->model->getSettingValue('setting_key_2'));
        $this->assertNull($this->model->getSettingValue('not_existing_setting_key'));
    }

    public function testUpdateSettingUpdatesExistingSetting(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'abc';
        $setting->save();

        $this->model->updateSetting('setting_key', 'zyx');

        $this->assertCount(1, Setting::get());
        $this->assertSame('zyx', Setting::first()->value);
    }

    public function testAllSettingsAttribute(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->assertInstanceOf(Collection::class, $allSettings = $this->model->all_settings);
        $this->assertCount(2, $allSettings);
        $this->assertSame('zyx', $allSettings->get('setting_key')['value']);
        $this->assertSame(123, $allSettings->get('setting_key_2')['value']);
    }

    public function testMappedSettingsAttribute(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->assertInstanceOf(Collection::class, $mappedSettings = $this->model->mapped_settings);
        $this->assertCount(2, $mappedSettings);
        $this->assertSame('zyx', $mappedSettings['setting_key']);
        $this->assertSame(123, $mappedSettings['setting_key_2']);
    }

    public function testDeleteSetting(): void
    {
        $setting = new Setting();
        $setting->model_type = HasSettingsFakeModel::class;
        $setting->model_id = 1;
        $setting->key = 'setting_key';
        $setting->type = Type::STRING;
        $setting->value = 'zyx';
        $setting->save();

        $this->model->deleteSetting('setting_key');
        $this->assertNull($setting->fresh());
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
        ];
    }
}

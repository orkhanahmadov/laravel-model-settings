<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings\Tests;

use Orkhanahmadov\ModelSettings\ModelSettingsServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ModelSettingsServiceProvider::class,
        ];
    }

    protected function setUpDatabase()
    {
        include_once __DIR__ . '/../database/migrations/model_settings_table.php.stub';
        (new \CreateModelSettingsTable())->up();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }
}

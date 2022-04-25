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
}
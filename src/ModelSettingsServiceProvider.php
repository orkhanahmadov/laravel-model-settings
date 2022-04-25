<?php

declare(strict_types=1);

namespace Orkhanahmadov\ModelSettings;

use Illuminate\Support\ServiceProvider;

class ModelSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('model-settings.php'),
            ], 'config');

            if (! class_exists('CreateModelSettingsTable')) {
                $this->publishes([
                    __DIR__ . '/../database/migrations/model_settings_table.php.stub' => database_path('migrations/' . date('Y_m_d_His') . '_create_model_settings_table.php'),
                ], 'migrations');
            }
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'model-settings');
    }
}

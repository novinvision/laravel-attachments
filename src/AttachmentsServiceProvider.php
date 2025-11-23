<?php

namespace NovinVision\Attachments;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;

class AttachmentsServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        $this->publishes([
            __DIR__ . '/../config' => config_path(),
        ], 'attachments');

        $this->mergeConfigFrom(__DIR__ . '/../config/attachments.php', 'attachments');
    }
}

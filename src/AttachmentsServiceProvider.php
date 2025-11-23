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
    }
}

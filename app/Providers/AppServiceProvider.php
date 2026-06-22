<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Services\CupsService::class);
        $this->app->singleton(\App\Services\FileUploadService::class);
        $this->app->singleton(\App\Services\FileConversionService::class);
        $this->app->singleton(\App\Services\StatisticsService::class);
    }

    public function boot(): void
    {
        //
    }
}

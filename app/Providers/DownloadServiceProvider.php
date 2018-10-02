<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DownloadService;

class DownloadServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the download service as a
     * reusable singleton class.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(DownloadService::class, function ($app) {
            return new DownloadService();
        });
    }
}

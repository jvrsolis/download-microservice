<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\DownloadService;

/**
 * Class DownloadServiceProvider
 *
 * A service provider class used to create
 * a singleton DownloadService class to use
 * throughout the application.
 *
 * @package App\Providers
 */
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

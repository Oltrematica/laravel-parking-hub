<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use Oltrematica\ParkingHub\Support\Manager\ParkingHubManager;

class ParkingHubServiceProvider extends LaravelServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/parking-hub.php', 'parking-hub');

        // Register the ParkingHubManager as a singleton
        $this->app->singleton(ParkingHubManager::class, fn (Container $app): ParkingHubManager => new ParkingHubManager($app));
    }

    public function boot(): void
    {
        $this->loadTranslations();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/parking-hub.php' => config_path('parking-hub.php'),
            ], 'parking-hub-config');
        }
    }

    /**
     * Load translations for the package.
     */
    private function loadTranslations(): void
    {
        $vendorTranslations = __DIR__.'/../resources/lang';
        $appTranslations = (function_exists('lang_path'))
            ? lang_path('vendor/oltrematica-parking-hub')
            : resource_path('lang/vendor/oltrematica-parking-hub');

        $this->loadTranslationsFrom($vendorTranslations, 'oltrematica-parking-hub');

        $this->loadJsonTranslationsFrom($vendorTranslations);
        $this->loadJsonTranslationsFrom($appTranslations);

        if ($this->app->runningInConsole()) {
            $this->publishes(
                [$vendorTranslations => $appTranslations],
                'oltrematica-parking-hub-translations'
            );
        }
    }
}

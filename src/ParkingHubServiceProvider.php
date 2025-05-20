<?php

declare(strict_types=1);

namespace Oltrematica\ParkingHub;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ParkingHubServiceProvider extends LaravelServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslations();
    }

    public function register() {}

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

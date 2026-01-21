<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Assets\Css;
use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentAsset;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentAsset::register([
            Js::make('tom-select', 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/js/tom-select.complete.min.js'),
        ]);

        FilamentAsset::register([
            Css::make('tom-select', 'https://cdn.jsdelivr.net/npm/tom-select@2.4.3/dist/css/tom-select.css'),
        ]);
    }
}

<?php

namespace App\Providers;

use Filament\Actions\BulkAction;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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

        BulkAction::configureUsing(function (BulkAction $action): void {
            $action->deselectRecordsAfterCompletion(function () {
                $keep_rows_selected = session('keep_rows_selected') ?? true;

                return ! $keep_rows_selected;
            });
        });

        FilamentView::registerRenderHook(
            TablesRenderHook::SELECTION_INDICATOR_ACTIONS_BEFORE,
            fn (): string => Blade::render('@livewire(\'keep-rows-selected-table\')'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_START,
            fn (): string => Blade::render('@livewire(\'issuer-switcher\')'),
        );
    }
}

<?php

namespace App\Providers;

use Akaunting\Money;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\TextInput;
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
        $this->formatter();

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
            fn(): string => Blade::render('@livewire(\'keep-rows-selected-table\')'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_START,
            fn(): string => Blade::render('@livewire(\'issuer-switcher\')'),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_HEADER_WIDGETS_START,
            fn(): string => Blade::render('@livewire(\'job-progress\')'),
            scopes: \App\Filament\Resources\ImportarLancamentoContabilGerals\Pages\ListImportarLancamentoContabilGerals::class,
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::PAGE_START,
            fn(): string => Blade::render('@livewire(\'job-progress-super-logica\')'),
            scopes: \App\Filament\Resources\ImportarLancamentoContabilSuperLogicas\Pages\ListImportarLancamentoContabilSuperLogicas::class,
        );

    }

    public function formatter(): void
    {
        $formatter = static function ($state, $evaluator, $currency, $shouldConvert) {
            if (blank($state)) {
                return null;
            }

            if (blank($currency)) {
                $currency = 'BRL';
            }
            if (is_null($shouldConvert)) {
                $shouldConvert = false;
            }

            return (new Money\Money(
                $state,
                (new Money\Currency(strtoupper((string) $evaluator->evaluate($currency)))),
                $shouldConvert,
            ))->format();
        };
        TextInput::macro('currencyMask', function ($thousandSeparator = ',', $decimalSeparator = '.', $precision = 2): TextInput {
            /**
             * @var TextInput $this
             */
            $this->view = 'filament.forms.components.currency-mask';
            $this->viewData(compact('thousandSeparator', 'decimalSeparator', 'precision'));

            return $this;
        });
    }
}

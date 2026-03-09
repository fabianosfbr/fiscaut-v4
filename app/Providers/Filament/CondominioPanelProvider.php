<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class CondominioPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('condominio')
            ->path('condominio')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->profile(isSimple: false)
            ->sidebarWidth('w-1/4')
            ->breadcrumbs(false)
            ->brandLogo(asset('images/application/logo-no-background.png'))
            ->brandLogoHeight('65px')
            ->brandName(config('app.name'))
            ->topNavigation()
            ->maxContentWidth(Width::Full)
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Condominio/Resources'), for: 'App\Filament\Condominio\Resources')
            ->discoverPages(in: app_path('Filament/Condominio/Pages'), for: 'App\Filament\Condominio\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Condominio/Widgets'), for: 'App\Filament\Condominio\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Dashboard')
                    ->icon('heroicon-o-home'),
                NavigationGroup::make()
                    ->label('Contatos')
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make()
                    ->label('Responsáveis')
                    ->icon('heroicon-o-user-circle'),

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

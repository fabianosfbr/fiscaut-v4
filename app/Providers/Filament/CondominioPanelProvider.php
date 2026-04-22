<?php

namespace App\Providers\Filament;

use App\Filament\Condominio\Pages\DashboardPorEmpresa;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
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
            ->maxContentWidth(Width::Full)
            ->databaseNotifications()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Condominio/Resources'), for: 'App\Filament\Condominio\Resources')
            ->discoverPages(in: app_path('Filament/Condominio/Pages'), for: 'App\Filament\Condominio\Pages')
            ->pages([
                DashboardPorEmpresa::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Condominio/Widgets'), for: 'App\Filament\Condominio\Widgets')
            ->widgets([])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Dashboard')
                    ->icon('heroicon-o-home'),
                NavigationGroup::make()
                    ->label('Contatos')
                    ->icon('heroicon-o-user-circle'),
                NavigationGroup::make()
                    ->label('Cobranças')
                    ->icon('heroicon-o-adjustments-vertical'),
                NavigationGroup::make()
                    ->label('Documentos')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make()
                    ->label('Assembleias')
                    ->icon('heroicon-o-building-office-2'),
                NavigationGroup::make()
                    ->label('Controles')
                    ->icon('heroicon-o-adjustments-horizontal'),

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
            ])
            ->plugin(
                \Octopy\Filament\Palette\PaletteSwitcherPlugin::make()
            );
    }
}

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
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->profile(isSimple: false)
            ->sidebarWidth('w-1/4')
            ->breadcrumbs(false)
            ->brandLogo(asset('images/application/logo-no-background.png'))
            ->brandLogoHeight('65px')
            ->brandName(config('app.name'))
            ->login()
            ->passwordReset()
            ->profile(isSimple: false)
            ->databaseNotifications()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([

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
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('NFe')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('CTe')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('NFSe')
                    ->icon('heroicon-o-clipboard-document')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('CFe')
                    ->icon('heroicon-o-clipboard-document')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Demais docs. fiscais')
                    ->icon('heroicon-o-document-duplicate')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Relatórios')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Ferramentas')
                    ->icon('heroicon-o-cpu-chip')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Usuários')
                    ->icon('heroicon-o-users')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Configurações')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Administração')
                    ->collapsed(true)
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->collapsed(),

            ]);
    }
}

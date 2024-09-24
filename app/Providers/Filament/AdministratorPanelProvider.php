<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentSettings\Filament\Plugins\FilamentSettingsPlugin;
use App\Filament\Pages\Settings\Settings;
use Saade\FilamentLaravelLog\FilamentLaravelLogPlugin;

class AdministratorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('administrator')
            ->path('administrator')
            ->favicon(url('favicon.png'))
            ->login()
            ->brandLogo(url('logo.png'))
            ->colors([
                'primary' => Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups([
                NavigationGroup::make("Category")->icon("heroicon-c-square-3-stack-3d"),
                NavigationGroup::make("Quiz")->icon("heroicon-o-pencil-square"),
                NavigationGroup::make("Telegram")->icon("heroicon-o-chat-bubble-bottom-center-text"),

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')

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
            ->plugins([
                FilamentSettingsPlugin::make()
                    ->pages([
                        Settings::class,
                    ]),
                FilamentLaravelLogPlugin::make()
                ->navigationIcon('heroicon-o-bug-ant')
                ->navigationSort(1)
                ->slug('logs')
            ]);
    }
}

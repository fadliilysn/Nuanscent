<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Nuanscent Admin')
            ->brandLogo($this->brandLogo())
            ->darkModeBrandLogo($this->brandLogo())
            ->brandLogoHeight('2.75rem')
            ->favicon(asset('images/logo-nuanscent.png'))
            ->colors([
                'primary' => Color::Amber,
                'gray' => Color::Stone,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<link rel="stylesheet" href="' . asset('css/nuanscent-admin.css') . '">',
                ),
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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

    private function brandLogo(): HtmlString
    {
        $logoUrl = asset('images/logo-nuanscent.png');

        return new HtmlString(<<<HTML
            <div class="nuanscent-admin-logo">
                <img class="nuanscent-admin-logo__image" src="{$logoUrl}" alt="Nuanscent" loading="eager">
                <span class="nuanscent-admin-logo__text">
                    <span class="nuanscent-admin-logo__name">Nuanscent</span>
                    <span class="nuanscent-admin-logo__label">Admin Panel</span>
                </span>
            </div>
        HTML);
    }
}

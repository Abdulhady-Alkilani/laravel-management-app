<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
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

class ServiceProposerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('service_proposer')
            ->path('service-proposer')
            // ->login() // نستخدم صفحة تسجيل الدخول المشتركة
            //->defaultAclantLocale('ar')
            //->rtl()
            ->brandLogo(asset('images/logos.png'))
            //->brandLogo(asset('images/logo.png')->width(120))
            ->brandName('لوحة خدماتي')
            ->colors([
                'primary' => Color::Pink, // لون مميز
            ])
            ->discoverResources(in: app_path('Filament/ServiceProposer/Resources'), for: 'App\\Filament\\ServiceProposer\\Resources')
            ->discoverPages(in: app_path('Filament/ServiceProposer/Pages'), for: 'App\\Filament\\ServiceProposer\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/ServiceProposer/Widgets'), for: 'App\\Filament\\ServiceProposer\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // سنضيف ويدجتات مقدم الخدمة المخصصة هنا لاحقاً
                \App\Filament\ServiceProposer\Widgets\ServiceOverviewStats::class,   // <== ويدجت الإحصائيات
                \App\Filament\ServiceProposer\Widgets\LatestRequestsList::class,    // <== ويدجت الطلبات
                \App\Filament\ServiceProposer\Widgets\LatestProposalsList::class,   // <== ويدجت المقترحات
            
            ])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'logout' => MenuItem::make()->label('تسجيل الخروج')
            ])
            ->favicon('images\logos.png')
            ->font('Poppins')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
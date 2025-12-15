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

class InvestorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('investor')
            ->path('investor')
            // ->login() // نستخدم صفحة تسجيل الدخول المشتركة
            //->defaultAclantLocale('ar')
            //->rtl()
            ->brandLogo(asset('images/logos.png'))
          //  ->brandLogo(asset('images/logo.png')->width(120))
            ->brandName('لوحة تحكم المستثمر')
            ->colors([
                'primary' => Color::Indigo, // لون مميز
            ])
            ->sidebarCollapsibleOnDesktop()
            ->userMenuItems([
                'logout' => MenuItem::make()->label('تسجيل الخروج')
            ])
            ->discoverResources(in: app_path('Filament/Investor/Resources'), for: 'App\\Filament\\Investor\\Resources')
            ->discoverPages(in: app_path('Filament/Investor/Pages'), for: 'App\\Filament\\Investor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Investor/Widgets'), for: 'App\\Filament\\Investor\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Investor\Widgets\InvestmentOverviewStats::class, // <== ويدجت الإحصائيات
                \App\Filament\Investor\Widgets\ProjectInvestmentChart::class,  // <== ويدجت الرسم البياني
                \App\Filament\Investor\Widgets\LatestProjectReports::class,    // <== ويدجت التقارير
                // سنضيف ويدجتات المستثمر المخصصة هنا لاحقاً
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
<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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

class EngineerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('engineer')
            ->path('engineer')
            // ->login() // نستخدم صفحة تسجيل الدخول المشتركة
            //->defaultAclantLocale('ar')
            //->rtl()
            ->brandLogo(asset('images/logo.png'))
            //->brandLogo(asset('images/logo.png')->width(120))
            ->brandName('لوحة تحكم المهندس')
            ->colors([
                'primary' => Color::Orange, // لون مميز
            ])
            ->discoverResources(in: app_path('Filament/Engineer/Resources'), for: 'App\\Filament\\Engineer\\Resources')
            ->discoverPages(in: app_path('Filament/Engineer/Pages'), for: 'App\\Filament\\Engineer\\Pages')
            ->pages([
                Pages\Dashboard::class,
                // سنضيف صفحة تقديم السيرة الذاتية هنا لاحقاً
            ])
            ->discoverWidgets(in: app_path('Filament/Engineer/Widgets'), for: 'App\\Filament\\Engineer\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // سنضيف ويدجتات المهندس المخصصة هنا لاحقاً
                \App\Filament\Engineer\Widgets\EngineerOverviewStats::class,       // <== ويدجت الإحصائيات
                \App\Filament\Engineer\Widgets\MyTasksListWidget::class,          // <== ويدجت المهام
                \App\Filament\Engineer\Widgets\MyRecentReports::class,            // <== ويدجت التقارير
            
            ])
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
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

class WorkshopSupervisorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('workshop_supervisor')
            ->path('workshop-supervisor')
            // ->login() // نستخدم صفحة تسجيل الدخول المشتركة
            //->defaultAclantLocale('ar')
            //->rtl()
            ->brandLogo(asset('images/logos.png'))
            //->brandLogo(asset('images/logo.png')->width(120))
            ->brandName('لوحة تحكم مشرف الورشة')
            ->colors([
                'primary' => Color::Teal, // لون مميز
            ])
            ->discoverResources(in: app_path('Filament/WorkshopSupervisor/Resources'), for: 'App\\Filament\\WorkshopSupervisor\\Resources')
            ->discoverPages(in: app_path('Filament/WorkshopSupervisor/Pages'), for: 'App\\Filament\\WorkshopSupervisor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/WorkshopSupervisor/Widgets'), for: 'App\\Filament\\WorkshopSupervisor\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\WorkshopSupervisor\Widgets\WorkshopOverviewStats::class, // <== ويدجت الإحصائيات
                \App\Filament\WorkshopSupervisor\Widgets\WorkshopTasksOverview::class, // <== ويدجت المهام
                \App\Filament\WorkshopSupervisor\Widgets\WorkerProductivityChart::class, // <== ويدجت الرسم البياني
                // سنضيف ويدجتات مشرف الورشة المخصصة هنا لاحقاً
            ])
            ->favicon('images\logos.png')
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
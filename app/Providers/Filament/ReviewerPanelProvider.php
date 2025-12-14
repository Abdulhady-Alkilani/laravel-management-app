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

class ReviewerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('reviewer')
            ->path('reviewer')
            // ->login() // نستخدم صفحة تسجيل الدخول المشتركة
           // ->defaultAclantLocale('ar')
            //->rtl()
            ->brandLogo(asset('images/logos.png'))
            //->brandLogo(asset('images/logo.png')->width(120))
            ->brandName('لوحة تحكم المراجع')
            ->colors([
                'primary' => Color::Purple, // لون مميز
            ])
            ->discoverResources(in: app_path('Filament/Reviewer/Resources'), for: 'App\\Filament\\Reviewer\\Resources')
            ->discoverPages(in: app_path('Filament/Reviewer/Pages'), for: 'App\\Filament\\Reviewer\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Reviewer/Widgets'), for: 'App\\Filament\\Reviewer\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                // سنضيف ويدجتات المراجع المخصصة هنا لاحقاً
            
                \App\Filament\Reviewer\Widgets\ReviewOverviewStats::class,     // <== ويدجت الإحصائيات
                \App\Filament\Reviewer\Widgets\PendingCvList::class,          // <== ويدجت السير الذاتية المعلقة
                \App\Filament\Reviewer\Widgets\PendingProposalsList::class,   // <== ويدجت المقترحات المعلقة
            
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
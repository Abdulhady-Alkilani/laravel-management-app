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

class ManagerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('manager') // <== معرف فريد للوحة
            ->path('manager') // <== المسار الذي ستكون عليه اللوحة
            //->login() // لتمكين صفحة تسجيل الدخول لـ Filament (هذا سيتم استبداله بالصفحة المشتركة)
            // ->registration() // لا نحتاج تسجيل من هنا
            ->brandLogo(asset('images/logos.png')) // شعارك
            ->brandName('لوحة تحكم مدير المشروع') // اسم اللوحة
            ->colors([
                'primary' => Color::Blue, // لون مميز لهذه اللوحة
            ])
            // مسارات الاكتشاف يجب أن تكون داخل مجلدات خاصة بالـ Panel
            ->discoverResources(in: app_path('Filament/Manager/Resources'), for: 'App\\Filament\\Manager\\Resources')
            ->discoverPages(in: app_path('Filament/Manager/Pages'), for: 'App\\Filament\\Manager\\Pages')
            ->pages([
                Pages\Dashboard::class, // لوحة التحكم الرئيسية
            ])
            ->discoverWidgets(in: app_path('Filament/Manager/Widgets'), for: 'App\\Filament\\Manager\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class, // ويدجت حساب المستخدم الافتراضي
                // هنا سنضيف ويدجتات المدير المخصصة لاحقاً
                \App\Filament\Manager\Widgets\ProjectStatsOverview::class, // <== ويدجت الإحصائيات
                \App\Filament\Manager\Widgets\ProjectProgressChart::class, // <== ويدجت الرسم البياني
                \App\Filament\Manager\Widgets\OverdueTasksList::class,    // <== ويدجت قائمة المهام المتأخرة
            
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
                Authenticate::class, // يتحقق من أن المستخدم مسجل للدخول
            ]);
    }
}
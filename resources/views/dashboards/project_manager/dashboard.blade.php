<x-layouts.app-layout title="لوحة تحكم مدير المشروع">
    <div class="dashboard-card">
        @php
            $user = Auth::user();
            // المتغيرات لـ CV Tab (تُستخدم حتى لو لم يكن الدور مهندسًا)
            $engineerRoles = ['Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer', 'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer', 'Environmental Engineer', 'Surveying Engineer'];
            $isEngineer = false;
            foreach ($engineerRoles as $roleName) {
                if ($user->hasRole($roleName)) { $isEngineer = true; break; }
            }
            $hasCv = $user->cvs()->exists();
            $allSkills = \App\Models\Skill::orderBy('name')->get();
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم مدير المشروع</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">الرئيسية</button>
            </li>
            @if($isEngineer && !$hasCv)
            <li class="nav-item" role="presentation">
                <button class="nav-link text-warning fw-bold" id="cv-tab" data-bs-toggle="tab" data-bs-target="#cv-tab-pane" type="button" role="tab" aria-controls="cv-tab-pane" aria-selected="false">تقديم السيرة الذاتية <i class="bi bi-exclamation-triangle-fill"></i></button>
            </li>
            @endif
        </ul>

        <div class="tab-content" id="myTabContent">
            {{-- Profile Tab Pane --}}
            <div class="tab-pane fade show active" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
                @include('partials._profile_tab_content')
            </div>

            {{-- Dashboard Content Tab Pane --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">مشاريعي تحت المراقبة</h3>
                <p>نظرة عامة على جميع المشاريع المدارة.</p>
                {{-- محتوى مدير المشروع --}}
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="card text-white bg-primary mb-3">
                            <div class="card-header">إجمالي المشاريع</div>
                            <div class="card-body">
                                <h5 class="card-title">10</h5>
                                <p class="card-text">عدد المشاريع التي تديرها حالياً.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success mb-3">
                            <div class="card-header">مشاريع مكتملة</div>
                            <div class="card-body">
                                <h5 class="card-title">3</h5>
                                <p class="card-text">مشاريع تم الانتهاء منها بنجاح.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning mb-3">
                            <div class="card-header">مهام متأخرة</div>
                            <div class="card-body">
                                <h5 class="card-title">2</h5>
                                <p class="card-text">مهام حرجة تحتاج إلى اهتمام.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <h4 class="mt-4">الإشعارات والتنبيهات</h4>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> المشروع "برج الأمل" تجاوز الميزانية بـ 15%.
                </div>
                <div class="alert alert-info" role="alert">
                    <i class="bi bi-info-circle-fill"></i> المهمة "صب الدور الخامس" في "مجمع الواحة" على وشك التأخر.
                </div>
            </div>

            {{-- CV Application Tab Pane (Conditional) --}}
            @if($isEngineer && !$hasCv)
            <div class="tab-pane fade" id="cv-tab-pane" role="tabpanel" aria-labelledby="cv-tab" tabindex="0">
                @include('partials._cv_tab_content', ['user' => $user, 'allSkills' => $allSkills])
            </div>
            @endif
        </div>
    </div>
</x-layouts.app-layout>
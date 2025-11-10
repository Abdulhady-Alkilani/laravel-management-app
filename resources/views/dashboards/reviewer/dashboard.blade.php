<x-layouts.app-layout title="لوحة تحكم المراجع">
    <div class="dashboard-card">
        @php
            $user = Auth::user();
            $engineerRoles = ['Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer', 'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer', 'Environmental Engineer', 'Surveying Engineer'];
            $isEngineer = false;
            foreach ($engineerRoles as $roleName) {
                if ($user->hasRole($roleName)) { $isEngineer = true; break; }
            }
            $hasCv = $user->cvs()->exists();
            $allSkills = \App\Models\Skill::orderBy('name')->get();
            
            // جلب السير الذاتية المعلقة للمراجعة
            $pendingCvs = \App\Models\Cv::where('cv_status', 'قيد الانتظار')->get();
            // جلب اقتراحات الخدمات الجديدة المعلقة للمراجعة
            $pendingProposals = \App\Models\NewServiceProposal::where('status', 'قيد المراجعة')->get();
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم المراجع</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">مراجعات معلقة</button>
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

            {{-- Dashboard Content Tab Pane - Pending Reviews --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">قائمة المراجعات المعلقة</h3>
                <p>جدول يعرض عدد العناصر التي تنتظر الموافقة.</p>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card text-white bg-warning">
                            <div class="card-header">سير ذاتية تنتظر المراجعة</div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $pendingCvs->count() }}</h5>
                                <p class="card-text">عناصر تحتاج قراراً منك.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-white bg-info">
                            <div class="card-header">مقترحات خدمات تنتظر المراجعة</div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $pendingProposals->count() }}</h5>
                                <p class="card-text">مقترحات جديدة تحتاج لتقييم.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4">السير الذاتية المعلقة</h4>
                <div class="table-responsive mb-4">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الخبرة</th>
                                <th>التعليم</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingCvs as $cv)
                            <tr>
                                <td>{{ $cv->user->first_name }} {{ $cv->user->last_name }}</td>
                                <td>{{ Str::limit($cv->experience, 50) }}</td>
                                <td>{{ Str::limit($cv->education, 50) }}</td>
                                <td><span class="badge bg-warning">{{ $cv->cv_status }}</span></td>
                                <td>
                                    {{-- روابط للمراجعة (يمكن توجيهها لصفحة تفاصيل الـ CV في Filament Admin إذا أردت أو صفحة مخصصة هنا) --}}
                                    <button class="btn btn-sm btn-primary" disabled>مراجعة</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">لا توجد سير ذاتية معلقة للمراجعة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h4 class="mt-4">مقترحات الخدمات المعلقة</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>اسم المقترح</th>
                                <th>المقترح من</th>
                                <th>تاريخ الاقتراح</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingProposals as $proposal)
                            <tr>
                                <td>{{ $proposal->proposed_service_name }}</td>
                                <td>{{ $proposal->proposer->first_name ?? 'N/A' }} {{ $proposal->proposer->last_name ?? '' }}</td>
                                <td>{{ $proposal->proposal_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-info">{{ $proposal->status }}</span></td>
                                <td>
                                    {{-- روابط للمراجعة --}}
                                    <button class="btn btn-sm btn-primary" disabled>مراجعة</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">لا توجد مقترحات خدمات معلقة للمراجعة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
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
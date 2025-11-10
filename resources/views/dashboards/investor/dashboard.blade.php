<x-layouts.app-layout title="لوحة تحكم المستثمر">
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
            // جلب المشاريع التي يستثمر فيها المستخدم
            $investedProjects = $user->projectInvestorLinks()->with('project')->get();
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم المستثمر</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">مشاريعي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-tab-pane" type="button" role="tab" aria-controls="reports-tab-pane" aria-selected="false">التقارير المالية</button>
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

            {{-- Dashboard Content Tab Pane - My Projects --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">مشاريعي المستثمر بها</h3>
                <p>بطاقات للقراءة فقط تعرض حالة، موقع، وميزانية، وتقدم جميع المشاريع التي تستثمر فيها.</p>

                @if($investedProjects->isEmpty())
                <div class="alert alert-info">لم يتم ربطك بأي مشاريع استثمارية بعد.</div>
                @else
                <div class="row g-3">
                    @foreach($investedProjects as $link)
                    @if($link->project)
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">{{ $link->project->name }}</div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $link->project->description }}</h5>
                                <p class="card-text"><strong>الموقع:</strong> {{ $link->project->location }}</p>
                                <p class="card-text"><strong>الميزانية:</strong> {{ number_format($link->project->budget, 2) }} SAR</p>
                                <p class="card-text"><strong>مبلغ استثمارك:</strong> {{ number_format($link->investment_amount, 2) }} SAR</p>
                                <p class="card-text"><strong>الحالة:</strong> <span class="badge bg-info">{{ $link->project->status }}</span></p>
                                <p class="card-text"><strong>تاريخ البدء:</strong> {{ $link->project->start_date->format('Y-m-d') }}</p>
                                <p class="card-text"><strong>تاريخ الانتهاء المخطط:</strong> {{ $link->project->end_date_planned->format('Y-m-d') }}</p>
                                {{-- يمكن عرض شريط تقدم هنا --}}
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ rand(0,100) }}%;" aria-valuenow="{{ rand(0,100) }}" aria-valuemin="0" aria-valuemax="100">{{ rand(0,100) }}% إنجاز</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Financial Reports Tab Pane --}}
            <div class="tab-pane fade" id="reports-tab-pane" role="tabpanel" aria-labelledby="reports-tab" tabindex="0">
                <h3 class="mb-3">تقارير المشاريع المستثمر بها</h3>
                <p>رسوم بيانية واتجاهات الأداء.</p>
                {{-- مثال لرسوم بيانية (قد تحتاج لمكتبة مثل Chart.js) --}}
                <div class="alert alert-secondary">
                    <i class="bi bi-graph-up-arrow"></i> سيتم عرض رسوم بيانية لتحليل الأداء هنا قريباً.
                </div>
                <p>يمكنك طلب تقارير مفصلة عبر البريد الإلكتروني.</p>
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
<x-layouts.app-layout title="لوحة تحكم مشرف الورشة">
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
            // بيانات وهمية للورشة
            $myWorkshop = \App\Models\Workshop::where('name', 'ورشة الهياكل الخرسانية')->first(); // استبدلها بمنطق لجلب ورشة المشرف
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم مشرف الورشة</h2>

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
                <h3 class="mb-3">ملخص الورشة: {{ $myWorkshop->name ?? 'غير محدد' }}</h3>
                <p>نظرة عامة على الورشة/الورشات التي تشرف عليها.</p>
                @if($myWorkshop)
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card text-white bg-info">
                            <div class="card-header">مهام قيد التنفيذ</div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $myWorkshop->tasks()->where('status', 'قيد التنفيذ')->count() }}</h5>
                                <p class="card-text">عدد المهام التي يعمل عليها حالياً.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-warning">
                            <div class="card-header">عمال متاحون</div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $myWorkshop->workers()->count() }}</h5>
                                <p class="card-text">عدد العمال المعينين للورشة.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white bg-success">
                            <div class="card-header">إنتاجية الأسبوع الماضي</div>
                            <div class="card-body">
                                <h5 class="card-title">جيدة</h5>
                                <p class="card-text">تقييم إجمالي لإنتاجية الورشة.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <h4 class="mt-4">مهام الورشة الحالية</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>الوصف</th>
                                <th>المشروع</th>
                                <th>العامل المسؤول</th>
                                <th>التقدم</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- مثال: عرض بعض المهام من الورشة --}}
                            @forelse($myWorkshop->tasks()->limit(5)->get() as $task)
                            <tr>
                                <td>{{ $task->description }}</td>
                                <td>{{ $task->project->name ?? 'لا يوجد' }}</td>
                                <td>{{ $task->assignedTo->first_name ?? 'غير معين' }}</td>
                                <td><div class="progress" style="height: 15px;"><div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%;" aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">{{ $task->progress }}%</div></div></td>
                                <td><span class="badge bg-secondary">{{ $task->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">لا توجد مهام حالياً في هذه الورشة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h4 class="mt-4">قائمة العمال</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>الاسم</th>
                                <th>الدور</th>
                                <th>تاريخ التعيين</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myWorkshop->workers()->limit(5)->get() as $worker)
                            <tr>
                                <td>{{ $worker->first_name }} {{ $worker->last_name }}</td>
                                <td><span class="badge bg-info">{{ $worker->roles->pluck('name')->implode(', ') }}</span></td>
                                <td>{{ $worker->pivot->assigned_date ?? 'N/A' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center">لا يوجد عمال معينين لهذه الورشة.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info">لم يتم تعيين ورشة عمل لهذا المشرف بعد.</div>
                @endif
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
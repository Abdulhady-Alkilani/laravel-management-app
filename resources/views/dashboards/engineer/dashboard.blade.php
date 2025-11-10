<x-layouts.app-layout title="لوحة تحكم المهندس">
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
            
            // جلب مهام المهندس وتقاريره ومشاريعة
            $myAssignedTasks = $user->assignedTasks()->orderBy('end_date_planned')->get();
            $myReports = $user->reports()->orderByDesc('created_at')->limit(5)->get();
            // المشاريع المعني بها (التي له فيها مهام)
            $myProjects = \App\Models\Project::whereHas('tasks', function($query) use ($user) {
                $query->where('assigned_to_user_id', $user->id);
            })->distinct()->get();
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم المهندس</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">مهامي الفنية</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="projects-tab" data-bs-toggle="tab" data-bs-target="#projects-tab-pane" type="button" role="tab" aria-controls="projects-tab-pane" aria-selected="false">المشاريع المعني بها</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="reports-tab" data-bs-toggle="tab" data-bs-target="#reports-tab-pane" type="button" role="tab" aria-controls="reports-tab-pane" aria-selected="false">تقاريري</button>
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

            {{-- Dashboard Content Tab Pane - My Technical Tasks --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">مهامي المعلقة والمكتملة</h3>
                <p>الواجهة الأساسية لإدارة مهامك المعينة إليك.</p>

                @if($myAssignedTasks->isEmpty())
                <div class="alert alert-info">لم يتم تعيين مهام فنية لك حالياً.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>الوصف</th>
                                <th>المشروع</th>
                                <th>الورشة</th>
                                <th>تاريخ الانتهاء</th>
                                <th>التقدم</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myAssignedTasks as $task)
                            <tr>
                                <td>{{ $task->description }}</td>
                                <td>{{ $task->project->name ?? 'N/A' }}</td>
                                <td>{{ $task->workshop->name ?? 'N/A' }}</td>
                                <td>{{ $task->end_date_planned->format('Y-m-d') }}</td>
                                <td><div class="progress" style="height: 15px;"><div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%;" aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">{{ $task->progress }}%</div></div></td>
                                <td><span class="badge bg-secondary">{{ $task->status }}</span></td>
                                <td>
                                    {{-- زر لتحديث التقدم والحالة هنا (يتطلب Livewire أو JavaScript) --}}
                                    <button class="btn btn-sm btn-info" disabled>تحديث</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Projects Involved Tab Pane --}}
            <div class="tab-pane fade" id="projects-tab-pane" role="tabpanel" aria-labelledby="projects-tab" tabindex="0">
                <h3 class="mb-3">ملخص المشاريع المعني بها</h3>
                <p>بطاقات سريعة تعرض حالة التقدم الكلي للمشاريع التي تساهم فيها.</p>

                @if($myProjects->isEmpty())
                <div class="alert alert-info">لم يتم ربطك بأي مشاريع حالياً.</div>
                @else
                <div class="row g-3">
                    @foreach($myProjects as $project)
                    <div class="col-md-6">
                        <div class="card mb-3">
                            <div class="card-header bg-primary text-white">{{ $project->name }}</div>
                            <div class="card-body">
                                <h5 class="card-title">{{ $project->description }}</h5>
                                <p class="card-text"><strong>الموقع:</strong> {{ $project->location }}</p>
                                <p class="card-text"><strong>الحالة:</strong> <span class="badge bg-info">{{ $project->status }}</span></p>
                                <p class="card-text"><strong>مدير المشروع:</strong> {{ $project->manager->first_name ?? 'N/A' }}</p>
                                {{-- يمكن عرض شريط تقدم هنا --}}
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ rand(0,100) }}%;" aria-valuenow="{{ rand(0,100) }}" aria-valuemin="0" aria-valuemax="100">{{ rand(0,100) }}% إنجاز (تقديري)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Reports Tab Pane --}}
            <div class="tab-pane fade" id="reports-tab-pane" role="tabpanel" aria-labelledby="reports-tab" tabindex="0">
                <h3 class="mb-3">سجل تقاريري المرسلة</h3>
                <p>جدول يوضح التقارير التي أعددتها ورفعتها مؤخراً.</p>

                @if($myReports->isEmpty())
                <div class="alert alert-info">لم تقم بتقديم أي تقارير بعد.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>نوع التقرير</th>
                                <th>المشروع/الخدمة</th>
                                <th>تاريخ الإنشاء</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($myReports as $report)
                            <tr>
                                <td>{{ $report->report_type }}</td>
                                <td>{{ $report->project->name ?? $report->service->name ?? 'عام' }}</td>
                                <td>{{ $report->created_at->format('Y-m-d') }}</td>
                                <td><span class="badge bg-secondary">{{ $report->report_status ?? 'N/A' }}</span></td>
                                <td>
                                    {{-- زر لعرض تفاصيل التقرير --}}
                                    <button class="btn btn-sm btn-info" disabled>عرض</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
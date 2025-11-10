<x-layouts.app-layout title="لوحة تحكم العامل">
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
            // جلب مهام العامل
            $myTasks = $user->assignedTasks()->orderBy('end_date_planned')->get();
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم العامل</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">مهامي اليوم/الأسبوع</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="workshop-tab" data-bs-toggle="tab" data-bs-target="#workshop-tab-pane" type="button" role="tab" aria-controls="workshop-tab-pane" aria-selected="false">الورشة الحالية</button>
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

            {{-- Dashboard Content Tab Pane - My Tasks --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">مهامي المعلقة والمكتملة</h3>
                <p>هنا يمكنك عرض مهامك المعينة وتحديث تقدمها.</p>

                @if($myTasks->isEmpty())
                <div class="alert alert-info">لا توجد مهام معينة لك حالياً.</div>
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
                            @foreach($myTasks as $task)
                            <tr>
                                <td>{{ $task->description }}</td>
                                <td>{{ $task->project->name ?? 'N/A' }}</td>
                                <td>{{ $task->workshop->name ?? 'N/A' }}</td>
                                <td>{{ $task->end_date_planned->format('Y-m-d') }}</td>
                                <td><div class="progress" style="height: 15px;"><div class="progress-bar" role="progressbar" style="width: {{ $task->progress }}%;" aria-valuenow="{{ $task->progress }}" aria-valuemin="0" aria-valuemax="100">{{ $task->progress }}%</div></div></td>
                                <td><span class="badge bg-secondary">{{ $task->status }}</span></td>
                                <td>
                                    {{-- يمكن إضافة زر لتحديث التقدم والحالة هنا (يتطلب Livewire أو JavaScript) --}}
                                    <button class="btn btn-sm btn-info" disabled>تحديث</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{-- تقويم المهام (مثال، يحتاج مكتبة تقويم مثل FullCalendar) --}}
                <h4 class="mt-5 mb-3">تقويم المهام</h4>
                <div class="alert alert-secondary">
                    <i class="bi bi-calendar"></i> سيتم عرض المهام هنا على تقويم مرئي قريباً.
                </div>
                @endif
            </div>

            {{-- Current Workshop Tab Pane --}}
            <div class="tab-pane fade" id="workshop-tab-pane" role="tabpanel" aria-labelledby="workshop-tab" tabindex="0">
                <h3 class="mb-3">الورشة المعين بها</h3>
                @php
                    $workerLinks = $user->workerWorkshopLinks;
                @endphp

                @if($workerLinks->isEmpty())
                <div class="alert alert-info">لم يتم تعيينك لأي ورشة عمل حالياً.</div>
                @else
                    @foreach($workerLinks as $link)
                    @if($link->workshop)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h5 class="card-title">{{ $link->workshop->name }}</h5>
                            <p class="card-text">{{ $link->workshop->description }}</p>
                            <p class="card-text"><small class="text-muted">تاريخ التعيين: {{ $link->assigned_date ? $link->assigned_date->format('Y-m-d') : 'غير محدد' }}</small></p>
                            @if($link->workshop->project)
                            <p class="card-text">المشروع الحالي: <span class="fw-bold">{{ $link->workshop->project->name }}</span></p>
                            @endif
                        </div>
                    </div>
                    @endif
                    @endforeach
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
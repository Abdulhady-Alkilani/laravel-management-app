<x-layouts.app-layout title="لوحة تحكم طالب/مقترح الخدمة">
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
            
            // جلب طلبات الخدمات والاقتراحات التي قدمها المستخدم
            $myServiceRequests = $user->serviceRequests()->orderByDesc('request_date')->limit(5)->get();
            $myServiceProposals = $user->proposedServices()->orderByDesc('proposal_date')->limit(5)->get();
            $allServices = \App\Models\Service::where('status', 'نشطة')->get();
        @endphp

        <h2 class="text-primary mb-4">لوحة تحكم طالب/مقترح الخدمة</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">حالة طلباتي الأخيرة</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="new-service-request-tab" data-bs-toggle="tab" data-bs-target="#new-service-request-tab-pane" type="button" role="tab" aria-controls="new-service-request-tab-pane" aria-selected="false">طلب خدمة جديدة</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="new-service-proposal-tab" data-bs-toggle="tab" data-bs-target="#new-service-proposal-tab-pane" type="button" role="tab" aria-controls="new-service-proposal-tab-pane" aria-selected="false">اقتراح خدمة جديدة</button>
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

            {{-- Dashboard Content Tab Pane - Latest Requests/Proposals --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">حالة طلباتي واقتراحاتي الأخيرة</h3>

                <h4 class="mt-4">طلبات الخدمات الأخيرة</h4>
                <div class="table-responsive mb-4">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>الخدمة</th>
                                <th>التفاصيل</th>
                                <th>تاريخ الطلب</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myServiceRequests as $request)
                            <tr>
                                <td>{{ $request->service->name ?? 'N/A' }}</td>
                                <td>{{ Str::limit($request->details, 50) }}</td>
                                <td>{{ $request->request_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-secondary">{{ $request->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">لم تقدم أي طلبات خدمة بعد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h4 class="mt-4">مقترحات الخدمات الأخيرة</h4>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>اسم المقترح</th>
                                <th>التفاصيل</th>
                                <th>تاريخ الاقتراح</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myServiceProposals as $proposal)
                            <tr>
                                <td>{{ $proposal->proposed_service_name }}</td>
                                <td>{{ Str::limit($proposal->service_details, 50) }}</td>
                                <td>{{ $proposal->proposal_date->format('Y-m-d') }}</td>
                                <td><span class="badge bg-info">{{ $proposal->status }}</span></td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center">لم تقدم أي اقتراحات خدمة جديدة بعد.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- New Service Request Tab Pane --}}
            <div class="tab-pane fade" id="new-service-request-tab-pane" role="tabpanel" aria-labelledby="new-service-request-tab" tabindex="0">
                <h3 class="mb-3">طلب خدمة موجودة</h3>
                <p class="text-muted">اختر خدمة من القائمة أدناه وأدخل تفاصيل طلبك.</p>
                <form action="{{ route('service-requests.store') }}" method="POST"> {{-- ستحتاج لتعريف هذا المسار والكنترولر --}}
                    @csrf
                    <div class="mb-3">
                        <x-forms.input-label for="service_id" :value="__('اختر الخدمة المطلوبة')" /><span class="text-danger">*</span>
                        <select class="form-select" id="service_id" name="service_id" required>
                            <option value="">-- اختر خدمة --</option>
                            @foreach($allServices as $service)
                                <option value="{{ $service->id }}" {{ old('service_id') == $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                        <x-forms.input-error :messages="$errors->get('service_id')" />
                    </div>
                    <div class="mb-3">
                        <x-forms.input-label for="details" :value="__('تفاصيل طلبك')" /><span class="text-danger">*</span>
                        <textarea class="form-control" id="details" name="details" rows="4" required placeholder="اشرح تفاصيل طلبك للخدمة المختارة...">{{ old('details') }}</textarea>
                        <x-forms.input-error :messages="$errors->get('details')" />
                    </div>
                    <input type="hidden" name="request_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="status" value="قيد الانتظار"> {{-- الحالة الافتراضية --}}
                    <div class="d-grid">
                        <x-forms.primary-button class="py-2 fs-5">{{ __('إرسال الطلب') }}</x-forms.primary-button>
                    </div>
                </form>
            </div>

            {{-- New Service Proposal Tab Pane --}}
            <div class="tab-pane fade" id="new-service-proposal-tab-pane" role="tabpanel" aria-labelledby="new-service-proposal-tab" tabindex="0">
                <h3 class="mb-3">اقتراح خدمة جديدة</h3>
                <p class="text-muted">اقترح خدمة جديدة يمكن إضافتها للنظام.</p>
                <form action="{{ route('new-service-proposals.store') }}" method="POST"> {{-- ستحتاج لتعريف هذا المسار والكنترولر --}}
                    @csrf
                    <div class="mb-3">
                        <x-forms.input-label for="proposed_service_name" :value="__('اسم الخدمة المقترحة')" /><span class="text-danger">*</span>
                        <x-forms.text-input id="proposed_service_name" type="text" name="proposed_service_name" value="{{ old('proposed_service_name') }}" required placeholder="مثال: خدمة فحص جودة المواد" />
                        <x-forms.input-error :messages="$errors->get('proposed_service_name')" />
                    </div>
                    <div class="mb-3">
                        <x-forms.input-label for="service_details" :value="__('تفاصيل الخدمة المقترحة')" /><span class="text-danger">*</span>
                        <textarea class="form-control" id="service_details" name="service_details" rows="4" required placeholder="وصف تفصيلي للخدمة المقترحة وفوائدها...">{{ old('service_details') }}</textarea>
                        <x-forms.input-error :messages="$errors->get('service_details')" />
                    </div>
                    <input type="hidden" name="proposal_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                    <input type="hidden" name="status" value="قيد المراجعة"> {{-- الحالة الافتراضية --}}
                    <div class="d-grid">
                        <x-forms.primary-button class="py-2 fs-5">{{ __('إرسال الاقتراح') }}</x-forms.primary-button>
                    </div>
                </form>
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
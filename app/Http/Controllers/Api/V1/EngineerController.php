<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Models\Skill;
use App\Models\Task;
use App\Models\Project;
use App\Models\Report;
use App\Http\Resources\CvResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\ProjectResource;
use App\Http\Resources\ReportResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EngineerController extends Controller
{
    // ==================== السيرة الذاتية ====================

    /**
     * GET /api/v1/engineer/cv
     */
    public function getCv()
    {
        $user = Auth::user();
        $cv = Cv::where('user_id', $user->id)->with('skills')->first();

        if (!$cv) {
            return response()->json([
                'success' => false,
                'message' => 'لا توجد سيرة ذاتية لهذا المستخدم.',
                'data' => null,
                'status_code' => 404,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب السيرة الذاتية بنجاح.',
            'data' => new CvResource($cv),
            'status_code' => 200,
        ]);
    }

    /**
     * PUT /api/v1/engineer/cv
     */
    public function updateCv(Request $request)
    {
        $request->validate([
            'profile_details' => ['nullable', 'string'],
            'experience' => ['nullable', 'string'],
            'education' => ['nullable', 'string'],
            'cv_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:5120'],
        ]);

        $user = Auth::user();
        $cv = Cv::firstOrCreate(['user_id' => $user->id]);

        $data = $request->only(['profile_details', 'experience', 'education']);

        if ($request->hasFile('cv_file')) {
            if ($cv->cv_file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($cv->cv_file_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($cv->cv_file_path);
            }
            $path = $request->file('cv_file')->store('cvs', 'public');
            $data['cv_file_path'] = $path;
        }

        $cv->update($data);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث السيرة الذاتية بنجاح.',
            'data' => new CvResource($cv->fresh()->load('skills')),
            'status_code' => 200,
        ]);
    }

    /**
     * POST /api/v1/engineer/skills
     */
    public function addSkills(Request $request)
    {
        $request->validate([
            'skills' => ['required', 'array', 'min:1'],
            'skills.*' => ['required'], // يقبل نصوص (أسماء) أو أرقام (IDs)
        ]);

        $user = Auth::user();
        $cv = Cv::firstOrCreate(['user_id' => $user->id]);

        $skillIds = [];
        foreach ($request->skills as $skillData) {
            if (is_numeric($skillData)) {
                $skill = Skill::find($skillData);
                if ($skill) {
                    $skillIds[] = $skill->id;
                }
            } else {
                $skill = Skill::firstOrCreate(['name' => trim($skillData)]);
                $skillIds[] = $skill->id;
            }
        }

        $cv->skills()->syncWithoutDetaching($skillIds);

        return response()->json([
            'success' => true,
            'message' => 'تم إضافة المهارات بنجاح.',
            'data' => new CvResource($cv->fresh()->load('skills')),
            'status_code' => 200,
        ]);
    }

    // ==================== المشاريع ====================

    /**
     * GET /api/v1/engineer/projects
     */
    public function getProjects()
    {
        $user = Auth::user();
        // المشاريع التي يملك المهندس مهاماً فيها
        $projectIds = Task::where('assigned_to_user_id', $user->id)
            ->whereNotNull('project_id')
            ->pluck('project_id')
            ->unique();

        $projects = Project::whereIn('id', $projectIds)
            ->with(['manager', 'workshops'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المشاريع بنجاح.',
            'data' => ProjectResource::collection($projects),
            'status_code' => 200,
        ]);
    }

    // ==================== المهام ====================

    /**
     * GET /api/v1/engineer/tasks
     */
    public function getTasks(Request $request)
    {
        $user = Auth::user();
        $query = Task::where('assigned_to_user_id', $user->id)
            ->with(['project', 'workshop']);

        // فلترة حسب المشروع
        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $tasks = $query->orderByDesc('created_at')->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب المهام بنجاح.',
            'data' => TaskResource::collection($tasks),
            'status_code' => 200,
        ]);
    }

    /**
     * GET /api/v1/engineer/tasks/{task}
     */
    public function getTask(Task $task)
    {
        $user = Auth::user();

        if ($task->assigned_to_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذه المهمة.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب تفاصيل المهمة بنجاح.',
            'data' => new TaskResource($task->load(['project', 'workshop'])),
            'status_code' => 200,
        ]);
    }

    /**
     * PUT /api/v1/engineer/tasks/{task}
     */
    public function updateTask(Request $request, Task $task)
    {
        $user = Auth::user();

        if ($task->assigned_to_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذه المهمة.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        $request->validate([
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
            'status' => ['nullable', 'string', 'in:قيد التنفيذ,مكتملة,معلقة,ملغاة'],
        ]);

        $progress = $request->input('progress', $task->progress);
        $status = $request->input('status', $task->status);

        if ($status === 'مكتملة' && $progress != 100) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن وضع حالة المهمة كمكتملة ونسبة الإنجاز أقل من 100%',
                'data' => null,
                'status_code' => 422,
            ], 422);
        }

        if ($progress == 100 && $status !== 'مكتملة') {
            return response()->json([
                'success' => false,
                'message' => 'عندما تكون نسبة الإنجاز 100% يجب أن تكون الحالة مكتملة',
                'data' => null,
                'status_code' => 422,
            ], 422);
        }

        $task->update($request->only(['progress', 'status']));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث المهمة بنجاح.',
            'data' => new TaskResource($task->fresh()->load(['project', 'workshop'])),
            'status_code' => 200,
        ]);
    }

    // ==================== التقارير ====================

    /**
     * GET /api/v1/engineer/reports
     */
    public function getReports()
    {
        $user = Auth::user();
        $reports = Report::where('employee_id', $user->id)
            ->with(['project', 'workshop', 'service'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب التقارير بنجاح.',
            'data' => ReportResource::collection($reports),
            'status_code' => 200,
        ]);
    }

    /**
     * GET /api/v1/engineer/reports/{report}
     */
    public function getReport(Report $report)
    {
        $user = Auth::user();

        if ($report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بعرض هذا التقرير.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم جلب التقرير بنجاح.',
            'data' => new ReportResource($report->load(['project', 'workshop', 'service'])),
            'status_code' => 200,
        ]);
    }

    /**
     * POST /api/v1/engineer/reports
     */
    public function createReport(Request $request)
    {
        $request->validate([
            'project_id' => ['nullable', 'exists:projects,id'],
            'workshop_id' => ['nullable', 'exists:workshops,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'report_type' => ['required', 'string', 'max:255'],
            'report_details' => ['required', 'string'],
        ]);

        $user = Auth::user();

        $report = Report::create([
            'employee_id' => $user->id,
            'project_id' => $request->project_id,
            'workshop_id' => $request->workshop_id,
            'service_id' => $request->service_id,
            'report_type' => $request->report_type,
            'report_details' => $request->report_details,
            'report_status' => 'معلق', // حالة افتراضية
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إنشاء التقرير بنجاح.',
            'data' => new ReportResource($report->load(['project', 'workshop', 'service'])),
            'status_code' => 201,
        ], 201);
    }

    /**
     * PUT /api/v1/engineer/reports/{report}
     */
    public function updateReport(Request $request, Report $report)
    {
        $user = Auth::user();

        if ($report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتعديل هذا التقرير.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        $request->validate([
            'report_type' => ['nullable', 'string', 'max:255'],
            'report_details' => ['nullable', 'string'],
        ]);

        $report->update($request->only(['report_type', 'report_details']));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التقرير بنجاح.',
            'data' => new ReportResource($report->fresh()->load(['project', 'workshop', 'service'])),
            'status_code' => 200,
        ]);
    }

    /**
     * DELETE /api/v1/engineer/reports/{report}
     */
    public function deleteReport(Report $report)
    {
        $user = Auth::user();

        if ($report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بحذف هذا التقرير.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        $report->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التقرير بنجاح.',
            'data' => null,
            'status_code' => 200,
        ]);
    }
}

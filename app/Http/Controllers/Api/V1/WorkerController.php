<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Models\Skill;
use App\Models\Task;
use App\Models\Workshop;
use App\Http\Resources\CvResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\WorkshopResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkerController extends Controller
{
    // ==================== السيرة الذاتية ====================

    /**
     * GET /api/v1/worker/cv
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
     * PUT /api/v1/worker/cv
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
     * POST /api/v1/worker/skills
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

    // ==================== الورشات ====================

    /**
     * GET /api/v1/worker/workshops
     */
    public function getWorkshops()
    {
        $user = Auth::user();

        $workshopIds = $user->workerWorkshopLinks()->pluck('workshop_id');
        $workshops = Workshop::whereIn('id', $workshopIds)
            ->with(['project', 'supervisor'])
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'تم جلب الورشات بنجاح.',
            'data' => WorkshopResource::collection($workshops),
            'status_code' => 200,
        ]);
    }

    // ==================== المهام ====================

    /**
     * GET /api/v1/worker/tasks
     */
    public function getTasks(Request $request)
    {
        $user = Auth::user();
        $query = Task::where('assigned_to_user_id', $user->id)
            ->with(['project', 'workshop']);

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
     * GET /api/v1/worker/tasks/{task}
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
     * PUT /api/v1/worker/tasks/{task}
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
}

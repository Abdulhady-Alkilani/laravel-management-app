<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\EngineerController;
use App\Http\Controllers\Api\V1\WorkerController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| RESTful API v1 لتطبيق Flutter
| يستخدم Laravel Sanctum للمصادقة
|
*/

// ==================== المصادقة (عامة) ====================
Route::prefix('v1')->group(function () {

    Route::post('/login', [AuthController::class, 'login']);

    // ==================== مسارات محمية بالتوكن ====================
    Route::middleware('auth:sanctum')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        // معلومات المستخدم الحالي
        Route::get('/user', function (Request $request) {
            $user = $request->user();
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('name'),
                ],
                'status_code' => 200,
            ]);
        });

        // ==================== صلاحيات المهندس ====================
        Route::prefix('engineer')->group(function () {
            // السيرة الذاتية
            Route::get('/cv', [EngineerController::class, 'getCv']);
            Route::put('/cv', [EngineerController::class, 'updateCv']);
            Route::post('/cv', [EngineerController::class, 'updateCv']); // لدعم رفع الملفات عبر POST
            Route::post('/skills', [EngineerController::class, 'addSkills']);

            // المشاريع
            Route::get('/projects', [EngineerController::class, 'getProjects']);

            // المهام
            Route::get('/tasks', [EngineerController::class, 'getTasks']);
            Route::get('/tasks/{task}', [EngineerController::class, 'getTask']);
            Route::put('/tasks/{task}', [EngineerController::class, 'updateTask']);

            // التقارير
            Route::get('/reports', [EngineerController::class, 'getReports']);
            Route::get('/reports/{report}', [EngineerController::class, 'getReport']);
            Route::post('/reports', [EngineerController::class, 'createReport']);
            Route::put('/reports/{report}', [EngineerController::class, 'updateReport']);
            Route::delete('/reports/{report}', [EngineerController::class, 'deleteReport']);
        });

        // ==================== صلاحيات العامل ====================
        Route::prefix('worker')->group(function () {
            // السيرة الذاتية
            Route::get('/cv', [WorkerController::class, 'getCv']);
            Route::put('/cv', [WorkerController::class, 'updateCv']);
            Route::post('/cv', [WorkerController::class, 'updateCv']); // لدعم رفع الملفات عبر POST
            Route::post('/skills', [WorkerController::class, 'addSkills']);

            // الورشات
            Route::get('/workshops', [WorkerController::class, 'getWorkshops']);

            // المهام
            Route::get('/tasks', [WorkerController::class, 'getTasks']);
            Route::get('/tasks/{task}', [WorkerController::class, 'getTask']);
            Route::put('/tasks/{task}', [WorkerController::class, 'updateTask']);
        });
    });
});

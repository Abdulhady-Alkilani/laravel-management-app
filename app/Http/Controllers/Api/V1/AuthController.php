<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * POST /api/v1/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'اسم المستخدم مطلوب.',
            'password.required' => 'كلمة المرور مطلوبة.',
        ]);

        $user = User::where('username', $request->username)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['بيانات تسجيل الدخول غير صحيحة.'],
            ]);
        }

        // التحقق من أن المستخدم عامل أو مهندس فقط
        $engineerRoles = [
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer', 'Information Technology Engineer',
            'Telecommunications Engineer',
        ];

        $allowedRoles = $user->roles->filter(function ($role) use ($engineerRoles) {
            return $role->name === 'Worker' || in_array($role->name, $engineerRoles);
        });

        if ($allowedRoles->isEmpty()) {
            throw ValidationException::withMessages([
                'username' => ['هذا الحساب غير مصرح له بالدخول عبر تطبيق الجوال.'],
            ]);
        }

        // حذف التوكنات السابقة (اختياري - لتسجيل دخول جلسة واحدة)
        // $user->tokens()->delete();

        $token = $user->createToken('mobile-app')->plainTextToken;

        $roles = $user->roles->pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'gender' => $user->gender,
                    'phone_number' => $user->phone_number,
                    'nationality' => $user->nationality,
                    'address' => $user->address,
                ],
                'roles' => $roles,
                'token' => $token,
            ],
            'status_code' => 200,
        ], 200);
    }

    /**
     * POST /api/v1/logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج بنجاح.',
            'data' => null,
            'status_code' => 200,
        ], 200);
    }
}

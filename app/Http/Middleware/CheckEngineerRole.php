<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckEngineerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. يرجى تسجيل الدخول.',
                'data' => null,
                'status_code' => 401,
            ], 401);
        }

        $engineerRoles = [
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer','Information Technology Engineer',
            'Telecommunications Engineer',
        ];

        $isEngineer = false;
        foreach ($engineerRoles as $roleName) {
            if ($user->hasRole($roleName)) {
                $isEngineer = true;
                break;
            }
        }

        if (!$isEngineer) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. هذا المسار مخصص للمهندسين فقط.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckWorkerRole
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

        if (!$user->hasRole('Worker')) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح. هذا المسار مخصص للعمال فقط.',
                'data' => null,
                'status_code' => 403,
            ], 403);
        }

        return $next($request);
    }
}

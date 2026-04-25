<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Skill;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    /**
     * GET /api/v1/skills
     * إرجاع جميع المهارات المخزنة في النظام
     */
    public function index()
    {
        $skills = Skill::select('id', 'name', 'description')->get();
        
        return response()->json([
            'success' => true,
            'message' => 'تم جلب المهارات بنجاح.',
            'data' => $skills,
            'status_code' => 200,
        ], 200);
    }
}

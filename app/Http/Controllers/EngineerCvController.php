<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cv;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class EngineerCvController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // تم إزالة دالة public function create() {} بالكامل

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->cvs()->exists()) {
            return redirect()->route($this->getDashboardRoute($user))
                             ->with('error', 'لا يمكنك تقديم أكثر من سيرة ذاتية واحدة.');
        }

        $request->validate([
            'profile_details' => ['nullable', 'string', 'max:1000'],
            'experience' => ['required', 'string', 'max:2000'],
            'education' => ['required', 'string', 'max:1000'],
            'selected_skills' => ['nullable', 'array'],
            'selected_skills.*' => ['exists:skills,id'],
            'new_skills' => ['nullable', 'string', 'max:500'],
        ], [
            'experience.required' => 'الخبرة مطلوبة لإكمال السيرة الذاتية.',
            'education.required' => 'المؤهلات العلمية مطلوبة لإكمال السيرة الذاتية.',
        ]);

        $cv = Cv::create([
            'user_id' => $user->id,
            'profile_details' => $request->profile_details,
            'experience' => $request->experience,
            'education' => $request->education,
            'cv_status' => 'قيد الانتظار',
        ]);

        $skillIdsToAttach = [];
        if (!empty($request->selected_skills)) {
            $skillIdsToAttach = array_merge($skillIdsToAttach, $request->selected_skills);
        }
        if (!empty($request->new_skills)) {
            $newSkillsArray = array_map('trim', explode(',', $request->new_skills));
            $newSkillsArray = array_filter($newSkillsArray);
            foreach ($newSkillsArray as $newSkillName) {
                if (!empty($newSkillName)) {
                    $skill = Skill::firstOrCreate(['name' => $newSkillName]);
                    $skillIdsToAttach[] = $skill->id;
                }
            }
        }
        if (!empty($skillIdsToAttach)) {
            $cv->skills()->syncWithoutDetaching(array_unique($skillIdsToAttach));
        }

        return redirect()->route($this->getDashboardRoute($user))
                         ->with('success', 'تم تقديم سيرتك الذاتية بنجاح! سيتم مراجعتها قريباً.');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,' . $user->id],
            'username' => ['required', 'string', 'unique:users,username,' . $user->id],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_details' => ['nullable', 'string', 'max:1000'],
        ]);

        $user->update([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'gender' => $request->gender,
            'address' => $request->address,
            'nationality' => $request->nationality,
            'phone_number' => $request->phone_number,
            'profile_details' => $request->profile_details,
        ]);

        return back()->with('success', 'تم تحديث معلومات ملفك الشخصي بنجاح!');
    }

    private function getDashboardRoute(User $user): string
    {
        if ($user->hasRole('Admin')) return 'admin.dashboard';
        if ($user->hasRole('Manager')) return 'manager.dashboard';
        if ($user->hasRole('Worker')) return 'worker.dashboard';
        if ($user->hasRole('Investor')) return 'investor.dashboard';
        if ($user->hasRole('Workshop Supervisor')) return 'workshop_supervisor.dashboard';
        if ($user->hasRole('Reviewer')) return 'reviewer.dashboard';
        if ($user->hasRole('Architectural Engineer')) return 'architectural_engineer.dashboard';
        if ($user->hasRole('Civil Engineer')) return 'civil_engineer.dashboard';
        if ($user->hasRole('Structural Engineer')) return 'structural_engineer.dashboard';
        if ($user->hasRole('Electrical Engineer')) return 'electrical_engineer.dashboard';
        if ($user->hasRole('Mechanical Engineer')) return 'mechanical_engineer.dashboard';
        if ($user->hasRole('Geotechnical Engineer')) return 'geotechnical_engineer.dashboard';
        if ($user->hasRole('Quantity Surveyor')) return 'quantity_surveyor.dashboard';
        if ($user->hasRole('Site Engineer')) return 'site_engineer.dashboard';
        if ($user->hasRole('Environmental Engineer')) return 'environmental_engineer.dashboard';
        if ($user->hasRole('Surveying Engineer')) return 'surveying_engineer.dashboard';

        return 'dashboard';
    }
}
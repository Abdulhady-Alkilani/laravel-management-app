<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User; // ستحتاج لجلب نموذج User للمديرين
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = Project::with('manager')->get();
        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $managers = User::all(); // يمكن تصفية المستخدمين الذين لديهم دور مدير مشروع
        return view('projects.create', compact('managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date_planned' => 'required|date|after_or_equal:start_date',
            'end_date_actual' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:مخطط,قيد التنفيذ,مكتمل,متوقف',
            'manager_user_id' => 'required|exists:users,id',
        ]);

        Project::create($request->all());

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $managers = User::all();
        return view('projects.edit', compact('project', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|string|max:255',
            'budget' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'end_date_planned' => 'required|date|after_or_equal:start_date',
            'end_date_actual' => 'nullable|date|after_or_equal:start_date',
            'status' => 'required|in:مخطط,قيد التنفيذ,مكتمل,متوقف',
            'manager_user_id' => 'required|exists:users,id',
        ]);

        $project->update($request->all());

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }
}
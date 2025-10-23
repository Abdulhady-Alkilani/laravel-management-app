<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use App\Models\Workshop;
use App\Models\User; // للعامل المسؤول
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tasks = Task::with('project', 'workshop', 'assignedTo')->get();
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();
        $workshops = Workshop::all();
        $workers = User::all(); // يمكن تصفية المستخدمين الذين لديهم دور عامل
        return view('tasks.create', compact('projects', 'workshops', 'workers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'workshop_id' => 'required|exists:workshops,id',
            'description' => 'required|string',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'required|date',
            'end_date_planned' => 'required|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:لم تبدأ,قيد التنفيذ,مكتملة,متوقفة',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        Task::create($request->all());

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        return view('tasks.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Task $task)
    {
        $projects = Project::all();
        $workshops = Workshop::all();
        $workers = User::all();
        return view('tasks.edit', compact('task', 'projects', 'workshops', 'workers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'workshop_id' => 'required|exists:workshops,id',
            'description' => 'required|string',
            'progress' => 'required|integer|min:0|max:100',
            'start_date' => 'required|date',
            'end_date_planned' => 'required|date|after_or_equal:start_date',
            'actual_end_date' => 'nullable|date|after_or_equal:start_date',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:لم تبدأ,قيد التنفيذ,مكتملة,متوقفة',
            'estimated_cost' => 'nullable|numeric|min:0',
            'actual_cost' => 'nullable|numeric|min:0',
        ]);

        $task->update($request->all());

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
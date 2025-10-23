<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\User;
use App\Models\Project;
use App\Models\Workshop;
use App\Models\Service;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reports = Report::with('employee', 'project', 'workshop', 'service')->get();
        return view('reports.index', compact('reports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $employees = User::all(); // يمكن تصفية المستخدمين حسب أدوارهم إذا كان هناك دور معين لـ employee
        $projects = Project::all();
        $workshops = Workshop::all();
        $services = Service::all();
        return view('reports.create', compact('employees', 'projects', 'workshops', 'services'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'workshop_id' => 'nullable|exists:workshops,id',
            'service_id' => 'nullable|exists:services,id',
            'report_type' => 'required|string|max:255',
            'report_details' => 'nullable|string',
            'report_status' => 'nullable|string|max:255',
        ]);

        Report::create($request->all());

        return redirect()->route('reports.index')->with('success', 'Report created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        return view('reports.show', compact('report'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Report $report)
    {
        $employees = User::all();
        $projects = Project::all();
        $workshops = Workshop::all();
        $services = Service::all();
        return view('reports.edit', compact('report', 'employees', 'projects', 'workshops', 'services'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $request->validate([
            'employee_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'workshop_id' => 'nullable|exists:workshops,id',
            'service_id' => 'nullable|exists:services,id',
            'report_type' => 'required|string|max:255',
            'report_details' => 'nullable|string',
            'report_status' => 'nullable|string|max:255',
        ]);

        $report->update($request->all());

        return redirect()->route('reports.index')->with('success', 'Report updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $report->delete();
        return redirect()->route('reports.index')->with('success', 'Report deleted successfully.');
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\ProjectInvestorLink;
use App\Models\Project;
use App\Models\User; // للمستثمرين
use Illuminate\Http\Request;

class ProjectInvestorLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projectInvestorLinks = ProjectInvestorLink::with('project', 'investor')->get();
        return view('project_investor_links.index', compact('projectInvestorLinks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $projects = Project::all();
        $investors = User::all(); // يمكن تصفية المستخدمين الذين لديهم دور مستثمر
        return view('project_investor_links.create', compact('projects', 'investors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'investor_user_id' => 'required|exists:users,id',
            'investment_amount' => 'nullable|numeric|min:0',
        ]);

        // للتأكد من عدم تكرار ربط المستثمر بنفس المشروع
        if (ProjectInvestorLink::where('project_id', $request->project_id)->where('investor_user_id', $request->investor_user_id)->exists()) {
            return back()->withErrors(['message' => 'This investor is already linked to this project.'])->withInput();
        }

        ProjectInvestorLink::create($request->all());

        return redirect()->route('project-investor-links.index')->with('success', 'Investor linked to project successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ProjectInvestorLink $projectInvestorLink)
    {
        return view('project_investor_links.show', compact('projectInvestorLink'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ProjectInvestorLink $projectInvestorLink)
    {
        $projects = Project::all();
        $investors = User::all();
        return view('project_investor_links.edit', compact('projectInvestorLink', 'projects', 'investors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ProjectInvestorLink $projectInvestorLink)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'investor_user_id' => 'required|exists:users,id',
            'investment_amount' => 'nullable|numeric|min:0',
        ]);

        // للتأكد من عدم تكرار ربط المستثمر بنفس المشروع بعد التحديث
        if (ProjectInvestorLink::where('project_id', $request->project_id)
                             ->where('investor_user_id', $request->investor_user_id)
                             ->where('id', '!=', $projectInvestorLink->id)
                             ->exists()) {
            return back()->withErrors(['message' => 'This investor is already linked to this project.'])->withInput();
        }

        $projectInvestorLink->update($request->all());

        return redirect()->route('project-investor-links.index')->with('success', 'Project investor link updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ProjectInvestorLink $projectInvestorLink)
    {
        $projectInvestorLink->delete();
        return redirect()->route('project-investor-links.index')->with('success', 'Project investor link deleted successfully.');
    }
}
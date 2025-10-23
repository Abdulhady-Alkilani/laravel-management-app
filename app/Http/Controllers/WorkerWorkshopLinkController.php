<?php

namespace App\Http\Controllers;

use App\Models\WorkerWorkshopLink;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Http\Request;

class WorkerWorkshopLinkController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workerWorkshopLinks = WorkerWorkshopLink::with('worker', 'workshop')->get();
        return view('worker_workshop_links.index', compact('workerWorkshopLinks'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $workers = User::all(); // يمكن تصفية المستخدمين الذين لديهم دور عامل
        $workshops = Workshop::all();
        return view('worker_workshop_links.create', compact('workers', 'workshops'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'worker_id' => 'required|exists:users,id',
            'workshop_id' => 'required|exists:workshops,id',
            'assigned_date' => 'nullable|date',
        ]);

        // للتأكد من عدم تكرار ربط العامل بنفس الورشة
        if (WorkerWorkshopLink::where('worker_id', $request->worker_id)->where('workshop_id', $request->workshop_id)->exists()) {
            return back()->withErrors(['message' => 'This worker is already assigned to this workshop.'])->withInput();
        }

        WorkerWorkshopLink::create($request->all());

        return redirect()->route('worker-workshop-links.index')->with('success', 'Worker assigned to workshop successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(WorkerWorkshopLink $workerWorkshopLink)
    {
        return view('worker_workshop_links.show', compact('workerWorkshopLink'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(WorkerWorkshopLink $workerWorkshopLink)
    {
        $workers = User::all();
        $workshops = Workshop::all();
        return view('worker_workshop_links.edit', compact('workerWorkshopLink', 'workers', 'workshops'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, WorkerWorkshopLink $workerWorkshopLink)
    {
        $request->validate([
            'worker_id' => 'required|exists:users,id',
            'workshop_id' => 'required|exists:workshops,id',
            'assigned_date' => 'nullable|date',
        ]);

        // للتأكد من عدم تكرار ربط العامل بنفس الورشة بعد التحديث
        if (WorkerWorkshopLink::where('worker_id', $request->worker_id)
                             ->where('workshop_id', $request->workshop_id)
                             ->where('id', '!=', $workerWorkshopLink->id)
                             ->exists()) {
            return back()->withErrors(['message' => 'This worker is already assigned to this workshop.'])->withInput();
        }

        $workerWorkshopLink->update($request->all());

        return redirect()->route('worker-workshop-links.index')->with('success', 'Worker workshop link updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(WorkerWorkshopLink $workerWorkshopLink)
    {
        $workerWorkshopLink->delete();
        return redirect()->route('worker-workshop-links.index')->with('success', 'Worker workshop link deleted successfully.');
    }
}
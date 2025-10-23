<?php

namespace App\Http\Controllers;

use App\Models\Cv;
use App\Models\User;
use Illuminate\Http\Request;

class CvController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cvs = Cv::with('user')->get();
        return view('cvs.index', compact('cvs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all();
        return view('cvs.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'profile_details' => 'nullable|string',
            'skills' => 'nullable|string',
            'experience' => 'nullable|string',
            'education' => 'nullable|string',
            'cv_status' => 'required|in:تحتاج تأكيد,تمت الموافقة,قيد الانتظار',
            'rejection_reason' => 'nullable|string',
        ]);

        Cv::create($request->all());

        return redirect()->route('cvs.index')->with('success', 'CV created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Cv $cv)
    {
        return view('cvs.show', compact('cv'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cv $cv)
    {
        $users = User::all();
        return view('cvs.edit', compact('cv', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cv $cv)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'profile_details' => 'nullable|string',
            'skills' => 'nullable|string',
            'experience' => 'nullable|string',
            'education' => 'nullable|string',
            'cv_status' => 'required|in:تحتاج تأكيد,تمت الموافقة,قيد الانتظار',
            'rejection_reason' => 'nullable|string',
        ]);

        $cv->update($request->all());

        return redirect()->route('cvs.index')->with('success', 'CV updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cv $cv)
    {
        $cv->delete();
        return redirect()->route('cvs.index')->with('success', 'CV deleted successfully.');
    }
}
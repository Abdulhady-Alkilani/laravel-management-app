<?php

namespace App\Http\Controllers;

use App\Models\NewServiceProposal;
use App\Models\User;
use Illuminate\Http\Request;

class NewServiceProposalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $newServiceProposals = NewServiceProposal::with('proposer', 'reviewer')->get();
        return view('new_service_proposals.index', compact('newServiceProposals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $users = User::all(); // للمقترح والمراجع
        return view('new_service_proposals.create', compact('users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'proposed_service_name' => 'required|string|max:255',
            'service_details' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'proposal_date' => 'required|date',
            'status' => 'required|in:قيد المراجعة,تمت الموافقة,مرفوض',
            'reviewer_user_id' => 'nullable|exists:users,id',
            'review_comments' => 'nullable|string',
        ]);

        NewServiceProposal::create($request->all());

        return redirect()->route('new-service-proposals.index')->with('success', 'New service proposal created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(NewServiceProposal $newServiceProposal)
    {
        return view('new_service_proposals.show', compact('newServiceProposal'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NewServiceProposal $newServiceProposal)
    {
        $users = User::all();
        return view('new_service_proposals.edit', compact('newServiceProposal', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NewServiceProposal $newServiceProposal)
    {
        $request->validate([
            'proposed_service_name' => 'required|string|max:255',
            'service_details' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'proposal_date' => 'required|date',
            'status' => 'required|in:قيد المراجعة,تمت الموافقة,مرفوض',
            'reviewer_user_id' => 'nullable|exists:users,id',
            'review_comments' => 'nullable|string',
        ]);

        $newServiceProposal->update($request->all());

        return redirect()->route('new-service-proposals.index')->with('success', 'New service proposal updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NewServiceProposal $newServiceProposal)
    {
        $newServiceProposal->delete();
        return redirect()->route('new-service-proposals.index')->with('success', 'New service proposal deleted successfully.');
    }
}
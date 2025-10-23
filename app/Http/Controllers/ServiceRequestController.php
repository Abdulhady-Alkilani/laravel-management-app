<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class ServiceRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $serviceRequests = ServiceRequest::with('service', 'user')->get();
        return view('service_requests.index', compact('serviceRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $services = Service::all();
        $users = User::all();
        return view('service_requests.create', compact('services', 'users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|string',
            'request_date' => 'required|date',
            'status' => 'required|in:قيد الانتظار,تمت الموافقة,مرفوض,قيد التنفيذ,مكتمل',
            'response_details' => 'nullable|string',
        ]);

        ServiceRequest::create($request->all());

        return redirect()->route('service-requests.index')->with('success', 'Service request created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ServiceRequest $serviceRequest)
    {
        return view('service_requests.show', compact('serviceRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ServiceRequest $serviceRequest)
    {
        $services = Service::all();
        $users = User::all();
        return view('service_requests.edit', compact('serviceRequest', 'services', 'users'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ServiceRequest $serviceRequest)
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'user_id' => 'required|exists:users,id',
            'details' => 'required|string',
            'request_date' => 'required|date',
            'status' => 'required|in:قيد الانتظار,تمت الموافقة,مرفوض,قيد التنفيذ,مكتمل',
            'response_details' => 'nullable|string',
        ]);

        $serviceRequest->update($request->all());

        return redirect()->route('service-requests.index')->with('success', 'Service request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ServiceRequest $serviceRequest)
    {
        $serviceRequest->delete();
        return redirect()->route('service-requests.index')->with('success', 'Service request deleted successfully.');
    }
}
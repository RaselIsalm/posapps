<?php

namespace App\Http\Controllers;

use App\DataTables\LeadStatusDataTable; // Import DataTable
use App\Models\Leads;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadStatusController extends Controller
{
    public function index(LeadStatusDataTable $dataTable)
    {
        return $dataTable->render('backend.lead_status.index');
    }

    public function create()
    {
        return view('backend.lead_status.ajax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:lead_statuses,name',
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $leadStatus = LeadStatus::create([
            'name' => $request->name,
            'progress' => $request->progress ?? 0,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead Status created successfully!',
            'status' => [
                'id' => $leadStatus->id,
                'name' => $leadStatus->name,
                'progress' => $leadStatus->progress,
                'status' => (bool) $leadStatus->status,
            ],
        ]);
    }

    public function edit($id)
    {
        $leadStatus = LeadStatus::findOrFail($id);

        return view('backend.lead_status.ajax.edit', compact('leadStatus'));
    }

    public function update(Request $request, $id)
    {
        $leadStatus = LeadStatus::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:lead_statuses,name,'.$id,
            'progress' => 'nullable|integer|min:0|max:100',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $leadStatus->update([
            'name' => $request->name,
            'progress' => $request->progress ?? 0,
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lead Status updated successfully!']);
    }

    public function destroy($id)
    {
        $leadStatus = LeadStatus::findOrFail($id);

        if (Leads::where('status_id', $id)->exists()) {
            return back()->with('error', 'Cannot delete this status because it is associated with one or more leads!');
        }

        $leadStatus->delete();

        return back()->with('success', 'Lead Status deleted successfully.');
    }
}

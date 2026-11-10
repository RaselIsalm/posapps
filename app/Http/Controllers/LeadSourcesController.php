<?php

namespace App\Http\Controllers;

use App\DataTables\LeadSourcesDataTable; // Import DataTable
use App\Models\Leads;
use App\Models\LeadSources;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadSourcesController extends Controller
{
    public function index(LeadSourcesDataTable $dataTable)
    {
        return $dataTable->render('backend.lead_source.index');
    }

    public function create()
    {
        return view('backend.lead_source.ajax.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:lead_sources,name',
        ]);

        $status = $request->has('status') ? 1 : 0;

        $leadSource = LeadSources::create([
            'name' => $request->name,
            'status' => $status,
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead Source created successfully!',
            'source' => [
                'id' => $leadSource->id,
                'name' => $leadSource->name,
                'status' => (bool) $leadSource->status,
            ],
        ]);
    }

    public function edit($id)
    {
        $leadSource = LeadSources::findOrFail($id);

        return view('backend.lead_source.ajax.edit', compact('leadSource'));
    }

    public function update(Request $request, $id)
    {
        $leadSource = LeadSources::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:lead_sources,name,'.$id,
        ]);

        $status = $request->has('status') ? 1 : 0;

        $leadSource->update([
            'name' => $request->name,
            'status' => $status,
            'updated_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Lead Source updated successfully!']);
    }

    public function destroy($id)
    {
        $leadSource = LeadSources::findOrFail($id);

        if (Leads::where('source_id', $id)->exists()) {
            return back()->with('error', 'Cannot delete this source because it is associated with one or more leads!');
        }

        $leadSource->delete();

        return back()->with('success', 'Lead Source deleted successfully.');
    }
}

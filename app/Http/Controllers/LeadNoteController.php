<?php

namespace App\Http\Controllers;

use App\DataTables\LeadNoteDataTable;
use App\Models\LeadNote;
use App\Models\Leads;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeadNoteController extends Controller
{
    public function index(LeadNoteDataTable $dataTable)
    {
        return $dataTable->render('backend.lead_notes.index');
    }

    public function edit($id)
    {
        $note = LeadNote::findOrFail($id);
        $leads = Leads::where('status', 1)->get();

        return view('backend.lead_notes.ajax.edit', compact('note', 'leads'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'notes' => 'nullable|string|max:5000',
        ]);

        $note = LeadNote::findOrFail($id);

        $note->update([
            'lead_id' => $request->lead_id,
            'notes' => $request->notes,
            'status' => $request->has('status') ? 1 : 0,
            'updated_by' => Auth::user()->id,
        ]);

        return redirect()->route('lead_notes.index')->with('success', 'Note updated successfully');
    }

    public function destroy($id)
    {
        $note = LeadNote::findOrFail($id);
        $note->delete();

        return redirect()->route('lead_notes.index')->with('success', 'Note deleted successfully');
    }
}

<?php

namespace App\Http\Controllers;

use App\DataTables\LeadsDataTable;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\LeadNote;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Models\LeadStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeadsController extends Controller
{
    public function index(LeadsDataTable $dataTable)
    {
        $sources = LeadSources::where('status', 1)->get();
        $statuses = LeadStatus::where('status', 1)->get();

        return $dataTable->render('backend.leads.index', compact('sources', 'statuses'));
    }

    public function create()
    {
        $statuses = LeadStatus::where('status', 1)->get();
        $sources = LeadSources::where('status', 1)->get();

        return view('backend.leads.ajax.create', compact('statuses', 'sources'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'email' => 'required|email|unique:leads,email',
            'address' => 'nullable|string|max:255',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'next_follow_up_date' => 'nullable|date',
        ]);

        $isActive = $request->has('status') ? 1 : 0;

        $lead = Leads::create([
            'name' => $request->name,
            'number' => $request->number,
            'email' => $request->email,
            'address' => $request->address,
            'status_id' => $request->status_id,
            'source_id' => $request->source_id,
            'next_follow_up_date' => $request->next_follow_up_date,
            'status' => $isActive,
            'created_by' => Auth::id(),
        ]);

        if ($request->status_id) {
            $statusName = LeadStatus::find($request->status_id)->name ?? 'Unknown';
            LeadNote::create([
                'lead_id' => $lead->id,
                'notes' => 'System: Lead created with status "'.$statusName.'".',
                'status' => 1,
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Lead added successfully!']);
    }

    public function edit($id)
    {
        $leads = Leads::findOrFail($id);
        $statuses = LeadStatus::where('status', 1)->get();
        $sources = LeadSources::where('status', 1)->get();

        return view('backend.leads.ajax.edit', compact('leads', 'statuses', 'sources'));
    }

    public function update(Request $request, $id)
    {
        $lead = Leads::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'number' => 'nullable|string|max:50',
            'email' => 'required|email|unique:leads,email,'.$id,
            'address' => 'nullable|string|max:255',
            'status_id' => 'nullable|exists:lead_statuses,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'next_follow_up_date' => 'nullable|date',
        ]);

        $isActive = $request->has('status') ? 1 : 0;

        $oldStatusId = $lead->status_id;

        $lead->update([
            'name' => $request->name,
            'number' => $request->number,
            'email' => $request->email,
            'address' => $request->address,
            'status_id' => $request->status_id,
            'source_id' => $request->source_id,
            'next_follow_up_date' => $request->next_follow_up_date,
            'status' => $isActive,
            'updated_by' => Auth::id(),
        ]);

        if ($oldStatusId != $request->status_id && $request->status_id) {
            $statusName = LeadStatus::find($request->status_id)->name ?? 'Unknown';
            LeadNote::create([
                'lead_id' => $lead->id,
                'notes' => 'System: Status changed to "'.$statusName.'".',
                'status' => 1,
                'created_by' => Auth::id(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Lead updated successfully!']);
    }

    public function createNote($id)
    {
        $lead = Leads::with('notes.creator')->findOrFail($id);

        return view('backend.leads.ajax.note', compact('lead'));
    }

    public function storeNote(Request $request)
    {
        $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'note' => 'required|string',
        ]);

        LeadNote::create([
            'lead_id' => $request->lead_id,
            'notes' => $request->note,
            'status' => 1,
            'created_by' => Auth::id(),
        ]);

        return response()->json(['success' => true, 'message' => 'Note added successfully!']);
    }

    public function destroy($id)
    {
        $lead = Leads::findOrFail($id);

        if (! empty($lead->customer_id)) {
            return redirect()->back()
                ->with('error', 'Warning: This lead has already been converted to a customer and cannot be deleted!');
        }

        if ($lead->notes()->exists()) {
            $lead->notes()->delete();
        }

        $lead->delete();

        return redirect()->route('leads.index')->with('success', 'Lead and associated notes deleted successfully.');
    }

    public function convert($id)
    {
        $lead = Leads::findOrFail($id);

        if (! empty($lead->customer_id)) {
            return redirect()->back()->with('error', 'Lead is already converted to a customer!');
        }

        if (empty($lead->number)) {
            return redirect()->back()->with('error', 'Lead phone number is required before converting to a customer.');
        }

        if (Customer::where('phone', $lead->number)->exists()) {
            return redirect()->back()->with('error', 'A customer with this phone number already exists.');
        }

        try {
            DB::transaction(function () use ($lead) {
                $defaultGroup = CustomerGroup::where('status', 1)->first();
                $prefix = $defaultGroup && ! empty($defaultGroup->prefix_code)
                    ? str_replace(' ', '_', $defaultGroup->prefix_code)
                    : 'CUS_';

                $customer = Customer::create([
                    'customer_group_id' => $defaultGroup ? $defaultGroup->id : null,
                    'name' => $lead->name,
                    'email' => $lead->email,
                    'phone' => $lead->number,
                    'address' => $lead->address,
                    'code' => $prefix.uniqid(),
                    'status' => 1,
                    'created_by' => Auth::id(),
                ]);

                $lead->update([
                    'customer_id' => $customer->id,
                    'status' => 0,
                ]);

                LeadNote::create([
                    'lead_id' => $lead->id,
                    'notes' => 'System: Lead successfully converted to Customer.',
                    'status' => 1,
                    'created_by' => Auth::id(),
                ]);
            });
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Convert failed: '.$e->getMessage());
        }

        return redirect()->route('leads.index')->with('success', 'Lead successfully converted to Customer!');
    }
}

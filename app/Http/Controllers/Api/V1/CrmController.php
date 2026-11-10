<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\LeadNote;
use App\Models\Leads;
use App\Models\LeadSources;
use App\Models\LeadStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CrmController extends ApiController
{
    private const PERMISSIONS = [
        'leads' => ['view_leads', 'create_leads', 'update_leads', 'delete_leads'],
        'lead-sources' => ['view_lead_source', 'create_lead_source', 'update_lead_source', 'delete_lead_source'],
        'lead-statuses' => ['view_lead_status', 'create_lead_status', 'update_lead_status', 'delete_lead_status'],
        'lead-notes' => ['view_lead_notes', 'create_lead_notes', 'update_lead_notes', 'delete_lead_notes'],
    ];

    public function index(Request $request, string $resource): JsonResponse
    {
        $this->assertResource($resource, 0);
        $query = match ($resource) {
            'leads' => Leads::query()->with(['leadStatus', 'source', 'customer']),
            'lead-sources' => LeadSources::query(),
            'lead-statuses' => LeadStatus::query(),
            'lead-notes' => LeadNote::query()->with(['lead', 'creator']),
        };
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($resource, $search) {
                $like = '%'.$search.'%';
                match ($resource) {
                    'leads' => $q->where('name', 'like', $like)->orWhere('email', 'like', $like)->orWhere('number', 'like', $like),
                    'lead-sources', 'lead-statuses' => $q->where('name', 'like', $like),
                    'lead-notes' => $q->where('notes', 'like', $like)->orWhereHas('lead', fn ($lead) => $lead->where('name', 'like', $like)),
                };
            });
        }
        if ($resource === 'leads') {
            $query->when($request->filled('status_id'), fn ($q) => $q->where('status_id', $request->integer('status_id')))
                ->when($request->filled('source_id'), fn ($q) => $q->where('source_id', $request->integer('source_id')))
                ->when($request->filled('active'), fn ($q) => $q->where('status', $request->boolean('active')));
        }
        if ($resource === 'lead-notes' && $request->filled('lead_id')) {
            $query->where('lead_id', $request->integer('lead_id'));
        }
        $data = $this->page($request, $query->latest('id'), fn ($row) => $this->serialize($resource, $row), $resource);
        if ($resource === 'leads') {
            $data['summary'] = [
                'total' => Leads::count(),
                'active' => Leads::where('status', 1)->count(),
                'converted' => Leads::whereNotNull('customer_id')->count(),
            ];
            $data['filters'] = $this->optionsData();
        }
        if ($resource === 'lead-notes') {
            $data['options'] = ['leads' => Leads::orderBy('name')->get(['id', 'name', 'email'])];
        }
        return $this->success($data);
    }

    public function options(string $resource): JsonResponse
    {
        $this->assertResource($resource, 0);
        return $this->success($this->optionsData());
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        $this->assertResource($resource, 0);
        $model = $this->find($resource, $id);
        if ($resource === 'leads') $model->load(['leadStatus', 'source', 'customer', 'notes.creator']);
        if ($resource === 'lead-notes') $model->load(['lead', 'creator', 'editor']);
        return $this->success(['item' => $this->serialize($resource, $model)]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $this->assertResource($resource, 1);
        $data = $this->validateResource($request, $resource);
        $item = DB::transaction(function () use ($resource, $data, $request) {
            $data['created_by'] = $request->user()->id;
            if ($resource === 'leads') {
                $data['status'] = $request->boolean('status', true);
                $lead = Leads::create($data);
                if ($lead->status_id) $this->systemNote($lead, 'Lead created with status "'.LeadStatus::find($lead->status_id)?->name.'".');
                return $lead->load(['leadStatus', 'source', 'customer']);
            }
            if ($resource === 'lead-sources') return LeadSources::create($data);
            if ($resource === 'lead-statuses') return LeadStatus::create($data);
            $data['status'] = $request->boolean('status', true);
            return LeadNote::create($data)->load(['lead', 'creator']);
        });
        return $this->success(['item' => $this->serialize($resource, $item)], 'Created successfully.', 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $this->assertResource($resource, 2);
        $model = $this->find($resource, $id);
        $data = $this->validateResource($request, $resource, $id);
        DB::transaction(function () use ($request, $resource, $model, $data) {
            $data['updated_by'] = $request->user()->id;
            if (in_array($resource, ['leads', 'lead-notes'], true)) $data['status'] = $request->boolean('status', true);
            if ($resource === 'leads') {
                $previousStatus = $model->status_id;
                $model->update($data);
                if ((string) $previousStatus !== (string) $model->status_id && $model->status_id) {
                    $this->systemNote($model, 'Status changed to "'.LeadStatus::find($model->status_id)?->name.'".');
                }
                return;
            }
            $model->update($data);
        });
        return $this->success(['item' => $this->serialize($resource, $this->find($resource, $id))], 'Updated successfully.');
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $this->assertResource($resource, 3);
        $model = $this->find($resource, $id);
        if ($resource === 'leads' && $model->customer_id) abort(409, 'A converted lead cannot be deleted.');
        if ($resource === 'lead-sources' && Leads::where('source_id', $id)->exists()) abort(409, 'This source is used by leads.');
        if ($resource === 'lead-statuses' && Leads::where('status_id', $id)->exists()) abort(409, 'This status is used by leads.');
        if ($resource === 'leads') $model->notes()->delete();
        $model->delete();
        return $this->success([], 'Deleted successfully.');
    }

    public function addNote(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('create_lead_notes');
        $lead = Leads::findOrFail($id);
        $data = $request->validate(['notes' => ['required', 'string', 'max:5000']]);
        $note = LeadNote::create(['lead_id' => $lead->id, 'notes' => $data['notes'], 'status' => 1, 'created_by' => $request->user()->id]);
        return $this->success(['item' => $this->serialize('lead-notes', $note->load(['lead', 'creator']))], 'Note added.', 201);
    }

    public function convertLead(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('update_leads');
        $lead = Leads::with('customer')->findOrFail($id);
        abort_if($lead->customer_id, 409, 'This lead has already been converted.');
        abort_if(! $lead->number, 422, 'A phone number is required before converting this lead.');
        abort_if(Customer::where('phone', $lead->number)->exists(), 409, 'A customer with this phone number already exists.');
        $customer = DB::transaction(function () use ($lead, $request) {
            $group = CustomerGroup::where('status', 1)->first();
            $prefix = $group?->prefix_code ? str_replace(' ', '_', $group->prefix_code) : 'CUS_';
            $customer = Customer::create([
                'customer_group_id' => $group?->id, 'name' => $lead->name, 'email' => $lead->email,
                'phone' => $lead->number, 'address' => $lead->address,
                'code' => $prefix.strtoupper(bin2hex(random_bytes(5))), 'status' => 1, 'created_by' => $request->user()->id,
            ]);
            $lead->update(['customer_id' => $customer->id, 'status' => 0, 'updated_by' => $request->user()->id]);
            $this->systemNote($lead, 'Lead successfully converted to Customer.');
            return $customer;
        });
        return $this->success(['customer' => ['id' => $customer->id, 'name' => $customer->name, 'phone' => $customer->phone], 'lead' => $this->serialize('leads', $lead->fresh(['leadStatus', 'source', 'customer']))], 'Lead converted to customer.');
    }

    private function assertResource(string $resource, int $action): void
    {
        abort_unless(isset(self::PERMISSIONS[$resource]), 404);
        $this->requirePermission(self::PERMISSIONS[$resource][$action]);
    }

    private function find(string $resource, int $id): mixed
    {
        return match ($resource) {
            'leads' => Leads::findOrFail($id), 'lead-sources' => LeadSources::findOrFail($id),
            'lead-statuses' => LeadStatus::findOrFail($id), 'lead-notes' => LeadNote::findOrFail($id),
        };
    }

    private function validateResource(Request $request, string $resource, ?int $id = null): array
    {
        $rules = match ($resource) {
            'leads' => [
                'name' => ['required', 'string', 'max:255'], 'number' => ['nullable', 'string', 'max:50'],
                'email' => ['required', 'email', Rule::unique('leads', 'email')->ignore($id)],
                'address' => ['nullable', 'string', 'max:255'], 'status_id' => ['nullable', 'integer', 'exists:lead_statuses,id'],
                'source_id' => ['nullable', 'integer', 'exists:lead_sources,id'], 'next_follow_up_date' => ['nullable', 'date'], 'status' => ['sometimes', 'boolean'],
            ],
            'lead-sources' => ['name' => ['required', 'string', 'max:255', Rule::unique('lead_sources', 'name')->ignore($id)], 'status' => ['sometimes', 'boolean']],
            'lead-statuses' => ['name' => ['required', 'string', 'max:255', Rule::unique('lead_statuses', 'name')->ignore($id)], 'progress' => ['nullable', 'integer', 'between:0,100'], 'status' => ['sometimes', 'boolean']],
            'lead-notes' => ['lead_id' => ['required', 'integer', 'exists:leads,id'], 'notes' => ['nullable', 'string', 'max:5000'], 'status' => ['sometimes', 'boolean']],
        };
        $data = $request->validate($rules);
        if ($resource === 'lead-statuses' && ! array_key_exists('progress', $data)) $data['progress'] = 0;
        return $data;
    }

    private function optionsData(): array
    {
        return [
            'sources' => LeadSources::where('status', 1)->orderBy('name')->get(['id', 'name']),
            'statuses' => LeadStatus::where('status', 1)->orderBy('name')->get(['id', 'name', 'progress']),
        ];
    }

    private function serialize(string $resource, mixed $model): array
    {
        $row = match ($resource) {
            'leads' => $model->only(['id', 'name', 'number', 'email', 'address', 'status_id', 'source_id', 'status', 'next_follow_up_date', 'customer_id', 'created_at', 'updated_at']),
            'lead-sources' => $model->only(['id', 'name', 'status', 'created_at', 'updated_at']),
            'lead-statuses' => $model->only(['id', 'name', 'progress', 'status', 'created_at', 'updated_at']),
            'lead-notes' => $model->only(['id', 'lead_id', 'notes', 'status', 'created_at', 'updated_at']),
        };
        if ($resource === 'leads') {
            $row['source'] = $model->source?->only(['id', 'name']);
            $row['lead_status'] = $model->leadStatus?->only(['id', 'name', 'progress']);
            $row['customer'] = $model->customer?->only(['id', 'name', 'phone']);
            if ($model->relationLoaded('notes')) $row['notes'] = $model->notes->map(fn ($note) => $this->serialize('lead-notes', $note))->values();
        } elseif ($resource === 'lead-notes') {
            $row['lead'] = $model->lead?->only(['id', 'name', 'email']);
            $row['creator'] = $model->creator?->only(['id', 'name']);
        }
        return $row;
    }

    private function systemNote(Leads $lead, string $text): void
    {
        LeadNote::create(['lead_id' => $lead->id, 'notes' => 'System: '.$text, 'status' => 1, 'created_by' => auth()->id()]);
    }
}

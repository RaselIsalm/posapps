<form action="{{ route('leads.store') }}" method="POST" id="lead-create-form">
    @csrf
    <div class="row">
        <div class="col-12 mb-3">
            <label for="name" class="form-label">Lead Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Lead Name" required>
        </div>
        <div class="col-6 mb-3">
            <label for="number" class="form-label">Phone Number</label>
            <input type="tel" class="form-control" id="number" name="number" placeholder="Phone Number">
        </div>
        <div class="col-6 mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Lead Email" required>
        </div>
        <div class="col-12 mb-3">
            <label for="address" class="form-label">Address</label>
            <input type="text" class="form-control" id="address" name="address" placeholder="Address">
        </div>
        <div class="col-6 mb-3">
            <label for="status_id" class="form-label">Lead Status</label>
            <div class="input-group flex-nowrap lead-qa-field-group">
                <select class="form-control" name="status_id" id="status_id">
                    <option value="">Select Status</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                @can('create_lead_status')
                    <button class="btn btn-outline-primary btn-lead-qa-add" type="button"
                        onclick="openLeadStatusModal()" title="Add Lead Status"
                        aria-label="Add Lead Status">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>
        <div class="col-6 mb-3">
            <label for="source_id" class="form-label">Lead Source</label>
            <div class="input-group flex-nowrap lead-qa-field-group">
                <select class="form-control" name="source_id" id="source_id">
                    <option value="">Select Source</option>
                    @foreach($sources as $source)
                        <option value="{{ $source->id }}">{{ $source->name }}</option>
                    @endforeach
                </select>
                @can('create_lead_source')
                    <button class="btn btn-outline-primary btn-lead-qa-add" type="button"
                        onclick="openLeadSourceModal()" title="Add Lead Source"
                        aria-label="Add Lead Source">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>
        <div class="col-8 mb-3">
            <label for="next_follow_up_date" class="form-label">Next Follow Up Date</label>
            <input type="date" class="form-control" id="next_follow_up_date" name="next_follow_up_date">
        </div>
        
        <div class="col-4 mb-3">
            <label class="form-check-label">Is Active</label>
            <div class="form-check form-switch text-center mt-1">
                <input class="form-check-input switch-size-lg" type="checkbox" checked
                       id="status" name="status"
                       style="width: 50px; height: 25px;">
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>

@include('backend.leads.ajax.partials.quick_add')

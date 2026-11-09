<form action="{{ route('lead_source.update', $leadSource->id) }}" method="POST" id="edit-source-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-8 mb-3">
            <label for="name" class="form-label">Source Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ $leadSource->name }}" placeholder="Lead Source Name" required>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="status" name="status" 
                       {{ $leadSource->status == 1 ? 'checked' : '' }} 
                       style="width: 40px; height: 20px;">
                <label class="form-check-label ms-2" for="status">
                    {{ $leadSource->status == 1 ? 'Active' : 'Inactive' }}
                </label>
            </div>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Change</button>
    </div>
</form>
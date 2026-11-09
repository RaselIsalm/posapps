<form action="{{ route('lead_status.update', $leadStatus->id) }}" method="POST" id="edit-status-form">
    @csrf
    @method('PUT')
    
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Status Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" 
                   value="{{ $leadStatus->name }}" placeholder="Status Name" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="progress" class="form-label">Progress (%)</label>
            <input type="number" class="form-control" id="progress" name="progress" 
                   value="{{ $leadStatus->progress }}" placeholder="0-100" min="0" max="100">
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label d-block">Is Active?</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="status" name="status" 
                       {{ $leadStatus->status == 1 ? 'checked' : '' }} 
                       style="width: 40px; height: 20px;">
                <label class="form-check-label ms-2" for="status">
                    {{ $leadStatus->status == 1 ? 'Active' : 'Inactive' }}
                </label>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Change</button>
    </div>
</form>
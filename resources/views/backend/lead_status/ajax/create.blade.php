<form action="{{ route('lead_status.store') }}" method="POST" id="create-status-form">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Status Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" 
                   placeholder="e.g. New, Contacted" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="progress" class="form-label">Progress (%)</label>
            <input type="number" class="form-control" id="progress" name="progress" 
                   placeholder="0-100" min="0" max="100">
        </div>

        <div class="col-md-12 mb-3">
            <label class="form-label d-block">Is Active?</label>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="status" name="status" checked 
                       style="width: 40px; height: 20px;">
                <label class="form-check-label ms-2" for="status">Active</label>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Create Status</button>
    </div>
</form>
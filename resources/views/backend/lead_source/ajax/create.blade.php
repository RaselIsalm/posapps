<form action="{{ route('lead_source.store') }}" method="POST" id="create-source-form">
    @csrf
    <div class="row">
        <div class="col-md-8 mb-3">
            <label for="name" class="form-label">Source Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="E.g. Facebook, Google" required>
        </div>

        <div class="col-md-4 mb-3">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="status" name="status" checked 
                       style="width: 40px; height: 20px;">
                <label class="form-check-label ms-2" for="status">Active</label>
            </div>
        </div>
    </div>

    <div class="modal-footer px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Create Source</button>
    </div>
</form>
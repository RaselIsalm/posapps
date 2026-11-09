<form action="{{ route('expenses-categories.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-6 mb-3">
            <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Category Name" required>
        </div>

        <div class="col-6 mb-3">
            <label class="form-label">Category Code <span class="text-danger">*</span></label>
            <div class="input-group">
                <input type="text" name="code" id="code" class="form-control" readonly>
                <button type="button" id="generateCodeBtn" class="btn btn-primary">
                    Generate
                </button>
            </div>
        </div>

        <div class="col-3 mb-3">
            <div class="row">
                <div class="col-12">
                    <label class="form-check-label" for="is_active">Is Active</label>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-size-lg" type="checkbox" role="switch" id="is_active"
                            checked name="is_active" style="width: 50px; height: 25px;">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>

<script>
    $('#generateCodeBtn').on('click', function() {
        let randomCode = "EXP-" + Math.floor(10000 + Math.random() * 90000);
        $('#code').val(randomCode);
    });
</script>

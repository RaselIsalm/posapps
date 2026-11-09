<form id="customer-group-form" action="{{ route('customer-groups.store') }}" method="POST">
    @csrf
    <div class="row">
        <div class="col-md-6 col-12 mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Customer Group Name" required>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <label for="prefix_code" class="form-label">Prefix Code <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="prefix_code" name="prefix_code" placeholder="Prefix Code" required>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <label for="discount" class="form-label">Discount</label>
            <input type="number" step="0.01" class="form-control" id="discount" name="discount" value="0" placeholder="Discount" required>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <label for="type" class="form-label">Discount Type</label>
            <select class="form-control" id="type" name="type">
                <option value="percentage" selected>Percentage</option>
                <option value="fixed">Fixed ({{ \App\Helpers\CurrencyHelper::getSymbol() }})</option>
            </select>
        </div>
        <div class="col-12 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" placeholder="Description" rows="3"></textarea>
        </div>
        <div class="col-md-3 col-12 mb-3">
            <div class="row">
                <div class="col-12">
                    <label class="form-check-label" for="status">Is Active</label>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-size-lg" type="checkbox" checked role="switch"
                            id="status" name="status" style="width: 50px; height: 25px;">
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
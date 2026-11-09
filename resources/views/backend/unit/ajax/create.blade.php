<form action="{{route('units.store')}}" method="POST">
    @csrf
    <div class="row">
        <div class="col-6 mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Unit Name" required>
        </div>
        <div class="col-6 mb-3">
            <label for="short_code" class="form-label">Short Code</label>
            <input type="text" class="form-control" id="short_code" name="short_code" placeholder="Short Code" required>
        </div>
        <div class="col-6 mb-3">
            <label for="base_unit" class="form-label">Base Unit</label>
            <select class="form-control selectpicker" id="base_unit" name="base_unit">
                <option value="">Select Base Unit</option>
                @foreach ($baseUnits as $item)
                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 mb-3">
            <label for="operator" class="form-label">Operator</label>
            <select class="form-control selectpicker" id="operator" name="operator">
                <option value="">Select Operator</option>
                <option value="*">Multiply </option>
                <option value="/">Divide</option>
            </select>
        </div>
        <div class="col-6 mb-3">
            <label for="conversion_rate" class="form-label">Conversion Rate</label>
            <input type="number" step="any" class="form-control" id="conversion_rate" value="1" name="conversion_rate" placeholder="Conversion Rate" required>
        </div>
        <div class="col-6 mb-3">
            <div class="row">
                <div class="col-12">
                    <label class="form-check-label" for="is_active">Is Active</label>
                </div>
                <div class="col-12 text-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-size-lg" type="checkbox" role="switch" id="is_active" checked name="is_active" style="width: 50px; height: 25px;">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="d-flex justify-content-start">
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>
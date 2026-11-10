<form id="brand-form" action="{{ route('brands.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row">
        <div class="col-12 mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Brand Name" required>
        </div>
        <div class="col-12 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea style="caret-color: black;" class="form-control" id="description" name="description" placeholder="Description"
                rows="2"></textarea>
        </div>
        <div class="col-12 mb-3">
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

        <div class="col-md-8 col-12 mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" class="form-control" id="image" name="image" placeholder="Image"
                accept=".jpg,.jpeg,.png"
                onchange="document.getElementById('create_image_preview').src = window.URL.createObjectURL(this.files[0])">
        </div>
        <div class="col-md-4 col-12 mb-3 text-md-center">
            <label class="form-label d-block">Preview</label>
            <img id="create_image_preview" src="{{ url('backend/assets/images/preview_image.png') }}"
                class="img-fluid border rounded" alt="Image Preview" style="max-height: 100px; max-width: 100px;" />
        </div>
    </div>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
        <button type="submit" class="btn btn-primary">Create</button>
    </div>
</form>

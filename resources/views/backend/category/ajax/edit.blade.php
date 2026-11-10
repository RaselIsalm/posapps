<form id="category-form" action="{{ route('categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6 col-12 mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $category->name }}"
                placeholder="Category Name" required>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <label for="parent_id" class="form-label">Parent Category</label>
            <select class="form-control selectpicker" id="parent_id" name="parent_id">
                <option value="">Select Parent</option>
                @foreach ($categories as $item)
                    @if ($item->id != $category->id)
                        <option value="{{ $item->id }}" {{ $item->id == $category->parent_id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="col-12 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea style="caret-color: black;" class="form-control" id="description" name="description" placeholder="Description"
                rows="2">{{ $category->description }}</textarea>
        </div>
        <div class="col-md-6 col-12 mb-3">
            <div class="row">
                <div class="col-12">
                    <label class="form-check-label" for="is_active">Is Active</label>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input class="form-check-input switch-size-lg" type="checkbox" role="switch" id="is_active"
                            name="is_active" style="width: 50px; height: 25px;"
                            {{ $category->status ? 'checked' : '' }}>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8 col-12 mb-3">
            <label for="image" class="form-label">Image</label>
            <input type="file" class="form-control" id="image" name="image" accept=".jpg,.jpeg,.png"
                onchange="document.getElementById('remove_image').value='0'; document.getElementById('edit_image_preview').src = window.URL.createObjectURL(this.files[0]); var b=document.getElementById('removeImageBtn'); if(b){b.hidden=false;b.style.display='';}">
            <input type="hidden" name="remove_image" id="remove_image" value="0">
        </div>
        <div class="col-md-4 col-12 mb-3 text-md-center">
            <label class="form-label d-block">Current/Preview</label>
            @php
                $imagePlaceholder = url('backend/assets/images/preview_image.png');
            @endphp
            <img id="edit_image_preview"
                src="{{ !empty($category->image) ? asset('storage/category/' . $category->image) : $imagePlaceholder }}"
                data-placeholder="{{ $imagePlaceholder }}"
                class="img-fluid border rounded" alt="Image Preview" style="max-height: 100px; max-width: 100px;" />
            @if (!empty($category->image))
                <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="removeImageBtn">Remove image</button>
            @endif
        </div>
    </div>

    <script>
        (function () {
            var flag = document.getElementById('remove_image');
            var img = document.getElementById('edit_image_preview');
            var file = document.getElementById('image');
            var btn = document.getElementById('removeImageBtn');
            if (btn) {
                btn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (flag) flag.value = '1';
                    if (file) file.value = '';
                    if (img) img.src = img.getAttribute('data-placeholder') || img.src;
                    btn.hidden = true;
                    btn.style.setProperty('display', 'none', 'important');
                });
            }
        })();
    </script>

    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Discard</button>
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>

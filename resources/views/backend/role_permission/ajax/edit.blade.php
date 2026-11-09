<form action="{{route('roles.update', $role->id)}}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-12 mb-3">
            <label for="name" class="form-label">Role Name</label>
            <input type="text" class="form-control" id="name" value="{{$role->name}}" name="name" placeholder="Role Name" required>
        </div>
        <div class="col-12 mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea style="caret-color: black;" class="form-control" id="description" name="description" placeholder="Description" rows="2">{{$role->description}}</textarea>
        </div>
    </div>
    <div class="d-flex justify-content-start">
        <button type="submit" class="btn btn-primary">Update</button>
    </div>
</form>


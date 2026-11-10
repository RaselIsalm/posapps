<form action="{{ route('employees.store') }}" method="POST" id="create-employee-form">
    @csrf
    <div class="row">
        @include('backend.employee.ajax.partials.photo_field')
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="name" name="name" placeholder="Employee Name" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
            <input type="email" class="form-control" id="email" name="email" placeholder="Login Email Address" required>
            <small class="text-muted">This email will be used to login.</small>
        </div>

        <div class="col-md-6 mb-3">
            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
            <input type="tel" class="form-control" id="phone" name="phone" placeholder="Phone Number" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="branch_id" class="form-label">Branch <span class="text-danger">*</span></label>
            <div class="input-group emp-field-group">
                <select class="form-select" id="branch_id" name="branch_id" required>
                    <option value="">-- Select Branch --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
                @can('create_branches')
                    <button type="button" class="btn btn-outline-primary btn-emp-add" onclick="openEmpBranchModal()"
                        title="Add Branch">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="department_id" class="form-label">Select Department <span class="text-danger">*</span></label>
            <div class="input-group emp-field-group">
                <select class="form-select" id="department_id" name="department_id" required>
                    <option value="">-- Select Department --</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                    @endforeach
                </select>
                @can('create_departments')
                    <button type="button" class="btn btn-outline-primary btn-emp-add" onclick="openQuickDepartmentModal()"
                        title="Add New Department">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
            <div class="input-group emp-field-group">
                <select class="form-select" id="role" name="role" required>
                    <option value="">-- Select Role --</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $role->name }}</option>
                    @endforeach
                </select>
                @can('manage_roles')
                    <button type="button" class="btn btn-outline-primary btn-emp-add" onclick="openEmpRoleModal()"
                        title="Add Role">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
            <small class="text-muted">Permissions come from the selected role.</small>
        </div>

        <div class="col-md-6 mb-3">
            <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Login Password" required minlength="6" autocomplete="new-password">
        </div>

        <div class="col-md-6 mb-3">
            <label for="password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" required minlength="6" autocomplete="new-password">
        </div>

        <div class="col-md-6 mb-3">
            <label for="salary" class="form-label">Salary <span class="text-danger">*</span></label>
            <input type="number" class="form-control" id="salary" name="salary" placeholder="0.00" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="max_commission" class="form-label">Max Commission</label>
            <input type="number" class="form-control" id="max_commission" name="max_commission" placeholder="0.00">
        </div>

        <div class="col-md-6 mb-3">
            <label for="sale_target" class="form-label">Sale Target</label>
            <input type="number" class="form-control" id="sale_target" name="sale_target" placeholder="0.00">
        </div>

        <div class="col-md-6 mb-3">
            <label for="joining_date" class="form-label">Joining Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="joining_date" name="joining_date" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="leaving_date" class="form-label">Leaving Date</label>
            <input type="date" class="form-control" id="leaving_date" name="leaving_date">
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="is_active" name="status" checked 
                       style="width: 40px; height: 20px;">
                <label class="form-check-label ms-2" for="is_active">Active</label>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Create Employee</button>
    </div>
</form>

@include('backend.employee.ajax.partials.quick_add')

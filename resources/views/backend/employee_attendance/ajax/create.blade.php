<form action="{{ route('attendance.store') }}" method="POST" id="create-attendance-form">
    @csrf
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="attendance_date" class="form-label">Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" id="attendance_date" name="attendance_date" required>
        </div>

        <div class="col-md-6 mb-3">
            <label for="employee_id" class="form-label">Employee <span class="text-danger">*</span></label>
            <div class="input-group att-field-group">
                <select name="employee_id" id="employee_id" class="form-control" required>
                    <option value="">-- Select Employee --</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->phone }})</option>
                    @endforeach
                </select>
                @can('create_employees')
                    <button type="button" class="btn btn-outline-primary btn-att-add"
                        onclick="openAttEmployeeModal()" title="Add New Employee">
                        <i class="fas fa-plus"></i>
                    </button>
                @endcan
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <label for="check_in_time" class="form-label">Check In Time</label>
            <input type="time" class="form-control" id="check_in_time" name="check_in_time">
        </div>

        <div class="col-md-6 mb-3">
            <label for="check_out_time" class="form-label">Check Out Time</label>
            <input type="time" class="form-control" id="check_out_time" name="check_out_time">
        </div>

        <div class="col-md-12 mb-3">
            <label for="note" class="form-label">Note</label>
            <textarea class="form-control" id="note" name="note" rows="2" placeholder="Optional note"></textarea>
        </div>

        <div class="col-md-6 mb-3">
            <label class="form-label d-block">Status</label>
            <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" role="switch" 
                       id="status" name="status" checked 
                       style="width: 40px; height: 20px;">
                <label class="form-check-label ms-2" for="status">Present</label>
            </div>
        </div>
    </div>

    <div class="modal-footer bg-light px-0 pb-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Attendance</button>
    </div>
</form>
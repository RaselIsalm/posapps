<div class="modal-header bg-primary text-white border-0">
    <h5 class="modal-title fw-bold">
        <i class="fas fa-edit me-2"></i>Edit Courier Gateway
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form action="{{ route('courier_settings.update', $courier->id) }}" method="POST">
    @csrf
    @method('PUT')
    <div class="modal-body">

        {{-- Provider Name --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Provider Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="name" value="{{ $courier->name }}" required>
        </div>

        {{-- Phone Number --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="phone" value="{{ $courier->phone }}" required>
        </div>

        {{-- Base URL --}}
        <div class="mb-3">
            <label class="form-label fw-bold">
                Base URL <span class="text-muted">(Optional)</span>
            </label>
            <input type="url" class="form-control" name="base_url" value="{{ $courier->api_config['base_url'] ?? '' }}" placeholder="https://example.com/api/v1">
        </div>

        <hr>

        {{-- Dynamic Parameters --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
            <label class="form-label fw-bold mb-0">Courier Configuration Parameters</label>
            <button type="button" class="btn btn-success btn-sm" id="addParamBtn">
                <i class="fas fa-plus me-1"></i> Add Row
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-sm" id="paramsTable">
                <thead class="table-light">
                    <tr>
                        <th width="40">#</th>
                        <th>Parameter</th>
                        <th>Value</th>
                        <th width="50">Action</th>
                    </tr>
                </thead>
                <tbody id="paramsBody">
                    @php
                        $params = $courier->api_config['params'] ?? [];
                    @endphp

                    @forelse($params as $index => $param)
                        <tr>
                            <td class="text-center align-middle row-num">{{ $index + 1 }}</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="params[{{ $index }}][key]" value="{{ $param['key'] ?? '' }}" placeholder="Parameter">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="params[{{ $index }}][value]" value="{{ $param['value'] ?? '' }}" placeholder="Value">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center align-middle row-num">1</td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="params[0][key]" placeholder="e.g., api_key">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="params[0][value]" placeholder="Value">
                            </td>
                            <td class="text-center align-middle">
                                <button type="button" class="btn btn-danger btn-sm remove-row">
                                    <i class="fas fa-times"></i>
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <hr>

        {{-- Address --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Address <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="address" value="{{ $courier->address }}" required>
        </div>

        <hr>

        {{-- Active Toggle --}}
        <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #e9ecef;">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ $courier->is_active ? 'checked' : '' }}>
                <label class="form-check-label fw-bold text-success">Set as Active Courier</label>
            </div>
            <small class="text-muted">Activating this will enable this courier for use.</small>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Update Courier
        </button>
    </div>
</form>

<script>
    (function() {
        var rowCount = {{ max(1, count($params ?? [])) }};

        $('#addParamBtn').off('click').on('click', function() {
            const tbody = document.getElementById('paramsBody');
            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="text-center align-middle row-num">${rowCount + 1}</td>
            <td><input type="text" class="form-control form-control-sm" name="params[${rowCount}][key]" placeholder="Parameter"></td>
            <td><input type="text" class="form-control form-control-sm" name="params[${rowCount}][value]" placeholder="Value"></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-danger btn-sm remove-row"><i class="fas fa-times"></i></button>
            </td>
        `;
            tbody.appendChild(row);
            rowCount++;
            updateRowNumbers();
        });

        $('#paramsBody').off('click').on('click', '.remove-row', function(e) {
            $(this).closest('tr').remove();
            updateRowNumbers();
        });

        function updateRowNumbers() {
            document.querySelectorAll('#paramsBody .row-num').forEach((el, i) => {
                el.textContent = i + 1;
            });
        }
    })();
</script>

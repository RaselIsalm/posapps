<div class="modal-header bg-primary text-white border-0">
    <h5 class="modal-title fw-bold">
        <i class="fas fa-sms me-2"></i>Add New SMS Gateway
    </h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
</div>

<form action="{{ route('sms_settings.store') }}" method="POST">
    @csrf
    <div class="modal-body">

        {{-- Provider Name --}}
        <div class="mb-3">
            <label class="form-label fw-bold">Provider Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="provider_name" placeholder="e.g., BD Bulk SMS, Twilio"
                required>
        </div>

        {{-- API URL --}}
        <div class="mb-3">
            <label class="form-label fw-bold">API URL <span class="text-danger">*</span></label>
            <input type="url" class="form-control" name="api_url" placeholder="https://bulksmsbd.net/api/smsapi"
                required>
        </div>

        {{-- Balance API URL --}}
        <div class="mb-3">
            <label class="form-label fw-bold">
                GET Balance API URL <span class="text-muted">(Optional)</span>
            </label>
            <input type="url" class="form-control" name="balance_api_url"
                placeholder="https://bulksmsbd.net/api/getBalanceApi">
            <small class="text-muted">Used to check SMS credit balance.</small>
        </div>

        <hr>

        {{-- Dynamic Parameters --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
            <label class="form-label fw-bold mb-0">SMS Configuration Parameters</label>
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
                    {{-- Default একটা row --}}
                    <tr>
                        <td class="text-center align-middle row-num">1</td>
                        <td>
                            <input type="text" class="form-control form-control-sm" name="params[0][key]"
                                placeholder="e.g., api_key">
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm" name="params[0][value]"
                                placeholder="Value">
                        </td>
                        <td class="text-center align-middle">
                            <button type="button" class="btn btn-danger btn-sm remove-row">
                                <i class="fas fa-times"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr>

        {{-- Active Toggle --}}
        <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #e9ecef;">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_active" value="1" checked>
                <label class="form-check-label fw-bold text-success">Set as Active Gateway</label>
            </div>
            <small class="text-muted">Activating this will automatically deactivate other gateways.</small>
        </div>
    </div>

    <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Save Gateway
        </button>
    </div>
</form>

<script>
    (function() {
        var rowCount = 1;

        $('#addParamBtn').off('click').on('click', function() {
            const tbody = document.getElementById('paramsBody');
            const row = document.createElement('tr');
            row.innerHTML = `
            <td class="text-center align-middle row-num">${rowCount + 1}</td>
            <td><input type="text" class="form-control form-control-sm" name="params[${rowCount}][key]" placeholder="e.g., sender_id"></td>
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

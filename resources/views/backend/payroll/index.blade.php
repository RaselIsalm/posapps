@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Payrolls
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
        .table thead th {
            background-color: #f8f9fa;
            color: #5b6e88;
            font-weight: 600;
            font-size: 13px;
            padding: 12px;
            border-bottom: 1px solid #eef1f6;
            text-transform: uppercase;
        }

        .table tbody td {
            vertical-align: middle;
            font-size: 14px;
            padding: 12px;
            border-bottom: 1px solid #f2f4f8;
            color: #333;
        }

        div.dataTables_wrapper div.dataTables_filter input {
            border: 1px solid #e0e6ed;
            border-radius: 6px;
            padding: 6px 12px;
            height: 38px;
            margin-left: 0;
            width: 200px;
        }

        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
            margin-bottom: -10px;
        }

        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 24px
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        /* Update selector according to table ID */
        #payroll-table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        #payroll-table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        .page-body {
            min-height: auto !important;
        }

        @media (max-width: 767.98px) {
            .dataTables_wrapper .row>.col-md-6.d-flex {
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 10px !important;
                margin-top: 10px;
            }

            div.dataTables_wrapper div.dataTables_filter {
                text-align: left;
                margin: 0;
                width: 100%;
            }

            div.dataTables_wrapper div.dataTables_filter label {
                display: block;
                width: 100%;
            }

            div.dataTables_wrapper div.dataTables_filter input {
                width: 100%;
                margin-left: 0;
                display: block;
            }

            .dt-buttons {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                gap: 5px;
                margin-bottom: 10px;
            }

            .dt-buttons .btn {
                flex: 1;
                margin: 0 !important;
                padding: 8px 4px;
                font-size: 13px;
            }

            .table thead th,
            .table tbody td {
                white-space: nowrap;
            }

            div.dataTables_wrapper div.dataTables_length {
                text-align: left;
            }

            div.dataTables_wrapper div.dataTables_paginate ul.pagination {
                justify-content: center !important;
                flex-wrap: wrap;
                margin-top: 15px;
            }

            div.dataTables_wrapper div.dataTables_paginate ul.pagination .page-link {
                white-space: nowrap !important;
                padding: 8px 12px;
            }
        }

        /* Hide theme-injected pseudo-element arrows globally */
        .dataTables_paginate .pagination .page-item::before,
        .dataTables_paginate .pagination .page-item::after,
        .dataTables_paginate .pagination .page-link::before,
        .dataTables_paginate .pagination .page-link::after {
            display: none !important;
            content: none !important;
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Payrolls
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Payrolls</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 d-flex justify-content-between align-items-center">
                            <h4>All Payroll Records</h4>
                            @can('create_payrolls')
                                <button type="button" class="btn btn-primary" onclick="openCreateModal()">
                                    <i class="fas fa-plus me-1"></i> Add New Payroll
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover table-striped w-100']) }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Payroll Modal --}}
    <div class="modal fade" id="payrollModal" tabindex="-1" aria-labelledby="payrollModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-money-check-alt me-2"></i><span id="modalTitle">Payroll Form</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modal-form-body">
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Payroll Record?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this record?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="confirmDeleteBtn">
                            <i class="fas fa-trash-alt me-1"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.2.2/js/buttons.print.min.js"></script>

    {{ $dataTable->scripts() }}

    <script>
        $('#payrolls').addClass('active');
        $('#payrolls-submenu').addClass('d-block');
        $('.payroll-menu').addClass('active');

        function openCreateModal() {
            // Remove previous quick-add modals moved to body (avoid duplicates)
            $('#payAddEmployeeModal, #payAddAccountModal').remove();

            $('#modalTitle').text(@json(__('Add New Payroll')));
            $('#payrollModal').modal('show');

            $('#modal-form-body').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>');

            $.ajax({
                url: "{{ route('payrolls.create') }}",
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    $('#modal-form-body').html(
                        '<div class="text-danger text-center">Something went wrong!</div>');
                }
            });
        }

        // Open Edit Modal
        function editPayroll(id) {
            // Remove previous quick-add modals moved to body (avoid duplicates)
            $('#payAddEmployeeModal, #payAddAccountModal').remove();

            $('#modalTitle').text(@json(__('Edit Payroll')));
            $('#payrollModal').modal('show');

            $('#modal-form-body').html(
                '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>');

            var url = "{{ route('payrolls.edit', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(data) {
                    $('#modal-form-body').html(data);
                },
                error: function() {
                    $('#modal-form-body').html(
                        '<div class="text-danger text-center">Something went wrong!</div>');
                }
            });
        }

        $(document).on('change', '#payment_method', function() {
            checkBalance();
        });

        $(document).on('input', '.calc-input', function() {
            calculateNet();
        });

        function calculateNet() {
            let basic = parseFloat($('#basic_salary').val()) || 0;
            let allowance = parseFloat($('#total_allowance').val()) || 0;
            let deduction = parseFloat($('#total_deduction').val()) || 0;
            let net = (basic + allowance) - deduction;
            $('#net_salary').val(net.toFixed(2));
            checkBalance();
        }

        $(document).on('change', 'select[name="employee_id"], select[name="salary_month"], select[name="salary_year"]', function() {
            let employee_id = $('select[name="employee_id"]').val();
            let month = $('select[name="salary_month"]').val();
            let year = $('select[name="salary_year"]').val();

            if(employee_id && month && year) {
                $.ajax({
                    url: "{{ route('payrolls.getEmployeeData') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        employee_id: employee_id,
                        month: month,
                        year: year
                    },
                    success: function(response) {
                        if(response.success) {
                            $('#basic_salary').val(response.basic_salary);
                            $('#total_deduction').val(response.total_deduction);
                            // Highlight fields to show they were updated
                            $('#basic_salary, #total_deduction').css('background-color', '#e8f5e9');
                            setTimeout(() => {
                                $('#basic_salary, #total_deduction').css('background-color', '');
                            }, 1500);

                            // Optional: show a small info text about present/absent days
                            let infoText = `<small class="text-info d-block mt-1"><i class="fas fa-info-circle"></i> Attendance info: ${response.present_days} present, ${response.absent_days} absent (salary is full base; no auto deduction).</small>`;
                            $('#basic_salary').parent().find('small.text-info').remove();
                            $('#basic_salary').parent().append(infoText);

                            calculateNet();
                        }
                    }
                });
            }
        });

        $(document).on('change', '#status', function() {
            if ($(this).val() === 'paid') {
                checkBalance();
            } else {
                $('#balance_alert').hide();
                $('#balance_ok').hide();
            }
        });

        function checkBalance() {
            let status = $('#status').val();
            let selectedOption = $('#payment_method option:selected');
            let balance = parseFloat(selectedOption.data('balance')) || 0;
            let netSalary = parseFloat($('#net_salary').val()) || 0;
            let accountName = selectedOption.text();

            if (status !== 'paid' || !$('#payment_method').val()) {
                $('#balance_alert').hide();
                $('#balance_ok').hide();
                return;
            }

            if (netSalary > 0 && balance < netSalary) {
                $('#balance_alert_msg').text(
                    @json(__('Insufficient balance in :name. Available :available, required :required.')).replace(':name', accountName).replace(':available', '{{ \App\Helpers\CurrencyHelper::getSymbol() }}' + balance.toFixed(2)).replace(':required', '{{ \App\Helpers\CurrencyHelper::getSymbol() }}' + netSalary.toFixed(2))
                );
                $('#balance_alert').show();
                $('#balance_ok').hide();
                $('#submit_payroll_btn').prop('disabled', true);
            } else if (netSalary > 0) {
                $('#balance_ok_msg').text(
                    @json(__('Sufficient balance in :name. Balance :balance.')).replace(':name', accountName).replace(':balance', '{{ \App\Helpers\CurrencyHelper::getSymbol() }}' + balance.toFixed(2))
                );
                $('#balance_ok').show();
                $('#balance_alert').hide();
                $('#submit_payroll_btn').prop('disabled', false);
            }
        }

        // MODAL CONFIRMATION FOR DELETE
        let formToSubmit = null;

        $(document).on('click', '.delete-btn', function(e) {
            e.preventDefault();
            formToSubmit = $(this).closest('form');
            $('#deleteConfirmModal').modal('show');
        });

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (formToSubmit) {
                $(this).html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);
                formToSubmit.submit();
            }
        });
    </script>
@endpush

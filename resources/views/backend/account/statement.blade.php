@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Account Statement
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <style>
        .balance-cell {
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
        }

        .credit-text {
            color: #198754;
        }

        .debit-text {
            color: #dc3545;
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

        .dataTables_wrapper .dt-buttons {
            position: relative;
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        /* Mobile Responsive Fixes */
        @media (max-width: 767.98px) {
            .dataTables_wrapper .row > .col-md-6.d-flex {
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
                top: 0 !important;
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
            Account Statement
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">Account Statement</li>
    @endcomponent
    <div class="container-fluid">

        <div class="d-flex justify-content-end align-items-center mb-4">
            <button type="button" class="btn btn-primary shadow-sm fw-bold" data-bs-toggle="modal"
                data-bs-target="#filterModal">
                <i class="fas fa-filter me-2"></i> Change Filter
            </button>
        </div>

        @if (request()->filled('account_id'))
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="m-0 fw-bold text-dark">
                            Statement for:
                            <span style="color: #218c74;">
                                {{ $account ? $account->name : 'All Accounts' }}
                            </span>
                        </h4>
                        @if ($account)
                            <div class="mt-2">
                                <span class="badge bg-secondary">{{ ucfirst($account->type) }}</span>
                                @if ($account->account_number)
                                    <small class="text-muted ms-2">({{ $account->account_number }})</small>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="table-responsive">
                        {{ $dataTable->table(['class' => 'table table-hover w-100']) }}
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5 bg-white rounded shadow-sm">
                <img src="https://cdn-icons-png.flaticon.com/512/2921/2921222.png" alt="Filter"
                    style="width: 80px; opacity: 0.6; margin-bottom: 20px;">
                <h4 class="text-muted">No Statement Loaded</h4>
                <p class="text-muted small">Please select an account and date range to view the ledger.</p>
                <button class="btn btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#filterModal">
                    Select Account & Date
                </button>
            </div>
        @endif

    </div>

    {{-- Filter Statement Modal --}}
    <div class="modal fade" id="filterModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-filter me-2"></i>Account Statement</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('accounts.statement') }}" method="GET">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Select Account <span
                                    class="text-danger">*</span></label>
                            <div class="stmt-field-group">
                                <select name="account_id" id="stmt_account_id" class="form-select form-select-lg" required>
                                    <option value="">Choose Account</option>
                                    @foreach ($accounts as $acc)
                                        <option value="{{ $acc->id }}"
                                            {{ request('account_id') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->name }}@if ($acc->account_number) ({{ ucfirst($acc->account_number) }})@endif
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_accounts')
                                    <button type="button" class="btn btn-outline-primary btn-stmt-add"
                                        onclick="openStmtAccountModal()" title="Add New Account">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold small text-muted">Transaction Type</label>
                                <select name="type" class="form-select">
                                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Transactions
                                        (Debit & Credit)</option>
                                    <option value="credit" {{ request('type') == 'credit' ? 'selected' : '' }}>Credit Only
                                        (Income/Deposit)</option>
                                    <option value="debit" {{ request('type') == 'debit' ? 'selected' : '' }}>Debit Only
                                        (Expense/Payment)</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">From Date</label>
                                <input type="date" name="start_date" class="form-control"
                                    value="{{ request('start_date', date('Y-m-01')) }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-muted">To Date</label>
                                <input type="date" name="end_date" class="form-control"
                                    value="{{ request('end_date', date('Y-m-d')) }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary fw-bold px-4 w-100">Load Statement</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('backend.account.partials.quick_add_statement_account', [
        'accountSelectId' => 'stmt_account_id',
        'parentModalId' => 'filterModal',
    ])

@endsection

@push('js')
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>

    {{ $dataTable->scripts(attributes: ['type' => 'module']) }}

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (!request()->has('account_id'))
                var filterModal = new bootstrap.Modal(document.getElementById('filterModal'), {
                    keyboard: false
                });
                filterModal.show();
            @endif

            // Keep Sidebar Active
            $('#accounts-menu').addClass('active');
            $('#accounts-menu-submenu').addClass('d-block');
            
            // Add active class to the specific submenu item based on the current URL route
            $('.sidebar-submenu a[href="{{ route('accounts.statement') }}"]').parent().addClass('active');
        });
    </script>
@endpush

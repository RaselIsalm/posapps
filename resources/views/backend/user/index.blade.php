@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - User Management
@endsection

@push('css')
    {{-- DataTables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
        .table thead th {
            background-color: #f8f9fa;
            color: #5b626b;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            border-bottom: 1px solid #ebedf2;
            padding: 15px;
            vertical-align: middle;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            color: #3b3f5c;
            font-size: 14px;
            border-bottom: 1px solid #ebedf2;
        }

        /* Processing Loader Fix */

        /* Dropdown Fix */
        .dropdown-custom {
            position: static;
        }

        .dropdown-menu-custom {
            min-width: 120px;
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

        @keyframes user-row-flash {
            0% { background-color: rgba(48, 142, 135, 0.28); }
            100% { background-color: transparent; }
        }

        #user-table tbody tr.user-row-flash > td {
            animation: user-row-flash 1.6s ease 1;
        }

        #user-table tbody tr.user-row-marked > td:first-child {
            box-shadow: inset 3px 0 0 var(--primary-color, #308e87);
        }

        .user-status-active,
        .user-status-inactive {
            font-weight: 600;
            font-size: 12px;
            border-radius: 999px;
            padding: 5px 10px;
        }

        .user-status-active {
            background: rgba(48, 142, 135, 0.12);
            color: #308e87;
        }

        .user-status-inactive {
            background: #f1f3f5;
            color: #6c757d;
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
            {{ trans('Manage Users') }} 
        @endslot
        @slot('subtitle')
            Manage and configure your system's fundamental preferences and parameters.
        @endslot
        <li class="breadcrumb-item active">{{ trans('Manage Users') }}</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-white py-4 border-0">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                        <h4 class="m-0 fw-bold text-dark">All Users</h4>
                        <div class="d-flex flex-wrap gap-2">
                            @if (auth()->user()?->hasRole('Super-Admin'))
                                <a href="{{ route('users.deletionRequests.index') }}" class="btn btn-outline-danger">Account deletion requests</a>
                            @endif
                            <a href="{{ route('users.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus me-1"></i> Create User
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="table-responsive">
                        {{-- DataTable Render --}}
                        {{ $dataTable->table(['class' => 'table table-hover w-100']) }}
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
                    <div id="removeUserIcon" class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="removeUserTitle"></h5>
                    <p class="text-muted mb-4" id="removeUserText"></p>
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
    {{-- Required Scripts for DataTables --}}
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

    {{-- Inject DataTable Scripts --}}
    {{ $dataTable->scripts() }}

    <script>
        // Matches the site-wide floating-alert style used for flash messages
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        (function () {
            var params = new URLSearchParams(window.location.search);
            var highlight = params.get('highlight');
            if (!highlight || !/^\d+$/.test(highlight)) {
                return;
            }

            var flashed = false;
            function markRow(animate) {
                var row = document.getElementById('user-row-' + highlight);
                if (!row) {
                    return;
                }
                row.classList.add('user-row-marked');
                if (!animate || flashed) {
                    return;
                }
                flashed = true;
                row.classList.add('user-row-flash');
                row.scrollIntoView({ block: 'center', behavior: 'smooth' });
                setTimeout(function () {
                    var current = document.getElementById('user-row-' + highlight);
                    if (current) {
                        current.classList.remove('user-row-flash');
                    }
                }, 1700);

                var table = $('#user-table').DataTable();
                var ajax = table.settings()[0].ajax;
                if (ajax && ajax.url) {
                    var nextUrl = new URL(ajax.url, window.location.origin);
                    nextUrl.searchParams.delete('highlight');
                    ajax.url = nextUrl.toString();
                }
                history.replaceState(null, '', window.location.pathname);
            }

            $('#user-table').on('draw.dt', function () {
                markRow(!flashed);
            });
            setTimeout(function () {
                markRow(true);
            }, 400);
        })();

        // Menu Active
        $('#settings').addClass('active');
        $('#settings-submenu').addClass('d-block');
        $('.manage-users-menu').addClass('active');

        // HANDLE AJAX DELETE / DEACTIVATE / ACTIVATE
        let deleteUserUrl = null;
        let deleteUserMethod = 'DELETE';

        var removeModalBusy = false;

        function showRemoveModal() {
            var modalEl = document.getElementById('deleteConfirmModal');
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        }

        function setRemoveModal(mode, title, message, confirmLabel) {
            var icon = $('#removeUserIcon');
            var btn = $('#confirmDeleteBtn');
            $('#removeUserTitle').text(title);
            $('#removeUserText').text(message);
            btn.prop('disabled', false);

            if (mode === 'deactivate' || mode === 'activate') {
                icon.css('background-color', 'rgba(48, 142, 135, 0.12)');
                icon.html('<i class="fas ' + (mode === 'activate' ? 'fa-user-check' : 'fa-user-slash') + ' fs-3" style="color:#308e87;"></i>');
                btn.removeClass('btn-danger').addClass('btn-primary').html('<i class="fas fa-check me-1"></i> ' + confirmLabel);
            } else {
                icon.css('background-color', 'rgba(220, 53, 69, 0.1)');
                icon.html('<i class="fas fa-trash-alt text-danger fs-3"></i>');
                btn.removeClass('btn-primary').addClass('btn-danger').html('<i class="fas fa-trash-alt me-1"></i> ' + confirmLabel);
            }
        }

        $(document).on('click', '.user-remove-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (removeModalBusy) {
                return;
            }
            removeModalBusy = true;
            var $item = $(this);
            var previewUrl = $item.attr('data-preview');
            deleteUserUrl = $item.attr('data-url');
            deleteUserMethod = 'DELETE';

            $.get(previewUrl)
                .done(function(res) {
                    setRemoveModal(res.mode, res.title, res.message, res.confirm);
                    showRemoveModal();
                })
                .fail(function(xhr) {
                    var errorMsg = @json(__('Could not check this user.'));
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    }
                    showFloatingAlert('danger', errorMsg);
                    deleteUserUrl = null;
                })
                .always(function() {
                    removeModalBusy = false;
                });
        });

        $(document).on('click', '.activate-btn', function(e) {
            e.preventDefault();
            deleteUserUrl = $(this).data('url');
            deleteUserMethod = 'POST';
            setRemoveModal('activate', @json(__('Activate user?')), @json(__('This user will be able to log in again. Their past records stay as they are.')), 'Activate');
            showRemoveModal();
        });

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (!deleteUserUrl) {
                return;
            }

            let btn = $(this);
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Working...').prop('disabled', true);

            $.ajax({
                url: deleteUserUrl,
                type: deleteUserMethod,
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#deleteConfirmModal').modal('hide');
                    $('#user-table').DataTable().ajax.reload();
                    showFloatingAlert('success', res.message || @json(__('User deleted successfully.')));
                },
                error: function(xhr) {
                    $('#deleteConfirmModal').modal('hide');
                    let errorMsg = @json(__('Failed to delete user.'));
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFloatingAlert('danger', errorMsg);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                    deleteUserUrl = null;
                }
            });
        });
    </script>
@endpush

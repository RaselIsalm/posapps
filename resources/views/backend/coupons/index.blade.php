@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Coupons
@endsection

@push('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.2/css/buttons.bootstrap5.min.css">
    <style>
        div.dataTables_wrapper div.dataTables_length select {
            padding-right: 28px
        }

        .dataTables_wrapper .dt-buttons {
            position: relative;
            top: -16px;
        }

        .dataTables_wrapper .dataTables_paginate {
            border: 0px !important;
        }

        @media (max-width: 767.98px) {
            /* Stack Search and Buttons on Mobile */
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
            div.dt-buttons {
                display: flex;
                flex-wrap: nowrap;
                width: 100%;
                gap: 5px;
                margin-bottom: 10px;
                float: none;
                margin-left: 0;
            }
            .dataTables_wrapper .dt-buttons {
                position: static !important;
                top: auto !important;
            }
            div.dt-buttons .btn {
                flex: 1;
                margin: 0 !important;
                padding: 8px 4px;
                font-size: 13px;
            }
            .table-responsive {
                overflow-x: auto !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch;
                border: 0;
            }
            .table thead th, .table tbody td {
                white-space: nowrap; /* Prevent squashing columns */
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
            /* Hide theme-injected pseudo-element arrows */
            .dataTables_paginate .pagination .page-item::before,
            .dataTables_paginate .pagination .page-item::after,
            .dataTables_paginate .pagination .page-link::before,
            .dataTables_paginate .pagination .page-link::after {
                display: none !important;
                content: none !important;
            }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Coupons
        @endslot
        @slot('subtitle')
            Create and manage discount coupons
        @endslot
        <li class="breadcrumb-item active">Coupons</li>
    @endcomponent

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                        <h5 class="mb-0 fw-bold">All Coupons</h5>
                        <button class="btn btn-primary btn-sm" onclick="openCreateModal()">
                            <i class="fas fa-plus me-1"></i> Add New Coupon
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            {!! $dataTable->table(['class' => 'table table-hover align-middle table-striped']) !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Create and Edit Modal --}}
    <div class="modal fade" id="couponModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-ticket-alt me-2"></i><span id="modalTitle">Loading...</span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="modalContent">
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
                    <h5 class="fw-bold mb-2">Delete Coupon?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently
                        delete this coupon?</p>
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

    <script>
        // Override default pagination language to remove arrows
        $.extend(true, $.fn.dataTable.defaults, {
            language: {
                paginate: {
                    previous: 'Previous',
                    next: 'Next'
                }
            }
        });
    </script>
    {!! $dataTable->scripts() !!}

    <script>
        let couponModal;

        document.addEventListener('DOMContentLoaded', function() {
            const modalEl = document.getElementById('couponModal');
            if (modalEl) {
                if (typeof bootstrap !== 'undefined') {
                    couponModal = new bootstrap.Modal(modalEl);
                }
            }
        });

        function openCreateModal() {
            document.getElementById('modalTitle').innerText = @json(__('Add New Coupon'));
            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                </div>
            `;

            if (couponModal) couponModal.show();

            $.ajax({
                url: "{{ route('coupons.create') }}",
                type: 'GET',
                success: function(response) {
                    $('#modalContent').html(response);
                },
                error: function(xhr) {
                    $('#modalContent').html('<p class="text-danger text-center">Error loading form.</p>');
                    console.error(xhr);
                }
            });
        }

        // Open View Modal
        window.viewCoupon = function(id) {
            document.getElementById('modalTitle').innerText = @json(__('Coupon Details'));
            document.getElementById('modalContent').innerHTML = `
                    <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                    </div>
                `;

            if (couponModal) couponModal.show();

            let url = "{{ route('coupons.show', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#modalContent').html(response);
                },
                error: function(xhr) {
                    $('#modalContent').html(
                    '<p class="text-danger text-center">Error loading details.</p>');
                    console.error(xhr);
                }
            });
        }

        // Open Edit Modal
        window.editCoupon = function(id) {
            document.getElementById('modalTitle').innerText = @json(__('Edit Coupon'));

            document.getElementById('modalContent').innerHTML = `
                <div class="text-center py-5">
                        <p class="mt-2 text-muted mb-0">Loading...</p>
                </div>
            `;

            if (couponModal) couponModal.show();

            let url = "{{ route('coupons.edit', ':id') }}";
            url = url.replace(':id', id);

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('#modalContent').html(response);

                    let updateUrl = "{{ route('coupons.update', ':id') }}";
                    updateUrl = updateUrl.replace(':id', id);

                    const editForm = document.getElementById('editCouponForm');
                    if (editForm) {
                        editForm.action = updateUrl;
                    }
                },
                error: function(xhr) {
                    $('#modalContent').html(
                        '<p class="text-danger text-center">Error loading data (404/500).</p>');
                    console.error(xhr);
                }
            });
        }

        window.toggleCreateMaxDiscount = function() {
            const type = document.getElementById('create_type').value;
            const maxDiv = document.getElementById('create_max_discount_div');
            const maxInput = document.getElementById('create_max_discount');

            if (type === 'percent') {
                maxDiv.style.display = 'block';
            } else {
                maxDiv.style.display = 'none';
                maxInput.value = '';
            }
        }

        window.generateRandomCode = function() {
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let result = '';
            for (let i = 0; i < 8; i++) {
                result += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            const codeInput = document.getElementById('create_code');
            if (codeInput) codeInput.value = result;
        }

        // Floating Alert Helper
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        // Delete Logic
        let deleteCouponUrl = null;

        window.openDeleteModal = function(url) {
            deleteCouponUrl = url;
            $('#deleteConfirmModal').modal('show');
        };

        $(document).on('click', '#confirmDeleteBtn', function() {
            if (!deleteCouponUrl) {
                return;
            }

            let btn = $(this);
            let originalText = btn.html();
            btn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...').prop('disabled', true);

            $.ajax({
                url: deleteCouponUrl,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    $('#deleteConfirmModal').modal('hide');
                    window.LaravelDataTables?.['coupon-table']?.ajax.reload(null, false);
                    showFloatingAlert('success', res.message || @json(__('Coupon deleted successfully!')));
                },
                error: function(xhr) {
                    $('#deleteConfirmModal').modal('hide');
                    let errorMsg = @json(__('Failed to delete coupon.'));
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    showFloatingAlert('danger', errorMsg);
                },
                complete: function() {
                    btn.html(originalText).prop('disabled', false);
                    deleteCouponUrl = null;
                }
            });
        });
    </script>
@endpush

@extends('backend.layout.main')

@section('title', 'SMS Templates')

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            SMS Templates
        @endslot
        @slot('subtitle')
            Manage and edit your dynamic SMS messages
        @endslot
        <li class="breadcrumb-item"><a href="{{ route('sms_settings.index') }}">SMS Settings</a></li>
        <li class="breadcrumb-item active">Templates</li>
    @endcomponent

    <div class="row">
        <div class="col-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-alt text-muted me-2"></i> All SMS Templates</h5>
                    <button class="btn btn-primary" id="createNewBtn">
                        <i class="fas fa-plus me-1"></i> Add New Template
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Template Name</th>
                                    <th width="45%">Message Content</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($templates as $key => $row)
                                    <tr>
                                        <td>{{ ($templates->currentPage() - 1) * $templates->perPage() + $key + 1 }}</td>
                                        <td class="fw-bold text-primary">
                                            {{ ucwords(str_replace('_', ' ', $row->template_name)) }}</td>
                                        <td><small class="text-muted">{{ $row->message_body }}</small></td>
                                        <td class="text-center">
                                            @if ($row->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="text-center">
                                                <button type="button" class="btn btn-info btn-icon btn-sm previewBtn"
                                                    title="Preview"
                                                    data-name="{{ ucwords(str_replace('_', ' ', $row->template_name)) }}"
                                                    data-body="{{ base64_encode($row->message_body) }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-primary btn-icon btn-sm editBtn ms-1"
                                                    data-url="{{ route('sms_templates.edit', $row->id) }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <form action="{{ route('sms_templates.destroy', $row->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">No templates found. Please
                                            create one.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($templates->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $templates->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="ajaxModal" tabindex="-1" aria-labelledby="ajaxModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" id="modal-content-area">
            </div>
        </div>
    </div>

    {{-- SMS Preview Modal --}}
    <div class="modal fade" id="smsPreviewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-mobile-alt me-2"></i>SMS Preview</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <p class="text-muted small mb-3" id="previewTemplateName"></p>
                    <div class="sms-phone">
                        <div class="sms-phone-notch"></div>
                        <div class="sms-bubble" id="listSmsPreviewText"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">Sample data preview</small>
                        <small class="text-muted" id="listSmsCharCount">0 chars</small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                    <h5 class="fw-bold mb-2">Delete SMS Template?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this template?</p>
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

@push('css')
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

        .table tbody tr {
            cursor: pointer;
            transition: background-color 0.2s ease-in-out;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa !important;
        }

        .sms-phone {
            background: linear-gradient(160deg, #1f2937 0%, #111827 100%);
            border-radius: 22px;
            padding: 18px 14px 20px;
            max-width: 340px;
            margin: 0 auto;
            box-shadow: 0 10px 28px rgba(17, 24, 39, 0.28);
        }

        .sms-phone-notch {
            width: 86px;
            height: 8px;
            border-radius: 999px;
            background: #374151;
            margin: 0 auto 16px;
        }

        .sms-bubble {
            background: #ffffff;
            color: #1f2937;
            border-radius: 16px 16px 16px 4px;
            padding: 12px 14px;
            font-size: 14px;
            line-height: 1.55;
            white-space: pre-wrap;
            word-break: break-word;
            min-height: 72px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }

        @media (max-width: 767.98px) {
            .card-header.d-flex {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 10px;
            }
            
            .table thead th,
            .table tbody td {
                white-space: nowrap;
            }
            
            .card-header .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            var sampleData = {
                '{customer_name}': @json(__('John Doe')),
                '{invoice_no}': 'INV-2026-001',
                '{quantity}': '3',
                '{grand_total}': '1,500.00',
                '{paid}': '1,000.00',
                '{due_amount}': '500.00',
                '{discount}': '50.00',
                '{tax}': '75.00',
                '{previous_due}': '200.00',
                '{invoice_link}': 'https://example.com/inv/001'
            };

            function fillSmsPreview(raw) {
                var preview = raw || '';
                Object.keys(sampleData).forEach(function(tag) {
                    preview = preview.split(tag).join(sampleData[tag]);
                });
                return preview;
            }

            // Create Button Click
            $('#createNewBtn').on('click', function(e) {
                e.preventDefault();
                $('#modal-content-area').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
                );
                $('#ajaxModal').modal('show');

                $.ajax({
                    url: "{{ route('sms_templates.create') }}",
                    type: "GET",
                    success: function(response) {
                        $('#modal-content-area').html(response);
                    }
                });
            });

            // Edit Button Click
            $('.editBtn').on('click', function(e) {
                e.preventDefault();
                let url = $(this).data('url');

                $('#modal-content-area').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
                );
                $('#ajaxModal').modal('show');

                $.ajax({
                    url: url,
                    type: "GET",
                    success: function(response) {
                        $('#modal-content-area').html(response);
                    }
                });
            });

            // List Preview Button
            $(document).on('click', '.previewBtn', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var name = $(this).data('name') || @json(__('SMS Template'));
                var encoded = $(this).attr('data-body') || '';
                var body = '';
                try {
                    body = decodeURIComponent(escape(window.atob(encoded)));
                } catch (err) {
                    body = encoded;
                }
                var preview = fillSmsPreview(body);

                $('#previewTemplateName').text(name);
                $('#listSmsPreviewText').text(preview || @json(__('Empty template')));
                $('#listSmsCharCount').text(body.length + ' chars');
                $('#smsPreviewModal').modal('show');
            });

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

        });
    </script>
@endpush

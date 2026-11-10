@extends('backend.layout.main')
@section('title', 'SMS API Settings')

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            SMS Configurations
        @endslot
        @slot('subtitle')
            Manage your SMS Gateways
        @endslot
        <li class="breadcrumb-item active">SMS Settings</li>
    @endcomponent




    <div class="row">
        <div class="col-12">



            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="m-0">All SMS Gateways</h4>
                    <button class="btn btn-primary" id="createNewBtn">
                        <i class="fas fa-plus me-1"></i> Add New Gateway
                    </button>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped w-100">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">#</th>
                                    <th>Provider Name</th>
                                    <th>API URL</th>
                                    <th>API Key</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center pe-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($settings as $key => $row)
                                    <tr>
                                        <td class="ps-4">{{ $settings->firstItem() + $key }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $row->provider_name }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted font-monospace">
                                                {{ Str::limit($row->api_url, 40) }}
                                            </small>
                                        </td>
                                        <td>
                                            <span class="font-monospace text-muted" id="key-display-{{ $row->id }}">
                                                {{ $row->masked_api_key }}
                                            </span>
                                            <button class="btn btn-link btn-sm p-0 ms-1 toggle-key"
                                                data-id="{{ $row->id }}" data-real="{{ $row->api_key }}"
                                                data-masked="{{ $row->masked_api_key }}" title="Show/Hide">
                                                <i class="fas fa-eye text-muted"></i>
                                            </button>
                                        </td>
                                        <td class="text-center">
                                            @if ($row->is_active)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-danger">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <div class="text-center">
                                                <button class="btn btn-success btn-icon btn-sm testBtn"
                                                    data-id="{{ $row->id }}" data-name="{{ $row->provider_name }}"
                                                    title="Send Test SMS">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>

                                                {{-- Edit --}}
                                                <button class="btn btn-primary btn-icon btn-sm ms-1 editBtn"
                                                    data-url="{{ route('sms_settings.edit', $row->id) }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                {{-- Delete --}}
                                                @if (!$row->is_active)
                                                    <form action="{{ route('sms_settings.destroy', $row->id) }}"
                                                        method="POST" style="display:inline;" class="delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn"
                                                            title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-light text-muted ms-1" disabled
                                                        title="Cannot delete active gateway">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                    @if ($row->balance_api_url)
                                                        <button class="btn btn-sm btn-light text-info balanceBtn"
                                                            data-id="{{ $row->id }}"
                                                            data-name="{{ $row->provider_name }}" title="Check Balance">
                                                            <i class="fas fa-wallet"></i>
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-satellite-dish fa-2x mb-3 d-block opacity-25"></i>
                                            No SMS API configured yet. Please add a new API.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if ($settings->hasPages())
                    <div class="card-footer border-0 bg-white pt-3 pb-0">
                        {{ $settings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add/Edit Modal --}}
    <div class="modal fade" id="ajaxModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" id="modal-content-area">
                <div class="text-center py-5">
                </div>
            </div>
        </div>
    </div>

    {{-- Test SMS Modal --}}
    <div class="modal fade" id="testSmsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send Test SMS — <span id="test-gateway-name"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" id="test-phone" class="form-control" placeholder="e.g., 01712345678">
                        <small class="text-muted">Enter the phone number to receive the test SMS.</small>
                    </div>
                    <div id="test-result" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendTestBtn">
                        <i class="fas fa-paper-plane me-1"></i> Send Test SMS
                    </button>
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
                    <h5 class="fw-bold mb-2">Delete SMS Gateway?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this gateway?</p>
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
    <script>
        $(document).ready(function() {

            // Reusable Floating Alert Function
            window.showFloatingAlert = function(type, message) {
                var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
                var $alert = $('<div class="alert alert-' + type + ' floating-alert" style="word-wrap: break-word; word-break: break-word;">' +
                    '<strong>' + label + '</strong> ' + message + '</div>');
                
                $('body').append($alert);
                
                setTimeout(function() {
                    $alert.remove();
                }, 5500); // 5.5s is after the global CSS fadeOut animation finishes (4.5s delay + 0.5s duration)
            };

            let currentTestId = null;
            let formToSubmit = null;

            // ── Delete Modal Confirmation ──
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

            // ── Add New ──
            $('#createNewBtn').on('click', function() {
                loadModal("{{ route('sms_settings.create') }}");
            });

            // ── Edit ──
            $(document).on('click', '.editBtn', function() {
                loadModal($(this).data('url'));
            });

            function loadModal(url) {
                $('#modal-content-area').html(
                    '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">Loading...</p></div>'
                );
                $('#ajaxModal').modal('show');
                $.get(url, function(response) {
                    $('#modal-content-area').html(response);
                }).fail(function() {
                    $('#modal-content-area').html(
                        '<div class="p-4 text-center text-danger"><h5>Error loading form!</h5></div>'
                    );
                });
            }

            // Balance Check
            $(document).on('click', '.balanceBtn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const btn = $(this);

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.get(`/api-setting/sms-settings/balance/${id}`, function(response) {
                    if (response.success) {
                        window.showFloatingAlert('success', name + ' ' + @json(__('Balance:')) + ' ' + response.balance);
                    } else {
                        window.showFloatingAlert('danger', response.message);
                    }
                }).fail(function(xhr) {
                    window.showFloatingAlert('danger', @json(__('Failed to fetch balance.')));
                }).always(function() {
                    btn.prop('disabled', false).html('<i class="fas fa-wallet"></i>');
                });
            });

            // ── API Key Show/Hide ──
            $(document).on('click', '.toggle-key', function() {
                const id = $(this).data('id');
                const real = $(this).data('real');
                const masked = $(this).data('masked');
                const display = $(`#key-display-${id}`);
                const icon = $(this).find('i');

                if (icon.hasClass('fa-eye')) {
                    display.text(real);
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                } else {
                    display.text(masked);
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                }
            });

            // ── Test SMS ──
            $(document).on('click', '.testBtn', function() {
                currentTestId = $(this).data('id');
                $('#test-gateway-name').text($(this).data('name'));
                $('#test-phone').val('');
                $('#test-result').hide().html('');
                $('#testSmsModal').modal('show');
            });

            $('#sendTestBtn').on('click', function() {
                const phone = $('#test-phone').val().trim();
                if (!phone) {
                    $('#test-result').html(
                        '<div class="alert alert-warning">Please enter a phone number.</div>'
                    ).show();
                    return;
                }

                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Sending...');

                $.ajax({
                    url: "{{ route('sms_settings.test', ['id' => '__ID__']) }}".replace('__ID__', currentTestId),
                    // url: `/api-setting/sms-settings/test/${currentTestId}`,
                    type: 'POST',
                    data: {
                        test_phone: phone,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#test-result').html(
                                `<div class="alert alert-success" style="word-wrap: break-word; word-break: break-word;">
                            <i class="fas fa-check-circle me-2"></i> ${response.message}
                        </div>`
                            ).show();
                        } else {
                            $('#test-result').html(
                                `<div class="alert alert-danger" style="word-wrap: break-word; word-break: break-word;">
                            <i class="fas fa-exclamation-circle me-2"></i> ${response.message}
                        </div>`
                            ).show();
                        }
                    },
                    error: function() {
                        $('#test-result').html(
                            '<div class="alert alert-danger">Something went wrong. Please try again.</div>'
                        ).show();
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fas fa-paper-plane me-1"></i> Send Test SMS');
                    }
                });
            });
        });
    </script>
@endpush

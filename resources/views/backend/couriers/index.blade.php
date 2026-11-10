@extends('backend.layout.main')
@section('title', 'Courier Settings')

@section('content')

    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Courier Settings
        @endslot
        @slot('subtitle')
            Manage your Couriers
        @endslot
        <li class="breadcrumb-item active">Courier Settings</li>
    @endcomponent

    <div class="row">
        <div class="col-12">

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-truck text-muted me-2"></i> Configured Couriers</h5>
                    <button class="btn btn-primary" id="toggleFormBtn">
                        <i class="fas fa-plus me-1"></i> Add Courier
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone Number</th>
                                    <th>Address</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($couriers as $key => $row)
                                    <tr>
                                        <td class="ps-4">{{ $couriers->firstItem() + $key }}</td>
                                        <td>
                                            <span class="fw-bold">{{ $row->name }}</span>
                                        </td>
                                        <td>{{ $row->phone }}</td>
                                        <td>{{ $row->address }}</td>
                                        <td class="text-center">
                                            @if ($row->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="text-center">
                                                <button class="btn btn-primary btn-icon btn-sm editCourierBtn"
                                                    data-id="{{ $row->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                
                                                <form action="{{ route('courier_settings.destroy', $row->id) }}" method="POST" style="display:inline;" class="delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-danger btn-icon btn-sm ms-1 delete-btn">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>

                                                @if($row->code === 'steadfast')
                                                    <button class="btn btn-info btn-icon btn-sm ms-1 balanceBtn text-white"
                                                        data-id="{{ $row->id }}"
                                                        data-name="{{ $row->name }}" title="Check SteadFast Balance">
                                                        <i class="fas fa-wallet"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fas fa-truck fa-2x mb-3 d-block opacity-25"></i>
                                            No Courier configured yet. Please add a new courier.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if ($couriers->hasPages())
                    <div class="card-footer border-0 bg-white pt-3 pb-0">
                        {{ $couriers->links() }}
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

    {{-- Delete Confirmation Modal --}}
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Delete Courier?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to permanently delete this courier?</p>
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

        let formToSubmit = null;

        // Delete Modal Confirmation
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

        // Open Modal for Add
        $('#toggleFormBtn').on('click', function() {
            loadModal("{{ route('courier_settings.create') }}");
        });

        // Edit Courier
        $(document).on('click', '.editCourierBtn', function() {
            const id = $(this).data('id');
            loadModal("{{ url('api-setting/courier-settings/edit') }}/" + id);
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

        // Reusable Floating Alert Function
        window.showFloatingAlert = function(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert" style="word-wrap: break-word; word-break: break-word;">' +
                '<strong>' + label + '</strong> ' + message + '</div>');
            
            $('body').append($alert);
            
            setTimeout(function() {
                $alert.remove();
            }, 5500); 
        };

        // Check SteadFast Balance
        $(document).on('click', '.balanceBtn', function() {
            const id = $(this).data('id');
            const name = $(this).data('name');
            const btn = $(this);
            
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            btn.prop('disabled', true);

            $.post("{{ route('delivery.steadfast-balance') }}", {
                _token: '{{ csrf_token() }}',
                courier_id: id
            }, function(res) {
                const balanceMsg = name + ' ' + @json(__('Balance:')) + ' ' + parseFloat(res.balance).toLocaleString('en-BD') + ' {{ \App\Helpers\CurrencyHelper::getSymbol() }}';
                window.showFloatingAlert('success', balanceMsg);
            }).fail(function(jqXHR) {
                let msg = @json(__('Failed to fetch balance'));
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    msg = jqXHR.responseJSON.message;
                }
                window.showFloatingAlert('danger', msg);
            }).always(function() {
                btn.html('<i class="fas fa-wallet"></i>').prop('disabled', false);
            });
        });

    });
</script>
@endpush

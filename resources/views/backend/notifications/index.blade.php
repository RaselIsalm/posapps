@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'Your Company Name' }} - Notifications
@endsection

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        @slot('title')
            Notifications
        @endslot
        @slot('subtitle')
            View and manage all your account notifications
        @endslot
        <li class="breadcrumb-item active">Notifications</li>
    @endcomponent

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-bell text-muted me-2"></i> All Notifications</h5>
                    @if (Auth::user()->unreadNotifications->count() > 0)
                        <button type="button" id="mark-all-read-btn" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-check-double me-1"></i> Mark all as read
                        </button>
                    @endif
                </div>
                <div class="card-body p-0">
                    @if ($notifications->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($notifications as $notification)
                                @php
                                    $type = $notification->data['type'] ?? 'primary';
                                    $message = $notification->data['message'] ?? 'New notification';
                                    $actionUrl = $notification->data['action_url'] ?? null;
                                    $hasLink = $actionUrl && $actionUrl !== '#';
                                @endphp
                                <li class="list-group-item notification-row d-flex align-items-start gap-3 py-3 {{ $notification->read_at ? '' : 'bg-light-primary' }}"
                                    data-id="{{ $notification->id }}"
                                    data-url="{{ $hasLink ? $actionUrl : '' }}"
                                    style="{{ $hasLink ? 'cursor: pointer;' : '' }}">
                                    <span class="activity-dot-{{ $type }} mt-2 flex-shrink-0"></span>
                                    <div class="flex-grow-1">
                                        <p class="mb-1 {{ $notification->read_at ? 'text-dark' : 'fw-bold text-dark' }}">{{ $message }}</p>
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                    </div>
                                    @if (! $notification->read_at)
                                        <span class="badge bg-primary rounded-pill align-self-center">New</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-bell-slash fa-2x text-muted mb-3 d-block"></i>
                            <p class="mb-0 text-muted">No notifications yet</p>
                        </div>
                    @endif
                </div>
                @if ($notifications->hasPages())
                    <div class="card-footer bg-white border-top">
                        {{ $notifications->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            $('.notification-row').on('click', function() {
                const id = $(this).data('id');
                const url = $(this).data('url');

                $.ajax({
                    url: `/notifications/mark-read/${id}`,
                    method: 'POST',
                    complete: function() {
                        if (url) {
                            window.location.href = url;
                        } else {
                            window.location.reload();
                        }
                    }
                });
            });

            $('#mark-all-read-btn').on('click', function() {
                $.ajax({
                    url: "{{ route('notifications.markAllRead') }}",
                    method: 'POST',
                    success: function() {
                        window.location.reload();
                    }
                });
            });
        });
    </script>
@endpush

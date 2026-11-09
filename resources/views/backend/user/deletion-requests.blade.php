@extends('backend.layout.main')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <h4 class="mb-1">Account deletion requests</h4>
            <p class="text-muted mb-0">Only verified requests are shown. Approval removes account profile data and de-identifies staff details in retained business records.</p>
        </div>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Back to users</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>Account</th><th>Verified</th><th>Requested</th><th class="text-end">Review</th></tr></thead>
                <tbody>
                @forelse ($requests as $deletionRequest)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $deletionRequest->user?->name ?? 'Account no longer exists' }}</div>
                            <div class="small text-muted">{{ $deletionRequest->email }}</div>
                        </td>
                        <td>{{ $deletionRequest->verified_at?->format('Y-m-d H:i') }}</td>
                        <td>{{ $deletionRequest->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            <div class="d-inline-flex gap-2">
                                <form method="post" action="{{ route('users.deletionRequests.approve', $deletionRequest) }}" onsubmit="return confirm('Remove this user’s profile data and de-identify retained staff records?')">
                                    @csrf
                                    <button class="btn btn-sm btn-danger" type="submit">Approve &amp; erase</button>
                                </form>
                                <form method="post" action="{{ route('users.deletionRequests.reject', $deletionRequest) }}" onsubmit="return confirm('Reject this deletion request?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-secondary" type="submit">Reject</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-5 text-center text-muted">There are no verified pending requests.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="card-footer bg-white">{{ $requests->links() }}</div>
        @endif
    </div>
</div>
@endsection

<div class="row mb-4">
    <div class="col-md-6 mb-2">
        <span class="text-muted d-block small fw-bold">Module / Section</span>
        <h6 class="mb-0"><i class="fas fa-cube text-primary"></i>
            {{ $activity->subject_type ? class_basename($activity->subject_type) : 'System' }}</h6>
    </div>
    <div class="col-md-6 mb-2">
        <span class="text-muted d-block small fw-bold">Action Performed</span>
        <h6 class="mb-0">
            @if ($activity->description == 'created')
                <span class="badge bg-success">Created</span>
            @elseif($activity->description == 'updated')
                <span class="badge bg-warning text-dark">Updated</span>
            @elseif($activity->description == 'deleted')
                <span class="badge bg-danger">Deleted</span>
            @else
                <span class="badge bg-info">{{ ucfirst($activity->description) }}</span>
            @endif
        </h6>
    </div>
    <div class="col-md-6 mb-2">
        <span class="text-muted d-block small fw-bold">Performed By</span>
        <h6 class="mb-0"><i class="fas fa-user text-secondary"></i>
            {{ $activity->causer ? $activity->causer->name : 'System/Guest' }}</h6>
    </div>
    <div class="col-md-6 mb-2">
        <span class="text-muted d-block small fw-bold">Date & Time</span>
        <h6 class="mb-0"><i class="fas fa-clock text-secondary"></i>
            {{ $activity->created_at->format('d M Y, h:i A') }}</h6>
    </div>
</div>

<div class="alert alert-secondary py-2 px-3 mb-4" role="alert">
    <div class="row align-items-center small">
        <div class="col-md-4">
            <strong>IP Address:</strong> {{ $activity->getExtraProperty('ip_address') ?? 'N/A' }}
        </div>
        <div class="col-md-8 text-truncate" title="{{ $activity->getExtraProperty('user_agent') }}">
            <strong>Browser/Device:</strong> {{ $activity->getExtraProperty('user_agent') ?? 'N/A' }}
        </div>
    </div>
</div>

<h6 class="fw-bold text-primary mb-3"><i class="fas fa-exchange-alt"></i> Data Changes</h6>

@php
    $properties = $activity->properties;
    $attributes = $properties['attributes'] ?? [];
    $old = $properties['old'] ?? [];
@endphp

@if (!empty($attributes) || !empty($old))
    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th width="30%">Field Name</th>
                    @if (!empty($old))
                        <th width="35%">Before (Old Value)</th>
                    @endif
                    @if (!empty($attributes))
                        <th width="35%">After (New Value)</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @php
                    $keys = array_unique(array_merge(array_keys($attributes), array_keys($old)));
                @endphp

                @foreach ($keys as $key)
                    <tr>
                        <td class="fw-bold text-capitalize">{{ str_replace('_', ' ', $key) }}</td>

                        @if (!empty($old))
                            <td class="text-danger">
                                @if (isset($old[$key]))
                                    {{ is_array($old[$key]) ? json_encode($old[$key]) : $old[$key] }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endif

                        @if (!empty($attributes))
                            <td class="text-success fw-semibold">
                                @if (isset($attributes[$key]))
                                    {{ is_array($attributes[$key]) ? json_encode($attributes[$key]) : $attributes[$key] }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-warning py-2">
        <i class="fas fa-exclamation-triangle"></i> No data changes recorded for this action.
    </div>
@endif

<div class="text-end mt-3">
    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
</div>

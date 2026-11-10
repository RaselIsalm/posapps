<div class="row">
    <div class="col-md-12 mb-3">
        <form action="{{ route('lead_notes.store') }}" method="POST" id="lead-note-form">
            @csrf
            <input type="hidden" name="lead_id" value="{{ $lead->id }}">

            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label fw-bold text-black m-0">
                    Note for: {{ $lead->name }}
                </label>
                <span class="badge bg-primary">{{ $lead->number ?? 'No Number' }}</span>
            </div>

            <div class="form-group">
                <textarea class="form-control" id="note" name="note" rows="3" 
                    placeholder="Write your note here..." required></textarea>
            </div>
            
            <div class="text-end mt-3">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn bg-primary btn-sm text-white px-3">
                    <i class="fas fa-save me-1"></i> Save Note
                </button>
            </div>
        </form>
    </div>

    <div class="col-md-12">
        <h6 class="border-bottom pb-2 mb-2 text-muted text-uppercase small fw-bold">Note History</h6>
        <div class="note-history bg-light p-2 rounded" style="max-height: 200px; overflow-y: auto;">
            @forelse($lead->notes as $note)
                <div class="card mb-2 border-0 shadow-sm">
                    <div class="card-body p-2">
                        <p class="mb-1 text-dark small">{{ $note->notes }}</p>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted" style="font-size: 10px;">
                                <i class="fas fa-user me-1"></i> {{ $note->creator->name ?? 'User' }}
                            </small>
                            <small class="text-muted" style="font-size: 10px;">
                                {{ $note->created_at->format('d M, Y h:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted small py-2">
                    No previous notes found.
                </div>
            @endforelse
        </div>
    </div>
</div>
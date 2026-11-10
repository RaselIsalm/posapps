<form action="{{ route('lead_notes.update', $note->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row">

        <div class="col-12 mb-3">
            <label class="form-label">Lead</label>
            <select name="lead_id" class="form-control" required>
                <option value="">Select Lead</option>
                @foreach ($leads as $lead)
                    <option value="{{ $lead->id }}" {{ $note->lead_id == $lead->id ? 'selected' : '' }}>
                        {{ $lead->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 mb-3">
            <label class="form-label">Notes</label>
            <textarea name="notes" class="form-control" rows="4" required>{{ $note->notes }}</textarea>
        </div>

        <div class="col-4 mb-3">
            <label class="form-check-label">Is Active</label>
            <div class="form-check form-switch text-center mt-1">
                <input class="form-check-input switch-size-lg" type="checkbox" name="status"
                    {{ $note->status ? 'checked' : '' }} style="width: 50px; height: 25px;">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
</form>

<section>
    <header class="mb-4">
        <h5 class="fw-bold text-dark mb-1">{{ __('Profile Information') }}</h5>
        <p class="text-muted small mb-0">
            Photo, name, designation and phone save immediately. Email and password change only after the correct OTP. A wrong or empty code does not update them.
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
            @php
                $avatarUrl = $user->profile_photo_url;
                $avatarInitial = mb_strtoupper(mb_substr(trim((string) $user->name), 0, 1));
                $hasAvatar = ! empty($user->avatar) && ! empty($avatarUrl);
            @endphp
            <div class="rounded-circle border overflow-hidden" style="position: relative; width: 88px; height: 88px;">
                <span id="avatarInitialFallback" aria-hidden="true"
                    style="width: 100%; height: 100%; display: {{ $avatarUrl ? 'none' : 'grid' }}; place-items: center; background: rgba(var(--primary-rgb, 48, 142, 135), .12); color: var(--primary-color, #308e87); font-size: 30px; font-weight: 700;">
                    {{ $avatarInitial ?: '?' }}
                </span>
                <img id="avatarPreview" @if ($avatarUrl) src="{{ $avatarUrl }}" @endif alt=""
                    class="rounded-circle" style="position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: {{ $avatarUrl ? 'block' : 'none' }};">
            </div>
            <div>
                <label for="avatar" class="form-label fw-medium mb-1">Profile photo</label>
                <input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"
                    class="form-control @error('avatar') is-invalid @enderror" style="max-width: 280px;">
                <input type="hidden" name="remove_avatar" id="remove_avatar" value="0">
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger" id="removeAvatarBtn"
                        @if (! $hasAvatar) style="display: none;" @endif>
                        Remove photo
                    </button>
                </div>
                <div class="form-text">JPG, PNG or WEBP. Max 2 MB. Click Remove photo, then Save profile.</div>
                @error('avatar')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label fw-medium">Name <span class="text-danger">*</span></label>
                <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror"
                    value="{{ old('name', $user->name) }}" required autocomplete="name">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="designation" class="form-label fw-medium">Designation</label>
                <input id="designation" name="designation" type="text"
                    class="form-control @error('designation') is-invalid @enderror"
                    value="{{ old('designation', $user->designation) }}" placeholder="e.g. Store Manager" maxlength="120">
                @error('designation')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label fw-medium">Email <span class="text-danger">*</span></label>
                <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email', $user->email) }}" required autocomplete="username">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label fw-medium">Phone number</label>
                <input id="phone" name="phone" type="text" class="form-control @error('phone') is-invalid @enderror"
                    value="{{ old('phone', $user->phone) }}" autocomplete="tel" maxlength="20">
                @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Username</label>
                <input type="text" class="form-control" value="{{ $user->username }}" disabled>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-medium">Role</label>
                <input type="text" class="form-control" value="{{ $user->getRoleNames()->first() ?? '—' }}" disabled>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary px-4 fw-medium">Save profile</button>
            @if (session('status') === 'profile-updated')
                <div class="alert alert-success floating-alert mb-0 py-2">
                    <i class="fas fa-check-circle me-1"></i> Saved successfully.
                </div>
            @endif
        </div>
    </form>
</section>

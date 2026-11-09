<section>
    <header class="mb-4">
        <h5 class="fw-bold text-dark mb-1">{{ __('Update Password') }}</h5>
        <p class="text-muted small mb-0">
            Enter a new password. We email a code to <strong>{{ $user->email }}</strong>. The password is saved only after the correct OTP. A wrong code leaves the old password unchanged.
        </p>
    </header>

    <form method="post" action="{{ route('profile.password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_password" class="form-label fw-medium">{{ __('New Password') }}</label>
            <input id="update_password_password" name="password" type="password"
                class="form-control @if($errors->updatePassword->has('password')) is-invalid @endif"
                autocomplete="new-password" required>
            @if($errors->updatePassword->has('password'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password') }}</div>
            @endif
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label fw-medium">{{ __('Confirm Password') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password"
                class="form-control @if($errors->updatePassword->has('password_confirmation')) is-invalid @endif"
                autocomplete="new-password" required>
            @if($errors->updatePassword->has('password_confirmation'))
                <div class="invalid-feedback">{{ $errors->updatePassword->first('password_confirmation') }}</div>
            @endif
        </div>

        <div class="d-flex flex-wrap align-items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary px-4 fw-medium">Send OTP</button>
            @if (session('status') === 'password-updated')
                <div class="alert alert-success floating-alert mb-0 py-2">
                    <i class="fas fa-check-circle me-1"></i> Password updated.
                </div>
            @endif
        </div>
    </form>
</section>

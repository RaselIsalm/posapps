<section>
    <header class="mb-4">
        <h5 class="fw-bold text-danger mb-1">
            {{ __('Delete Account') }}
        </h5>

        <p class="text-muted small">
            {{ __('Your request will be reviewed by Super Admin. If approved, your personal profile data will be removed. Business records that must be retained may remain in de-identified form.') }}
        </p>
    </header>

    <div class="d-flex flex-wrap align-items-center gap-3">
        <button type="button" class="btn btn-danger px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#confirmUserDeletionModal">
            {{ __('Delete Account') }}
        </button>

        @if (session('status') === 'delete-requested')
            <div class="alert alert-success floating-alert mb-0 py-2">
                <i class="fas fa-check-circle me-1"></i> {{ __('Account deletion requested successfully.') }}
            </div>
        @endif
    </div>

    <!-- Modal -->
    <div class="modal fade @if($errors->userDeletion->isNotEmpty()) show d-block @endif" id="confirmUserDeletionModal" tabindex="-1" aria-labelledby="confirmUserDeletionModalLabel" aria-hidden="true" @if($errors->userDeletion->isNotEmpty()) style="background: rgba(0,0,0,0.5);" @endif>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <form method="post" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('delete')

                    <div class="modal-body p-4 text-center">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                            style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                            <i class="fas fa-user-times text-danger fs-3"></i>
                        </div>
                        <h5 class="fw-bold mb-2" id="confirmUserDeletionModalLabel">
                            {{ __('Are you sure you want to delete your account?') }}
                        </h5>
                        <p class="text-muted mb-4">
                            {{ __('Please enter your password to confirm this request. If approved, your profile information will be removed and retained business records will be de-identified.') }}
                        </p>

                        <div class="mb-4 text-start">
                            <label for="password" class="sr-only">{{ __('Password') }}</label>
                            <input type="password" class="form-control @if($errors->userDeletion->has('password')) is-invalid @endif" id="password" name="password" placeholder="{{ __('Password') }}">
                            @if($errors->userDeletion->has('password'))
                                <div class="invalid-feedback">{{ $errors->userDeletion->first('password') }}</div>
                            @endif
                        </div>

                        <div class="d-flex justify-content-center gap-2 flex-wrap">
                            @if(!$errors->userDeletion->isNotEmpty())
                                <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                            @else
                                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary px-4">{{ __('Cancel') }}</a>
                            @endif
                            <button type="submit" class="btn btn-danger px-4">
                                <i class="fas fa-user-times me-1"></i> {{ __('Request Account Deletion') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

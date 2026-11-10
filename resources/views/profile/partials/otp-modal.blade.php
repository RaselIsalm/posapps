@php
    // Open modal only when OTP was sent, verify failed, or resend mail failed (purpose in old input)
    $showOtp = session('status') === 'otp-sent'
        || $errors->has('otp')
        || ($errors->has('mail') && filled(old('purpose')));
    $otpPurpose = session('otp_purpose', old('purpose', 'profile'));
@endphp
<div class="modal fade" id="profileOtpModal" tabindex="-1" aria-labelledby="profileOtpModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form method="post" action="{{ route('profile.otp.verify') }}">
                @csrf
                <input type="hidden" name="purpose" value="{{ $otpPurpose }}">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold" id="profileOtpModalLabel"><i class="fas fa-shield-alt me-2"></i>Enter verification code</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    @if ($errors->has('mail'))
                        <div class="alert alert-danger py-2 px-3 small mb-3" role="alert" style="word-break: break-word; overflow-wrap: anywhere;">
                            {{ $errors->first('mail') }}
                        </div>
                    @endif
                    <p class="text-muted small mb-3">
                        {{ session('otp_message') ?: 'Enter the 6-digit code from your email. Email or password will change only if this code is correct.' }}
                    </p>
                    <label for="otp" class="form-label fw-medium">OTP</label>
                    <input id="otp" name="otp" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                        class="form-control form-control-lg text-center @error('otp') is-invalid @enderror"
                        style="letter-spacing: 8px; font-weight: 700;" required autocomplete="one-time-code">
                    @error('otp')
                        <div class="invalid-feedback d-block" style="word-break: break-word; overflow-wrap: anywhere;">{{ $message }}</div>
                    @enderror
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" form="profileOtpResendForm" class="btn btn-outline-primary"
                        style="background: #fff; color: var(--primary-color, #308e87); border: 1px solid var(--primary-color, #308e87);">
                        Resend code
                    </button>
                    <button type="submit" class="btn btn-primary">Verify</button>
                </div>
            </form>
            <form id="profileOtpResendForm" method="post" action="{{ route('profile.otp.resend') }}" class="d-none">
                @csrf
                <input type="hidden" name="purpose" value="{{ $otpPurpose }}">
            </form>
        </div>
    </div>
</div>
@if ($showOtp)
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var el = document.getElementById('profileOtpModal');
        if (el && window.bootstrap) {
            new bootstrap.Modal(el).show();
        }
    });
</script>
@endif

<style>
    .floating-alert {
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        min-width: 250px;
        max-width: 400px;
        padding: 15px;
        border-radius: 5px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        opacity: 0;
        transform: translateX(100%);
        animation: slideIn 0.5s forwards, fadeOut 0.5s 4.5s forwards;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .floating-alert.alert-success {
        background-color: #ffffff !important;
        background-image: none !important;
        border: 1px solid rgba(var(--primary-rgb, 48, 142, 135), 0.15) !important;
        border-left: 4px solid var(--toast-success-color, var(--primary-color, #308e87)) !important;
        color: #2c323f !important;
    }

    .floating-alert.alert-success strong {
        color: var(--toast-success-color, var(--primary-color, #308e87)) !important;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(100%);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1;
        }

        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
</style>

@if (session('success'))
    <div class="alert alert-success floating-alert">
        <strong>{{ __('Success!') }}</strong> {{ __(session('success')) }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger floating-alert">
        <strong>{{ __('Error!') }}</strong> {{ __(session('error')) }}
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning floating-alert">
        <strong>{{ __('Warning!') }}</strong> {{ __(session('warning')) }}
    </div>
@endif

@if (session('message'))
    <div class="alert alert-info floating-alert">
        <strong>{{ __('Message!') }}</strong> {{ __(session('message')) }}
    </div>
@endif

@php
    // Profile OTP modal already shows otp/mail errors — skip duplicate toast (DRY)
    $otpModalShowsErrors = $errors->has('otp')
        || ($errors->has('mail') && filled(old('purpose')));
    $toastValidationErrors = collect($errors->messages());
    if ($otpModalShowsErrors) {
        $toastValidationErrors = $toastValidationErrors->except(['otp', 'mail']);
    }
    $toastValidationErrors = $toastValidationErrors->flatten()->filter();
@endphp
@if ($toastValidationErrors->isNotEmpty())
    <div class="alert alert-danger floating-alert">
        <strong>{{ __('Error!') }}</strong>
        <ul>
            @foreach ($toastValidationErrors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif



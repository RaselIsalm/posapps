@extends('backend.layout.main')
@section('title', 'Account Settings')

@section('content')
    @component('backend.layout.partials.bredcrumbs')
        <li class="breadcrumb-item active" aria-current="page">Profile Settings</li>
    @endcomponent

    <div class="row layout-top-spacing">
        <div class="col-xl-8 col-lg-10 col-md-12 mx-auto">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            @unless(auth()->user()?->hasRole('Super-Admin'))
            <div class="card shadow-sm border-0 mb-4 border-top border-danger border-3">
                <div class="card-body p-4 p-md-5">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
            @endunless
        </div>
    </div>
    @include('profile.partials.otp-modal')
@endsection

@push('js')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const alerts = document.querySelectorAll('.floating-alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(function() {
                    alert.style.display = "none";
                }, 500);
            }, 3000);
        });

        const avatarInput = document.getElementById('avatar');
        const preview = document.getElementById('avatarPreview');
        const avatarFallback = document.getElementById('avatarInitialFallback');
        const removeAvatar = document.getElementById('remove_avatar');
        const removeAvatarBtn = document.getElementById('removeAvatarBtn');
        let previewObjectUrl = null;
        if (avatarInput && preview) {
            avatarInput.addEventListener('change', function () {
                const file = this.files && this.files[0];
                if (!file) return;
                if (file.size > 2 * 1024 * 1024) {
                    showAppToast(@json(__('Image must be 2 MB or smaller.')), 'warning');
                    this.value = '';
                    return;
                }
                if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
                previewObjectUrl = URL.createObjectURL(file);
                preview.src = previewObjectUrl;
                preview.style.display = 'block';
                if (avatarFallback) avatarFallback.style.display = 'none';
                if (removeAvatar) removeAvatar.value = '0';
                if (removeAvatarBtn) removeAvatarBtn.style.display = '';
            });
            preview.addEventListener('error', function () {
                preview.style.display = 'none';
                if (avatarFallback) avatarFallback.style.display = 'grid';
            });
        }
        if (removeAvatarBtn && preview && removeAvatar) {
            removeAvatarBtn.addEventListener('click', function () {
                removeAvatar.value = '1';
                if (avatarInput) avatarInput.value = '';
                if (previewObjectUrl) {
                    URL.revokeObjectURL(previewObjectUrl);
                    previewObjectUrl = null;
                }
                preview.removeAttribute('src');
                preview.style.display = 'none';
                if (avatarFallback) avatarFallback.style.display = 'grid';
                removeAvatarBtn.style.display = 'none';
            });
        }
    });
</script>
@endpush

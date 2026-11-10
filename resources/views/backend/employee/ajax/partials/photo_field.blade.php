@php
    $photoUrl = null;
    $photoInitials = '';
    if (isset($employee)) {
        $photoUrl = \App\Helpers\ImageHelper::publicUrl($employee->image, 'employees');
        $parts = preg_split('/\s+/u', trim((string) $employee->name)) ?: [];
        foreach (array_slice($parts, 0, 2) as $part) {
            $photoInitials .= mb_strtoupper(mb_substr($part, 0, 1));
        }
    }
@endphp
<div class="col-12 mb-3">
    <label class="form-label" for="employee_image">{{ __('Photo') }} <span class="text-muted fw-normal">({{ __('Optional') }})</span></label>
    <div class="emp-photo">
        <div class="emp-photo-preview" aria-hidden="true">
            <img src="{{ $photoUrl ?: '' }}" alt="" class="emp-photo-img {{ $photoUrl ? '' : 'd-none' }}">
            <span class="emp-photo-fallback {{ $photoUrl ? 'd-none' : '' }}">
                @if ($photoInitials !== '')
                    {{ $photoInitials }}
                @else
                    <i class="fas fa-user"></i>
                @endif
            </span>
        </div>
        <div class="emp-photo-fields">
            <div class="pos-file">
                <input type="file" class="pos-file-input emp-photo-input" name="image" id="employee_image"
                    accept="image/jpeg,image/png,image/webp,image/gif,image/avif,image/bmp,image/heic,image/heif,.jpg,.jpeg,.png,.webp,.gif,.avif,.bmp,.heic,.heif">
                <span class="pos-file-btn">{{ __('Choose File') }}</span>
                <span class="pos-file-name" data-empty="{{ __('No file chosen') }}">{{ __('No file chosen') }}</span>
            </div>
            <small class="text-muted d-block mt-1">{{ __('Employee photo hint') }}</small>
            @if ($photoUrl)
                <div class="form-check mt-2">
                    <input class="form-check-input emp-photo-remove" type="checkbox" name="remove_image" value="1" id="remove_employee_image">
                    <label class="form-check-label" for="remove_employee_image">{{ __('Remove photo') }}</label>
                </div>
            @endif
            <div class="emp-photo-error text-danger small mt-1 d-none"></div>
        </div>
    </div>
</div>

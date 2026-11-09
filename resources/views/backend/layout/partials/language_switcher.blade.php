@php
    $currentLocale = app()->getLocale();
@endphp
<div class="language-switcher-item">
    <div class="language-switcher" role="group" aria-label="{{ __('Language') }}">
        <a href="{{ route('locale.switch', 'en') }}"
            class="language-switcher-btn {{ $currentLocale === 'en' ? 'is-active' : '' }}"
            @if($currentLocale === 'en') aria-current="true" @endif>EN</a>
        <a href="{{ route('locale.switch', 'bn') }}"
            class="language-switcher-btn {{ $currentLocale === 'bn' ? 'is-active' : '' }}"
            @if($currentLocale === 'bn') aria-current="true" @endif>বাংলা</a>
    </div>
</div>
<style>
    .language-switcher {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        padding: 3px;
        border-radius: 8px;
        background: #f3f6f6;
        border: 1px solid #e6eeee;
    }
    .language-switcher-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 42px;
        height: 28px;
        padding: 0 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1;
        color: #5c6b6a;
        text-decoration: none;
    }
    .language-switcher-btn.is-active {
        background: var(--primary-color, #308e87);
        color: #fff;
    }
    .language-switcher-item {
        display: flex;
        align-items: center;
        flex: 0 0 auto;
        margin-right: 8px;
    }
    @media (min-width: 768px) {
        header .language-switcher-item {
            display: flex;
        }
    }
    @media (max-width: 767.98px) {
        header .language-switcher-item {
            display: none !important;
        }
    }
    .profile-lang-row {
        flex-direction: column;
        align-items: stretch !important;
        gap: 8px;
        padding: 12px 14px 10px !important;
        border-bottom: 1px solid #e8eeee;
    }
    .profile-lang-label {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #6b7c7b;
    }
    .profile-lang-row .language-switcher {
        width: 100%;
    }
    .profile-lang-row .language-switcher-btn {
        flex: 1;
        min-width: 0;
        height: 34px;
    }
</style>

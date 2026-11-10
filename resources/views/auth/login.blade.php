<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f4f8f8">
    <title>{{ __('Sign in') }} · {{ $general_settings['company_name'] ?? config('app.name', 'ONEPOS') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('storage/settings/' . ($general_settings['favicon'] ?? 'favicon.png')) }}">

    @include('backend.layout.partials.theme-variables')

    @php
        $companyName = $general_settings['company_name'] ?? config('app.name', 'ONEPOS');
        $configuredLogo = ! empty($general_settings['logo'])
            ? asset('storage/settings/' . $general_settings['logo'])
            : asset('landing/assets/onepos-logo.png');
        $fallbackLogo = asset('landing/assets/onepos-logo.png');
    @endphp

    <style>
        :root {
            --login-brand: var(--primary-color, #308e87);
            --login-brand-hover: var(--primary-hover, #256e69);
            --login-brand-rgb: var(--primary-rgb, 48, 142, 135);
            --login-ink: #172b3a;
            --login-muted: #71828d;
            --login-border: #e4ebed;
            --login-surface: #ffffff;
            color-scheme: light;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            min-width: 320px;
            min-height: 100%;
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100vh;
            min-height: 100svh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 24px;
            overflow-x: hidden;
            background:
                radial-gradient(ellipse at 12% 12%, rgba(var(--login-brand-rgb), .10), transparent 35%),
                radial-gradient(ellipse at 88% 88%, rgba(32, 114, 130, .08), transparent 34%),
                #f4f8f8;
            color: var(--login-ink);
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .phpdebugbar,
        .phpdebugbar-openhandler,
        .phpdebugbar-openhandler-overlay,
        .phpdebugbar-widgets-datasets-panel {
            display: none !important;
        }

        button,
        input {
            font: inherit;
        }

        .login-shell {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: minmax(280px, .92fr) minmax(390px, 1.08fr);
            width: min(100%, 940px);
            min-height: 600px;
            overflow: hidden;
            border: 1px solid rgba(218, 230, 231, .9);
            border-radius: 28px;
            background: var(--login-surface);
            box-shadow: 0 28px 80px rgba(22, 53, 62, .12), 0 4px 16px rgba(22, 53, 62, .04);
        }

        .brand-panel {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-width: 0;
            padding: 40px 34px 32px;
            overflow: hidden;
            background: linear-gradient(150deg, #123d44 0%, #176b67 62%, #228c7e 100%);
            color: #fff;
        }

        .brand-panel::before,
        .brand-panel::after {
            position: absolute;
            content: "";
            pointer-events: none;
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 50%;
        }

        .brand-panel::before {
            width: 430px;
            height: 430px;
            right: -235px;
            top: 150px;
            box-shadow: 0 0 0 36px rgba(255, 255, 255, .025), 0 0 0 72px rgba(255, 255, 255, .02);
        }

        .brand-panel::after {
            width: 210px;
            height: 210px;
            left: -138px;
            bottom: 38px;
            box-shadow: 0 0 0 28px rgba(255, 255, 255, .025);
        }

        .brand-logo-wrap {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: fit-content;
            min-width: 148px;
            min-height: 52px;
            padding: 9px 14px;
            border: 1px solid rgba(255, 255, 255, .58);
            border-radius: 14px;
            background: rgba(255, 255, 255, .96);
            box-shadow: 0 8px 24px rgba(7, 35, 38, .12);
        }

        .brand-logo {
            display: block;
            max-width: 176px;
            max-height: 34px;
            width: auto;
            object-fit: contain;
        }

        .brand-copy {
            position: relative;
            z-index: 1;
            margin: auto 0;
            padding: 58px 0 48px;
        }

        .brand-eyebrow,
        .login-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            line-height: 1.4;
            text-transform: uppercase;
        }

        .brand-eyebrow {
            color: rgba(255, 255, 255, .78);
        }

        .brand-eyebrow::before {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #8de1bf;
            box-shadow: 0 0 0 4px rgba(141, 225, 191, .16);
            content: "";
        }

        .brand-title {
            max-width: 350px;
            margin: 19px 0 14px;
            font-size: clamp(32px, 4vw, 43px);
            font-weight: 650;
            letter-spacing: -.045em;
            line-height: 1.12;
        }

        .brand-description {
            max-width: 330px;
            margin: 0;
            color: rgba(255, 255, 255, .76);
            font-size: 15px;
            line-height: 1.75;
        }

        .feature-list {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 28px;
        }

        .feature-chip {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            min-height: 34px;
            padding: 7px 10px;
            border: 1px solid rgba(255, 255, 255, .16);
            border-radius: 999px;
            background: rgba(255, 255, 255, .09);
            color: rgba(255, 255, 255, .9);
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
        }

        .feature-chip svg {
            width: 14px;
            height: 14px;
            opacity: .9;
        }

        .brand-footnote {
            position: relative;
            z-index: 1;
            margin: 0;
            color: rgba(255, 255, 255, .62);
            font-size: 11px;
            letter-spacing: .02em;
        }

        .form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            padding: 54px clamp(34px, 6vw, 68px);
        }

        .login-content {
            width: 100%;
            max-width: 372px;
            text-align: center;
        }

        .mobile-brand {
            display: none;
            margin-bottom: 34px;
            justify-content: center;
        }

        .mobile-brand .brand-logo-wrap {
            border-color: var(--login-border);
            box-shadow: 0 5px 16px rgba(22, 53, 62, .06);
        }

        .login-eyebrow {
            display: flex;
            justify-content: center;
            margin-bottom: 13px;
            color: var(--login-brand);
        }

        .login-title {
            margin: 0;
            color: var(--login-ink);
            font-size: 30px;
            font-weight: 680;
            letter-spacing: -.045em;
            line-height: 1.2;
            text-align: center;
        }

        .login-description {
            margin: 10px 0 28px;
            color: var(--login-muted);
            font-size: 14px;
            line-height: 1.65;
            text-align: center;
        }

        .login-alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 0 0 20px;
            padding: 12px 14px;
            border: 1px solid #f2d5d6;
            border-radius: 12px;
            background: #fff7f7;
            color: #a83940;
            font-size: 13px;
            line-height: 1.5;
        }

        .login-alert svg {
            flex: 0 0 auto;
            width: 17px;
            height: 17px;
            margin-top: 1px;
        }

        .field-group {
            margin-bottom: 18px;
        }

        .field-label {
            display: block;
            margin-bottom: 8px;
            color: #344954;
            font-size: 12px;
            font-weight: 650;
            letter-spacing: .01em;
            text-align: left;
        }

        .field-control-wrap {
            position: relative;
        }

        .field-icon {
            position: absolute;
            top: 50%;
            left: 15px;
            width: 18px;
            height: 18px;
            transform: translateY(-50%);
            color: #8999a2;
            pointer-events: none;
        }

        .field-control {
            display: block;
            width: 100%;
            height: 52px;
            padding: 0 46px 0 44px;
            border: 1px solid var(--login-border);
            border-radius: 12px;
            outline: 0;
            background: #fff;
            color: var(--login-ink);
            font-size: 14px;
            transition: border-color .18s ease, box-shadow .18s ease, background-color .18s ease;
        }

        .field-control::placeholder {
            color: #a5b1b7;
            opacity: 1;
        }

        .field-control:hover {
            border-color: #cbd8db;
        }

        .field-control:focus {
            border-color: var(--login-brand);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(var(--login-brand-rgb), .12);
        }

        .field-control.is-invalid {
            border-color: #d95760;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            display: inline-grid;
            width: 36px;
            height: 36px;
            transform: translateY(-50%);
            place-items: center;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: #82939b;
            cursor: pointer;
            transition: color .18s ease, background-color .18s ease;
        }

        .password-toggle:hover,
        .password-toggle:focus-visible {
            outline: 0;
            background: #f0f6f5;
            color: var(--login-brand);
        }

        .password-toggle svg {
            width: 18px;
            height: 18px;
        }

        .field-error {
            display: block;
            margin-top: 6px;
            color: #b6434b;
            font-size: 12px;
            line-height: 1.4;
            text-align: left;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin: 3px 0 24px;
        }

        .remember-option {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 28px;
            color: #647781;
            font-size: 12px;
            cursor: pointer;
        }

        .remember-option input {
            width: 16px;
            height: 16px;
            margin: 0;
            accent-color: var(--login-brand);
        }

        .forgot-link {
            color: var(--login-brand);
            font-size: 12px;
            font-weight: 650;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: var(--login-brand-hover);
            text-decoration: underline;
            text-underline-offset: 3px;
        }

        .submit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 52px;
            padding: 0 18px;
            border: 1px solid var(--login-brand);
            border-radius: 12px;
            background: linear-gradient(135deg, var(--login-brand), var(--login-brand-hover));
            box-shadow: 0 8px 18px rgba(var(--login-brand-rgb), .2);
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
        }

        .submit-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 11px 24px rgba(var(--login-brand-rgb), .24);
            filter: brightness(1.03);
        }

        .submit-button:active {
            transform: translateY(0);
        }

        .submit-button:focus-visible {
            outline: 3px solid rgba(var(--login-brand-rgb), .24);
            outline-offset: 3px;
        }

        .submit-button:disabled {
            cursor: wait;
            opacity: .78;
        }

        .submit-spinner {
            display: none;
            width: 15px;
            height: 15px;
            border: 2px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        .submit-button.is-loading .submit-spinner {
            display: inline-block;
        }

        .mobile-mode-row,
        .mobile-login-error {
            display: none;
        }

        .onepos-app-mode .mobile-mode-row {
            display: flex;
            gap: 10px;
            margin-top: 13px;
        }

        .onepos-app-mode .mobile-mode-button {
            flex: 1 1 0;
            min-height: 42px;
            padding: 0 12px;
            border: 1px solid var(--login-border);
            border-radius: 11px;
            background: #fff;
            color: #344954;
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .onepos-app-mode .mobile-mode-button.is-selected {
            border-color: var(--login-brand);
            background: rgba(var(--login-brand-rgb), .09);
            color: var(--login-brand);
        }

        .onepos-app-mode .mobile-login-error:not([hidden]) {
            display: flex;
            margin: 14px 0 0;
        }

        .onepos-app-mode.is-demo-mode #mobileBaseUrlGroup,
        .onepos-app-mode.is-demo-mode #credentialFields,
        .onepos-app-mode.is-demo-mode #mobilePasswordGroup,
        .onepos-app-mode.is-demo-mode .form-options {
            display: none;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .login-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin: 22px 0 0;
            color: #8b9ba2;
            font-size: 11px;
            text-align: center;
        }

        .login-footer svg {
            width: 14px;
            height: 14px;
            color: var(--login-brand);
        }

        .login-legal-links {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px 16px;
            margin-top: 10px;
            font-size: 11px;
        }

        .login-legal-links a {
            color: var(--login-brand);
            text-decoration: none;
        }

        .login-legal-links a:hover {
            text-decoration: underline;
        }

        @media (max-width: 760px) {
            body {
                padding: 20px 16px;
            }

            .login-shell {
                display: block;
                width: min(100%, 440px);
                min-height: 0;
                border-radius: 23px;
            }

            .brand-panel {
                display: none;
            }

            .form-panel {
                display: block;
                padding: clamp(25px, 7vw, 36px) clamp(22px, 7vw, 34px) 26px;
            }

            .login-content {
                max-width: none;
            }

            .mobile-brand {
                display: flex;
                margin-bottom: 28px;
            }

            .mobile-brand .brand-logo-wrap {
                min-width: 126px;
                min-height: 45px;
                padding: 7px 11px;
                border-radius: 12px;
            }

            .mobile-brand .brand-logo {
                max-width: 150px;
                max-height: 29px;
            }

            .login-eyebrow {
                margin-bottom: 10px;
            }

            .login-title {
                font-size: clamp(27px, 8vw, 31px);
            }

            .login-description {
                margin: 8px 0 23px;
                font-size: 13px;
            }

            .field-group {
                margin-bottom: 16px;
            }

            .field-control,
            .submit-button {
                min-height: 50px;
            }

            .form-options {
                margin-bottom: 21px;
            }
        }

        @media (max-width: 360px) {
            body {
                padding: 12px;
            }

            .login-shell {
                border-radius: 19px;
            }

            .form-panel {
                padding-right: 19px;
                padding-left: 19px;
            }

            .form-options {
                align-items: flex-start;
            }
        }

        @media (max-height: 690px) and (min-width: 761px) {
            body {
                align-items: flex-start;
            }

            .login-shell {
                margin-block: auto;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }
    </style>
</head>

<body @class(['onepos-app-mode' => $isOnePosApp])>
    @php
        $loginError = session('login_error_toast');
    @endphp

    <main class="login-shell" aria-label="{{ __('Sign in to your account') }}">
        <section class="brand-panel" aria-label="{{ $companyName }}">
            <a class="brand-logo-wrap" href="{{ route('landing') }}" aria-label="{{ $companyName }} home">
                <img class="brand-logo" src="{{ $configuredLogo }}" alt="{{ $companyName }}"
                    onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';">
            </a>

            <div class="brand-copy">
                <span class="brand-eyebrow">{{ __('Store management') }}</span>
                <h1 class="brand-title">{{ __('Everything your store needs, in one place.') }}</h1>
                <p class="brand-description">{{ __('Manage sales, inventory, customers and daily operations from one secure workspace.') }}</p>

                <div class="feature-list" aria-label="{{ __('Workspace features') }}">
                    <span class="feature-chip">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 5.5h16v13H4zM8 9h8M8 13h5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('Sales') }}
                    </span>
                    <span class="feature-chip">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m4.5 7.8 7.5 4.4 7.5-4.4M12 12.2V21" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg>
                        {{ __('Inventory') }}
                    </span>
                    <span class="feature-chip">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 19V5m0 14h16M8 15l3-4 3 2 5-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 7h3v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        {{ __('Reports') }}
                    </span>
                </div>
            </div>

            <p class="brand-footnote">{{ __('A clear view of your business, every day.') }}</p>
        </section>

        <section class="form-panel">
            <div class="login-content">
                <div class="mobile-brand">
                    <a class="brand-logo-wrap" href="{{ route('landing') }}" aria-label="{{ $companyName }} home">
                        <img class="brand-logo" src="{{ $configuredLogo }}" alt="{{ $companyName }}"
                            onerror="this.onerror=null;this.src='{{ $fallbackLogo }}';">
                    </a>
                </div>

                <span class="login-eyebrow">{{ __('Welcome back') }}</span>
                <h2 class="login-title">{{ __('Sign in to your account') }}</h2>
                <p class="login-description">{{ __('Enter your email and password to continue to your workspace.') }}</p>

                @if (session('logout_toast'))
                    <div class="login-alert" role="status" style="border-color:#cde8df;background:#f3fbf7;color:#216c55;">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span>{{ session('logout_toast') }}</span>
                    </div>
                @endif

                @if ($loginError)
                    <div class="login-alert" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.8"/><path d="M12 8v5m0 3h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                        <span>{{ $loginError }}</span>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST" id="loginForm">
                    @csrf

                    @if ($isOnePosApp)
                        <div class="field-group" id="mobileBaseUrlGroup">
                            <label class="field-label" for="mobileBaseUrl">{{ __('Base URL') }}</label>
                            <div class="field-control-wrap">
                                <svg class="field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18m0-18a15 15 0 0 0 0 18" stroke="currentColor" stroke-width="1.5"/></svg>
                                <input id="mobileBaseUrl" name="base_url" type="text" inputmode="url" value="{{ old('base_url', $mobileBaseUrl) }}"
                                    class="field-control" placeholder="https://pos.onetech.bd" autocomplete="url"
                                    autocapitalize="none" spellcheck="false" required>
                            </div>
                        </div>
                    @endif

                    <div class="field-group" id="credentialFields">
                        <label class="field-label" for="email">{{ $isOnePosApp ? __('Email or username') : __('Email address') }}</label>
                        <div class="field-control-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="14" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="m5 7 7 5.5L19 7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <input id="email" name="email" type="{{ $isOnePosApp ? 'text' : 'email' }}" value="{{ old('email') }}"
                                class="field-control @error('email') is-invalid @enderror"
                                placeholder="{{ $isOnePosApp ? __('Email or username') : __('name@example.com') }}" autocomplete="username" inputmode="{{ $isOnePosApp ? 'text' : 'email' }}"
                                autocapitalize="none" spellcheck="false" required autofocus
                                @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')
                            <span class="field-error" id="email-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field-group" id="mobilePasswordGroup">
                        <label class="field-label" for="password">{{ __('Password') }}</label>
                        <div class="field-control-wrap">
                            <svg class="field-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="4.5" y="10" width="15" height="10.5" rx="2.5" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 1 1 8 0v3m-4 4v2.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                            <input id="password" name="password" type="password"
                                class="field-control @error('password') is-invalid @enderror"
                                placeholder="{{ __('Enter your password') }}" autocomplete="current-password" required
                                @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" class="password-toggle" id="passwordToggle" aria-label="{{ __('Show password') }}" aria-pressed="false">
                                <svg id="passwordToggleIcon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M2.8 12s3.2-6 9.2-6 9.2 6 9.2 6-3.2 6-9.2 6-9.2-6-9.2-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.7"/></svg>
                            </button>
                        </div>
                        @error('password')
                            <span class="field-error" id="password-error" role="alert">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-options">
                        <label class="remember-option" for="remember">
                            <input id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                            <span>{{ __('Remember me') }}</span>
                        </label>
                        <a class="forgot-link" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                    </div>

                    <button type="submit" id="submitButton" class="submit-button">
                        <span class="submit-spinner" aria-hidden="true"></span>
                        <span id="submitButtonText">{{ __('Sign in') }}</span>
                    </button>

                    @if ($isOnePosApp)
                        <div class="mobile-mode-row" role="group" aria-label="{{ __('Sign-in mode') }}">
                            <button type="button" class="mobile-mode-button" data-mode="demo" aria-pressed="false">{{ __('Demo') }}</button>
                            <button type="button" class="mobile-mode-button is-selected" data-mode="user" aria-pressed="true">{{ __('User') }}</button>
                        </div>
                        <div class="login-alert mobile-login-error" id="mobileLoginError" role="alert" hidden>
                            <span id="mobileLoginErrorText"></span>
                        </div>
                    @endif
                </form>

                <p class="login-footer">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 19 6v5c0 4.5-2.8 8-7 10-4.2-2-7-5.5-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span>{{ __('Secure access to :company', ['company' => $companyName]) }}</span>
                </p>
                <div class="login-legal-links">
                    <a href="{{ route('privacy') }}">{{ __('Privacy policy') }}</a>
                    <a href="{{ route('account-deletion.create') }}">{{ __('Request account deletion') }}</a>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function () {
            var password = document.getElementById('password');
            var toggle = document.getElementById('passwordToggle');
            var icon = document.getElementById('passwordToggleIcon');

            toggle.addEventListener('click', function () {
                var showing = password.type === 'password';
                password.type = showing ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', showing ? 'true' : 'false');
                toggle.setAttribute('aria-label', showing ? @json(__('Hide password')) : @json(__('Show password')));
                icon.innerHTML = showing
                    ? '<path d="M3 3 21 21M10.6 10.6a2 2 0 0 0 2.8 2.8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/><path d="M9.9 5.2A10.8 10.8 0 0 1 12 5c6 0 9.2 7 9.2 7a15 15 0 0 1-3.2 4.1M6.1 6.1C3.9 7.7 2.8 12 2.8 12s3.2 7 9.2 7c1.2 0 2.3-.3 3.3-.7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>'
                    : '<path d="M2.8 12s3.2-6 9.2-6 9.2 6 9.2 6-3.2 6-9.2 6-9.2-6-9.2-6Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><circle cx="12" cy="12" r="2.6" stroke="currentColor" stroke-width="1.7"/>';
            });

            var form = document.getElementById('loginForm');
            var onePosApp = @json($isOnePosApp);

            if (onePosApp) {
                var mode = 'user';
                var baseUrl = document.getElementById('mobileBaseUrl');
                var identifier = document.getElementById('email');
                var modeButtons = document.querySelectorAll('.mobile-mode-button');
                var errorBox = document.getElementById('mobileLoginError');
                var errorText = document.getElementById('mobileLoginErrorText');
                var submitText = document.getElementById('submitButtonText');

                function setMode(nextMode) {
                    mode = nextMode;
                    document.body.classList.toggle('is-demo-mode', mode === 'demo');
                    baseUrl.required = mode === 'user';
                    identifier.required = mode === 'user';
                    password.required = mode === 'user';
                    submitText.textContent = mode === 'demo' ? @json(__('Continue as Guest')) : @json(__('Sign in'));
                    modeButtons.forEach(function (button) {
                        var selected = button.dataset.mode === mode;
                        button.classList.toggle('is-selected', selected);
                        button.setAttribute('aria-pressed', selected ? 'true' : 'false');
                    });
                    errorBox.hidden = true;
                }

                modeButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        setMode(button.dataset.mode);
                    });
                });

                form.addEventListener('submit', async function (event) {
                    event.preventDefault();
                    if (!this.reportValidity()) return;

                    errorBox.hidden = true;
                    var button = document.getElementById('submitButton');
                    button.disabled = true;
                    button.classList.add('is-loading');
                    button.setAttribute('aria-busy', 'true');
                    submitText.textContent = @json(__('Signing in…'));

                    try {
                        var bridge = window.flutter_inappwebview;
                        if (!bridge || typeof bridge.callHandler !== 'function') {
                            throw new Error(@json(__('The ONEPOS app connection is unavailable.')));
                        }

                        var result = await bridge.callHandler('oneposAuth', mode === 'demo'
                            ? { mode: 'demo' }
                            : {
                                mode: 'user',
                                baseUrl: baseUrl.value,
                                identifier: identifier.value,
                                password: password.value
                            });

                        if (!result || result.ok !== true) {
                            throw new Error(result && result.message ? result.message : @json(__('Sign-in failed. Please try again.')));
                        }

                        password.value = '';
                    } catch (error) {
                        errorText.textContent = error && error.message ? error.message : @json(__('Sign-in failed. Please try again.'));
                        errorBox.hidden = false;
                    } finally {
                        button.disabled = false;
                        button.classList.remove('is-loading');
                        button.removeAttribute('aria-busy');
                        submitText.textContent = mode === 'demo' ? @json(__('Continue as Guest')) : @json(__('Sign in'));
                    }
                });

                setMode('user');
                return;
            }

            form.addEventListener('submit', function (event) {
                if (!this.reportValidity()) {
                    event.preventDefault();
                    return;
                }

                var button = document.getElementById('submitButton');
                button.disabled = true;
                button.classList.add('is-loading');
                button.setAttribute('aria-busy', 'true');
                document.getElementById('submitButtonText').textContent = @json(__('Signing in…'));
            });
        })();
    </script>
</body>

</html>

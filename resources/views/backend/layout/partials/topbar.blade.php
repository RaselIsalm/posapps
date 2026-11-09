<header class="page-header row">
    <div class="logo-wrapper d-flex align-items-center col-auto">
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none header-brand">
            @if(!empty($general_settings['logo']) && $general_settings['logo'] !== '')
                <img class="light-logo img-fluid" style="max-height: 32px" src="{{ asset('storage/settings/' . $general_settings['logo']) }}" alt="{{ $general_settings['company_name'] ?? 'OMS' }}" onerror="this.hidden=true;this.nextElementSibling.hidden=false;" />
                <span class="topbar-logo-text" hidden>{{ $general_settings['company_name'] ?? 'OMS' }}</span>
            @else
                <h4 class="mb-0 text-primary font-weight-bold topbar-logo-text">{{ $general_settings['company_name'] ?? 'OMS' }}</h4>
            @endif
        </a>
        <a class="close-btn toggle-sidebar" href="javascript:void(0)" aria-label="Toggle sidebar">
            <img src="{{ asset('backend/assets/svg/sidebar-grid.svg') }}" alt="" class="sidebar-grid-icon" width="18" height="18">
        </a>
    </div>
    <div class="page-main-header col position-relative">
        <div class="header-left">
            <form class="form-inline search-full col" action="#" method="get">
                <div class="form-group w-100">
                    <div class="Typeahead Typeahead--twitterUsers">
                        <div class="u-posRelative">
                            <input class="demo-input Typeahead-input form-control-plaintext w-100" type="text"
                                placeholder="Search Admiro .." name="q" title="" autofocus="autofocus" />
                            <div class="spinner-border Typeahead-spinner" role="status"><span
                                    class="sr-only">Loading...</span></div><i class="close-search" data-feather="x"></i>
                        </div>
                        <div class="Typeahead-menu"></div>
                    </div>
                </div>
            </form>
            <div class="form-group-header d-none">
                <div class="Typeahead Typeahead--twitterUsers">
                    <div class="u-posRelative d-flex align-items-center">
                        <input class="demo-input py-0 Typeahead-input form-control-plaintext w-100" type="text"
                            placeholder="Type to Search..." name="q" title="" /><i
                            class="search-bg iconly-Search icli"></i>
                    </div>
                    <div class="Typeahead-menu"></div>
                </div>
            </div>
        </div>
        <div class="nav-right">
            @include('backend.layout.partials.language_switcher')
            <ul class="header-right">
                @can('manage_general_settings')
                    <li class="header-clear-cache">
                        <a href="{{ route('clear-cache') }}" title="{{ __('Clear Cache') }}">
                            <i class="fa-solid fa-broom"></i>
                        </a>
                    </li>
                @endcan
                @canany(['view_quick_sale', 'create_quick_sale'])
                    <li class="header-quick-sale"><a href="{{ route('quick-sale.index') }}" title="{{ __('Quick Sale') }}">
                            <svg>
                                <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#cart-icon"></use>
                            </svg></a>
                    </li>
                @endcan
                <li class="header-tour-btn">
                    <a href="javascript:void(0)" id="btn-pos-start-tour" title="{{ __('Start UI Tour') }}">
                        <i class="fa-solid fa-route" style="font-size: 16px; color: var(--primary-color);"></i>
                    </a>
                </li>
                <li>
                    <a href="{{ route('help.index') }}" title="{{ __('Help & FAQ') }}" id="help-guide-menu-top">
                        <i class="fa-solid fa-circle-question" style="font-size: 16px; color: var(--primary-color);"></i>
                    </a>
                </li>
                <li class="custom-dropdown notification-dropdown-wrapper">
                    <a href="javascript:void(0)">
                        <svg>
                            <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#notification"></use>
                        </svg>
                    </a>
                    <span class="badge rounded-pill badge-primary notification-count" style="display: none;">0</span>
                    <div class="custom-menu notification-dropdown py-0 overflow-hidden">
                        <h3 class="title bg-primary-light dropdown-title">
                            {{ __('Notification') }}
                            <a href="{{ route('notifications.index') }}" class="font-primary float-end">{{ __('View all') }}</a>
                        </h3>
                        <ul class="activity-timeline" id="notification-list">
                            <li class="p-3 text-center no-notification-msg">
                                <p class="mb-0 text-muted">{{ __('No new notifications') }}</p>
                            </li>
                        </ul>
                        <div class="dropdown-footer text-center p-2 border-top" id="mark-all-read-container" style="display: none;">
                            <a href="javascript:void(0)" onclick="markAllNotificationsAsRead()" class="text-primary small fw-bold">{{ __('Mark all as read') }}</a>
                        </div>
                    </div>
                </li>


                <li class="d-none d-md-flex"><a class="full-screen" href="javascript:void(0)">
                        <svg>
                            <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#scanfull"></use>
                        </svg></a>
                </li>
                <li class="profile-nav custom-dropdown" role="button" tabindex="0" aria-label="Account menu">
                    <div class="user-wrap">
                        <div class="user-img">
                            @php
                                $profileAvatarUrl = auth()->user()->profile_photo_url;
                                $profileAvatarInitial = mb_strtoupper(mb_substr(trim((string) auth()->user()->name), 0, 1)) ?: '?';
                            @endphp
                            @if ($profileAvatarUrl)
                                <img src="{{ $profileAvatarUrl }}" alt="" width="36" height="36"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='grid';" />
                                <span class="user-avatar-initial" style="display: none;">{{ $profileAvatarInitial }}</span>
                            @else
                                <span class="user-avatar-initial">{{ $profileAvatarInitial }}</span>
                            @endif
                        </div>
                        <div class="user-content">
                            <h6 class="mb-0 text-truncate" title="{{ Auth::user()->name }}">{{ Auth::user()->name }}</h6>
                            <p class="mb-0 text-truncate" title="{{ Auth::user()->getRoleNames()->first() ?? 'Admin' }}">
                                <span class="text-truncate">{{ Auth::user()->getRoleNames()->first() ?? 'Admin' }}</span>
                                <i class="fa-solid fa-chevron-down"></i>
                            </p>
                        </div>
                    </div>
                    <div class="custom-menu overflow-hidden">
                        <ul class="profile-body">
                            <li class="d-flex d-md-none profile-lang-row">
                                <span class="profile-lang-label">{{ __('Language') }}</span>
                                <div class="language-switcher" role="group" aria-label="{{ __('Language') }}">
                                    <a href="{{ route('locale.switch', 'en') }}"
                                        class="language-switcher-btn {{ app()->getLocale() === 'en' ? 'is-active' : '' }}">EN</a>
                                    <a href="{{ route('locale.switch', 'bn') }}"
                                        class="language-switcher-btn {{ app()->getLocale() === 'bn' ? 'is-active' : '' }}">বাংলা</a>
                                </div>
                            </li>
                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#Profile"></use>
                                </svg><a class="ms-2" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a>
                            </li>
                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#Setting"></use>
                                </svg><a class="ms-2" href="{{ route('settings.general') }}">{{ __('Settings') }}</a>
                            </li>
                            <li class="d-flex">
                                <svg class="svg-color">
                                    <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#Shield"></use>
                                </svg><a class="ms-2" href="{{ route('privacy') }}">{{ __('Privacy policy') }}</a>
                            </li>
                            <li class="d-flex">
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                                <svg class="svg-color" onclick="document.getElementById('logout-form').submit()">
                                    <use href="{{ url('backend') }}/assets/svg/iconly-sprite.svg#Login"></use>
                                </svg><a class="ms-2" onclick="document.getElementById('logout-form').submit()">{{ __('Log Out') }}</a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="header-live-clock d-flex" title="Local time ({{ $general_settings['timezone'] ?? 'Europe/Madrid' }})">
        <span class="header-live-clock-pill">
            <i class="fas fa-clock me-1 text-primary"></i>
            <span class="app-live-clock-text" data-timezone="{{ $general_settings['timezone'] ?? 'Europe/Madrid' }}"></span>
        </span>
    </div>
</header>
<script>
(function () {
    const timezone = @json($general_settings['timezone'] ?? 'Europe/Madrid');

    function makeFormatter() {
        const compact = window.matchMedia('(max-width: 767.98px)').matches;
        return new Intl.DateTimeFormat('en-GB', compact ? {
            timeZone: timezone,
            day: '2-digit',
            month: 'short',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        } : {
            timeZone: timezone,
            weekday: 'short',
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false,
        });
    }

    let formatter = makeFormatter();
    window.addEventListener('resize', function () {
        formatter = makeFormatter();
    });

    function tick() {
        const text = formatter.format(new Date()).replace(',', '');
        document.querySelectorAll('.app-live-clock-text').forEach(function (el) {
            el.textContent = text;
        });
    }

    tick();
    setInterval(tick, 1000);
})();
</script>

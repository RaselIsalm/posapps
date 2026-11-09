@auth
    <nav class="mobile-bottom-nav" aria-label="{{ __('Main navigation') }}">
        @can('view_dashboard')
            <a class="mobile-bottom-nav__item {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <i class="fa-solid fa-house" aria-hidden="true"></i>
                <span>{{ __('Dashboard') }}</span>
            </a>
        @else
            <span class="mobile-bottom-nav__spacer" aria-hidden="true"></span>
        @endcan

        @can('view_products')
            <a class="mobile-bottom-nav__item {{ request()->routeIs('products.*', 'product.*', 'categories.*', 'brands.*', 'units.*') ? 'is-active' : '' }}"
                href="{{ route('products.index') }}"
                @if(request()->routeIs('products.*', 'product.*', 'categories.*', 'brands.*', 'units.*')) aria-current="page" @endif>
                <i class="fa-solid fa-boxes-stacked" aria-hidden="true"></i>
                <span>{{ __('Products') }}</span>
            </a>
        @else
            <span class="mobile-bottom-nav__spacer" aria-hidden="true"></span>
        @endcan

        @canany(['view_quick_sale', 'create_quick_sale'])
            <a class="mobile-bottom-nav__item mobile-bottom-nav__item--featured {{ request()->routeIs('quick-sale.*') ? 'is-active' : '' }}"
                href="{{ route('quick-sale.index') }}"
                @if(request()->routeIs('quick-sale.*')) aria-current="page" @endif>
                <span class="mobile-bottom-nav__featured-icon">
                    <i class="fa-solid fa-cart-shopping" aria-hidden="true"></i>
                </span>
                <span class="mobile-bottom-nav__label">{{ __('Quick Sale') }}</span>
            </a>
        @else
            <span class="mobile-bottom-nav__spacer" aria-hidden="true"></span>
        @endcanany

        @can('manage_general_settings')
            <a class="mobile-bottom-nav__item {{ request()->routeIs('settings.*', 'mail-settings.*') ? 'is-active' : '' }}"
                href="{{ route('settings.general') }}"
                @if(request()->routeIs('settings.*', 'mail-settings.*')) aria-current="page" @endif>
                <i class="fa-solid fa-sliders" aria-hidden="true"></i>
                <span>{{ __('Settings') }}</span>
            </a>
        @else
            <span class="mobile-bottom-nav__spacer" aria-hidden="true"></span>
        @endcan

        @can('view_accounts')
            <a class="mobile-bottom-nav__item {{ request()->routeIs('accounts.*', 'account.*') ? 'is-active' : '' }}"
                href="{{ route('accounts.index') }}"
                @if(request()->routeIs('accounts.*', 'account.*')) aria-current="page" @endif>
                <i class="fa-solid fa-wallet" aria-hidden="true"></i>
                <span>{{ __('Accounts') }}</span>
            </a>
        @else
            <span class="mobile-bottom-nav__spacer" aria-hidden="true"></span>
        @endcan
    </nav>
@endauth

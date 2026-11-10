<aside class="page-sidebar">
    <div class="left-arrow" id="left-arrow"><i data-feather="arrow-left"></i></div>
    <div class="main-sidebar" id="main-sidebar">
        <div class="sidebar-logo d-lg-none d-flex justify-content-between align-items-center w-100" style="padding: 15px 20px; border-bottom: 1px solid #f1f1f1; margin-bottom: 10px;">
            <a href="{{ route('dashboard') }}" class="text-decoration-none">
                @if(!empty($general_settings['logo']) && $general_settings['logo'] !== '')
                    <img class="img-fluid" style="max-height: 35px" src="{{ asset('storage/settings/' . $general_settings['logo']) }}" alt="logo" />
                @else
                    <h5 class="mb-0 text-primary font-weight-bold" style="font-weight: 800; font-size: 18px;">{{ $general_settings['company_name'] ?? 'OMS' }}</h5>
                @endif
            </a>
            <a class="sidebar-mobile-close toggle-sidebar" href="javascript:void(0)" aria-label="Toggle sidebar">
                <img src="{{ asset('backend/assets/svg/sidebar-grid.svg') }}" alt="" class="sidebar-grid-icon" width="16" height="16">
            </a>
        </div>
        <ul class="sidebar-menu" id="simple-bar">
            <li class="sidebar-list sidebar-menu-search pt-3 pb-3" style="border-bottom: 1px solid #f1f1f1;">
                <div class="Typeahead Typeahead--twitterUsers" style="position: relative; width: 100%;">
                    <div class="u-posRelative d-flex align-items-center w-100" style="background: #ffffff; border-radius: 8px; padding: 10px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); border: 1px solid #eaeaea; transition: all 0.3s ease;">
                        <i class="fa-solid fa-search" style="color: #6c757d; margin-right: 12px; font-size: 14px;"></i>
                        <input class="Typeahead-input form-control-plaintext w-100" type="text"
                            placeholder="{{ __('Search menu...') }}" name="q" autocomplete="off"
                            style="border: none; background: transparent; padding: 0; font-size: 14px; outline: none; color: #2b2b2b;" />
                    </div>
                    <div class="Typeahead-menu sidebar-search-menu"></div>
                </div>
            </li>

            <li class="pin-title sidebar-main-title">
                <div>
                    <h5 class="sidebar-title f-w-700">{{ __('Pinned') }}</h5>
                </div>
            </li>
            <li class="sidebar-main-title">
                <div>
                    <h5 class="lan-1 f-w-700 sidebar-title">{{ __('General') }}</h5>
                </div>
            </li>

            @php
                $menus = \App\Helpers\SidebarMenuHelper::getSidebarMenu();
            @endphp

            @foreach ($menus as $menu)
                <li class="sidebar-list" data-tour-step="{{ $menu['id'] }}">
                    <i class="fa-solid fa-thumbtack"></i>
                    <a class="sidebar-link {{ isset($menu['locked']) && $menu['locked'] ? 'locked-menu' : '' }}"
                        id="{{ $menu['id'] }}" href="javascript:void(0)"
                        title="{{ trans($menu['title']) }}"
                        data-tour-target="{{ $menu['id'] }}">
                        {!! isset($menu['icon']) ? $menu['icon'] : '<i class="fa-solid fa-house"></i>' !!}
                        <h6>
                            {{ trans($menu['title']) }}
                            @if (isset($menu['locked']) && $menu['locked'])
                                <i class="fa-solid fa-lock" style="font-size: 11px; color: #f39c12;"></i>
                            @endif
                        </h6>
                        <i class="iconly-Arrow-Right-2 icli"></i>
                    </a>

                    @if (isset($menu['sub_menu']) && count($menu['sub_menu']) > 0)
                        <ul class="sidebar-submenu" id="{{ $menu['id'] }}-submenu">
                            <li class="sidebar-flyout-title">{{ trans($menu['title']) }}</li>
                            @foreach ($menu['sub_menu'] as $submenu)
                                <li class="summary-{{ $menu['id'] }}-menu">
                                    @if (isset($submenu['locked']) && $submenu['locked'])
                                        <a href="javascript:void(0)" class="locked-submenu" style="color: #aaa;"
                                            onclick="showUpgradeAlert()">
                                            {{ trans($submenu['title']) }}
                                            <i class="fa-solid fa-lock" style="font-size: 10px; color: #f39c12;"></i>
                                        </a>
                                    @else
                                        <a href="{{ route($submenu['route']) }}">
                                            {{ trans($submenu['title']) }}
                                        </a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach


        </ul>
    </div>
    <div class="right-arrow" id="right-arrow"><i data-feather="arrow-right"></i></div>
</aside>

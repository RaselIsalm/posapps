<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description"
        content="Admiro admin is super flexible, powerful, clean &amp; modern responsive bootstrap 5 admin template with unlimited possibilities." />
    <meta name="keywords"
        content="admin template, Admiro admin template, best javascript admin, dashboard template, bootstrap admin template, responsive admin template, web app" />
    <meta name="author" content="pixelstrap" />
    <title>@yield('title')</title>
    <script>document.cookie = "pos_lang={{ app()->getLocale() === 'bn' ? 'bn' : 'en' }};path=/;max-age=31536000;SameSite=Lax";</script>
    <!-- Favicon icon-->
    <link rel="icon" href="{{ asset('storage/settings/' . ($general_settings['favicon'] ?? 'favicon.png')) }}"
        type="image/x-icon" />
    <link rel="shortcut icon" href="{{ asset('storage/settings/' . ($general_settings['favicon'] ?? 'favicon.png')) }}"
        type="image/x-icon" />
    <!-- Google font-->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="" />
    <link
        href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@300;400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap"
        rel="stylesheet" />

    <!-- Flag icon css -->
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/vendors/flag-icon.css" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/iconly-icon.css" />
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/bulk-style.css" />
    <!-- iconly-icon-->
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/themify.css" />
    <!--fontawesome-->
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/fontawesome-min.css" />
    <!-- Whether Icon css-->
    <link rel="stylesheet" type="text/css"
        href="{{ url('backend') }}/assets/css/vendors/weather-icons/weather-icons.min.css" />
    <link rel="stylesheet" type="text/css" href="{{ url('backend') }}/assets/css/vendors/scrollbar.css" />
    <link rel="stylesheet" type="text/css" href="{{ url('backend') }}/assets/css/vendors/slick.css" />
    <link rel="stylesheet" type="text/css" href="{{ url('backend') }}/assets/css/vendors/slick-theme.css" />
    <!-- App css -->
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/quick_sale.css" />
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/style.css" />
    <link id="color" rel="stylesheet" href="{{ url('backend') }}/assets/css/color-1.css" media="screen" />
    <link rel="stylesheet" href="{{ url('backend/assets/newstyle.css') }}">

    @include('backend.layout.partials.theme-variables')
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/app-theme.css?v={{ @filemtime(public_path('backend/assets/css/app-theme.css')) ?: time() }}" />
    <link rel="stylesheet" href="{{ url('backend') }}/assets/css/app-mobile.css?v={{ @filemtime(public_path('backend/assets/css/app-mobile.css')) ?: time() }}" />

    <style>
        /* Global Layout Fixes for Footer */
        html, body { height: 100%; }
        .page-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .page-body-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .page-body {
            flex: 1 0 auto;
            min-height: auto !important; /* override the fixed calc */
        }
        .footer {
            margin-top: auto;
            flex-shrink: 0;
        }

        /* Bangla glyphs only. English keeps the theme font (Nunito Sans). */
        html[lang="bn"] body,
        html[lang="bn"] h1, html[lang="bn"] h2, html[lang="bn"] h3, html[lang="bn"] h4, html[lang="bn"] h5, html[lang="bn"] h6,
        html[lang="bn"] p, html[lang="bn"] a, html[lang="bn"] li, html[lang="bn"] label,
        html[lang="bn"] input, html[lang="bn"] button, html[lang="bn"] table {
            font-family: "Nunito Sans", "Noto Sans Bengali", sans-serif !important;
        }
        
        /* Fix for excessively bold sidebar text with Inter font */
        .sidebar-menu .sidebar-link h6 {
            font-weight: 500 !important;
            letter-spacing: 0.2px;
        }
        .sidebar-menu .sidebar-main-title h5,
        .sidebar-menu .sidebar-title {
            font-weight: 600 !important;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            font-size: 0.75rem;
        }
        .sidebar-submenu li a {
            font-weight: 400 !important;
        }
        
        /* Hide template default arrows from pagination */
        .dataTables_paginate .pagination .page-item::before,
        .dataTables_paginate .pagination .page-item::after,
        .dataTables_paginate .pagination .page-link::before,
        .dataTables_paginate .pagination .page-link::after,
        .page-item::before, .page-item::after,
        .page-link::before, .page-link::after {
            display: none !important;
        }

        /* Fix for Mobile Sidebar Scroll Issue */
        @media (max-width: 991.98px) {
            .sidebar-menu, .simplebar-content {
                padding-bottom: 120px !important;
            }
        }

        /* Dynamic Global Search Styling */
        .Typeahead-menu {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1000;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            max-height: 350px;
            overflow-y: auto;
            display: none;
            margin-top: 5px;
            border: 1px solid #f1f1f1;
        }
        .Typeahead-menu.show {
            display: block;
        }
        .search-result-item {
            padding: 12px 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            border-bottom: 1px solid #f8f9fa;
            transition: all 0.2s ease;
            text-decoration: none !important;
            color: #2b2b2b;
        }
        .search-result-item:last-child {
            border-bottom: none;
        }
        .search-result-item:hover, .search-result-item.active {
            background-color: var(--primary-light);
            color: var(--theme-default, var(--primary-color));
        }
        .search-result-item.active {
            background-color: rgba(var(--primary-rgb), 0.1);
            font-weight: 600;
        }
        .search-result-item i {
            margin-right: 12px;
            font-size: 16px;
            color: var(--theme-default, var(--primary-color));
            opacity: 0.8;
        }
        .search-empty-state {
            padding: 25px 20px;
            text-align: center;
            color: #6c757d;
        }
        .search-empty-state i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #adb5bd;
            display: block;
        }
        .search-empty-state span {
            font-size: 14px;
            font-weight: 500;
        }

        /* Sidebar specific search adjustments */
        .sidebar-search-menu {
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            margin-top: 5px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
            z-index: 1000;
            border: 1px solid #eee;
        }

        /* Back to top: always clear + scroll progress ring (theme matched) */
        .tap-top {
            display: none;
            align-items: center;
            justify-content: center;
            width: 46px !important;
            height: 46px !important;
            padding: 0 !important;
            opacity: 1 !important;
            background: #ffffff !important;
            color: var(--theme-default, var(--primary-color)) !important;
            border: none !important;
            border-radius: 50% !important;
            box-shadow: 0 4px 14px rgba(var(--primary-rgb, 48, 142, 135), 0.28) !important;
            overflow: visible !important;
            z-index: 1045 !important;
        }
        .tap-top:hover,
        .tap-top:focus {
            opacity: 1 !important;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(var(--primary-rgb, 48, 142, 135), 0.38) !important;
            outline: none;
        }
        .tap-top-progress {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            transform: rotate(-90deg);
            pointer-events: none;
        }
        .tap-top-progress-bg {
            stroke: rgba(var(--primary-rgb, 48, 142, 135), 0.18);
            stroke-width: 2.75;
        }
        .tap-top-progress-bar {
            stroke: var(--theme-default, var(--primary-color));
            stroke-width: 2.75;
            stroke-linecap: round;
            stroke-dasharray: 97.389;
            stroke-dashoffset: 97.389;
            transition: stroke-dashoffset 80ms linear;
        }
        .tap-top-icon {
            position: relative;
            z-index: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: rgba(var(--primary-rgb, 48, 142, 135), 0.08);
        }
        .tap-top i {
            line-height: 1 !important;
            font-size: 18px;
            color: var(--theme-default, var(--primary-color)) !important;
        }
    </style>

    @stack('css')

    @include('backend.layout.partials.theme-script')
    <script>
        window.AppCurrency = {
            symbol: @json(\App\Helpers\CurrencyHelper::getSymbol()),
            code: @json(\App\Helpers\CurrencyHelper::getCode()),
            position: @json(\App\Helpers\CurrencyHelper::getPosition())
        };

        function formatCurrency(amount, decimals = 2) {
            let parsedAmount = parseFloat(amount);
            if (isNaN(parsedAmount)) {
                parsedAmount = 0;
            }
            let formattedAmount = parsedAmount.toFixed(decimals);
            
            if (window.AppCurrency.position === 'suffix') {
                return formattedAmount + ' ' + window.AppCurrency.symbol;
            }
            return window.AppCurrency.symbol + ' ' + formattedAmount;
        }
    </script>
</head>

<body>
    <script>
        (function () {
            var key = 'posMobileNavSkipLoaderUntil';
            try {
                var expiresAt = Number(sessionStorage.getItem(key) || 0);
                sessionStorage.removeItem(key);
                if (expiresAt > Date.now() && window.matchMedia('(max-width: 767.98px)').matches) {
                    document.documentElement.classList.add('mobile-nav-fast-load');
                    window.addEventListener('load', function () {
                        document.documentElement.classList.remove('mobile-nav-fast-load');
                    }, { once: true });
                }
            } catch (error) {}
        })();
    </script>
    <!-- page-wrapper Start-->
    <!-- tap on top starts-->
    <div class="tap-top" id="tapTop" role="button" tabindex="0" aria-label="Back to top" title="Back to top">
        <svg class="tap-top-progress" viewBox="0 0 36 36" aria-hidden="true">
            <circle class="tap-top-progress-bg" cx="18" cy="18" r="15.5" fill="none"></circle>
            <circle class="tap-top-progress-bar" cx="18" cy="18" r="15.5" fill="none"></circle>
        </svg>
        <span class="tap-top-icon"><i class="iconly-Arrow-Up icli"></i></span>
    </div>
    <!-- tap on tap ends-->
    <!-- loader-->
    <div class="loader-wrapper">
        <div class="loader"><span></span><span></span><span></span><span></span><span></span></div>
    </div>
    <script>
        // Failsafe: hide white full-page loader even if later JS fails
        (function () {
            function forceHideLoader() {
                var nodes = document.querySelectorAll('.loader-wrapper');
                for (var i = 0; i < nodes.length; i++) {
                    nodes[i].style.display = 'none';
                    if (nodes[i].parentNode) {
                        nodes[i].parentNode.removeChild(nodes[i]);
                    }
                }
            }
            if (document.readyState === 'complete' || document.readyState === 'interactive') {
                setTimeout(forceHideLoader, 1200);
            } else {
                document.addEventListener('DOMContentLoaded', function () {
                    setTimeout(forceHideLoader, 1200);
                });
            }
            window.addEventListener('load', function () {
                setTimeout(forceHideLoader, 400);
            });
            setTimeout(forceHideLoader, 4000);
        })();
    </script>
    <div class="page-wrapper compact-wrapper" id="pageWrapper">
        @include('backend.layout.partials.topbar')
        <!-- Page Body Start-->
        <div class="page-body-wrapper">
            <!-- Page sidebar start-->
            @include('backend.layout.partials.sidebar')
            <div class="sidebar-backdrop" id="sidebarBackdrop" aria-hidden="true"></div>
            <!-- Page sidebar end-->
            <div class="page-body">

                <!-- Container-fluid starts-->
                @yield('content')
            </div>
            @include('backend.layout.partials.footer')
        </div>
    </div>

    @include('backend.layout.partials.mobile-bottom-nav')

    @include('backend.layout.partials.commonmodal')
    @include('backend.layout.partials.alert')
    @include('backend.components.invoice.shell')


    <!-- jquery-->
    <script src="{{ url('backend') }}/assets/js/vendors/jquery/jquery.min.js"></script>
    @if(app()->getLocale() === 'bn')
    <script>
        window.PosLoadingInvoice = 'ইনভয়েস লোড হচ্ছে...';
        window.PosDataTableLanguage = {
            search: 'খুঁজুন:',
            lengthMenu: 'দেখান _MENU_ টি',
            info: '_START_ থেকে _END_, মোট _TOTAL_',
            infoEmpty: 'কোনো তথ্য নেই',
            infoFiltered: '(মোট _MAX_ থেকে বাছাই)',
            zeroRecords: 'কোনো মিল পাওয়া যায়নি',
            emptyTable: 'টেবিলে কোনো তথ্য নেই',
            paginate: { first: 'প্রথম', last: 'শেষ', next: 'পরের', previous: 'আগের' },
            processing: 'লোড হচ্ছে...',
            buttons: {
                copy: 'কপি',
                csv: 'সিএসভি',
                excel: 'এক্সেল',
                pdf: 'পিডিএফ',
                print: 'প্রিন্ট',
                colvis: 'কলাম'
            }
        };
        (function () {
            var nativeAlert = window.alert;
            var nativeConfirm = window.confirm;
            function posBn(msg) {
                msg = String(msg == null ? '' : msg);
                var rules = [
                    [/^Cannot add more! Only (.+) items available in stock\.?$/, 'আর যোগ করা যাবে না! স্টকে মাত্র $1 টি আছে।'],
                    [/^Minimum purchase of (.+) required to use reward points\.?$/, 'রিওয়ার্ড পয়েন্ট ব্যবহার করতে কমপক্ষে $1 টাকার ক্রয় লাগবে।'],
                    [/^Gift Card Balance: (.+) TK$/, 'গিফট কার্ড ব্যালেন্স: $1 টাকা'],
                    [/^Success! Gift Card Active: (.+)$/, 'সফল! গিফট কার্ড চালু: $1'],
                    [/^Error: (.+)$/, 'ত্রুটি: $1'],
                    [/^Customer Group "(.+)" created!$/, 'কাস্টমার গ্রুপ "$1" তৈরি হয়েছে!'],
                    [/^Select IMEI for (.+)$/, '$1 এর আইএমইআই সিলেক্ট করুন'],
                    [/^(.+) Balance: (.+)$/, '$1 ব্যালেন্স: $2']
                ];
                for (var i = 0; i < rules.length; i++) {
                    if (rules[i][0].test(msg)) {
                        return msg.replace(rules[i][0], rules[i][1]);
                    }
                }
                return msg;
            }
            window.alert = function (msg) { nativeAlert(posBn(msg)); };
            window.confirm = function (msg) { return nativeConfirm(posBn(msg)); };
        })();
    </script>
    @endif
    <script src="{{ url('backend') }}/assets/js/bubble-loader.js?v={{ @filemtime(public_path('backend/assets/js/bubble-loader.js')) ?: time() }}"></script>
    <!-- bootstrap js-->
    <script src="{{ url('backend') }}/assets/js/vendors/bootstrap/dist/js/bootstrap.bundle.min.js" defer=""></script>
    <script src="{{ url('backend') }}/assets/js/vendors/bootstrap/dist/js/popper.min.js" defer=""></script>
    <!--fontawesome-->
    <script src="{{ url('backend') }}/assets/js/vendors/font-awesome/fontawesome-min.js"></script>
    <!-- feather-->
    <script src="{{ url('backend') }}/assets/js/vendors/feather-icon/feather.min.js"></script>
    <script src="{{ url('backend') }}/assets/js/vendors/feather-icon/custom-script.js"></script>
    <!-- sidebar -->
    <script src="{{ url('backend') }}/assets/js/sidebar.js?v={{ @filemtime(public_path('backend/assets/js/sidebar.js')) ?: time() }}"></script>
    <!-- height_equal-->
    <script src="{{ url('backend') }}/assets/js/height-equal.js"></script>
    <!-- config-->
    <script src="{{ url('backend') }}/assets/js/config.js"></script>
    <script src="{{ url('backend') }}/assets/js/global-invoice.js?v={{ @filemtime(public_path('backend/assets/js/global-invoice.js')) ?: time() }}"></script>
    <script>
        window.PosHelpUrl = @json(route('help.index'));
    </script>
    <div id="pos-tour-data" class="d-none" aria-hidden="true"
         data-help-url="{{ route('help.index') }}"
         data-tour-en='@json(config("help_guide.tour.en"))'
         data-tour-bn='@json(config("help_guide.tour.bn"))'></div>
    <a href="{{ route('help.index') }}" id="help-guide-menu" class="d-none" aria-hidden="true">Help</a>
    <script src="{{ url('backend') }}/assets/js/pos-tour.js?v={{ @filemtime(public_path('backend/assets/js/pos-tour.js')) ?: time() }}"></script>
    <!-- scrollbar-->
    <script src="{{ url('backend') }}/assets/js/scrollbar/simplebar.js"></script>
    <script src="{{ url('backend') }}/assets/js/scrollbar/custom.js"></script>
    <!-- theme_customizer-->
    @stack('js')
    @if(app()->getLocale() === 'bn')
    <script>
        (function () {
            if (!window.PosDataTableLanguage || !window.jQuery || !jQuery.fn.dataTable) {
                return;
            }
            jQuery.extend(true, jQuery.fn.dataTable.defaults, { language: window.PosDataTableLanguage });
            try {
                jQuery.fn.dataTable.tables({ api: true }).every(function () {
                    jQuery.extend(true, this.settings()[0].oLanguage, window.PosDataTableLanguage);
                });
            } catch (e) {}
            if (jQuery.fn.select2) {
                jQuery.fn.select2.defaults.set('language', {
                    noResults: function () { return 'কোনো ফলাফল নেই'; },
                    searching: function () { return 'খুঁজছে...'; },
                    inputTooShort: function () { return 'আরও লিখুন'; },
                    errorLoading: function () { return 'ফলাফল লোড করা যায়নি'; },
                    loadingMore: function () { return 'আরও লোড হচ্ছে...'; }
                });
            }
        })();
    </script>
    @endif
    <script src="{{ url('backend') }}/assets/js/datatable-export-style.js?v={{ @filemtime(public_path('backend/assets/js/datatable-export-style.js')) ?: time() }}"></script>
    <!-- tilt-->
    <script src="{{ url('backend') }}/assets/js/animation/tilt/tilt.jquery.js"></script>
    <!-- page_tilt-->
    <script src="{{ url('backend') }}/assets/js/animation/tilt/tilt-custom.js"></script>
    <!-- dashboard_1-->

    {{-- Barcode --}}
    <Script src="{{ url('backend') }}/assets/qrcode/html5-qrcode.min.js"></Script>
    <!-- custom script -->
    <script src="{{ url('backend') }}/assets/js/script.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    <script>
        /**
         * Notification System (Production Standard)
         */
        function fetchNotifications() {
            $.ajax({
                url: "{{ route('notifications.unread') }}",
                method: 'GET',
                success: function(response) {
                    updateNotificationUI(response.notifications, response.unread_count);
                }
            });
        }

        function updateNotificationUI(notifications, count) {
            const $countBadge = $('.notification-count');
            const $list = $('#notification-list');
            const $markAllContainer = $('#mark-all-read-container');

            // Update badge
            if (count > 0) {
                $countBadge.text(count).show();
                $markAllContainer.show();
            } else {
                $countBadge.hide();
                $markAllContainer.hide();
            }

            // Update list
            if (notifications.length > 0) {
                let html = '';
                notifications.forEach(notification => {
                    const timeAgo = formatTimeAgo(notification.created_at);
                    const actionUrl = (notification.data && notification.data.action_url && notification.data.action_url !== '#')
                        ? String(notification.data.action_url).replace(/"/g, '&quot;')
                        : '';
                    html += `
                        <li class="notification-item" data-id="${notification.id}" data-url="${actionUrl}">
                            <span class="notification-unread-dot" aria-hidden="true"></span>
                            <div class="notification-copy">
                                <span class="notification-time">${timeAgo}</span>
                                <p>${notification.data.message || @json(__('New notification received'))}</p>
                            </div>
                        </li>
                    `;
                });
                $list.html(html);
            } else {
                $list.html(
                    '<li class="p-3 text-center no-notification-msg"><p class="mb-0 text-muted">No new notifications</p></li>'
                );
            }
        }

        $(document).on('click', '#notification-list .notification-item', function () {
            markNotificationAsRead($(this).attr('data-id'), $(this).attr('data-url'));
        });

        function markNotificationAsRead(id, url) {
            $.ajax({
                url: `/notifications/mark-read/${id}`,
                method: 'POST',
                complete: function() {
                    if (url) {
                        window.location.href = url;
                    } else {
                        fetchNotifications();
                    }
                }
            });
        }

        function markAllNotificationsAsRead() {
            $.ajax({
                url: "{{ route('notifications.markAllRead') }}",
                method: 'POST',
                success: function() {
                    fetchNotifications();
                }
            });
        }

        function formatTimeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return @json(__('Just now'));
            if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + 'm ago';
            if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + 'h ago';
            return Math.floor(diffInSeconds / 86400) + 'd ago';
        }

        // Initialize and Poll
        $(document).ready(function() {
            fetchNotifications();
            // Check for new notifications every 60 seconds
            setInterval(fetchNotifications, 60000);
        });
    </script>
    <script>
        window.addEventListener("load", function() {
            document.body.style.overflow = '';
            document.body.style.overflowY = 'auto';
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Gather all sidebar links dynamically
            const searchItems = [];
            
            // Extract items that don't have submenus
            document.querySelectorAll('.sidebar-list > a').forEach(link => {
                if(!link.nextElementSibling || !link.nextElementSibling.classList.contains('sidebar-submenu')) {
                    const titleElement = link.querySelector('h6');
                    const title = titleElement ? titleElement.innerText.trim() : link.innerText.trim();
                    const url = link.getAttribute('href');
                    const isLocked = link.classList.contains('locked-menu');
                    
                    if(url && url !== 'javascript:void(0)' && !isLocked && title) {
                        searchItems.push({
                            title: title,
                            url: url,
                            icon: '<i class="fa-solid fa-link"></i>'
                        });
                    }
                }
            });

            // Extract items that are inside submenus
            document.querySelectorAll('.sidebar-submenu > li > a').forEach(link => {
                const title = link.innerText.trim();
                const url = link.getAttribute('href');
                const isLocked = link.classList.contains('locked-submenu');
                
                const parentTitleElement = link.closest('.sidebar-list').querySelector('.sidebar-link h6');
                const parentTitle = parentTitleElement ? parentTitleElement.innerText.trim() : '';
                
                if(url && url !== 'javascript:void(0)' && !isLocked && title) {
                    searchItems.push({
                        title: `${parentTitle} > ${title}`,
                        url: url,
                        icon: '<i class="fa-solid fa-caret-right"></i>'
                    });
                }
            });

            // 2. Setup Search Logic for all search inputs
            const searchInputs = document.querySelectorAll('.Typeahead-input');
            
            searchInputs.forEach(searchInput => {
                const searchMenu = searchInput.closest('.Typeahead').querySelector('.Typeahead-menu');
                let currentIndex = -1;

                if(!searchMenu) return;

                searchInput.addEventListener('input', function(e) {
                    const query = e.target.value.toLowerCase().trim();
                    currentIndex = -1;
                    
                    if(query.length === 0) {
                        searchMenu.classList.remove('show');
                        searchMenu.innerHTML = '';
                        return;
                    }

                    const results = searchItems.filter(item => item.title.toLowerCase().includes(query));
                    renderResults(results, query, searchMenu);
                });

                // Show dropdown when clicking input if there's text
                searchInput.addEventListener('click', function() {
                    if(this.value.trim().length > 0 && searchMenu.innerHTML !== '') {
                        searchMenu.classList.add('show');
                    }
                });

                // 3. Keyboard Navigation
                searchInput.addEventListener('keydown', function(e) {
                    const items = searchMenu.querySelectorAll('.search-result-item');
                    
                    if(searchMenu.classList.contains('show') && items.length > 0) {
                        if (e.key === 'ArrowDown') {
                            e.preventDefault();
                            currentIndex = (currentIndex + 1) % items.length;
                            updateSelection(items, currentIndex);
                        } else if (e.key === 'ArrowUp') {
                            e.preventDefault();
                            currentIndex = (currentIndex - 1 + items.length) % items.length;
                            updateSelection(items, currentIndex);
                        } else if (e.key === 'Enter') {
                            e.preventDefault();
                            if (currentIndex >= 0 && items[currentIndex]) {
                                window.location.href = items[currentIndex].getAttribute('href');
                            } else if(items.length > 0) {
                                window.location.href = items[0].getAttribute('href');
                            }
                        } else if (e.key === 'Escape') {
                            searchMenu.classList.remove('show');
                            searchInput.blur();
                        }
                    }
                });
            });

            // Hide dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if(!e.target.closest('.Typeahead')) {
                    document.querySelectorAll('.Typeahead-menu').forEach(menu => menu.classList.remove('show'));
                }
            });

            function updateSelection(items, currentIndex) {
                items.forEach((item, index) => {
                    if (index === currentIndex) {
                        item.classList.add('active');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('active');
                    }
                });
            }

            function renderResults(results, query, searchMenu) {
                searchMenu.classList.add('show');
                
                if(results.length === 0) {
                    searchMenu.innerHTML = `
                        <div class="search-empty-state">
                            <i class="fa-solid fa-search"></i>
                            <span>No result to show</span>
                        </div>
                    `;
                    return;
                }

                let html = '';
                results.forEach((result, index) => {
                    // Highlight matching text using regex
                    const regex = new RegExp(`(${query})`, "gi");
                    const highlightedTitle = result.title.replace(regex, "<span class='text-primary fw-bold'>$1</span>");
                    
                    html += `
                        <a href="${result.url}" class="search-result-item ${index === 0 ? 'active' : ''}">
                            ${result.icon}
                            <span>${highlightedTitle}</span>
                        </a>
                    `;
                });
                searchMenu.innerHTML = html;
            }
        });
    </script>

    <script src="{{ url('backend') }}/assets/js/mobile-bottom-nav.js?v={{ @filemtime(public_path('backend/assets/js/mobile-bottom-nav.js')) ?: time() }}"></script>

</body>

</html>

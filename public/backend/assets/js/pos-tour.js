/**
 * POS UI Tour — Driver.js walkthrough (EN / BN).
 * Highlights real sidebar module rows + topbar help.
 * Start: window.PosTour.start('en'|'bn')
 */
(function (window, document) {
    'use strict';

    var DRIVER_CSS = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.css';
    var DRIVER_JS = 'https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js';
    var loading = null;
    var activeDriver = null;

    function loadScript(src) {
        return new Promise(function (resolve, reject) {
            if (document.querySelector('script[src="' + src + '"]')) {
                resolve();
                return;
            }
            var s = document.createElement('script');
            s.src = src;
            s.onload = resolve;
            s.onerror = reject;
            document.head.appendChild(s);
        });
    }

    function loadCss(href) {
        if (document.querySelector('link[href="' + href + '"]')) return;
        var l = document.createElement('link');
        l.rel = 'stylesheet';
        l.href = href;
        document.head.appendChild(l);
    }

    function ensureDriver() {
        if (window.driver && window.driver.js && window.driver.js.driver) {
            return Promise.resolve(window.driver.js.driver);
        }
        if (!loading) {
            loadCss(DRIVER_CSS);
            loading = loadScript(DRIVER_JS).then(function () {
                return window.driver.js.driver;
            });
        }
        return loading;
    }

    function tourDataEl() {
        return document.getElementById('help-guide-root') || document.getElementById('pos-tour-data');
    }

    function getCopy(lang) {
        var root = tourDataEl();
        var fallback = {
            en: {
                welcome: { title: 'Welcome to ONEPOS', desc: 'This tour highlights each module in the left sidebar. Use Next, or Skip anytime.' },
                sidebar: { title: 'Menu search', desc: 'Type here to quickly find any module.' },
                dashboard: { title: 'Dashboard', desc: 'Business snapshot: sales, purchases, profit, dues. Use date filters at the top of the page.' },
                products: { title: 'Products', desc: 'Product list, create, bulk import, categories, brands, barcode.' },
                purchase: { title: 'Purchase', desc: 'Buy stock from suppliers. Status Received increases inventory.' },
                sale: { title: 'Sale / POS', desc: 'Quick Sale billing, Sale List, coupons, gift cards, delivery.' },
                stock: { title: 'Stock', desc: 'Branch-wise inventory and stock history.' },
                returns: { title: 'Returns', desc: 'Sale return ↑ stock on approve. Purchase return ↓ stock on approve.' },
                transfers: { title: 'Transfers', desc: 'Move stock between branches (global qty unchanged).' },
                accounts: { title: 'Accounts', desc: 'Cash/bank accounts, statement, expenses, balance sheet.' },
                reports: { title: 'Reports', desc: 'Summary, sales, purchases, ledgers, inventory, branch reports.' },
                settings: { title: 'Settings', desc: 'Company, colors, branches, roles, users, units, currencies.' },
                help: { title: 'Help Center', desc: 'Open Help Center anytime for module guides and FAQ (EN / বাংলা). Also available from the ? icon in the header.' },
                peoples: { title: 'Peoples', desc: 'Customers, groups, suppliers.' },
                quotations: { title: 'Quotations', desc: 'Quotes that convert to sale or purchase.' },
                hrm: { title: 'HRM', desc: 'Employees, attendance, payroll.' },
                crm: { title: 'CRM', desc: 'Leads and convert to customer.' },
                sms: { title: 'SMS', desc: 'Templates and logs.' },
                api: { title: 'API Settings', desc: 'SMS, courier, fraud-check APIs.' },
                clock: { title: 'Live clock', desc: 'Shop timezone from General Settings.' },
                posSearch: { title: 'Scan or search', desc: 'Search or scan barcode on phone.' },
                posCart: { title: 'Cart & Pay', desc: 'Customer, tax, then Pay.' },
                posMobileCart: { title: 'Open cart', desc: 'Tap to open the cart on mobile.' },
                done: { title: 'You are ready!', desc: 'Recommended path: Settings → Products → Purchase → Quick Sale.' }
            },
            bn: {
                welcome: { title: 'ONEPOS-এ স্বাগতম', desc: 'এই ট্যুর বাম সাইডবারের প্রতিটি মডিউল দেখাবে। Next চাপুন, অথবা যেকোনো সময় Skip করুন।' },
                sidebar: { title: 'মেনু সার্চ', desc: 'এখানে টাইপ করে যেকোনো মডিউল দ্রুত খুঁজুন।' },
                dashboard: { title: 'ড্যাশবোর্ড', desc: 'বিক্রি, ক্রয়, লাভ, বাকি। পেজের উপরে ডেট ফিল্টার ব্যবহার করুন।' },
                products: { title: 'প্রোডাক্ট', desc: 'লিস্ট, তৈরি, বাল্ক ইমপোর্ট, ক্যাটাগরি, ব্র্যান্ড, বারকোড।' },
                purchase: { title: 'পারচেজ', desc: 'সাপ্লায়ার থেকে ক্রয়। Received হলে স্টক বাড়ে।' },
                sale: { title: 'সেল / POS', desc: 'Quick Sale বিলিং, Sale List, কুপন, গিফট কার্ড, ডেলিভারি।' },
                stock: { title: 'স্টক', desc: 'ব্রাঞ্চভিত্তিক ইনভেন্টরি ও স্টক হিস্টরি।' },
                returns: { title: 'রিটার্ন', desc: 'সেল রিটার্ন Approve-এ স্টক ↑। পারচেজ রিটার্ন Approve-এ স্টক ↓।' },
                transfers: { title: 'ট্রান্সফার', desc: 'ব্রাঞ্চের মধ্যে স্টক সরান (মোট qty একই)।' },
                accounts: { title: 'অ্যাকাউন্ট', desc: 'ক্যাশ/ব্যাংক, স্টেটমেন্ট, খরচ, ব্যালেন্স শিট।' },
                reports: { title: 'রিপোর্ট', desc: 'সারাংশ, সেল, পারচেজ, লেজার, ইনভেন্টরি, ব্রাঞ্চ।' },
                settings: { title: 'সেটিংস', desc: 'কোম্পানি, কালার, ব্রাঞ্চ, ভূমিকা, ইউজার, ইউনিট, কারেন্সি।' },
                help: { title: 'Help Center', desc: 'মডিউল গাইড ও FAQ ইংরেজি/বাংলায় Help Center থেকে খুলুন। হেডারে ? আইকনও আছে।' },
                peoples: { title: 'পিপলস', desc: 'কাস্টমার, গ্রুপ, সাপ্লায়ার।' },
                quotations: { title: 'কোটেশন', desc: 'অফার → পরে সেল/পারচেজ।' },
                hrm: { title: 'এইচআরএম', desc: 'কর্মচারী, উপস্থিতি, পে-রোল।' },
                crm: { title: 'সিআরএম', desc: 'লিড থেকে কাস্টমার।' },
                sms: { title: 'এসএমএস', desc: 'টেমপ্লেট ও লগ।' },
                api: { title: 'এপিআই সেটিংস', desc: 'SMS, কুরিয়ার, fraud-check।' },
                clock: { title: 'লাইভ ঘড়ি', desc: 'Settings-এর টাইমজোন।' },
                posSearch: { title: 'স্ক্যান বা সার্চ', desc: 'বারকোড সার্চ বা ক্যামেরা স্ক্যান।' },
                posCart: { title: 'কার্ট ও পে', desc: 'কাস্টমার, ট্যাক্স, তারপর Pay।' },
                posMobileCart: { title: 'কার্ট খুলুন', desc: 'মোবাইলে কার্ট খুলতে চাপুন।' },
                done: { title: 'প্রস্তুত!', desc: 'সঠিক পথ: Settings → Products → Purchase → Quick Sale।' }
            }
        };

        lang = lang === 'bn' ? 'bn' : 'en';
        try {
            if (root) {
                var raw = root.getAttribute(lang === 'bn' ? 'data-tour-bn' : 'data-tour-en');
                if (raw) return JSON.parse(raw);
            }
        } catch (e) {}
        return fallback[lang];
    }

    /** Resolve a visible menu row (li), never a hidden helper link */
    function resolveTourEl(selector) {
        if (!selector) return null;
        var el = document.querySelector(selector);
        if (!el) return null;
        if (el.classList.contains('d-none') || el.getAttribute('aria-hidden') === 'true') {
            return null;
        }
        var style = window.getComputedStyle(el);
        if (style.display === 'none' || style.visibility === 'hidden') {
            return null;
        }
        return el;
    }

    function scrollSidebarTarget(el) {
        if (!el) return;
        try {
            el.scrollIntoView({ block: 'center', inline: 'nearest', behavior: 'instant' });
        } catch (e) {
            el.scrollIntoView(true);
        }
        var wrapper = document.querySelector('#main-sidebar .simplebar-content-wrapper')
            || document.querySelector('.page-sidebar .simplebar-content-wrapper');
        if (wrapper && el.offsetParent) {
            var er = el.getBoundingClientRect();
            var wr = wrapper.getBoundingClientRect();
            if (er.top < wr.top || er.bottom > wr.bottom) {
                wrapper.scrollTop += (er.top + er.bottom) / 2 - (wr.top + wr.bottom) / 2;
            }
        }
    }

    function isMobile() {
        return window.innerWidth < 992;
    }

    function openSidebarForTour() {
        var wrapper = document.getElementById('pageWrapper');
        if (!wrapper || !isMobile()) return;
        if (window.innerWidth < 768) {
            wrapper.classList.add('mobile-sidebar-open');
        } else if (!wrapper.classList.contains('sidebar-open')) {
            wrapper.classList.add('sidebar-open');
        }
    }

    function popoverSide() {
        return isMobile() ? 'bottom' : 'right';
    }

    function buildSteps(copy, helpUrl) {
        var steps = [];
        var side = popoverSide();

        var searchEl = resolveTourEl('.page-sidebar .sidebar-list .Typeahead')
            || resolveTourEl('.page-sidebar .sidebar-list:first-child');
        if (searchEl && copy.welcome) {
            steps.push({
                element: searchEl,
                popover: {
                    title: copy.welcome.title,
                    description: copy.welcome.desc,
                    side: side,
                    align: 'start'
                }
            });
        }

        if (searchEl && copy.sidebar) {
            steps.push({
                element: searchEl,
                popover: {
                    title: copy.sidebar.title,
                    description: copy.sidebar.desc,
                    side: side,
                    align: 'start'
                }
            });
        }

        var map = [
            ['dashboard-menu', 'dashboard'],
            ['products-menu', 'products'],
            ['peoples-menu', 'peoples'],
            ['purchase-menu', 'purchase'],
            ['quick-sale-menu', 'sale'],
            ['inventory-menu', 'stock'],
            ['return-menu', 'returns'],
            ['transfers-menu', 'transfers'],
            ['quotations-menu', 'quotations'],
            ['accounts-menu', 'accounts'],
            ['hrm-menu', 'hrm'],
            ['crm-menu', 'crm'],
            ['reports-menu', 'reports'],
            ['sms-menu', 'sms'],
            ['api-settings-menu', 'api'],
            ['settings-menu', 'settings']
        ];

        map.forEach(function (pair) {
            var menuId = pair[0];
            var key = pair[1];
            if (!copy[key]) return;

            var el = resolveTourEl('[data-tour-step="' + menuId + '"]')
                || resolveTourEl('#' + menuId);
            if (!el) return;

            // Prefer highlighting the full menu row
            if (el.id === menuId) {
                el = el.closest('.sidebar-list') || el;
            }

            steps.push({
                element: el,
                popover: {
                    title: copy[key].title,
                    description: copy[key].desc,
                    side: side,
                    align: isMobile() ? 'start' : 'center'
                }
            });
        });

        var clockEl = resolveTourEl('.header-live-clock');
        if (clockEl && copy.clock) {
            steps.push({
                element: clockEl,
                popover: {
                    title: copy.clock.title,
                    description: copy.clock.desc,
                    side: 'bottom',
                    align: 'center'
                }
            });
        }

        var posSearch = resolveTourEl('[data-tour-step="pos-search"]') || resolveTourEl('#searchInput');
        if (posSearch && copy.posSearch) {
            steps.push({
                element: posSearch,
                popover: {
                    title: copy.posSearch.title,
                    description: copy.posSearch.desc,
                    side: 'bottom',
                    align: 'start'
                }
            });
        }

        var posMobileCart = resolveTourEl('[data-tour-step="pos-mobile-cart"]');
        if (isMobile() && posMobileCart && copy.posMobileCart) {
            steps.push({
                element: posMobileCart,
                popover: {
                    title: copy.posMobileCart.title,
                    description: copy.posMobileCart.desc,
                    side: 'top',
                    align: 'center'
                }
            });
        } else {
            var posCart = resolveTourEl('[data-tour-step="pos-cart"]');
            if (posCart && copy.posCart && window.getComputedStyle(posCart).display !== 'none') {
                steps.push({
                    element: posCart,
                    popover: {
                        title: copy.posCart.title,
                        description: copy.posCart.desc,
                        side: isMobile() ? 'bottom' : 'left',
                        align: 'start'
                    }
                });
            }
        }

        // Help Center: prefer sidebar module, fallback to topbar icon
        var helpEl = resolveTourEl('[data-tour-step="help-center-menu"]')
            || resolveTourEl('#help-center-menu')
            || resolveTourEl('#help-guide-menu-top')
            || resolveTourEl('a[href*="/help"]')
            || resolveTourEl('#btn-pos-start-tour');
        if (helpEl && copy.help) {
            var helpTarget = helpEl.closest('.sidebar-list') || helpEl.closest('li') || helpEl;
            steps.push({
                element: helpTarget,
                popover: {
                    title: copy.help.title,
                    description: copy.help.desc,
                    side: helpEl.closest('.page-sidebar, .main-sidebar, #simple-bar') ? (isMobile() ? 'bottom' : 'right') : 'bottom',
                    align: 'start'
                }
            });
        }

        if (copy.done) {
            steps.push({
                popover: {
                    title: copy.done.title,
                    description: copy.done.desc + (helpUrl
                        ? ' <a href="' + helpUrl + '" style="color:#308e87;font-weight:700;text-decoration:underline;">Open Help</a>'
                        : '')
                }
            });
        }

        return steps;
    }

    function addSkipButton(popover, lang, driverObj) {
        if (!popover || !popover.footerButtons) return;
        if (popover.footerButtons.querySelector('.pos-tour-skip')) return;

        var skip = document.createElement('button');
        skip.type = 'button';
        skip.className = 'driver-popover-prev-btn pos-tour-skip';
        skip.textContent = lang === 'bn' ? 'স্কিপ' : 'Skip';
        skip.style.marginRight = 'auto';
        skip.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            try {
                driverObj.destroy();
            } catch (err) {}
            activeDriver = null;
            localStorage.setItem('pos_tour_seen', '1');
        });

        // Put Skip on the left of the footer
        popover.footerButtons.insertBefore(skip, popover.footerButtons.firstChild);
    }

    function notify(message) {
        if (typeof window.showAppToast === 'function') {
            window.showAppToast(message, 'warning');
        } else {
            alert(message);
        }
    }

    function start(lang) {
        lang = lang || localStorage.getItem('pos_help_lang') || 'en';
        localStorage.setItem('pos_help_lang', lang);

        if (activeDriver) {
            try { activeDriver.destroy(); } catch (e) {}
            activeDriver = null;
        }

        ensureDriver().then(function (driverFactory) {
            var root = tourDataEl();
            var helpUrl = (root && root.getAttribute('data-help-url')) || (window.PosHelpUrl || '/help');
            var copy = getCopy(lang);
            openSidebarForTour();
            var steps = buildSteps(copy, helpUrl);

            if (!steps.length) {
                notify(lang === 'bn' ? 'ট্যুরের জন্য মেনু পাওয়া যায়নি।' : 'No tour targets found in the menu.');
                return;
            }

            var driverObj = driverFactory({
                showProgress: true,
                animate: true,
                allowClose: true,
                overlayOpacity: 0.55,
                stagePadding: 8,
                stageRadius: 8,
                smoothScroll: true,
                popoverOffset: 12,
                nextBtnText: lang === 'bn' ? 'পরবর্তী' : 'Next',
                prevBtnText: lang === 'bn' ? 'আগে' : 'Previous',
                doneBtnText: lang === 'bn' ? 'শেষ' : 'Done',
                progressText: lang === 'bn' ? '{{current}} / {{total}}' : '{{current}} of {{total}}',
                steps: steps,
                onHighlighted: function (element) {
                    if (element) {
                        // Wait a tick so layout settles, then re-scroll for SimpleBar
                        setTimeout(function () {
                            scrollSidebarTarget(element);
                        }, 30);
                    }
                },
                onPopoverRender: function (popover) {
                    addSkipButton(popover, lang, driverObj);
                },
                onDestroyStarted: function () {
                    if (driverObj) {
                        driverObj.destroy();
                    }
                    activeDriver = null;
                    localStorage.setItem('pos_tour_seen', '1');
                }
            });

            activeDriver = driverObj;
            driverObj.drive();
            localStorage.setItem('pos_tour_seen', '1');
        }).catch(function () {
            notify(lang === 'bn' ? 'ট্যুর লোড করা যায়নি। ইন্টারনেট/CDN চেক করুন।' : 'Could not load tour. Check internet/CDN.');
        });
    }

    function maybeAutoStart() {
        if (localStorage.getItem('pos_tour_seen') === '1') return;
        if (!document.querySelector('[data-tour-step="dashboard-menu"], #dashboard-menu')) return;
        if (window.location.pathname.indexOf('/dashboard') === -1 && window.location.pathname !== '/') return;
        setTimeout(function () {
            var lang = (document.documentElement.getAttribute('lang') || '').toLowerCase().indexOf('bn') === 0 ? 'bn' : 'en';
            var bn = lang === 'bn';
            var ask = typeof window.showAppConfirm === 'function'
                ? window.showAppConfirm({
                    variant: 'primary',
                    icon: 'fas fa-route',
                    confirmIcon: 'fas fa-play',
                    title: bn ? 'প্রথমবার ব্যবহার করছেন?' : 'New here?',
                    text: bn ? 'এক নজরে গাইড দেখবেন? যেকোনো সময় বাদ দিতে পারবেন।' : 'Start a quick UI tour? You can skip anytime.',
                    confirmText: bn ? 'গাইড দেখুন' : 'Start Tour',
                    cancelText: bn ? 'এখন না' : 'Not Now'
                })
                : Promise.resolve(window.confirm(bn
                    ? 'প্রথমবার ব্যবহার করছেন? এক নজরে গাইড দেখবেন? যেকোনো সময় বাদ দিতে পারবেন।'
                    : 'New here? Start a quick UI tour? (You can Skip anytime)'));
            ask.then(function (ok) {
                if (ok) {
                    start(lang);
                } else {
                    localStorage.setItem('pos_tour_seen', '1');
                }
            });
        }, 800);
    }

    window.PosTour = {
        start: start,
        skip: function () {
            if (activeDriver) {
                try { activeDriver.destroy(); } catch (e) {}
                activeDriver = null;
            }
            localStorage.setItem('pos_tour_seen', '1');
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('#btn-pos-start-tour, [data-pos-start-tour]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                var pageLang = (document.documentElement.getAttribute('lang') || '').toLowerCase().indexOf('bn') === 0 ? 'bn' : 'en';
                start(btn.getAttribute('data-lang') || pageLang);
            });
        });
        maybeAutoStart();
    });
})(window, document);

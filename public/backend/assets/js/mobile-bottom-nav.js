(function () {
    var nav = document.querySelector('.mobile-bottom-nav');
    if (!nav) return;

    var idleTimer = null;
    var idleDelay = 500;
    var hideAfterDownTravel = 44;
    var showAfterUpTravel = 18;
    var userIntentLifetime = 2500;
    var mobileQuery = window.matchMedia('(max-width: 767.98px)');
    var skipLoaderKey = 'posMobileNavSkipLoaderUntil';
    var lastScrollY = window.scrollY || 0;
    var downTravel = 0;
    var upTravel = 0;
    var lastUserScrollIntentAt = 0;

    function showNav() {
        if (idleTimer) {
            window.clearTimeout(idleTimer);
            idleTimer = null;
        }

        nav.classList.remove('is-scroll-hidden');
        downTravel = 0;
        upTravel = 0;
        lastScrollY = window.scrollY || 0;
    }

    function noteUserScrollIntent() {
        lastUserScrollIntentAt = Date.now();
    }

    function scheduleScrollIdle() {
        if (idleTimer) window.clearTimeout(idleTimer);
        idleTimer = window.setTimeout(function () {
            if (nav.classList.contains('is-scroll-hidden')) {
                showNav();
            } else {
                downTravel = 0;
                upTravel = 0;
                idleTimer = null;
            }
        }, idleDelay);
    }

    function handleWindowScroll() {
        if (!mobileQuery.matches) {
            showNav();
            return;
        }

        var currentY = window.scrollY || 0;
        var delta = currentY - lastScrollY;
        lastScrollY = currentY;

        // Ignore browser scroll restoration, focus scrolling, and other tiny or programmatic shifts.
        if (Date.now() - lastUserScrollIntentAt > userIntentLifetime || Math.abs(delta) < 1) return;

        if (delta > 0) {
            downTravel += delta;
            upTravel = 0;

            if (currentY > 36 && downTravel >= hideAfterDownTravel) {
                nav.classList.add('is-scroll-hidden');
            }
        } else {
            upTravel += Math.abs(delta);
            downTravel = 0;

            if (upTravel >= showAfterUpTravel) showNav();
        }

        scheduleScrollIdle();
    }

    function activateLink(link) {
        nav.querySelectorAll('.mobile-bottom-nav__item.is-active').forEach(function (item) {
            item.classList.remove('is-active');
            item.removeAttribute('aria-current');
        });

        link.classList.add('is-active');
        link.setAttribute('aria-current', 'page');
    }

    function handleNavClick(event) {
        var link = event.target.closest('a[href]');
        if (!link || !nav.contains(link) || event.defaultPrevented) return;
        if (event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.hasAttribute('download') || (link.target && link.target !== '_self')) return;

        var destination;
        try {
            destination = new URL(link.href, window.location.href);
        } catch (error) {
            return;
        }

        if (destination.origin !== window.location.origin) return;

        activateLink(link);

        var current = new URL(window.location.href);
        if (destination.pathname === current.pathname && destination.search === current.search) {
            event.preventDefault();
            lastUserScrollIntentAt = 0;
            showNav();
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }

        lastUserScrollIntentAt = 0;

        try {
            sessionStorage.setItem(skipLoaderKey, String(Date.now() + 5000));
        } catch (error) {}

        showNav();
    }

    window.addEventListener('scroll', handleWindowScroll, { passive: true });
    window.addEventListener('wheel', noteUserScrollIntent, { passive: true });
    window.addEventListener('touchmove', noteUserScrollIntent, { passive: true });
    window.addEventListener('keydown', function (event) {
        if (event.target && event.target.closest && event.target.closest('input, textarea, select, [contenteditable="true"]')) return;
        if (['ArrowDown', 'ArrowUp', 'PageDown', 'PageUp', 'Home', 'End', ' '].includes(event.key)) {
            noteUserScrollIntent();
        }
    });
    nav.addEventListener('click', handleNavClick);

    window.addEventListener('resize', function () {
        showNav();
    }, { passive: true });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') showNav();
    });
})();

/**
 * Global bubble loader — replaces blue Bootstrap / DataTables spinners sitewide.
 * Search boxes never trigger the loader (tables, modals, and other actions still do).
 */
(function (window, $) {
    'use strict';

    if (!$) {
        return;
    }

    var loaderDepth = 0;
    var hideTimer = null;
    var shownAt = 0;
    var minVisibleMs = 450;
    var dtSkipInitial = {};
    var dtSearchSkipCounts = {};
    var dtSearchPending = {};
    var skipAjaxLoaderForSearch = 0;

    var SEARCH_INPUT_SELECTOR = [
        '.dataTables_filter input',
        '.Typeahead-input',
        'input[type="search"]',
        '.search-input',
        '.custom-search-input',
        '.help-search',
        '#searchInput',
        '#product_search',
        '#invoiceSearchInput',
        '#purchaseSearchInput',
        '#imeiSearchInput',
        'input[id*="Search"]',
        'input[id*="search"]'
    ].join(', ');

    var SEARCH_TRIGGER_SELECTOR = [
        '#btnSearchInvoice',
        '#btnSearchPurchase',
        'button[id*="Search"]',
        'button[id*="search"]'
    ].join(', ');

    function ensureLoader() {
        if (!$('.loader-wrapper').length) {
            $('body').prepend(
                '<div class="loader-wrapper dt-ajax-loader">' +
                    '<div class="loader"><span></span><span></span><span></span><span></span><span></span></div>' +
                '</div>'
            );
        }

        return $('.loader-wrapper');
    }

    function revealLoader() {
        clearTimeout(hideTimer);
        shownAt = Date.now();
        ensureLoader()
            .addClass('dt-ajax-loader')
            .stop(true, true)
            .css({ display: 'flex', opacity: 1 });

        // Never leave the white overlay stuck if ajaxComplete is missed
        clearTimeout(revealLoader._safety);
        revealLoader._safety = setTimeout(function () {
            loaderDepth = 0;
            hideBubbleLoader();
            var $loader = $('.loader-wrapper');
            if ($loader.length) {
                $loader.stop(true, true).fadeOut(200);
            }
        }, 8000);
    }

    function showBubbleLoader() {
        loaderDepth++;
        if (loaderDepth === 1) {
            revealLoader();
        }
    }

    function hideBubbleLoader() {
        loaderDepth = Math.max(0, loaderDepth - 1);
        if (loaderDepth > 0) {
            return;
        }

        var $loader = $('.loader-wrapper');
        if (!$loader.length) {
            return;
        }

        var elapsed = Date.now() - shownAt;
        var delay = Math.max(0, minVisibleMs - elapsed);
        hideTimer = setTimeout(function () {
            $loader.stop(true, true).fadeOut(200);
        }, delay);
    }

    window.showBubbleLoader = showBubbleLoader;
    window.hideBubbleLoader = hideBubbleLoader;

    function tableKey(settings) {
        if (!settings || !settings.nTable) {
            return 'default';
        }

        return settings.sTableId || settings.nTable.id || settings.nTable.getAttribute('id') || 'default';
    }

    function tableKeyFromWrapper($wrapper) {
        if (!$wrapper || !$wrapper.length) {
            return 'default';
        }

        return $wrapper.find('table.dataTable').attr('id') || 'default';
    }

    function clearTableSearchState(key) {
        if (!key) {
            return;
        }

        delete dtSearchPending[key];
        dtSearchSkipCounts[key] = 0;
    }

    function shouldSkipDtLoader(key) {
        return (dtSearchSkipCounts[key] || 0) > 0 || dtSearchPending[key] === true;
    }

    function finishDtSearchRequest(key) {
        if ((dtSearchSkipCounts[key] || 0) > 0) {
            dtSearchSkipCounts[key] = Math.max(0, dtSearchSkipCounts[key] - 1);
        }

        delete dtSearchPending[key];

        return shouldSkipDtLoader(key);
    }

    function isSearchInputElement(el) {
        if (!el || el.nodeType !== 1 || typeof el.matches !== 'function') {
            return false;
        }

        try {
            if (el.matches(SEARCH_INPUT_SELECTOR)) {
                return true;
            }

            if (el.closest('.dataTables_filter, .Typeahead, [data-search-box]')) {
                return true;
            }
        } catch (error) {
            return false;
        }

        return false;
    }

    function shouldSkipAjaxLoader() {
        if (skipAjaxLoaderForSearch > 0) {
            return true;
        }

        return isSearchInputElement(document.activeElement);
    }

    function consumeSearchAjaxSkip() {
        if (skipAjaxLoaderForSearch > 0) {
            skipAjaxLoaderForSearch = Math.max(0, skipAjaxLoaderForSearch - 1);
            return true;
        }

        return false;
    }

    $(document).on('input search keyup paste cut', '.dataTables_filter input', function () {
        dtSearchPending[tableKeyFromWrapper($(this).closest('.dataTables_wrapper'))] = true;
    });

    $(document).on('search.dt', function (e, settings) {
        var key = tableKey(settings);
        dtSearchSkipCounts[key] = (dtSearchSkipCounts[key] || 0) + 1;
    });

    $(document).on(
        'click change',
        '.dataTables_paginate .paginate_button:not(.disabled), .dataTables_length select, table.dataTable thead th, #btn-filter, #btn-reset, #filterBtn, .btn-filter, [data-table-filter]',
        function () {
            var $wrapper = $(this).closest('.dataTables_wrapper');
            if ($wrapper.length) {
                clearTableSearchState(tableKeyFromWrapper($wrapper));
            }
        }
    );

    $(document).on('click', SEARCH_TRIGGER_SELECTOR, function () {
        skipAjaxLoaderForSearch++;
    });

    $(document).on('processing.dt', function (e, settings, processing) {
        var key = tableKey(settings);

        if (!dtSkipInitial[key]) {
            if (!processing) {
                dtSkipInitial[key] = true;
            }
            return;
        }

        if (processing) {
            if (shouldSkipDtLoader(key)) {
                return;
            }

            showBubbleLoader();
            return;
        }

        if (finishDtSearchRequest(key)) {
            return;
        }

        hideBubbleLoader();
    });

    function isDataTablesRequest(settings) {
        if (!settings) {
            return false;
        }

        var data = settings.data;

        if (typeof data === 'string') {
            return /(?:^|[?&])draw=/.test(data);
        }

        if (typeof data === 'object' && data !== null) {
            return data.draw !== undefined;
        }

        return false;
    }

    function isDetailsModalRequest(settings) {
        var method = ((settings && (settings.type || settings.method)) || 'GET').toString().toUpperCase();
        if (method !== 'GET') {
            return false;
        }

        var url = (settings && settings.url ? settings.url : '').toString().split('?')[0];
        if (!url) {
            return false;
        }

        // Detail / invoice modal content loads (eye icon, row click, usage history, etc.)
        if (/\/show\//i.test(url)) {
            return true;
        }
        if (/\/history\//i.test(url)) {
            return true;
        }
        if (/\/invoice\//i.test(url)) {
            return true;
        }
        if (/employee-details\//i.test(url)) {
            return true;
        }

        return false;
    }

    function isExcludedRequest(settings) {
        var url = (settings && settings.url ? settings.url : '').toString();

        if (url.indexOf('notifications/') !== -1 || url.indexOf('notifications.unread') !== -1) {
            return true;
        }

        if (url.indexOf('_debugbar') !== -1) {
            return true;
        }

        if (settings && settings.noBubbleLoader) {
            return true;
        }

        if (settings && settings.headers && settings.headers['X-No-Bubble-Loader']) {
            return true;
        }

        if (isDetailsModalRequest(settings)) {
            return true;
        }

        return false;
    }

    function maybeShowForAjax(settings) {
        if (isExcludedRequest(settings) || isDataTablesRequest(settings) || shouldSkipAjaxLoader()) {
            return;
        }

        showBubbleLoader();
    }

    function maybeHideForAjax(settings) {
        if (isExcludedRequest(settings) || isDataTablesRequest(settings)) {
            return;
        }

        if (consumeSearchAjaxSkip() || shouldSkipAjaxLoader()) {
            return;
        }

        hideBubbleLoader();
    }

    $(document).ajaxSend(function (event, jqXHR, settings) {
        maybeShowForAjax(settings);
    });

    $(document).ajaxComplete(function (event, jqXHR, settings) {
        maybeHideForAjax(settings);
    });
})(window, window.jQuery);

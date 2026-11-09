/**
 * Global invoice modal helpers.
 * Usage: openGlobalInvoiceModal(url)
 * Resolves jQuery at call-time (pages may reload jQuery after this script).
 */
(function (window) {
    'use strict';

    function jq() {
        return window.jQuery || window.$;
    }

    function invoiceModalEl() {
        return document.getElementById('globalInvoiceModal')
            || document.getElementById('invoiceModal')
            || document.getElementById('detailsModal')
            || document.getElementById('showQuotationModal')
            || document.getElementById('returnDetailsModal')
            || document.getElementById('transferDetailsModal');
    }

    function invoiceContentEl(modalEl) {
        var scoped = modalEl
            ? (modalEl.querySelector('#global_invoice_modal_content')
                || modalEl.querySelector('#invoice_modal_content')
                || modalEl.querySelector('#invoice_modal_body')
                || modalEl.querySelector('#modal_body_content')
                || modalEl.querySelector('#modal-content-area')
                || modalEl.querySelector('#details_modal_content')
                || modalEl.querySelector('.modal-body'))
            : null;

        return scoped
            || document.getElementById('global_invoice_modal_content')
            || document.getElementById('invoice_modal_content')
            || document.getElementById('invoice_modal_body')
            || document.getElementById('modal_body_content')
            || document.getElementById('modal-content-area')
            || document.getElementById('details_modal_content');
    }

    function loadingHtml(message) {
        message = message || window.PosLoadingInvoice || 'Loading invoice...';
        return '<div class="text-center py-5"><p class="mt-2 text-muted mb-0">' + message + '</p></div>';
    }

    function showModal(el) {
        if (!el) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            window.bootstrap.Modal.getOrCreateInstance(el).show();
            return;
        }

        var $ = jq();
        if ($ && $.fn && typeof $.fn.modal === 'function') {
            $(el).modal('show');
        }
    }

    function hideModal(el) {
        if (!el) {
            return;
        }

        if (window.bootstrap && window.bootstrap.Modal) {
            var instance = window.bootstrap.Modal.getInstance(el) || window.bootstrap.Modal.getOrCreateInstance(el);
            instance.hide();
            return;
        }

        var $ = jq();
        if ($ && $.fn && typeof $.fn.modal === 'function') {
            $(el).modal('hide');
        }
    }

    window.openGlobalInvoiceModal = function (url, options) {
        options = options || {};
        var $ = jq();
        var modalEl = invoiceModalEl();
        var contentEl = invoiceContentEl(modalEl);

        if (!modalEl || !contentEl) {
            console.error('Global invoice modal markup missing.');
            return;
        }

        if (!$ || typeof $.ajax !== 'function') {
            console.error('jQuery is required to load invoice modal content.');
            return;
        }

        showModal(modalEl);
        contentEl.innerHTML = loadingHtml(options.loadingText);

        $.ajax({
            url: url,
            type: 'GET',
            noBubbleLoader: true,
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-No-Bubble-Loader': '1' },
            success: function (data) {
                contentEl.innerHTML = data;
            },
            error: function () {
                contentEl.innerHTML =
                    '<div class="text-center text-danger py-5"><h3>⚠️</h3><p>Failed to load invoice.</p></div>';
            }
        });
    };

    window.forceCloseInvoiceModal = function () {
        var modalEl = invoiceModalEl();
        hideModal(modalEl);
        setTimeout(function () {
            if (modalEl) {
                modalEl.classList.remove('show');
                modalEl.style.display = 'none';
            }
            document.querySelectorAll('.modal-backdrop').forEach(function (node) {
                node.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.paddingRight = '';
        }, 100);
    };

    window.printInvoiceSection = function () {
        var a4 = document.getElementById('a4_print_invoice_content');
        if (!a4) {
            console.error('A4 invoice content not found.');
            return;
        }

        var printContents = a4.innerHTML;
        var printWindow = window.open('', '', 'height=800,width=1000');
        printWindow.document.write('<html><head><title> </title>');
        printWindow.document.write(
            '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">'
        );
        printWindow.document.write(
            '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">'
        );
        printWindow.document.write(`
            <style>
                @page { size: A4; margin: 0; }
                @page {
                    @top-left { content: none; }
                    @top-center { content: none; }
                    @top-right { content: none; }
                    @bottom-left { content: none; }
                    @bottom-center { content: none; }
                    @bottom-right { content: none; }
                }
                html, body {
                    background: #fff !important;
                    width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .d-print-none, .btn-close { display: none !important; }
                .invoice-a4-body {
                    display: flex !important;
                    flex-direction: column !important;
                    box-sizing: border-box !important;
                    min-height: 270mm !important;
                    padding: 10mm !important;
                    margin: 0 !important;
                }
                .invoice-a4-main { flex: 1 1 auto !important; }
                .invoice-a4-footer {
                    flex-shrink: 0 !important;
                    margin-top: auto !important;
                    padding-top: 10px !important;
                    page-break-inside: avoid;
                    break-inside: avoid;
                }
                .invoice-a4-signatures {
                    page-break-inside: avoid;
                    break-inside: avoid;
                }
                table { page-break-inside: auto; }
                tr { page-break-inside: avoid; break-inside: avoid; }
                @media print {
                    html, body { margin: 0 !important; padding: 0 !important; }
                    .invoice-a4-body { min-height: 270mm !important; padding: 10mm !important; }
                }
            </style>
        `);
        printWindow.document.write('</head><body style="background:#fff;margin:0;padding:0;">');
        printWindow.document.write(printContents);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.document.title = '';

        setTimeout(function () {
            printWindow.print();
            printWindow.close();
        }, 500);
    };

    /**
     * Shared thermal print CSS (screen + print). Keep in sync with thermal_page.blade.php.
     */
    function thermalPrintStyles(size) {
        // Printable content slightly narrower than paper → equal side gaps (~4mm each on 80mm).
        var paperMm = parseFloat(size) || 80;
        var contentMm = Math.max(paperMm - 8, Math.round(paperMm * 0.9));
        var contentSize = contentMm + 'mm';

        return `
            <style>
                @media print {
                    @page { margin: 0; size: ${size} auto; }
                    @page {
                        @top-left { content: none; }
                        @top-center { content: none; }
                        @top-right { content: none; }
                        @bottom-left { content: none; }
                        @bottom-center { content: none; }
                        @bottom-right { content: none; }
                    }
                    html, body {
                        margin: 0 !important;
                        padding: 0 !important;
                        width: 100% !important;
                        height: auto !important;
                        min-height: 0 !important;
                        overflow: hidden !important;
                        background: #fff !important;
                        color: #000 !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .thermal-center-wrap {
                        width: 100% !important;
                        border-collapse: collapse !important;
                        margin: 0 !important;
                        padding: 0 !important;
                    }
                    .thermal-center-wrap > tbody > tr > td {
                        padding: 0 !important;
                        margin: 0 !important;
                        text-align: center !important;
                        vertical-align: top !important;
                    }
                    #thermal_container {
                        display: block !important;
                        width: ${contentSize} !important;
                        max-width: 92% !important;
                        margin: 0 auto !important;
                        padding: 2mm 0 !important;
                        box-sizing: border-box !important;
                        box-shadow: none !important;
                        border: none !important;
                        background: #fff !important;
                        font-weight: 600 !important;
                        font-size: 12px !important;
                        color: #000 !important;
                        text-align: left !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .thermal-receipt-body {
                        width: 100% !important;
                        max-width: 100% !important;
                        margin: 0 !important;
                        padding: 0 !important;
                        box-sizing: border-box !important;
                        font-size: 12px !important;
                        font-weight: 600 !important;
                        color: #000 !important;
                        line-height: 1.45 !important;
                        text-align: left !important;
                    }
                    .text-muted, .text-secondary, small, .small {
                        color: #000 !important;
                        opacity: 1 !important;
                        font-weight: 600 !important;
                    }
                    strong, b, .fw-bold, .font-weight-bold {
                        font-weight: 800 !important;
                        color: #000 !important;
                    }
                    .thermal-banner {
                        background: #000 !important;
                        color: #fff !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                    .thermal-banner * {
                        color: #fff !important;
                    }
                    .thermal-logo {
                        display: inline-block !important;
                        width: auto !important;
                        height: auto !important;
                        max-width: 70% !important;
                        object-fit: contain !important;
                        filter: none !important;
                        -webkit-filter: none !important;
                        -webkit-print-color-adjust: exact !important;
                        print-color-adjust: exact !important;
                    }
                }
                html, body {
                    margin: 0;
                    padding: 0;
                    width: 100%;
                    height: auto;
                    min-height: 0;
                    background: #fff;
                    color: #000;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .thermal-center-wrap {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 0;
                    padding: 0;
                }
                .thermal-center-wrap > tbody > tr > td {
                    padding: 0;
                    text-align: center;
                    vertical-align: top;
                }
                #thermal_container {
                    display: block;
                    width: ${contentSize};
                    max-width: 92%;
                    margin: 0 auto;
                    padding: 2mm 0;
                    box-sizing: border-box;
                    background-color: #fff;
                    border: none;
                    text-align: left;
                    color: #000;
                    font-weight: 600;
                    font-size: 12px;
                }
                .thermal-receipt-body {
                    width: 100%;
                    max-width: 100%;
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                    font-size: 12px;
                    font-weight: 600;
                    color: #000;
                    text-align: left;
                }
                .text-muted, .text-secondary {
                    color: #000 !important;
                    opacity: 1;
                }
                .thermal-banner {
                    background: #000 !important;
                    color: #fff !important;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
                .thermal-banner * {
                    color: #fff !important;
                }
                .thermal-logo {
                    display: inline-block;
                    width: auto;
                    height: auto;
                    max-width: 70%;
                    object-fit: contain;
                    filter: none;
                    -webkit-filter: none;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }
            </style>
        `;
    }

    /**
     * Print thermal HTML via hidden iframe (no about:blank popup).
     * With Chrome --kiosk-printing this prints silently to the default printer.
     * options: { onStart: fn, onDone: fn }
     */
    window.printThermalHtml = function (printContents, existingWindow, options) {
        options = options || {};
        var onStart = typeof options.onStart === 'function' ? options.onStart : null;
        var onDone = typeof options.onDone === 'function' ? options.onDone : null;
        var doneFired = false;
        var iframe = null;

        // Legacy popup window no longer used — close if opened by caller
        if (existingWindow && !existingWindow.closed) {
            try { existingWindow.close(); } catch (e) {}
        }

        var finish = function () {
            if (doneFired) {
                return;
            }
            doneFired = true;
            try {
                if (iframe && iframe.parentNode) {
                    iframe.parentNode.removeChild(iframe);
                }
            } catch (e) {}
            if (onDone) {
                onDone();
            }
        };

        if (!printContents) {
            console.error('Thermal print content is empty.');
            if (onDone) {
                onDone();
            }
            return;
        }

        var allowed = window.AppThermalSizes || ['58mm', '72mm', '76mm', '80mm', '100mm', '112mm'];
        var size = window.AppThermalSize || '80mm';
        if (allowed.indexOf(size) === -1) {
            size = '80mm';
        }

        if (onStart) {
            onStart();
        }

        iframe = document.createElement('iframe');
        iframe.setAttribute('title', 'Thermal print');
        iframe.setAttribute('aria-hidden', 'true');
        iframe.style.cssText = 'position:fixed;right:0;bottom:0;width:0;height:0;border:0;opacity:0;pointer-events:none;';
        document.body.appendChild(iframe);

        var idoc = iframe.contentDocument || iframe.contentWindow.document;
        idoc.open();
        idoc.write('<html><head><title> </title>');
        idoc.write(thermalPrintStyles(size));
        idoc.write('</head><body>');
        idoc.write('<table class="thermal-center-wrap" width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center" valign="top">');
        idoc.write('<div id="thermal_container">');
        idoc.write(printContents);
        idoc.write('</div></td></tr></table></body></html>');
        idoc.close();

        var waitForImages = function (doc, cb) {
            var called = false;
            var once = function () {
                if (called) {
                    return;
                }
                called = true;
                cb();
            };
            var images = Array.prototype.slice.call(doc.images || []);
            if (!images.length) {
                once();
                return;
            }
            var pending = images.length;
            var settle = function () {
                pending -= 1;
                if (pending <= 0) {
                    once();
                }
            };
            images.forEach(function (img) {
                if (img.complete) {
                    settle();
                    return;
                }
                img.addEventListener('load', settle);
                img.addEventListener('error', settle);
            });
            setTimeout(once, 2000);
        };

        waitForImages(idoc, function () {
            setTimeout(function () {
                var win = iframe.contentWindow;
                try {
                    win.onafterprint = function () {
                        finish();
                    };
                } catch (e) {}

                // Fallback if afterprint never fires (some Chromium builds)
                setTimeout(finish, 15000);

                try {
                    win.focus();
                } catch (e) {}
                try {
                    win.print();
                } catch (e) {
                    finish();
                }
            }, 300);
        });
    };

    /**
     * Print thermal from Sale List modal (#thermal_invoice_content).
     * Always uses General Settings size (window.AppThermalSize).
     */
    window.printThermalInvoiceSection = function () {
        var thermal = document.getElementById('thermal_invoice_content');
        if (!thermal) {
            console.error('Thermal invoice content not found.');
            return;
        }
        window.printThermalHtml(thermal.innerHTML);
    };

    /**
     * Fetch Sale List–identical thermal HTML and print (used by Quick Sale).
     * existingWindow is ignored (kept for call-site compatibility); printing uses a hidden iframe.
     * options: { onStart, onDone } forwarded to printThermalHtml.
     */
    window.printThermalFromUrl = function (url, existingWindow, options) {
        var $ = window.jQuery || window.$;
        var separator = url.indexOf('?') >= 0 ? '&' : '?';
        var fragmentUrl = url + separator + 'fragment=1';
        options = options || {};

        if (existingWindow && !existingWindow.closed) {
            try { existingWindow.close(); } catch (e) {}
        }

        var doPrint = function (html) {
            window.printThermalHtml(html, null, options);
        };

        var onFail = function () {
            console.error('Failed to load thermal receipt.');
            if (typeof options.onStart === 'function') {
                options.onStart();
            }
            var fallback = window.open(url, '_blank', 'noopener,noreferrer,width=480,height=720');
            setTimeout(function () {
                if (typeof options.onDone === 'function') {
                    options.onDone();
                }
            }, 4000);
            if (!fallback && typeof options.onDone === 'function') {
                options.onDone();
            }
        };

        if ($ && typeof $.ajax === 'function') {
            $.ajax({
                url: fragmentUrl,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                success: doPrint,
                error: onFail
            });
            return;
        }

        fetch(fragmentUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
        })
            .then(function (r) {
                if (!r.ok) {
                    throw new Error('bad status');
                }
                return r.text();
            })
            .then(doPrint)
            .catch(onFail);
    };
})(window);

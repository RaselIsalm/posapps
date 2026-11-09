<script>
    window.AppTheme = {
        primary: @json(\App\Helpers\ThemeHelper::getPrimaryColor()),
        hover: @json(\App\Helpers\ThemeHelper::getHoverColor()),
        light: @json(\App\Helpers\ThemeHelper::getLightColor()),
        link: @json(\App\Helpers\ThemeHelper::getLinkColor()),
        toastSuccess: @json(\App\Helpers\ThemeHelper::getToastSuccessColor()),
        rgb: @json(\App\Helpers\ThemeHelper::getRgb())
    };

    window.AppThermalSize = @json(\App\Helpers\InvoiceHelper::getThermalPrinterSize());
    window.AppThermalSizes = @json(array_keys(\App\Helpers\InvoiceHelper::thermalSizes()));

    // Prefer server theme over stale localStorage for template charts/config
    try {
        localStorage.setItem('primary', window.AppTheme.primary);
    } catch (e) {}

    /**
     * Shared floating toast helper (success uses theme toast color via CSS).
     */
    window.showAppToast = function (message, type) {
        type = type || 'success';
        var title = type === 'success' ? @json(__('Success!')) : (type === 'danger' || type === 'error' ? @json(__('Error!')) : @json(__('Warning!')));
        var alertClass = type === 'error' ? 'danger' : type;
        var alertHtml = '<div class="alert alert-' + alertClass + ' floating-alert"><strong>' + title + '</strong> ' + message + '</div>';
        if (window.jQuery) {
            jQuery('body').append(alertHtml);
            setTimeout(function () {
                jQuery('.floating-alert').last().remove();
            }, 5000);
        } else {
            var el = document.createElement('div');
            el.innerHTML = alertHtml;
            document.body.appendChild(el.firstChild);
            setTimeout(function () {
                var alerts = document.querySelectorAll('.floating-alert');
                if (alerts.length) alerts[alerts.length - 1].remove();
            }, 5000);
        }
    };

    /**
     * Shared confirm dialog (same design as the delete / clear-order confirm modals).
     * Resolves true on confirm, false on cancel / Esc / close.
     */
    window.showAppConfirm = function (options) {
        options = options || {};
        var danger = options.variant !== 'primary';
        var modalBootstrap = window.bootstrap && window.bootstrap.Modal;
        if (!modalBootstrap) {
            return Promise.resolve(window.confirm(options.text || options.title || @json(__('Are you sure?'))));
        }

        var el = document.getElementById('appConfirmModal');
        if (!el) {
            el = document.createElement('div');
            el.className = 'modal fade';
            el.id = 'appConfirmModal';
            el.tabIndex = -1;
            el.setAttribute('aria-hidden', 'true');
            el.setAttribute('aria-labelledby', 'appConfirmTitle');
            el.setAttribute('data-bs-backdrop', 'static');
            el.innerHTML =
                '<div class="modal-dialog modal-dialog-centered">' +
                    '<div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">' +
                        '<div class="modal-body p-4 text-center">' +
                            '<div class="app-confirm-icon d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 72px; height: 72px;">' +
                                '<i class="fs-3"></i>' +
                            '</div>' +
                            '<h5 class="fw-bold mb-2" id="appConfirmTitle"></h5>' +
                            '<p class="text-muted mb-4 app-confirm-text"></p>' +
                            '<div class="d-flex justify-content-center gap-2 flex-wrap">' +
                                '<button type="button" class="btn btn-outline-secondary px-4 app-confirm-cancel" data-bs-dismiss="modal"></button>' +
                                '<button type="button" class="btn px-4 app-confirm-ok"><i class="me-1"></i><span></span></button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';
            document.body.appendChild(el);
        }

        var iconWrap = el.querySelector('.app-confirm-icon');
        var icon = iconWrap.querySelector('i');
        var okBtn = el.querySelector('.app-confirm-ok');
        var iconClass = options.icon || (danger ? 'fas fa-trash-alt' : 'fas fa-question');

        iconWrap.style.backgroundColor = danger
            ? 'rgba(220, 53, 69, 0.1)'
            : 'rgba(var(--primary-rgb, 48, 142, 135), 0.1)';
        icon.className = iconClass + ' fs-3' + (danger ? ' text-danger' : '');
        icon.style.color = danger ? '' : 'var(--primary-color, #308e87)';
        el.querySelector('#appConfirmTitle').textContent = options.title || @json(__('Are you sure?'));
        var textEl = el.querySelector('.app-confirm-text');
        textEl.textContent = options.text || '';
        textEl.classList.toggle('d-none', !options.text);
        el.querySelector('.app-confirm-cancel').textContent = options.cancelText || @json(__('Cancel'));
        okBtn.className = 'btn px-4 app-confirm-ok ' + (danger ? 'btn-danger' : 'btn-primary text-white');
        okBtn.querySelector('i').className = (options.confirmIcon || iconClass) + ' me-1';
        okBtn.querySelector('span').textContent = options.confirmText || (danger ? @json(__('Yes, Delete')) : @json(__('Confirm')));

        var modal = modalBootstrap.getOrCreateInstance(el);
        return new Promise(function (resolve) {
            var confirmed = false;
            okBtn.onclick = function () {
                confirmed = true;
                modal.hide();
            };
            el.addEventListener('hidden.bs.modal', function () {
                okBtn.onclick = null;
                resolve(confirmed);
            }, { once: true });
            modal.show();
        });
    };

    /**
     * Apply theme colors to SweetAlert2 / Toastr when those libs load.
     */
    window.applyAppThemeToPlugins = function () {
        if (typeof Swal !== 'undefined' && Swal.mixin && !window.__appThemeSwalApplied) {
            var swalOpts = {
                confirmButtonColor: window.AppTheme.primary,
                cancelButtonColor: '#d33'
            };
            if ((document.documentElement.lang || '').indexOf('bn') === 0) {
                swalOpts.confirmButtonText = 'নিশ্চিত';
                swalOpts.cancelButtonText = 'বাতিল';
            }
            window.Swal = Swal.mixin(swalOpts);
            window.__appThemeSwalApplied = true;
        }
        if (typeof toastr !== 'undefined') {
            toastr.options = toastr.options || {};
            if (!document.getElementById('app-theme-toastr-style')) {
                var style = document.createElement('style');
                style.id = 'app-theme-toastr-style';
                style.textContent = '.toast-success{background-color:' + window.AppTheme.toastSuccess + '!important;}';
                document.head.appendChild(style);
            }
        }
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.applyAppThemeToPlugins();
        // Re-apply shortly after in case scripts load late
        setTimeout(window.applyAppThemeToPlugins, 500);
    });
</script>

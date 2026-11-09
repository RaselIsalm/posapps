{{-- Shared fraud-check UI: include once per page that needs Check Fraud / shipping risk. --}}
<style>
    .fraud-inline-panel {
        border: 1px solid #ebedf2;
        border-radius: 10px;
        padding: 12px 14px;
        background: #fff;
    }
    .fraud-inline-panel.is-low { border-color: #a7f3d0; background: #f0fdf4; }
    .fraud-inline-panel.is-medium { border-color: #fde68a; background: #fffbeb; }
    .fraud-inline-panel.is-high { border-color: #fecaca; background: #fef2f2; }
    .fraud-inline-panel.is-unknown { border-color: #e5e7eb; background: #f8fafc; }
    .fraud-inline-panel.is-loading,
    .fraud-inline-panel.is-error { background: #f8fafc; }
    .fraud-inline-title {
        font-size: 13px;
        font-weight: 700;
        margin: 0;
        color: #1f2937;
    }
    .fraud-inline-hint {
        font-size: 12px;
        color: #6b7280;
        margin: 2px 0 0;
        line-height: 1.4;
    }
    .fraud-stat-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 10px;
    }
    .fraud-stat {
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 8px;
        padding: 8px 6px;
        text-align: center;
    }
    .fraud-stat strong {
        display: block;
        font-size: 16px;
        line-height: 1.2;
        color: #111827;
    }
    .fraud-stat span {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: #8494b2;
    }
    .fraud-courier-row {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 10px;
    }
    .fraud-courier-chip {
        font-size: 11px;
        font-weight: 600;
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 999px;
        padding: 3px 8px;
        color: #374151;
    }
    .badge-soft-success {
        background-color: #d1fae5;
        color: #065f46;
        padding: 6px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 11px;
    }
    .badge-soft-warning {
        background-color: #fef3c7;
        color: #b45309;
        padding: 6px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 11px;
    }
    .badge-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
        padding: 6px 10px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 11px;
    }
    #fraudModal .modal-content,
    #fraudConfirmModal .modal-content {
        border-radius: 15px;
        overflow: hidden;
    }
    #fraudModal .modal-header {
        background: var(--primary-color, #308e87);
    }
    @media (max-width: 575.98px) {
        .fraud-stat-grid { gap: 6px; }
        .fraud-stat strong { font-size: 14px; }
    }
</style>

<div class="modal fade" id="fraudModal" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 text-white p-3 p-md-4">
                <div class="d-flex align-items-center min-w-0">
                    <div class="p-2 bg-white bg-opacity-25 rounded-3 me-3 d-none d-sm-flex">
                        <i class="fas fa-user-shield fa-lg"></i>
                    </div>
                    <div class="min-w-0">
                        <h5 class="modal-title fw-bold mb-0">Customer delivery record</h5>
                        <small class="opacity-75 text-truncate d-block" id="fraudPhoneLabel">Checking history...</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" id="fraudContent">
                <p class="text-muted fw-semibold mb-0 text-center py-4">Checking courier history...</p>
            </div>
            <div class="modal-footer border-0 px-3 pb-3 bg-light">
                <button type="button" class="btn btn-secondary w-100 rounded-pill fw-bold" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fraudConfirmModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-body p-4 text-center">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                    style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                    <i class="fas fa-exclamation-triangle text-danger fs-3"></i>
                </div>
                <h5 class="fw-bold mb-2">High cancel risk</h5>
                <p class="text-muted mb-4" id="fraudConfirmText">This number has a high cancel rate. Ship anyway?</p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Go back</button>
                    <button type="button" class="btn btn-danger px-4" id="fraudConfirmShipBtn">Ship anyway</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    window.FraudCheck = window.FraudCheck || (function () {
        const checkUrl = @json(route('fraud-check.ajax.check'));
        const csrf = @json(csrf_token());
        let shipState = { level: null, acknowledged: false, phone: '' };
        let activeRequest = null;

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, function (char) {
                return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char];
            });
        }

        function requestCheck(phone) {
            if (activeRequest && activeRequest.readyState !== 4) {
                activeRequest.abort();
            }
            activeRequest = $.ajax({
                url: checkUrl,
                method: 'POST',
                timeout: 10000,
                data: { _token: csrf, phone: phone }
            });
            return activeRequest;
        }

        function panelClass(level) {
            if (level === 'low') return 'is-low';
            if (level === 'medium') return 'is-medium';
            if (level === 'high') return 'is-high';
            if (level === 'unknown') return 'is-unknown';
            return '';
        }

        function courierChips(couriers) {
            if (!couriers || !couriers.length) return '';
            const chips = couriers.map(function (row) {
                return `<span class="fraud-courier-chip">${escapeHtml(row.name)} ${row.success}/${row.total}</span>`;
            }).join('');
            return `<div class="fraud-courier-row">${chips}</div>`;
        }

        function resultHtml(res, compact) {
            if (!res.success) {
                return `<p class="fraud-inline-hint mb-0">${escapeHtml(res.message || @json(__('No courier history found.')))}</p>`;
            }
            const risk = res.risk || {};
            const data = res.data || {};
            const total = data.total_parcel || 0;
            const success = data.success_parcel || 0;
            const cancel = data.cancel_parcel || 0;
            const rate = Number(risk.success_rate || 0);
            const rateLabel = risk.level === 'unknown' ? @json(__('New')) : (rate + '%');
            const rateClass = risk.color === 'secondary' ? 'bg-light text-muted border' : ('badge-soft-' + (risk.color || 'warning'));
            const labelColor = ({
                success: 'text-success',
                warning: 'text-warning',
                danger: 'text-danger',
                secondary: 'text-muted'
            })[risk.color] || 'text-dark';

            return `
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <div>
                        <p class="fraud-inline-title ${labelColor}">${escapeHtml(risk.label || 'Checked')}</p>
                        <p class="fraud-inline-hint">${escapeHtml(risk.hint || '')}</p>
                    </div>
                    <span class="badge ${rateClass}">${rateLabel}</span>
                </div>
                <div class="fraud-stat-grid">
                    <div class="fraud-stat"><strong>${total}</strong><span>Total</span></div>
                    <div class="fraud-stat"><strong class="text-success">${success}</strong><span>Success</span></div>
                    <div class="fraud-stat"><strong class="text-danger">${cancel}</strong><span>Cancel</span></div>
                </div>
                ${compact ? '' : courierChips(res.couriers)}
            `;
        }

        function renderInline(target, res) {
            const $el = $(target);
            $el.removeClass('d-none is-low is-medium is-high is-unknown is-loading is-error');
            if (!res.success) {
                $el.addClass('is-error').html(resultHtml(res, true));
                return;
            }
            $el.addClass(panelClass(res.risk && res.risk.level)).html(resultHtml(res, false));
        }

        function renderModalResult(res) {
            if (res.success && res.risk) {
                $('#fraudContent').html(`<div class="fraud-inline-panel ${panelClass(res.risk.level)}">${resultHtml(res, false)}</div>`);
                return;
            }
            if (res.success) {
                $('#fraudContent').html(`
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle fa-2x text-muted mb-3"></i>
                        <h6 class="fw-bold mb-1">No record yet</h6>
                        <p class="text-muted small mb-0 px-3">${escapeHtml(res.message || @json(__('No courier history for this number.')))}</p>
                    </div>
                `);
                return;
            }
            $('#fraudContent').html(`
                <div class="text-center py-4 text-danger">
                    <i class="fas fa-times-circle fa-2x mb-3"></i>
                    <h6 class="fw-bold mb-1">Could not check</h6>
                    <p class="text-muted small mb-0 px-3">${escapeHtml(res.message || @json(__('Check API settings and try again.')))}</p>
                </div>
            `);
        }

        function runModalCheck(phone) {
            phone = String(phone || '').trim();
            if (!phone) {
                showPhoneForm();
                $('#fraudManualPhone').addClass('is-invalid').trigger('focus');
                return;
            }

            $('#fraudPhoneLabel').text(@json(__('Checking: ')) + phone);
            $('#fraudContent').html('<p class="text-muted fw-semibold mb-0 text-center py-4">Checking courier history...</p>');

            requestCheck(phone).done(renderModalResult).fail(function (xhr, status) {
                if (status === 'abort') {
                    return;
                }
                const timedOut = status === 'timeout';
                $('#fraudContent').html(`
                    <div class="text-center py-4 text-danger">
                        <i class="fas fa-times-circle fa-2x mb-3"></i>
                        <h6 class="fw-bold">${timedOut ? @json(__('Check timed out')) : @json(__('Could not check'))}</h6>
                        <p class="small mb-0">${timedOut ? @json(__('BDCourier did not respond. Check the API key and try again.')) : @json(__('Check API settings and try again.'))}</p>
                    </div>
                `);
            });
        }

        function showPhoneForm() {
            $('#fraudPhoneLabel').text(@json(__('Enter a phone number')));
            $('#fraudContent').html(`
                <p class="text-muted small mb-3">This sale has no saved phone. Type the number to check courier history.</p>
                <label class="small fw-bold text-muted" for="fraudManualPhone">Phone Number</label>
                <div class="d-flex flex-column flex-sm-row gap-2">
                    <input type="text" class="form-control fw-bold" id="fraudManualPhone" placeholder="01XXXXXXXXX" maxlength="15" inputmode="numeric" autocomplete="off">
                    <button type="button" class="btn btn-primary fw-bold px-4" id="fraudManualCheckBtn">Check</button>
                </div>
            `);
            $('#fraudManualCheckBtn').on('click', function () {
                runModalCheck($('#fraudManualPhone').val());
            });
            $('#fraudManualPhone').on('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    runModalCheck($(this).val());
                }
            });
            setTimeout(function () {
                $('#fraudManualPhone').trigger('focus');
            }, 250);
        }

        function showModal(phone) {
            $('#fraudModal').modal('show');
            phone = String(phone || '').trim();
            if (!phone) {
                showPhoneForm();
                return;
            }
            runModalCheck(phone);
        }

        function bindShipping(options) {
            const phoneInput = options.phoneInput;
            const panel = options.panel;
            let timer = null;

            function run() {
                const phone = $(phoneInput).val();
                shipState.acknowledged = false;
                shipState.phone = phone || '';
                shipState.level = null;
                if (!phone || String(phone).replace(/\D/g, '').length < 11) {
                    $(panel).addClass('d-none').empty();
                    return;
                }
                $(panel).removeClass('d-none is-low is-medium is-high is-unknown is-error').addClass('is-loading')
                    .html('<p class="fraud-inline-hint mb-0"><i class="fas fa-spinner fa-spin me-1"></i> ' + @json(__('Checking delivery record...')) + '</p>');
                requestCheck(phone).done(function (res) {
                    if (res.configured === false) {
                        $(panel).addClass('d-none').empty();
                        return;
                    }
                    shipState.level = res.risk ? res.risk.level : null;
                    renderInline(panel, res);
                }).fail(function (xhr, status) {
                    if (status === 'abort') {
                        return;
                    }
                    shipState.level = null;
                    $(panel).removeClass('is-loading').addClass('is-error')
                        .html('<p class="fraud-inline-hint mb-0">' + (status === 'timeout'
                            ? @json(__('Check timed out. Try again or check the API key.'))
                            : @json(__('Could not check this number right now.'))) + '</p>');
                });
            }

            $(phoneInput).off('input.fraudCheck').on('input.fraudCheck', function () {
                clearTimeout(timer);
                timer = setTimeout(run, 600);
            });

            return { run: run, state: shipState };
        }

        function confirmHighRisk(onConfirm) {
            const modalEl = document.getElementById('fraudConfirmModal');
            if (!modalEl) {
                onConfirm();
                return;
            }
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            $('#fraudConfirmShipBtn').off('click.fraudConfirm').on('click.fraudConfirm', function () {
                modal.hide();
                onConfirm();
            });
            modal.show();
        }

        return {
            showModal: showModal,
            bindShipping: bindShipping,
            confirmHighRisk: confirmHighRisk,
            getShipState: function () { return shipState; }
        };
    })();

    window.checkFraud = function (phone) {
        window.FraudCheck.showModal(phone);
    };

    $(document).off('click.checkDeliveryRecord', '.js-check-delivery-record')
        .on('click.checkDeliveryRecord', '.js-check-delivery-record', function (e) {
            e.preventDefault();
            window.checkFraud($(this).attr('data-phone') || '');
        });
</script>

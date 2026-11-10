@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Dashboard
@endsection

@push('css')
<style>
    .dash-card {
        background: #fff;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.04);
        text-align: center;
        transition: all 0.3s cubic-bezier(.4,0,.2,1);
        height: 100%;
        position: relative;
        overflow: hidden;
    }
    .dash-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    .dash-card::after {
        content: '';
        position: absolute;
        top: -30px;
        right: -30px;
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(0,0,0,0.02);
        pointer-events: none;
    }
    .dash-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-bottom: 12px;
    }
    .dash-card-val {
        font-size: 1.4rem;
        font-weight: 800;
        line-height: 1.2;
        margin-bottom: 4px;
    }
    .dash-card-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .welcome-strip .filter-btn-group .btn {
        padding: 6px 16px;
        font-size: 0.8rem;
        font-weight: 600;
        border-radius: 999px;
        border: 1px solid rgba(255, 255, 255, 0.28);
        background: rgba(255, 255, 255, 0.14);
        color: #fff;
        box-shadow: none;
        transition: background 0.2s, color 0.2s, border-color 0.2s, box-shadow 0.2s;
    }
    .welcome-strip .filter-btn-group .btn:hover {
        background: rgba(255, 255, 255, 0.26);
        color: #fff;
        border-color: rgba(255, 255, 255, 0.45);
    }
    .welcome-strip .filter-btn-group .btn.active {
        background: #fff;
        color: var(--primary-color, #308e87);
        border-color: #fff;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
    }

    .nav-tabs-modern { border-bottom: 2px solid #f3f4f6; gap: 0; }
    .nav-tabs-modern .nav-link {
        border: none;
        color: #9ca3af;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 8px 16px;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        transition: all 0.2s;
    }
    .nav-tabs-modern .nav-link:hover { color: var(--primary-color, #308e87); }
    .nav-tabs-modern .nav-link.active {
        color: var(--primary-color, #308e87);
        border-bottom-color: var(--primary-color, #308e87);
        background: transparent;
    }

    .product-thumb {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .welcome-strip {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-color) 50%, var(--primary-color) 100%);
        border-radius: 16px;
        padding: 24px 32px;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .welcome-strip::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.08);
    }
    .welcome-strip::after {
        content: '';
        position: absolute;
        bottom: -60%;
        right: 15%;
        width: 200px;
        height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }

    .table thead th {
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #6b7280;
        border-bottom-width: 1px !important;
    }
    .table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
        padding: 10px 12px;
    }
    .bg-success-subtle { background-color: rgba(34,197,94,0.1) !important; }
    .bg-danger-subtle  { background-color: rgba(239,68,68,0.1) !important; }
    .bg-warning-subtle { background-color: rgba(249,115,22,0.1) !important; }

    /* Dashboard-only responsive layout; the business data and actions stay unchanged. */
    .dashboard-page {
        min-width: 0;
    }
    @media (max-width: 767.98px) {
        .dashboard-page > .container-fluid {
            padding-right: 0;
            padding-left: 0;
        }

        .dashboard-page .page-title {
            margin: 0 0 14px;
            padding-top: 8px;
        }
        .dashboard-page .page-title h2 {
            margin-bottom: 3px;
            font-size: 1.35rem;
            font-weight: 800;
            letter-spacing: -0.02em;
        }
        .dashboard-page .page-title .breadcrumb {
            margin: 8px 0 0;
        }

        .dashboard-page .row.g-3 {
            --bs-gutter-x: 0.75rem;
            --bs-gutter-y: 0.75rem;
        }

        .dashboard-page .welcome-strip {
            background: linear-gradient(135deg, var(--primary-color, #308e87) 0%, var(--primary-hover, #256e69) 100%);
            min-height: 0;
            padding: 20px;
            border-radius: 18px;
            text-align: left;
            align-items: stretch !important;
            box-shadow: 0 10px 24px rgba(var(--primary-rgb, 48, 142, 135), 0.18);
        }
        .dashboard-page .welcome-strip h4 {
            font-size: 1.2rem;
            line-height: 1.3;
        }
        .dashboard-page .welcome-strip p {
            max-width: 34ch;
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .dashboard-page .filter-btn-group {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
            gap: 8px !important;
            margin-top: 4px;
        }
        .dashboard-page .filter-btn-group .filter-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 8px 10px;
            white-space: normal;
            line-height: 1.2;
        }
        .dashboard-page .filter-btn-group .filter-btn:focus-visible {
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        .dashboard-page .nav-tabs-modern .nav-link:focus-visible {
            outline: 2px solid var(--primary-color, #308e87);
            outline-offset: 1px;
        }

        .dashboard-page .dash-card {
            display: flex;
            min-height: 132px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 14px 10px;
            border: 1px solid rgba(15, 23, 42, 0.045);
            border-radius: 17px;
            box-shadow: 0 5px 18px rgba(20, 30, 48, 0.055);
        }
        .dashboard-page .dash-card-val {
            overflow-wrap: anywhere;
            font-variant-numeric: tabular-nums;
        }
        .dashboard-page .card {
            border-color: rgba(15, 23, 42, 0.06) !important;
        }
        .dashboard-page .dash-card-icon {
            width: 40px;
            height: 40px;
            margin-bottom: 8px;
            border-radius: 13px;
            font-size: 1rem;
        }
        .dashboard-page .dash-card-val {
            max-width: 100%;
            margin-bottom: 5px;
            font-size: clamp(0.86rem, 3.7vw, 1.05rem);
            line-height: 1.25;
        }
        .dashboard-page .dash-card-label {
            font-size: 0.64rem;
            line-height: 1.35;
            letter-spacing: 0.025em;
        }

        .dashboard-page .card {
            overflow: hidden;
            border-radius: 17px !important;
            box-shadow: 0 5px 18px rgba(20, 30, 48, 0.055) !important;
        }
        .dashboard-page .card-header {
            gap: 8px;
            padding: 14px 15px 10px !important;
        }
        .dashboard-page .card-header h6 {
            min-width: 0;
            font-size: 0.92rem;
            line-height: 1.35;
        }
        .dashboard-page .card-header .badge {
            flex-shrink: 0;
        }
        .dashboard-page .card-body {
            min-width: 0;
            padding: 12px 14px 14px !important;
        }
        .dashboard-page .card-body > .table {
            width: 100%;
        }
        .dashboard-page .card-body > .table td {
            padding: 7px 3px;
            font-size: 0.8rem;
            line-height: 1.4;
            overflow-wrap: anywhere;
        }
        .dashboard-page .card-body > .table td:last-child {
            padding-left: 10px;
        }

        .dashboard-page .nav-tabs-modern {
            display: flex;
            flex-wrap: nowrap !important;
            gap: 4px;
            margin-right: -4px;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 0 4px 3px 0;
            border-bottom: 1px solid rgba(148, 163, 184, 0.22);
            scrollbar-width: none;
            -webkit-overflow-scrolling: touch;
        }
        .dashboard-page .nav-tabs-modern::-webkit-scrollbar {
            display: none;
        }
        .dashboard-page .nav-tabs-modern .nav-item {
            flex: 0 0 auto;
        }
        .dashboard-page .nav-tabs-modern .nav-link {
            display: inline-flex;
            min-height: 42px;
            align-items: center;
            padding: 8px 13px;
            border: 0;
            border-bottom: 2px solid transparent;
            margin-bottom: -1px;
            white-space: nowrap;
        }

        .dashboard-page .table-responsive {
            border-radius: 10px;
            -webkit-overflow-scrolling: touch;
        }
        .dashboard-page #trxTab + .tab-content .table-responsive > .table {
            min-width: 520px;
        }
        .dashboard-page .product-thumb {
            width: 30px;
            height: 30px;
        }

        .dark-only .dashboard-page .dash-card,
        .dark-only .dashboard-page .card {
            border-color: rgba(255, 255, 255, 0.08) !important;
            background-color: #252c38 !important;
            color: #e7ebf2;
        }
        .dark-only .dashboard-page .card-header {
            border-color: rgba(255, 255, 255, 0.08) !important;
        }
        .dark-only .dashboard-page .card-header h6,
        .dark-only .dashboard-page .text-dark {
            color: #e7ebf2 !important;
        }
        .dark-only .dashboard-page .table {
            --bs-table-color: #d5dbe5;
            --bs-table-bg: transparent;
            --bs-table-hover-color: #fff;
            --bs-table-hover-bg: rgba(255, 255, 255, 0.04);
        }
        .dark-only .dashboard-page .table-light {
            --bs-table-color: #d5dbe5;
            --bs-table-bg: #303846;
        }
    }

    @media (max-width: 575.98px) {
        .dashboard-page .welcome-strip {
            padding: 18px 16px;
        }
        .dashboard-page .welcome-strip h4 {
            font-size: 1.12rem;
        }
        .dashboard-page .welcome-strip p {
            font-size: 0.82rem;
        }
        .dashboard-page .dash-card {
            min-height: 126px;
        }
    }

    @media (max-width: 359.98px) {
        .dashboard-page .dash-card {
            min-height: 120px;
            padding-right: 7px;
            padding-left: 7px;
        }
        .dashboard-page .dash-card-val {
            font-size: 0.82rem;
        }
        .dashboard-page .dash-card-label {
            font-size: 0.59rem;
        }
    }

    @media (max-width: 767.98px) and (hover: none) {
        .dashboard-page .dash-card:hover {
            transform: none;
            box-shadow: 0 5px 18px rgba(20, 30, 48, 0.055);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .dashboard-page .dash-card,
        .dashboard-page .filter-btn,
        .dashboard-page .nav-tabs-modern .nav-link {
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')
    <div class="dashboard-page">
        @component('backend.layout.partials.bredcrumbs')
            @slot('title') Dashboard @endslot
            <li class="breadcrumb-item active">Dashboard</li>
        @endcomponent

        <div class="container-fluid">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-strip d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h4 class="fw-bold mb-1">{{ __('Welcome') }}, {{ Auth::user()->name }} 👋</h4>
                            <p class="mb-0 opacity-75">{{ __("Here's what's happening with your business") }} <span id="filter-label-text">{{ app()->getLocale() === 'bn' ? __($filterLabel) : strtolower($filterLabel) }}</span>.</p>
                        </div>
                        <div class="filter-btn-group d-flex gap-2 position-relative flex-wrap" style="z-index:2">
                            <button type="button" class="btn filter-btn {{ $filter=='today'?'active':'' }}" data-filter="today">{{ __('Today') }}</button>
                            <button type="button" class="btn filter-btn {{ $filter=='last_7_days'?'active':'' }}" data-filter="last_7_days">{{ __('Last 7 Days') }}</button>
                            <button type="button" class="btn filter-btn {{ $filter=='this_month'?'active':'' }}" data-filter="this_month">{{ __('This Month') }}</button>
                            <button type="button" class="btn filter-btn {{ $filter=='this_year'?'active':'' }}" data-filter="this_year">{{ __('This Year') }}</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary Cards --}}
            @include('backend.dashboard.cards')

            {{-- Financial Summary --}}
            @include('backend.dashboard.financials')

            {{-- Tables --}}
            @include('backend.dashboard.tables')
        </div>
    </div>
@endsection

@push('js')
    <script>
        $('#dashboard').addClass('active');
        $('#dashboard-submenu').addClass('d-block');
        $('.dashboard-menu').addClass('active');

        $(document).on('click', '.filter-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var filter = $btn.data('filter');

            // Update active state
            $('.filter-btn').removeClass('active');
            $btn.addClass('active');

            // Add loading animation to all data-key elements
            $('[data-key]').css('opacity', '0.4');

            $.ajax({
                url: '{{ url("dashboard/data") }}',
                type: 'GET',
                data: { filter: filter },
                dataType: 'json',
                success: function(data) {
                    // Update welcome subtitle
                    $('#filter-label-text').text(@json(app()->getLocale() === 'bn') ? data.filterLabel : data.filterLabel.toLowerCase());

                    // Currency settings
                    var currencySymbol = @json(\App\Helpers\CurrencyHelper::getSymbol());
                    var currencyPosition = @json(\App\Helpers\CurrencyHelper::getPosition());
                    var nonMoneyKeys = ['totalCustomers', 'totalSuppliers', 'filterLabel'];

                    // Update all elements with data-key
                    $.each(data, function(key, value) {
                        var $el = $('[data-key="' + key + '"]');
                        if ($el.length) {
                            if (nonMoneyKeys.includes(key)) {
                                $el.text(value);
                            } else {
                                // Add symbol according to position
                                var formattedValue = currencyPosition === 'suffix' 
                                    ? value + ' ' + currencySymbol 
                                    : currencySymbol + ' ' + value;
                                $el.text(formattedValue);
                            }
                        }
                    });

                    // Restore opacity with animation
                    $('[data-key]').animate({ opacity: 1 }, 300);

                    // Update URL without reload
                    window.history.replaceState({}, '', '{{ url("dashboard") }}?filter=' + filter);
                },
                error: function() {
                    $('[data-key]').css('opacity', '1');
                    showAppToast(@json(__('Failed to load data. Please try again.')), 'danger');
                }
            });
        });
    </script>
@endpush

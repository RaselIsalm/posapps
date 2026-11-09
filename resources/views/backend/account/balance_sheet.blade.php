@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Balance Sheet
@endsection

@push('css')
    <style>
        :root {
            --primary-accent: #0f172a;
            --text-main: #111827;
            --text-muted: #6b7280;
        }

        body {
            background-color: #f3f4f6;
        }

        .report-container {
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            max-width: 900px;
            margin: 0 auto;
            border: 1px solid #e5e7eb;
        }

        .report-header {
            margin-bottom: 30px;
            border-bottom: 2px solid var(--primary-accent);
            padding-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }

        .company-name {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .report-title {
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--primary-accent);
            text-transform: uppercase;
        }

        .report-date {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .section-heading {
            font-size: 1.1rem;
            font-weight: 700;
            background-color: #f1f5f9;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary-accent);
            text-transform: uppercase;
        }

        .sub-section-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #4b5563;
            margin-top: 15px;
            margin-bottom: 8px;
            padding-left: 10px;
            border-left: 3px solid #cbd5e1;
        }

        .table-report {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .table-report td {
            padding: 6px 12px;
            font-size: 0.9rem;
            border-bottom: 1px dashed #e5e7eb;
        }

        .amount-col {
            text-align: right;
            font-variant-numeric: tabular-nums;
            font-weight: 500;
            width: 130px;
        }

        .subtotal-row td {
            border-top: 1px solid #9ca3af;
            font-weight: 700;
            padding-top: 8px;
            border-bottom: none;
        }

        .grand-total-box {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            padding: 10px 12px;
            border-top: 1px solid #000;
            border-bottom: 4px double #000;
            font-weight: 800;
            font-size: 1rem;
            color: #000;
            background-color: #f8fafc;
        }

        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            html,
            body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Hide chrome: topbar, sidebar, filter bar, buttons */
            .no-print,
            .loader-wrapper,
            .tap-top,
            .sidebar-backdrop,
            .page-header,
            header.page-header,
            header,
            header *,
            .page-main-header,
            .logo-wrapper,
            .header-brand,
            .header-brand img,
            .light-logo,
            .topbar-logo-text,
            .page-sidebar,
            .sidebar,
            footer,
            .footer,
            .btn,
            .breadcrumb,
            .page-title,
            #sidebar,
            .header-navbar,
            .customizer-links,
            .theme-cutomizer {
                display: none !important;
                visibility: hidden !important;
                opacity: 0 !important;
                height: 0 !important;
                width: 0 !important;
                max-height: 0 !important;
                overflow: hidden !important;
                position: absolute !important;
                left: -9999px !important;
            }

            .page-wrapper,
            .page-body-wrapper,
            .page-body,
            .container-fluid {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: auto !important;
                overflow: visible !important;
                position: static !important;
                background: #fff !important;
            }

            #report-content {
                display: block !important;
                visibility: visible !important;
                position: static !important;
                left: auto !important;
                top: auto !important;
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                margin: 0 !important;
                page-break-inside: auto !important;
                background: #fff !important;
            }

            #report-content * {
                visibility: visible !important;
            }

            .report-header {
                margin-bottom: 10px !important;
                padding-bottom: 5px !important;
                border-bottom-width: 1px !important;
            }

            .company-name {
                font-size: 1.2rem !important;
                color: #000 !important;
            }

            .report-title {
                font-size: 1.4rem !important;
            }

            .report-date,
            .text-muted {
                color: #444 !important;
            }

            .section-heading {
                font-size: 0.9rem !important;
                padding: 4px 8px !important;
                margin-bottom: 5px !important;
                background-color: #eee !important;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .sub-section-title {
                font-size: 0.85rem !important;
                margin-top: 8px !important;
                margin-bottom: 4px !important;
                color: #000 !important;
            }

            .table-report {
                width: 100% !important;
            }

            .table-report td {
                padding: 2px 4px !important;
                font-size: 8.5pt !important;
                color: #000 !important;
                border-bottom: 1px dashed #ccc !important;
            }

            .table-report a {
                color: #000 !important;
                text-decoration: none !important;
            }

            .mb-5 {
                margin-bottom: 10px !important;
            }

            .mb-4 {
                margin-bottom: 8px !important;
            }

            .mt-5 {
                margin-top: 10px !important;
            }

            .mt-3 {
                margin-top: 5px !important;
            }

            .grand-total-box {
                margin-top: 5px !important;
                padding: 4px 0 !important;
                font-size: 0.9rem !important;
                border-bottom-width: 3px !important;
                color: #000 !important;
                background-color: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .row,
            .col-lg-10 {
                display: block !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            tr,
            td {
                page-break-inside: avoid;
            }
        }

        @media (max-width: 767.98px) {
            .report-container {
                padding: 15px;
            }

            .report-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .company-name {
                font-size: 1.2rem;
            }

            .report-meta {
                width: 100%;
                text-align: left;
            }

            .table-report td {
                font-size: 0.85rem;
                padding: 6px;
            }

            .amount-col {
                width: 100px;
            }

            .section-heading {
                font-size: 1rem;
            }

            .grand-total-box {
                font-size: 0.9rem;
                flex-direction: column;
                gap: 5px;
                text-align: right;
            }
            .grand-total-box span:first-child {
                text-align: left;
            }
        }

        .pdf-mode {
            padding: 20px !important;
            width: 100% !important;
            max-width: 100% !important;
            border: none !important;
            box-shadow: none !important;
        }

        .pdf-mode .report-header {
            margin-bottom: 10px !important;
            padding-bottom: 5px !important;
        }

        .pdf-mode .company-name {
            font-size: 16px !important;
        }

        .pdf-mode .report-title {
            font-size: 20px !important;
        }

        .pdf-mode .report-date {
            font-size: 10px !important;
        }

        .pdf-mode .section-heading {
            font-size: 12px !important;
            padding: 5px !important;
            margin-bottom: 5px !important;
        }

        .pdf-mode .mb-5 {
            margin-bottom: 10px !important;
        }

        .pdf-mode .mb-4 {
            margin-bottom: 5px !important;
        }

        .pdf-mode .ms-3 {
            margin-left: 0 !important;
        }

        .pdf-mode .table-report td {
            padding: 2px 4px !important;
            font-size: 10px !important;
        }

        .pdf-mode .grand-total-box {
            margin-top: 5px !important;
            padding: 5px !important;
            font-size: 11px !important;
        }
    </style>
@endpush

@section('content')
    <div class="no-print">
        @component('backend.layout.partials.bredcrumbs')
            @slot('title')
                Balance Sheet
            @endslot
            @slot('subtitle')
                Financial Position Snapshot
            @endslot
            <li class="breadcrumb-item active">Balance Sheet</li>
        @endcomponent
    </div>

    <div class="container-fluid py-4">
        <div class="row mb-4 no-print">
            <div class="col-12">
                <div class="card border-0 shadow-sm" style="background: #eef2f6;">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap justify-content-center align-items-center gap-3 gap-md-5">

                            <form action="{{ route('accounts.balance-sheet') }}" method="GET"
                                class="d-flex align-items-center gap-2">
                                <label class="fw-bold text-muted small text-uppercase mb-0">As Of:</label>
                                <input type="date" name="date" value="{{ $filter_date }}"
                                    class="form-control form-control-sm border-secondary shadow-none"
                                    style="width: 150px; font-weight: 600;" onchange="this.form.submit()">
                            </form>

                            <div class="d-flex gap-2">
                                <button onclick="downloadPDF()" class="btn btn-danger btn-sm text-white">
                                    <i class="fas fa-file-pdf me-1"></i> PDF
                                </button>
                                <button onclick="window.print()" class="btn btn-info btn-sm text-white">
                                    <i class="fas fa-print me-1"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="report-container" id="report-content">
            <div class="report-header">
                <div>
                    <div class="company-name">{{ $general_settings['company_name'] ?? 'OMS' }}</div>
                    <div class="text-muted small">Balance Sheet</div>
                </div>
                <div class="report-meta">
                    <div class="report-date">As of {{ date('F d, Y', strtotime($filter_date)) }}</div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-10">

                    <div class="mb-5">
                        <h4 class="section-heading">ASSETS</h4>

                        <div class="ms-3 mb-4">
                            <div class="sub-section-title">Current Assets</div>
                            <table class="table-report">
                                <tr class="fw-bold bg-light">
                                    <td colspan="2" class="ps-2 py-2 text-muted" style="font-size: 0.85rem;">Bank
                                        Accounts</td>
                                </tr>
                                @foreach ($account_balances as $acc)
                                    <tr>
                                        <td class="account-name ps-4">
                                            <a href="{{ route('accounts.statement', ['account_id' => $acc['id'] ?? null]) }}"
                                                class="text-dark text-decoration-none hover-link">
                                                {{ $acc['name'] }}
                                            </a>
                                            @if (!empty($acc['number']))
                                                <span class="text-muted small ms-1">#{{ $acc['number'] }}</span>
                                            @endif
                                        </td>
                                        <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($acc['balance'], 2) }}</td>
                                    </tr>
                                @endforeach

                                @if ($total_customer_due > 0)
                                    <tr>
                                        <td class="account-name ps-4">Accounts Receivable (A/R)</td>
                                        <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($total_customer_due, 2) }}</td>
                                    </tr>
                                @endif

                                <tr class="subtotal-row">
                                    <td class="ps-0">Total Current Assets</td>
                                    <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($total_assets, 2) }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="grand-total-box mt-3">
                            <span>TOTAL ASSETS</span>
                            <span class="amount-col">{{ \App\Helpers\CurrencyHelper::format($total_assets, 2) }}</span>
                        </div>
                    </div>


                    <div class="mb-5">
                        <h4 class="section-heading">LIABILITIES AND EQUITY</h4>

                        <div class="ms-3 mb-4">
                            <div class="sub-section-title">Liabilities</div>
                            <table class="table-report">
                                <tr class="fw-bold bg-light">
                                    <td colspan="2" class="ps-2 py-2 text-muted" style="font-size: 0.85rem;">Current
                                        Liabilities</td>
                                </tr>

                                @if ($total_supplier_due > 0)
                                    <tr>
                                        <td class="account-name ps-4">Accounts Payable (A/P)</td>
                                        <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($total_supplier_due, 2) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="account-name ps-4 text-muted fst-italic">No outstanding payables</td>
                                        <td class="amount-col">0.00</td>
                                    </tr>
                                @endif

                                <tr class="subtotal-row">
                                    <td class="ps-0">Total Liabilities</td>
                                    <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($total_liabilities, 2) }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="ms-3 mb-4">
                            <div class="sub-section-title">Equity</div>
                            <table class="table-report">
                                <tr>
                                    <td class="account-name ps-4">Retained Earnings / Net Income</td>
                                    <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($net_equity, 2) }}</td>
                                </tr>
                                <tr class="subtotal-row">
                                    <td class="ps-0">Total Equity</td>
                                    <td class="amount-col">{{ \App\Helpers\CurrencyHelper::format($net_equity, 2) }}</td>
                                </tr>
                            </table>
                        </div>

                        <div class="grand-total-box mt-3">
                            <span>TOTAL LIABILITIES AND EQUITY</span>
                            <span class="amount-col">{{ \App\Helpers\CurrencyHelper::format($total_liabilities + $net_equity, 2) }}</span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="text-center mt-5 no-print">
                <p class="text-muted small">Generated on {{ date('l, F j, Y \a\t h:i A') }}</p>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <script>
        function downloadPDF() {
            var element = document.getElementById('report-content');
            element.classList.add('pdf-mode');

            var opt = {
                margin: [0.3, 0.3, 0.3, 0.3],
                filename: 'Balance_Sheet_{{ $filter_date }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'a4',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(element).save().then(function() {
                element.classList.remove('pdf-mode');
            });
        }
    </script>
@endpush

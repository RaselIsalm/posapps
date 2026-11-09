@extends('backend.layout.main')

@section('title')
    {{ $general_settings['company_name'] ?? 'OMS' }} - Quick Sale
@endsection

@push('css')
    <style>
        :root {
            --bg-body: #f5f7fa;
            --text-dark: #2c323f;
            --text-muted: #8e9092;
            --card-radius: 15px;
        }

        .pos-container {
            height: calc(100vh - 100px);
            overflow: hidden;
            background-color: var(--bg-body);
            font-family: 'Roboto', 'Inter', sans-serif;
        }

        .cart-section {
            background: #fff;
            border-right: 1px solid #ebebeb;
            display: flex;
            flex-direction: column;
            height: 100%;
            position: relative;
            z-index: 1;
            transition: transform 0.3s ease-in-out;
        }

        .cart-header {
            padding: 15px;
            border-bottom: 1px solid #ebebeb;
        }

        .cart-items-container {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            background-color: #fff;
            display: flex;
            flex-direction: column;
        }

        .cart-items-container::-webkit-scrollbar,
        .product-section::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        .cart-items-container::-webkit-scrollbar-thumb,
        .product-section::-webkit-scrollbar-thumb {
            background: #d3d3d3;
            border-radius: 10px;
        }

        .cart-items-container::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
        }

        .cart-item {
            border: 1px solid #f1f1f1;
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 10px;
            background: #fff;
        }

        .cart-item-img {
            width: 45px;
            height: 45px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #eee;
        }

        .cart-qty-box {
            display: inline-flex;
            align-items: center;
            background: #f8f9fa;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            overflow: hidden;
            height: 28px;
        }

        .cart-qty-box .btn {
            line-height: 1;
            border: 0;
            border-radius: 0;
            color: #6c757d;
            min-width: 26px;
            padding: 0 6px;
            font-size: 14px;
        }

        .cart-qty-box .btn:hover:not(:disabled) {
            color: var(--primary-color);
            background: rgba(var(--primary-rgb), 0.08);
        }

        .cart-qty-input {
            width: 2.5ch;
            min-width: 2.5ch;
            max-width: 9ch;
            height: 100%;
            border: 0;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
            background: #fff;
            text-align: center;
            font-weight: 700;
            font-size: 0.85rem;
            color: #212529;
            padding: 0 4px;
            box-sizing: content-box;
            -moz-appearance: textfield;
        }

        .cart-qty-input:focus {
            outline: none;
            box-shadow: inset 0 0 0 1px var(--primary-color);
        }

        .cart-qty-input:read-only {
            background: #f1f3f5;
            cursor: not-allowed;
        }

        .cart-qty-input::-webkit-outer-spin-button,
        .cart-qty-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .modifier-box {
            background-color: #f8f9fa;
            border: 1px dashed #ced4da;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 15px;
        }

        .discount-type-group {
            background: #e9ecef;
            border-radius: 8px;
            padding: 3px;
            display: inline-flex;
            margin-right: 8px;
        }

        .discount-type-radio {
            display: none;
        }

        .discount-type-label {
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 700;
            color: #6c757d;
        }

        .discount-type-radio:checked+.discount-type-label {
            background-color: white;
            color: var(--primary-color);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .clean-input {
            border: none;
            background: transparent;
            font-weight: 700;
            color: var(--text-dark);
            text-align: right;
            width: 100%;
            outline: none;
            font-size: 1.1rem;
        }

        .tax-badge-input {
            background: #f1f1f1;
            border: 1px solid transparent;
            border-radius: 6px;
            padding: 2px 5px;
            width: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            text-align: center;
        }

        .product-section {
            height: 100%;
            overflow-y: auto;
            padding: 25px;
            padding-bottom: 80px;
            background-color: var(--bg-body);
            min-width: 0;
        }

        .empty-state-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 20px;
            max-width: 380px;
            min-height: 55vh;
            margin: 0 auto;
        }

        .empty-state-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(var(--primary-rgb), 0.08);
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
        }

        .empty-state-title {
            font-weight: 700;
            color: var(--text-dark);
            font-size: 1.05rem;
            margin-bottom: 8px;
        }

        .empty-state-text {
            color: var(--text-muted);
            font-size: 0.88rem;
            line-height: 1.5;
            margin-bottom: 0;
        }

        @media (max-width: 575.98px) {
            .empty-state-box {
                padding: 15px;
                min-height: 0;
            }

            .empty-state-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
                margin-bottom: 14px;
            }

            .empty-state-title {
                font-size: 0.95rem;
            }

            .empty-state-text {
                font-size: 0.8rem;
            }
        }

        .product-card {
            background: #fff;
            border-radius: var(--card-radius);
            border: 1px solid #f0f2f5;
            padding: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        .product-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.1);
            transform: translateY(-2px);
        }

        .product-img-wrapper {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 10px;
            position: relative;
            height: 120px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-img-wrapper img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .product-card:hover .product-img-wrapper img {
            transform: scale(1.05);
        }

        .pos-badge-top-left {
            position: absolute;
            top: 5px;
            left: 5px;
            z-index: 10;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .pos-badge-top-right {
            position: absolute;
            top: 5px;
            right: 5px;
            z-index: 10;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 4px;
        }

        .badge-custom-variant,
        .badge-custom-imei {
            font-size: 0.6rem;
            padding: 3px 6px;
            border-radius: 4px;
            color: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .badge-custom-variant {
            background-color: #2c323f;
        }

        .badge-custom-imei {
            background-color: var(--primary-color);
        }

        .custom-search-input {
            border: 1px solid #e6e6e6;
            border-radius: 30px;
            padding: 10px 20px;
            background: white;
        }

        .custom-search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.1);
        }

        .category-rail {
            display: flex;
            align-items: center;
            gap: 8px;
            width: 100%;
            min-width: 0;
        }

        .category-nav {
            position: relative;
            z-index: 2;
            flex: 0 0 32px;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            border: 1px solid #e6e6e6;
            background: #fff;
            color: var(--primary-color);
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
        }

        .category-nav i {
            font-size: 11px;
            pointer-events: none;
        }

        .category-rail.can-scroll-left .category-nav-prev,
        .category-rail.can-scroll-right .category-nav-next {
            display: flex;
        }

        .category-nav:hover {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }

        .category-scroll {
            display: flex;
            flex-wrap: nowrap;
            flex: 1 1 0;
            gap: 10px;
            width: 0;
            min-width: 0;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 5px 2px 2px;
            scroll-snap-type: x proximity;
            scroll-padding-inline: 8px;
            scrollbar-width: none;
            -ms-overflow-style: none;
            -webkit-overflow-scrolling: touch;
        }

        .category-scroll::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        .category-rail.can-scroll-right .category-scroll {
            -webkit-mask-image: linear-gradient(to right, #000 0, #000 calc(100% - 72px), transparent calc(100% - 12px));
            mask-image: linear-gradient(to right, #000 0, #000 calc(100% - 72px), transparent calc(100% - 12px));
        }

        .category-rail.can-scroll-left .category-scroll {
            -webkit-mask-image: linear-gradient(to right, transparent 12px, #000 72px);
            mask-image: linear-gradient(to right, transparent 12px, #000 72px);
        }

        .category-rail.can-scroll-left.can-scroll-right .category-scroll {
            -webkit-mask-image: linear-gradient(to right, transparent 12px, #000 72px, #000 calc(100% - 72px), transparent calc(100% - 12px));
            mask-image: linear-gradient(to right, transparent 12px, #000 72px, #000 calc(100% - 72px), transparent calc(100% - 12px));
        }

        .category-tab {
            flex: 0 0 auto;
            scroll-snap-align: start;
            border: 1px solid #ebebeb;
            background: #fff;
            color: var(--text-dark);
            padding: 8px 20px;
            border-radius: 5px;
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            transition: all 0.3s;
        }

        .category-tab:hover,
        .category-tab.active {
            background: var(--primary-color);
            color: #fff;
            border-color: var(--primary-color);
        }

        .text-primary-theme {
            color: var(--primary-color) !important;
        }

        .btn-primary-theme {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            transition: all 0.3s;
        }

        .btn-primary-theme:hover {
            background-color: var(--primary-hover, #256e69);
            color: #fff;
        }

        .payment-modal-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-hover, #256e69) 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }

        .payment-modal-header::after {
            content: '';
            position: absolute;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 40px;
            background: white;
            border-radius: 50% 50% 0 0;
        }

        .qs-fulfillment-toggle {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }

        .qs-fulfillment-toggle .qs-fulfill-btn {
            border: 1px solid #e5e7eb;
            background: #f8f9fa;
            color: #5b6e88;
            font-weight: 600;
            font-size: 0.9rem;
            border-radius: 10px;
            padding: 10px 12px;
        }

        .qs-fulfillment-toggle .btn-check:checked + .qs-fulfill-btn {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.25);
        }

        .qs-fulfillment-toggle .qs-fulfill-btn:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .qs-fulfillment-toggle .btn-check:checked + .qs-fulfill-btn:hover {
            color: #fff;
        }

        .total-amount-display {
            font-size: 3rem;
            font-weight: 800;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin: 10px 0;
        }

        .payment-row-card {
            background: white;
            border: 1px solid #f0f0f0;
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
        }

        .payment-field-method {
            flex: 1 1 180px;
            width: auto;
            min-width: 160px;
            max-width: 100%;
        }

        .payment-field-row {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 2 1 240px;
            min-width: 0;
        }

        .payment-field-amount,
        .payment-field-note {
            flex: 1 1 0;
            min-width: 0;
        }

        .payment-remove-btn {
            flex-shrink: 0;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .qs-pay-field-group {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: stretch !important;
            width: 100%;
            gap: 0;
        }

        .qs-pay-field-group > .custom-payment-select,
        .qs-pay-field-group > .payment-method,
        .qs-pay-field-group > .form-select {
            flex: 1 1 auto !important;
            width: 1% !important;
            max-width: none !important;
            min-width: 0 !important;
            height: 38px;
            padding-top: 6px;
            padding-bottom: 6px;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            /* Keep single custom chevron; ignore Bootstrap form-select arrow */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 12px !important;
        }

        .qs-pay-field-group > .btn-qs-pay-add {
            min-width: 38px !important;
            width: 38px !important;
            height: 38px !important;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            flex: 0 0 38px !important;
            border-top-left-radius: 0 !important;
            border-bottom-left-radius: 0 !important;
            border-color: #e2e8f0;
            background: #fff;
            color: var(--primary-color);
            margin-left: -1px;
            z-index: 1;
        }

        .qs-pay-field-group > .btn-qs-pay-add:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: #fff;
        }

        .payment-modal-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            position: relative;
            z-index: 6;
            margin-bottom: 4px;
        }

        .payment-modal-top-spacer,
        .payment-modal-close {
            width: 36px;
            height: 36px;
            flex-shrink: 0;
        }

        .payment-modal-close {
            position: static;
            top: auto;
            right: auto;
            border: none;
            border-radius: 50%;
            background: #fff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.12);
            padding: 0;
        }

        .payment-modal-close i {
            color: var(--primary-color);
            font-size: 16px;
        }

        .mobile-close-cart {
            width: 36px !important;
            height: 36px !important;
            background: #111827 !important;
            border: none !important;
            color: #fff !important;
        }

        .mobile-close-cart i {
            color: #fff !important;
            font-size: 14px;
        }

        .payment-row-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 4px 12px rgba(var(--primary-rgb), 0.1);
        }

        .input-icon-group {
            position: relative;
        }

        .input-icon-group i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            z-index: 10;
        }

        .input-icon-group .form-control {
            padding-left: 35px;
            font-weight: 600;
            border: 1px solid #eee;
            background: #fcfcfc;
            min-width: 0;
            width: 100%;
        }

        .input-icon-group .payment-currency-symbol {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 10;
            pointer-events: none;
            line-height: 1;
            padding-right: 6px;
        }

        .input-icon-group .payment-amount {
            padding-left: 2.35rem !important;
        }

        .input-icon-group .form-control:focus {
            background: white;
            border-color: var(--primary-color);
            box-shadow: none;
        }

        .btn-add-method {
            border: 2px dashed #dce0e4;
            color: #6c757d;
            background: #f8f9fa;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-add-method:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background: white;
        }

        .summary-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
        }

        .custom-payment-select {
            background-color: #f4f6f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 30px 8px 12px;
            font-size: 0.9rem;
            font-weight: 700;
            color: #2c323f;
            cursor: pointer;
            width: 100%;
            box-sizing: border-box;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%236c757d' viewBox='0 0 16 16'%3E%3Cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            transition: all 0.3s ease;
        }

        .custom-payment-select:focus {
            background-color: #ffffff;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15);
            outline: none;
        }

        .custom-payment-select:hover {
            background-color: #eef1f6;
        }

        .qs-form-error {
            display: none;
            border: 1px solid #f5c2c7;
            background: #f8d7da;
            color: #842029;
            border-radius: 8px;
            padding: 10px 12px;
            margin-bottom: 12px;
            font-size: 0.8125rem;
            line-height: 1.45;
        }

        .qs-form-error.is-visible {
            display: block;
        }

        .qs-form-error-title {
            font-weight: 700;
            margin-bottom: 4px;
        }

        .qs-form-error ul {
            margin: 0;
            padding-left: 1.1rem;
        }

        .qs-quick-modal {
            z-index: 1065 !important;
        }

        .modal-backdrop.qs-quick-backdrop {
            z-index: 1060 !important;
        }

        @media (max-width: 575.98px) {
            .qs-pay-field-group .btn-qs-pay-add {
                min-width: 38px;
                height: 36px;
            }
        }

        @media (min-width: 992px) {
            #paymentModal .modal-dialog {
                max-width: 620px;
            }
        }

        #overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99;
            display: none;
            backdrop-filter: blur(2px);
        }

        #overlay.show {
            display: block;
        }

        .mobile-cart-btn {
            position: fixed;
            right: 16px;
            left: auto;
            bottom: calc(76px + env(safe-area-inset-bottom, 0px));
            width: 56px;
            height: 56px;
            border-radius: 50%;
            padding: 0;
            background: var(--primary-color);
            color: #fff;
            border: none;
            box-shadow: 0 8px 22px rgba(var(--primary-rgb, 48, 142, 135), 0.4);
            z-index: 1046;
            display: none;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            transform: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .mobile-cart-btn .mobile-cart-count {
            position: absolute;
            top: -4px;
            right: -4px;
            min-width: 22px;
            height: 22px;
            padding: 0 5px;
            border-radius: 999px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            line-height: 22px;
            text-align: center;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,.15);
        }

        .mobile-cart-btn .mobile-cart-count.is-empty {
            display: none;
        }

        .mobile-cart-btn:hover,
        .mobile-cart-btn:focus {
            background: var(--primary-hover, #256e69);
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 26px rgba(var(--primary-rgb, 48, 142, 135), 0.5);
        }

        .coupon-wrapper {
            position: relative;
            margin-bottom: 15px;
        }

        .coupon-dashed-box {
            border: 2px dashed #dce0e4;
            border-radius: 10px;
            padding: 3px;
            background-color: #fcfcfc;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .coupon-dashed-box:focus-within {
            border-color: var(--primary-color);
            background-color: #fff;
            box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.1);
        }

        .coupon-icon-box {
            width: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #a0a0a0;
        }

        .coupon-dashed-box:focus-within .coupon-icon-box {
            color: var(--primary-color);
        }

        .coupon-input-field {
            border: none;
            background: transparent;
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            box-shadow: none !important;
            padding: 8px 5px;
        }

        .coupon-input-field::placeholder {
            font-weight: 400;
            color: #adb5bd;
            font-size: 0.85rem;
        }

        .btn-apply-custom {
            border-radius: 8px;
            padding: 6px 15px;
            font-size: 0.8rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background-color: var(--primary-color);
            border: none;
            color: white;
            transition: all 0.3s;
            margin-right: 2px;
        }

        .btn-apply-custom:hover {
            background-color: var(--primary-hover, #256e69);
            color: #fff;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-apply-custom:disabled {
            background-color: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        #couponMessage {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 991px) {
            .cart-section {
                position: fixed;
                top: 0;
                left: 0;
                width: min(92vw, 400px);
                max-width: 400px;
                height: 100dvh !important;
                transform: translateX(-100%);
                z-index: 1040;
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
                display: flex;
                flex-direction: column;
            }

            .cart-section.show {
                transform: translateX(0);
            }

            .cart-items-container {
                flex: 1 1 auto;
                min-height: 0;
            }

            #desktopFooterPlaceholder,
            #cartFooter {
                flex-shrink: 0;
                margin-top: auto;
            }

            #cartFooter {
                padding: 12px !important;
            }

            .payment-row-card {
                flex-direction: column;
                align-items: stretch;
                gap: 6px;
                padding: 8px;
            }

            .payment-field-method {
                width: 100%;
                flex: 0 0 auto !important;
                min-width: 0;
            }

            .payment-field-method .qs-pay-field-group {
                display: flex !important;
                flex-wrap: nowrap !important;
                width: 100%;
            }

            .payment-field-method .qs-pay-field-group > .custom-payment-select,
            .payment-field-method .qs-pay-field-group > .payment-method,
            .payment-field-method .qs-pay-field-group > select,
            .payment-field-method .qs-pay-field-group > .form-select {
                width: 1% !important;
                flex: 1 1 auto !important;
                min-width: 0 !important;
            }

            .qs-pay-field-group .btn-qs-pay-add {
                height: 36px !important;
                width: 36px !important;
                min-width: 36px !important;
                flex: 0 0 36px !important;
            }

            .payment-field-row {
                width: 100%;
                flex: 0 0 auto !important;
                flex-wrap: nowrap;
                gap: 6px;
            }

            .payment-field-amount,
            .payment-field-note {
                flex: 1 1 0;
                min-width: 0;
            }

            .payment-field-amount .form-control,
            .payment-field-note .form-control,
            .payment-field-method .qs-pay-field-group > .custom-payment-select {
                min-height: 36px;
            }

            #paymentModal .modal-dialog {
                margin: 0.6rem auto;
                max-width: calc(100% - 1.2rem);
            }

            #paymentModal .modal-content {
                border-radius: 16px !important;
                max-height: 90dvh;
            }

            #paymentModal .payment-modal-header {
                padding: 10px 14px 22px !important;
            }

            #paymentModal .payment-modal-header::after {
                bottom: -14px;
                height: 28px;
            }

            #paymentModal .payment-modal-header .badge {
                display: none !important;
            }

            #paymentModal .total-amount-display {
                font-size: 1.65rem !important;
                margin: 2px 0 0 !important;
            }

            #paymentModal .modal-body {
                padding: 6px 12px 12px !important;
                max-height: none;
                overflow-y: auto;
            }

            #paymentModal .mb-3 {
                margin-bottom: 0.5rem !important;
            }

            #paymentModal .mb-4 {
                margin-bottom: 0.6rem !important;
            }

            #paymentModal .mt-3,
            #paymentModal .mt-4 {
                margin-top: 0.55rem !important;
            }

            #paymentModal .btn-add-method {
                padding-top: 6px !important;
                padding-bottom: 6px !important;
                font-size: 0.82rem;
            }

            #paymentModal .summary-section {
                padding: 10px 12px !important;
                margin-top: 8px !important;
                border-radius: 10px !important;
            }

            #paymentModal .summary-section .h5,
            #paymentModal .summary-section .h4 {
                font-size: 1.05rem !important;
            }

            #paymentModal #paymentNote {
                padding: 8px 10px !important;
                min-height: 52px;
                font-size: 0.82rem !important;
            }

            #paymentModal .btn-primary-theme {
                padding-top: 10px !important;
                padding-bottom: 10px !important;
                font-size: 0.85rem;
            }

            .total-amount-display {
                font-size: 1.65rem;
            }

            .cart-section.show {
                transform: translateX(0);
            }

            .pos-container {
                height: calc(100vh - 65px) !important;
            }

            .product-section {
                padding: 15px;
                padding-bottom: 90px;
            }

            .mobile-cart-btn {
                display: flex;
            }

            .category-rail,
            .category-scroll {
                display: none !important;
            }

            .mobile-close-cart {
                display: block !important;
            }
        }

        @media (min-width: 992px) {
            .mobile-close-cart {
                display: none !important;
            }
        }

        #qsPrintingOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 20000;
            background: rgba(15, 23, 42, 0.55);
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(2px);
        }

        #qsPrintingOverlay.is-visible {
            display: flex;
        }

        #qsPrintingOverlay .qs-print-card {
            background: #fff;
            border-radius: 16px;
            padding: 28px 36px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.25);
            min-width: 260px;
            max-width: 90vw;
        }

        #qsPrintingOverlay .qs-print-spinner {
            width: 48px;
            height: 48px;
            margin: 0 auto 16px;
            border: 4px solid #e5e7eb;
            border-top-color: var(--primary-color, #308e87);
            border-radius: 50%;
            animation: qsPrintSpin 0.8s linear infinite;
        }

        #qsPrintingOverlay .qs-print-bars {
            display: flex;
            gap: 4px;
            justify-content: center;
            margin-top: 14px;
            height: 22px;
            align-items: flex-end;
        }

        #qsPrintingOverlay .qs-print-bars span {
            width: 5px;
            background: var(--primary-color, #308e87);
            border-radius: 2px;
            animation: qsPrintBar 1s ease-in-out infinite;
        }

        #qsPrintingOverlay .qs-print-bars span:nth-child(2) { animation-delay: 0.12s; }
        #qsPrintingOverlay .qs-print-bars span:nth-child(3) { animation-delay: 0.24s; }
        #qsPrintingOverlay .qs-print-bars span:nth-child(4) { animation-delay: 0.36s; }
        #qsPrintingOverlay .qs-print-bars span:nth-child(5) { animation-delay: 0.48s; }

        @keyframes qsPrintSpin {
            to { transform: rotate(360deg); }
        }

        @keyframes qsPrintBar {
            0%, 100% { height: 8px; opacity: 0.45; }
            50% { height: 22px; opacity: 1; }
        }
    </style>
@endpush

@section('content')
    @component('backend.layout.partials.bredcrumbs')
    @slot('title')
    Quick Sale
    @endslot
    @slot('subtitle')
    Manage and configure your system's fundamental preferences.
    @endslot
    <li class="breadcrumb-item active">Quick Sale</li>
    @endcomponent

    <div class="container-fluid p-0 pos-container">
        <div class="row g-0 h-100">

            {{-- Cart Section --}}
            <div class="col-lg-4 col-xl-5 cart-section" id="cartSection" data-tour-step="pos-cart">
                <div class="cart-header d-flex justify-content-between align-items-center bg-white">
                    <h5 class="mb-0 fw-bold text-dark"><i class="fas fa-shopping-cart me-2 text-primary-theme"></i> Current
                        Order</h5>
                    <button class="btn btn-light btn-sm rounded-circle mobile-close-cart" onclick="toggleCart()" type="button" aria-label="Close cart">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="desktopFormPlaceholder">
                    <div id="cartForm" class="d-flex flex-wrap cart-header p-2">
                        <div class="col-6 col-md-6 px-1 mb-2">
                            <label class="small text-muted">Date</label>
                            <input type="date" class="form-control form-control-sm" id="date" name="date"
                                value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6 col-md-6 px-1 mb-2">
                            <label class="small text-muted">Customer</label>
                            <div class="input-group input-group-sm">
                                <select name="customer_id" id="customerSelect" class="form-control" required
                                    onchange="handleCustomerChange()">
                                    <option value="" data-points="0">Walk in Customer</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}" data-points="{{ $customer->reward_points ?? 0 }}"
                                            data-group-discount="{{ $customer->customerGroup->discount ?? 0 }}"
                                            data-group-discount-type="{{ $customer->customerGroup->type ?? 'percentage' }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @can('create_customers')
                                    <button class="btn btn-outline-primary" type="button"
                                        onclick="openQuickCustomerModal()" title="Add New Customer">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="col-6 col-md-6 px-1 mb-2">
                            <label class="small text-muted">Branch</label>
                            @php $isEmployeeLocked = ! empty($lockedEmployee); @endphp
                            <div class="input-group input-group-sm">
                                <select name="branch_id" id="branchSelect" class="form-control" required
                                    onchange="filterProductsByBranch()"
                                    @if ($isEmployeeLocked) disabled @endif>
                                    @unless ($isEmployeeLocked)
                                        <option value="">Select Branch</option>
                                    @endunless
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}"
                                            @if ($isEmployeeLocked && (int) $lockedEmployee->branch_id === (int) $branch->id) selected @endif>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (! $isEmployeeLocked)
                                    @can('create_branches')
                                        <button class="btn btn-outline-primary" type="button"
                                            onclick="(function(){var f=document.getElementById('addBranchForm');if(f){f.reset();if(typeof qsClearFormError==='function')qsClearFormError('qsBranchFormError',f);var a=document.getElementById('qs_branch_is_active');if(a)a.checked=true;}$('#addBranchModal').modal('show');})()" title="Add New Branch">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                            @if ($isEmployeeLocked)
                                <input type="hidden" name="branch_id" id="lockedBranchId" value="{{ $lockedEmployee->branch_id }}">
                            @endif
                        </div>
                        <div class="col-6 col-md-6 px-1 mb-2">
                            <label class="small text-muted">Salesman</label>
                            <div class="input-group input-group-sm">
                                <select name="employee_id" id="employeeSelect" class="form-control"
                                    @if ($isEmployeeLocked) disabled @endif>
                                    @unless ($isEmployeeLocked)
                                        <option value="">Select Salesman</option>
                                    @endunless
                                    @foreach ($sellers as $seller)
                                        <option value="{{ $seller->id }}"
                                            @if ($isEmployeeLocked && (int) $lockedEmployee->id === (int) $seller->id) selected @endif>
                                            {{ $seller->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @if (! $isEmployeeLocked)
                                    @can('create_employees')
                                        <button class="btn btn-outline-primary" type="button"
                                            onclick="openQuickSalesmanModal()" title="Add New Salesman">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                @endif
                            </div>
                            @if ($isEmployeeLocked)
                                <input type="hidden" name="employee_id" id="lockedEmployeeId" value="{{ $lockedEmployee->id }}">
                            @endif
                        </div>
                    </div>
                </div>

                <div class="cart-items-container" id="cartContainer"></div>

                <div id="desktopFooterPlaceholder">
                    <div id="cartFooter" class="p-4 bg-white border-top shadow-sm z-3">
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Subtotal</span>
                            <span class="fw-bold text-dark" id="subtotalDisplay">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        </div>

                        {{-- Coupon Input Section --}}
                        <div class="coupon-wrapper">
                            @if (\App\Services\FeatureService::getActive('coupons'))
                                <div class="coupon-dashed-box">
                                    <div class="coupon-icon-box">
                                        <i class="fas fa-ticket-alt"></i>
                                    </div>
                                    <input type="text" id="couponCodeInput" class="form-control coupon-input-field"
                                        placeholder="Have a coupon? Code here..." autocomplete="off">
                                    <button class="btn btn-apply-custom" type="button" onclick="applyCoupon()"
                                        id="btnApplyCoupon">
                                        Apply
                                    </button>
                                </div>
                            @else
                                <div class="coupon-dashed-box" style="opacity: 0.6; cursor: not-allowed;"
                                    onclick="showUpgradeAlert()">
                                    <div class="coupon-icon-box">
                                        <i class="fas fa-lock" style="color: #f39c12;"></i>
                                    </div>
                                    <input type="text" class="form-control coupon-input-field"
                                        placeholder="Coupon not available in your package..." disabled
                                        style="cursor: not-allowed;">
                                    <button class="btn btn-apply-custom" type="button" disabled style="opacity: 0.5;">
                                        🔒 Locked
                                    </button>
                                </div>
                            @endif
                            <div id="couponMessage" class="small mt-2 ps-1 fw-bold" style="display: none;"></div>
                        </div>

                        {{-- Manual Discount Box --}}
                        <div class="modifier-box d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-primary-theme bg-white p-2 rounded shadow-sm">
                                    <i class="fas fa-percentage"></i>
                                </div>
                                <span class="small fw-bold text-muted">Discount</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="discount-type-group">
                                    <label>
                                        <input type="radio" name="discType" value="percent" class="discount-type-radio"
                                            checked onchange="updateDiscountType('percent')">
                                        <span class="discount-type-label">%</span>
                                    </label>
                                    <label>
                                        <input type="radio" name="discType" value="fixed" class="discount-type-radio"
                                            onchange="updateDiscountType('fixed')">
                                        <span class="discount-type-label">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                    </label>
                                </div>
                                <div style="width: 80px; border-bottom: 2px solid #e9ecef;">
                                    <input type="number" id="discountInput" class="clean-input" placeholder="0" min="0">
                                </div>
                            </div>
                        </div>

                        {{-- Group Discount Result Row --}}
                        <div class="d-flex justify-content-between mb-2 text-primary small" id="groupDiscountRow"
                            style="display:none !important;">
                            <span><i class="fas fa-users"></i> Group Discount</span>
                            <span class="fw-bold">-<span id="groupDiscountDisplay">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span></span>
                        </div>

                        {{-- Coupon Result Row --}}
                        <div class="d-flex justify-content-between mb-2 text-info small" id="couponRow"
                            style="display:none !important;">
                            <div class="d-flex align-items-center">
                                <span class="me-1"><i class="fas fa-tag"></i> Coupon (<span id="displayCouponCode"
                                        class="fw-bold"></span>)</span>
                                <i class="fas fa-times-circle text-danger ms-2" style="cursor:pointer;"
                                    onclick="removeCoupon()" title="Remove Coupon"></i>
                            </div>
                            <span class="fw-bold">-<span id="displayCouponAmount">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span></span>
                        </div>

                        {{-- Reward Points Box --}}
                        @if (\App\Services\FeatureService::getActive('reward_points'))
                            <div class="modifier-box flex-column mb-3" id="rewardPointBox" style="display: none;">
                                <div class="d-flex align-items-center justify-content-between w-100">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="text-warning bg-white p-2 rounded shadow-sm">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <span class="small fw-bold text-muted">Reward Points (<span
                                                id="availablePointsDisplay">0</span>)</span>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="useRewardPointsToggle"
                                            onchange="toggleRewardPoints()">
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top"
                                    id="rewardPointsInputContainer" style="display: none !important;">
                                    <span class="small text-muted">Points to use:</span>
                                    <div style="width: 80px; border-bottom: 2px solid #e9ecef;">
                                        <input type="number" id="usedPointsInput" class="clean-input text-warning"
                                            placeholder="0" min="0" oninput="validateAndUpdatePoints()">
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Manual Discount Result Row --}}
                        <div class="d-flex justify-content-between mb-2 text-success small" id="discountRow"
                            style="display:none !important;">
                            <span>Applied Discount</span>
                            <span class="fw-bold">-<span id="discountDisplay">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span></span>
                        </div>

                        {{-- Reward Point Result Row --}}
                        @if (\App\Services\FeatureService::getActive('reward_points'))
                            <div class="d-flex justify-content-between mb-2 text-warning small" id="rewardPointRow"
                                style="display:none !important;">
                                <span>Points Discount (<span id="usedPointsDisplay">0</span> pts)</span>
                                <span class="fw-bold">-<span id="pointDiscountDisplay">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span></span>
                            </div>
                        @endif

                        <div class="d-flex justify-content-between align-items-center mb-3 small">
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted">Tax</span>
                                <div class="position-relative">
                                    <input type="number" id="taxInput" class="tax-badge-input"
                                        value="{{ $general_settings['default_tax'] ?? '5' }}">
                                    <span class="position-absolute top-0 end-0 pe-1 text-muted"
                                        style="font-size: 0.7rem; pointer-events: none; margin-top: 2px;">%</span>
                                </div>
                            </div>
                            <span class="fw-bold text-dark" id="taxDisplay">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 p-3 rounded"
                            style="background-color: var(--primary-color); color: white;">
                            <div>
                                <span class="d-block small opacity-75">Grand Total</span>
                                <span class="h5 mb-0 fw-bold" id="totalDisplay">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                            </div>
                            <button class="btn btn-light btn-sm fw-bold text-primary-theme px-3 py-2"
                                onclick="openPaymentModal()">
                                Pay <i class="fas fa-chevron-right ms-1"></i>
                            </button>
                        </div>

                        <button class="btn btn-light w-100 py-2 fw-bold text-danger border" onclick="clearCart()">
                            <i class="fas fa-trash me-2"></i> Clear Order
                        </button>
                    </div>
                </div>
            </div>

            {{-- Product Section --}}
            <div class="col-lg-8 col-xl-7 product-section">
                <div id="mobileFormPlaceholder" class="d-lg-none mb-3 bg-white rounded shadow-sm border"></div>
                <div class="mb-4">
                    <div class="row g-3 align-items-center">
                        <div class="col-md-7">
                            <div class="position-relative">
                                <i
                                    class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                                <div class="cursor-pointer shadow-sm position-absolute top-50 end-0 translate-middle-y me-2 bg-light rounded-circle d-flex align-items-center justify-content-center"
                                    onclick="openScannerModal()" style="width: 35px; height: 35px; z-index: 10;"
                                    title="Camera Scanner">
                                    <i class="fas fa-qrcode text-primary-theme"></i>
                                </div>
                                <input type="text" id="searchInput" class="form-control custom-search-input ps-5 pe-5"
                                    data-tour-step="pos-search"
                                    placeholder="Search products or barcode...">
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-end gap-2">
                                @if (\App\Services\FeatureService::getActive('gift_cards'))
                                    <button type="button"
                                        class="btn btn-primary text-white btn-sm px-4 py-2 shadow-sm fw-bold rounded-pill w-100 text-nowrap"
                                        style="max-width: 250px;" onclick="openGiftCardModal()">
                                        <i class="fas fa-gift me-1"></i> Gift Card
                                    </button>
                                @else
                                    <button type="button"
                                        class="btn btn-primary text-white btn-sm px-4 py-2 shadow-sm fw-bold rounded-pill w-100"
                                        style="max-width: 250px; opacity: 0.7;" onclick="showUpgradeAlert()">
                                        <i class="fa-solid fa-lock me-1" style="font-size: 11px; color: #f39c12;"></i>
                                        Gift Card
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="category-rail mt-3">
                        <button type="button" class="category-nav category-nav-prev" aria-label="Previous categories">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <div class="category-scroll">
                            <button class="category-tab active" data-cat="all">All Items</button>
                            @foreach ($categories as $cat)
                                <button class="category-tab" data-cat="{{ $cat->name }}">{{ $cat->name }}</button>
                            @endforeach
                        </div>
                        <button type="button" class="category-nav category-nav-next" aria-label="Next categories">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-3 row-cols-xl-4 g-3" id="productGrid"></div>
                <div id="mobileFooterPlaceholder" class="d-lg-none mt-4 pb-5 mb-5 bg-white rounded shadow-sm border"
                    style="padding-bottom: 80px !important;"></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="paymentModal" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">

                <div class="payment-modal-header">
                    <div class="payment-modal-top">
                        <span class="payment-modal-top-spacer" aria-hidden="true"></span>
                        <div class="small text-uppercase opacity-75 fw-bold letter-spacing-1 mb-0">Total Payable</div>
                        <button type="button" class="payment-modal-close" data-bs-dismiss="modal" aria-label="Close">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="total-amount-display" id="modalTotalPayable">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</div>
                    <div class="badge bg-white text-primary-theme px-3 py-1 rounded-pill shadow-sm">
                        <i class="fas fa-file-invoice me-1"></i> Finalize Payment
                    </div>
                </div>

                <div class="modal-body px-4 pb-4 pt-2">

                    <div class="mb-3">
                        <label class="small text-muted fw-bold mb-2 ms-1">FULFILLMENT</label>
                        <div class="qs-fulfillment-toggle" role="group" aria-label="Fulfillment type">
                            <input type="radio" class="btn-check" name="qs_fulfillment" id="qsFulfillPickup" value="pickup" checked autocomplete="off">
                            <label class="btn qs-fulfill-btn" for="qsFulfillPickup">
                                <i class="fas fa-store me-1"></i> Pickup
                            </label>
                            <input type="radio" class="btn-check" name="qs_fulfillment" id="qsFulfillDelivery" value="delivery" autocomplete="off">
                            <label class="btn qs-fulfill-btn" for="qsFulfillDelivery">
                                <i class="fas fa-truck me-1"></i> Delivery
                            </label>
                        </div>
                        <div id="qsDeliveryHint" class="small text-muted mt-2 d-none">
                            Sale stays <strong>Pending</strong>. After payment, open Sale List → <strong>Ship Order</strong> to assign courier.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="small text-muted fw-bold mb-2 ms-1">PAYMENT METHODS</label>
                        <div id="paymentRowsContainer">
                        </div>
                    </div>

                    <button type="button" class="btn btn-add-method w-100 py-2 rounded-3 mb-4" onclick="addPaymentRow()">
                        <i class="fas fa-plus-circle me-1"></i> Add Another Payment Method
                    </button>

                    <div class="summary-section">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-secondary fw-bold">Total Paid</span>
                            <span class="h5 mb-0 fw-bold text-success" id="displayTotalPaid">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-2">
                            <span class="text-secondary fw-bold" id="balanceLabel">Due Amount</span>
                            <span class="h4 mb-0 fw-bold text-danger" id="displayBalance">0.00{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                        </div>
                    </div>

                    <div class="mt-3">
                        <textarea id="paymentNote" class="form-control border-0 bg-light rounded-3 p-3" rows="2"
                            placeholder="Add a sale note (optional)..."
                            style="font-size: 0.9rem; color: #000 !important;"></textarea>
                    </div>

                    <div class="mt-4">
                        <button
                            class="btn btn-primary-theme w-100 py-3 rounded-pill fw-bold shadow-lg text-uppercase letter-spacing-1"
                            onclick="processSplitPayment()">
                            Complete Order <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Variant Modal --}}
    <div class="modal fade" id="variantModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h6 class="modal-title fw-bold"><i class="fas fa-layer-group me-2"></i>Select Variant</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <h6 id="variantProductName" class="text-primary-theme mb-3">Product Name</h6>
                    <div id="variantButtons" class="d-grid gap-2"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Scanner Modal --}}
    <div class="modal fade" id="scannerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="fas fa-qrcode me-2"></i>Scan Barcode</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="stopScanner()"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <div id="reader" style="width: 100%; min-height: 300px; background: #000;"></div>
                    <p class="text-muted small mt-3 mb-3">Point camera at a barcode</p>
                </div>
            </div>
        </div>
    </div>

    <div id="overlay" onclick="toggleCart()" style="z-index: 1030;"></div>
    <button class="mobile-cart-btn" type="button" onclick="toggleCart()" data-tour-step="pos-mobile-cart" aria-label="View cart">
        <i class="fas fa-shopping-cart"></i>
        <span class="mobile-cart-count is-empty" id="mobileCartCount">0</span>
    </button>

    {{-- IMEI Modal --}}
    <div class="modal fade" id="imeiSelectModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white border-0 pb-2">
                    <h6 class="modal-title fw-bold"><i class="fas fa-barcode me-2"></i><span id="imeiSelectTitle">Select IMEI</span></h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="mb-3">
                        <label class="small fw-bold text-muted mb-1">Search/Scan Available IMEI</label>
                        <input type="text" id="imeiSearchInput" class="form-control"
                            placeholder="Type or Scan Serial Number...">
                        <div id="imeiListContainer" class="list-group mt-2" style="max-height: 200px; overflow-y: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Add Customer Modal --}}
    @can('create_customers')
        <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Customer</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addCustomerForm" onsubmit="return saveQuickCustomer(event)">
                            @csrf
                            <div class="nest-form-error" id="qsCustomerFormError" role="alert"></div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Customer Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter customer name">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Phone *</label>
                                <input type="text" name="phone" class="form-control" required
                                    placeholder="Enter phone number">
                            </div>
                            <div class="mb-2">
                                <label class="small fw-bold text-muted mb-1">Email (Optional)</label>
                                <input type="email" name="email" class="form-control" placeholder="Enter email">
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-1">Address (Optional)</label>
                                <textarea name="address" class="form-control" rows="2"
                                    placeholder="Enter address"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="small fw-bold text-muted mb-1">Note (Optional)</label>
                                <textarea name="note" class="form-control" rows="2" placeholder="Enter note"></textarea>
                            </div>
                            <div class="text-end">
                                <button type="button" class="btn btn-secondary btn-sm"
                                    data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveCustomer">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Add Branch Modal --}}
    @can('create_branches')
        <div class="modal fade" id="addBranchModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-code-branch me-2"></i>Add Branch</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addBranchForm" onsubmit="return saveQuickBranch(event)" enctype="multipart/form-data">
                            @csrf
                            <div class="nest-form-error" id="qsBranchFormError" role="alert"></div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Branch name">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Manager *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="manager_id" id="qsBranchManagerSelect" class="form-control" required>
                                            <option value="">Select Manager</option>
                                            @foreach ($branchManagers as $manager)
                                                <option value="{{ $manager->id }}">{{ $manager->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('manage_users')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestManagerModal()" title="Add Manager">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Account *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="account_id" id="qsBranchAccountSelect" class="form-control" required>
                                            <option value="">Select Account</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('create_accounts')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestAccountModal()" title="Add Account">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Address *</label>
                                    <input type="text" name="address" class="form-control" required placeholder="Branch address">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Phone (Mobile)</label>
                                    <input type="text" name="phone" class="form-control" placeholder="Mobile">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Telephone (Landline)</label>
                                    <input type="text" name="telephone" class="form-control" placeholder="Landline">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">BIN No</label>
                                    <input type="text" name="bin_number" class="form-control" placeholder="BIN number">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Email</label>
                                    <input type="email" name="email" class="form-control" placeholder="Email">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Website</label>
                                    <input type="text" name="website" class="form-control" placeholder="https://example.com">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Branch Logo</label>
                                    <input type="file" name="branch_logo" class="form-control" accept="image/*">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qs_branch_is_active"
                                            name="is_active" checked style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="qs_branch_is_active">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveBranch">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    {{-- Add Salesman Modal --}}
    @can('create_employees')
        <div class="modal fade" id="addSalesmanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-user-tie me-2"></i>Add Salesman</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="addSalesmanForm" onsubmit="return saveQuickSalesman(event)">
                            @csrf
                            <div class="nest-form-error" id="qsSalesmanFormError" role="alert"></div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Employee name">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Email *</label>
                                    <input type="email" name="email" class="form-control" required placeholder="Login email">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Phone *</label>
                                    <input type="tel" name="phone" class="form-control" required placeholder="Phone number">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Branch *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="branch_id" id="qsSalesmanBranch" class="form-control" required>
                                            <option value="">Select Branch</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('create_branches')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openQsSalesmanBranchModal()" title="Add New Branch">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Department *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="department_id" id="qsSalesmanDepartmentSelect" class="form-control"
                                            required>
                                            <option value="">Select Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('create_departments')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestDepartmentModal()" title="Add Department">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Role *</label>
                                    <div class="input-group nest-field-group">
                                        <select name="role" id="qsSalesmanRoleSelect" class="form-control" required>
                                            <option value="">Select Role</option>
                                            @foreach ($employeeRoles as $role)
                                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @can('manage_roles')
                                            <button type="button" class="btn btn-outline-primary btn-nest-add"
                                                onclick="openNxNestRoleModal()" title="Add Role">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        @endcan
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Password *</label>
                                    <input type="password" name="password" class="form-control" required minlength="6"
                                        autocomplete="new-password" placeholder="Login password">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Confirm Password *</label>
                                    <input type="password" name="password_confirmation" class="form-control" required
                                        minlength="6" autocomplete="new-password" placeholder="Confirm password">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Salary *</label>
                                    <input type="number" name="salary" class="form-control" required step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Max Commission</label>
                                    <input type="number" name="max_commission" class="form-control" step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Sale Target</label>
                                    <input type="number" name="sale_target" class="form-control" step="any" placeholder="0.00">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Joining Date *</label>
                                    <input type="date" name="joining_date" class="form-control" required
                                        value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Leaving Date</label>
                                    <input type="date" name="leaving_date" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Status</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qs_salesman_status"
                                            name="status" checked style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="qs_salesman_status">Active</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveSalesman">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    @include('backend.shared.partials.nested_branch_employee_quick_add', [
        'nx' => [
            'managerSelectId' => 'qsBranchManagerSelect',
            'accountSelectId' => 'qsBranchAccountSelect',
            'departmentSelectId' => 'qsSalesmanDepartmentSelect',
            'roleSelectId' => 'qsSalesmanRoleSelect',
            'branchParentModalId' => 'addBranchModal',
            'employeeParentModalId' => 'addSalesmanModal',
            'roles' => $employeeRoles ?? [],
        ],
    ])

    {{-- Gift Card Modal --}}
    @if (\App\Services\FeatureService::getActive('gift_cards'))
        <div class="modal fade" id="giftCardModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                    <div class="modal-header bg-primary text-white border-0">
                        <h5 class="modal-title fw-bold"><i class="fas fa-gift me-2"></i>Sell Gift Card</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="giftCardForm">
                            <div class="mb-3">
                                <label class="small fw-bold text-muted">Card Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-barcode"></i></span>
                                    <input type="text" name="card_no" id="gc_card_no" class="form-control fw-bold"
                                        placeholder="Scan or Auto Generate" required>
                                    <button type="button" class="btn btn-outline-primary" onclick="generateGiftCardNo()">
                                        <i class="fas fa-magic"></i> Auto
                                    </button>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-6">
                                    <label class="small fw-bold text-muted">Card Value</label>
                                    <input type="number" name="value" id="gc_value" class="form-control fw-bold"
                                        placeholder="5000" required oninput="syncGcPrice()">
                                </div>
                                <div class="col-6">
                                    <label class="small fw-bold text-muted">Price</label>
                                    <input type="number" name="price" id="gc_price" class="form-control fw-bold"
                                        placeholder="5000" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="small fw-bold text-muted">Expiry Date</label>
                                <input type="date" name="expiry_date" id="gc_expiry" class="form-control" required
                                    value="{{ date('Y-m-d', strtotime('+1 year')) }}">
                            </div>

                            <div class="mb-3">
                                <label class="small fw-bold text-muted" for="gc_customer_id">Customer</label>
                                <div class="input-group gc-field-group">
                                    <select name="customer_id" id="gc_customer_id" class="form-select">
                                        <option value="">Walk-in Customer</option>
                                        @foreach ($customers as $customer)
                                            <option value="{{ $customer->id }}">{{ $customer->name }}
                                                ({{ $customer->phone }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @can('create_customers')
                                        <button class="btn btn-outline-primary btn-gc-add" type="button"
                                            onclick="openGcCustomerModal('#gc_customer_id')" title="Add Customer"
                                            aria-label="Add Customer">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="small fw-bold text-muted" for="gc_payment_method">Payment Method</label>
                                <div class="input-group gc-field-group">
                                    <select name="payment_method" id="gc_payment_method" class="form-select">
                                        @foreach ($accounts as $account)
                                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                                        @endforeach
                                    </select>
                                    @can('create_accounts')
                                        <button class="btn btn-outline-primary btn-gc-add" type="button"
                                            onclick="openGcAccountModal('#gc_payment_method')" title="Add Payment Method"
                                            aria-label="Add Payment Method">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary text-white w-100 py-2 fw-bold rounded-pill shadow-sm">
                                Confirm & Activate <i class="fas fa-check-circle ms-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if (\App\Services\FeatureService::getActive('gift_cards'))
        @include('backend.gift_card.partials.quick_add')
    @endif
    {{-- Clear Order Confirmation Modal --}}
    <div class="modal fade" id="clearOrderConfirmModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(220, 53, 69, 0.1);">
                        <i class="fas fa-trash-alt text-danger fs-3"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Clear current order?</h5>
                    <p class="text-muted mb-4">This action cannot be undone. Are you sure you want to remove all items from the cart?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger px-4" id="confirmClearOrderBtn">
                            <i class="fas fa-trash-alt me-1"></i> Yes, clear it
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sale Success / Print Receipt Modal --}}
    <div class="modal fade qs-quick-modal" id="saleSuccessPrintModal" tabindex="-1" aria-hidden="true"
        aria-labelledby="saleSuccessPrintTitle" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-body p-4 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3"
                        style="width: 72px; height: 72px; background-color: rgba(var(--primary-rgb, 48, 142, 135), 0.1);">
                        <i class="fas fa-check fs-3" style="color: var(--primary-color, #308e87);"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="saleSuccessPrintTitle">Sale Successful!</h5>
                    <p class="text-muted mb-4">Do you want to print the receipt?</p>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">No Receipt</button>
                        <button type="button" class="btn btn-primary text-white px-4" id="saleSuccessPrintBtn">
                            <i class="fas fa-print me-1"></i> Print Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Add Account / Payment Method (from payment modal) --}}
    @can('create_accounts')
        <div class="modal fade qs-quick-modal" id="qsAddAccountModal" tabindex="-1" aria-hidden="true"
            data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white border-0 pb-2">
                        <h6 class="modal-title fw-bold"><i class="fas fa-wallet me-2"></i>Add Payment Method</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body pt-3">
                        <form id="qsAddAccountForm" onsubmit="return saveQsPaymentAccount(event)">
                            @csrf
                            <div class="qs-form-error" id="qsAccountFormError" role="alert"></div>
                            <div class="row g-2">
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Name *</label>
                                    <input type="text" name="name" class="form-control" required placeholder="Account name">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Type *</label>
                                    <select name="type" id="qs_account_type" class="form-control" required>
                                        <option value="cash" selected>Cash</option>
                                        <option value="bank">Bank</option>
                                        <option value="mobile banking">Mobile Banking</option>
                                        <option value="digital wallet">Digital Wallet</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">
                                        Account Number <span id="qs_account_number_required" class="text-danger d-none">*</span>
                                    </label>
                                    <input type="text" name="account_number" id="qs_account_number" class="form-control"
                                        placeholder="Not required for cash">
                                    <small id="qs_account_number_hint" class="text-muted">Cash accounts do not need an account number.</small>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Initial Balance</label>
                                    <input type="number" name="initial_balance" class="form-control" step="0.01" min="0"
                                        value="0" placeholder="0.00">
                                </div>
                                <div class="col-12 mb-2">
                                    <label class="small fw-bold text-muted mb-1">Note</label>
                                    <textarea name="note" class="form-control" rows="2" placeholder="Optional note"></textarea>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Active</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qs_account_is_active"
                                            name="is_active" checked style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="qs_account_is_active">Yes</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Default</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qs_account_is_default"
                                            name="is_default" style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="qs_account_is_default">Yes</label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="small fw-bold text-muted mb-1 d-block">Manual Amount</label>
                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qs_account_manual"
                                            name="allow_manual_amount" style="width: 40px; height: 20px;">
                                        <label class="form-check-label ms-2" for="qs_account_manual">Yes</label>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm" id="btnSaveQsAccount">
                                    <i class="fas fa-save me-1"></i> Save
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endcan

    <div id="qsPrintingOverlay" aria-live="polite" aria-busy="true" role="status">
        <div class="qs-print-card">
            <div class="qs-print-spinner" aria-hidden="true"></div>
            <h5 class="mb-1 fw-bold" style="color: #1f2937;">Printing bill…</h5>
            <p class="mb-0 text-muted small">Please wait while the receipt is sent to the printer.</p>
            <div class="qs-print-bars" aria-hidden="true">
                <span></span><span></span><span></span><span></span><span></span>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Website's custom toaster for all alerts
        function showFloatingAlert(type, message) {
            var label = type === 'success' ? @json(__('Success!')) : @json(__('Error!'));
            var $alert = $('<div class="alert alert-' + type + ' floating-alert"><strong>' + label + '</strong> ' + message + '</div>');
            $('body').append($alert);
            setTimeout(function() {
                $alert.remove();
            }, 5000);
        }

        // Override native alert globally for this page
        window.alert = function(message) {
            let type = 'danger';
            if (typeof message === 'string') {
                var lower = message.toLowerCase();
                var failed = lower.includes('error') || lower.includes('fail') || message.includes('ভুল') || message.includes('হয়নি') || message.includes('যায়নি') || message.includes('পারবেন না');
                var ok = lower.includes('success') || lower.includes('loaded') || lower.includes('active') || message.includes('সফল') || message.includes('তৈরি হয়েছে') || message.includes('সেভ হয়েছে') || message.includes('যোগ হয়েছে') || message.includes('আপডেট হয়েছে') || message.includes('মুছে ফেলা') || message.includes('লোড হয়েছে');
                if (!failed && ok) {
                    type = 'success';
                }
            }
            // Strip duplicate prefix if present
            let cleanMessage = message;
            if (typeof message === 'string') {
                cleanMessage = message.replace(/^(Success!|Error!|Success:|Error:)\s*/i, '');
            }
            showFloatingAlert(type, cleanMessage);
        };
        const products = [
            @foreach ($products as $product)
                @php
                    $saleUnit = $product->sellingUnit ?: $product->unit;
                    $unitLabel = 'Pcs';
                    if ($saleUnit) {
                        $short = trim((string) ($saleUnit->short_code ?? ''));
                        $unitLabel = $short !== '' ? $short : (string) $saleUnit->name;
                    }
                @endphp
                            {
                        id: {{ $product->id }},
                        barcode: "{{ $product->product_code ?? '880' . $product->id }}",
                        name: {{ \Illuminate\Support\Js::from($product->product_title) }},
                        category: {{ \Illuminate\Support\Js::from($product->category->name ?? 'General') }},
                        unit: {{ \Illuminate\Support\Js::from($unitLabel) }},
                        price: {{ $product->selling_price ?? 0 }},
                        image: "{{ !empty($product->thumbnail) ? asset('storage/products/' . $product->thumbnail) : asset('backend/assets/images/preview_image.png') }}",
                        has_imei: {{ $product->has_imei ?? 0 }},
                        branch_stocks: {
                            @foreach ($product->productBranches as $pb)
                                {{ $pb->branch_id }}: {{ $pb->qty }},
                            @endforeach
                                },
                available_imeis: [
                    @foreach ($product->imeis as $imei)
                                        {
                        number: "{{ $imei->imei_number }}",
                        variant_id: {{ $imei->variant_id ?? 'null' }}
                                        },
                    @endforeach
                                ],
                    variants: [
                        @foreach ($product->productVariants as $variant)
                                        {
                            id: {{ $variant->id }},
                            name: {{ \Illuminate\Support\Js::from($variant->variant_name) }},
                            price: {{ (float) ($product->selling_price ?? 0) + (float) ($variant->additional_price ?? 0) }},
                            branch_stocks: {
                                @foreach ($variant->productBranches as $vpb)
                                    {{ $vpb->branch_id }}: {{ $vpb->qty }},
                                @endforeach
                                            }
                                        },
                        @endforeach
                                ]
                            },
            @endforeach
            ];

        // Globals
        let cart = [];
        let currentCustomerPoints = 0;
        let currentCustomerGroupDiscount = {
            type: 'percentage',
            value: 0
        };
        let usedRewardPoints = 0;
        let pointDiscountAmount = 0;

        // Reward Point Settings
        const REWARD_POINTS_ENABLED = {{ $rewardSetting && $rewardSetting->is_active ? 'true' : 'false' }};
        const MIN_REDEEMABLE_POINTS = {{ $rewardSetting->min_redeem_point ?? 100 }};
        const MIN_PURCHASE_FOR_POINTS = {{ $rewardSetting->min_purchase_amount ?? 1000 }};
        const POINT_REDEMPTION_RATE = {{ $rewardSetting->point_redemption_rate ?? 1 }};
        let currentDiscountType = 'percent';
        let appliedCoupon = null;
        let couponDiscountAmount = 0;
        let currentCategory = 'all';
        const availableAccounts = @json($accounts ?? []);
        const canCreateAccounts = @json(auth()->user()->can('create_accounts'));
        @php
            $activeAccounts = $accounts ?? collect();
            $defaultAccount = $activeAccounts->first(fn ($acc) => (int) $acc->is_default === 1);

            if (! $defaultAccount) {
                $settingsDefaultId = (string) ($general_settings['default_payment_method'] ?? '');
                if ($settingsDefaultId !== '') {
                    $defaultAccount = $activeAccounts->first(fn ($acc) => (string) $acc->id === $settingsDefaultId);
                }
            }

            if (! $defaultAccount) {
                $defaultAccount = $activeAccounts->first();
            }

            $defaultPaymentAccountId = (string) (optional($defaultAccount)->id ?? '');
        @endphp
        const defaultPaymentAccountId = @json($defaultPaymentAccountId);

        // Sync Globals
        let globalSubTotal = 0;
        let globalManualDiscount = 0;
        let globalGroupDiscountAmount = 0;
        let globalTaxAmount = 0;
        let finalGrandTotal = 0;

        // Modals
        let clearOrderModalInstance;
        let paymentModalInstance;
        let variantModalInstance;
        let scannerModalInstance;
        let imeiSelectModalInstance;
        let giftCardModalInstance;

        // Logic Vars
        let html5QrcodeScanner = null;
        let selectedProductForVariant = null;
        let selectedProductForImei = null;
        let tempVariantForImei = null;

        const productGrid = document.getElementById('productGrid');
        const cartContainer = document.getElementById('cartContainer');
        const searchInput = document.getElementById('searchInput');
        const taxInput = document.getElementById('taxInput');
        const discountInput = document.getElementById('discountInput');
        const imeiSearchInput = document.getElementById('imeiSearchInput');
        const isEmployeeLocked = @json(! empty($lockedEmployee));

        function getSelectedBranchId() {
            const locked = document.getElementById('lockedBranchId');
            if (locked && locked.value) {
                return locked.value;
            }
            return document.getElementById('branchSelect')?.value || '';
        }

        function getSelectedSellerId() {
            const locked = document.getElementById('lockedEmployeeId');
            if (locked && locked.value) {
                return locked.value;
            }
            return document.querySelector('select[name="employee_id"]')?.value || '';
        }

        document.addEventListener('DOMContentLoaded', () => {
            if (sessionStorage.getItem('quick_sale_success')) {
                showFloatingAlert('success', sessionStorage.getItem('quick_sale_success'));
                sessionStorage.removeItem('quick_sale_success');
            }

            clearOrderModalInstance = new bootstrap.Modal(document.getElementById('clearOrderConfirmModal'));
            paymentModalInstance = new bootstrap.Modal(document.getElementById('paymentModal'));
            variantModalInstance = new bootstrap.Modal(document.getElementById('variantModal'));
            scannerModalInstance = new bootstrap.Modal(document.getElementById('scannerModal'));
            imeiSelectModalInstance = new bootstrap.Modal(document.getElementById('imeiSelectModal'));
            const gcModalEl = document.getElementById('giftCardModal');
            if (gcModalEl) {
                giftCardModalInstance = new bootstrap.Modal(gcModalEl);
                window.giftCardModalInstance = giftCardModalInstance;
            }

            filterProductsByBranch();
            searchInput.focus();

            @if (isset($quotation))
                @if ($quotation->customer_id)
                    document.getElementById('customerSelect').value = "{{ $quotation->customer_id }}";
                    handleCustomerChange();
                @endif

                @if (empty($lockedEmployee))
                    @if ($quotation->branch_id)
                        document.getElementById('branchSelect').value = "{{ $quotation->branch_id }}";
                        filterProductsByBranch();
                    @endif

                    @if ($quotation->seller_id)
                        document.querySelector('select[name="employee_id"]').value = "{{ $quotation->seller_id }}";
                    @endif
                @endif

                document.getElementById('taxInput').value = "{{ (int) $quotation->order_tax_rate }}";
                document.getElementById('discountInput').value = "{{ $quotation->order_discount }}";
                @foreach ($quotation->items as $item)
                    var originalProduct = products.find(p => p.id == {{ $item->product_id }});

                    if (originalProduct) {
                        let variantId = {{ $item->variant_id ?? 'null' }};
                        let variantName = null;

                        if (variantId) {
                            let v = originalProduct.variants.find(v => v.id == variantId);
                            if (v) variantName = v.name;
                        }

                        let imeisArray = [];
                        let savedImeis = "{{ $item->imei_number }}";
                        if (savedImeis) {
                            imeisArray = savedImeis.split(',');
                        }

                        cart.push({
                            ...originalProduct,
                            id: originalProduct.id,
                            price: {{ $item->net_unit_price }},
                            qty: {{ (int) $item->qty }},
                            variant_id: variantId,
                            variant_name: variantName,
                            unique_key: originalProduct.id + '_' + variantId,
                            imei_numbers: imeisArray,
                            item_discount: {{ $item->discount ?? 0 }}
                                    });
                    }
                @endforeach

                setTimeout(() => {
                    renderCart();
                    updateTotals();
                    updateMobileBadge();
                }, 500);

                setTimeout(() => {
                    showFloatingAlert('success', @json(__('Quotation data loaded successfully!')));
                }, 800);
            @endif
            });

        taxInput.addEventListener('input', updateTotals);
        discountInput.addEventListener('input', updateTotals);

        // IMEI Filter
        imeiSearchInput.addEventListener('input', function (e) {
            if (!selectedProductForImei) return;
            const term = e.target.value.toLowerCase();

            let pool = [];
            if (tempVariantForImei) {
                pool = selectedProductForImei.available_imeis
                    .filter(i => i.variant_id == tempVariantForImei.id)
                    .map(i => i.number);
            } else {
                pool = selectedProductForImei.available_imeis
                    .filter(i => i.variant_id == null)
                    .map(i => i.number);
            }

            const filtered = pool.filter(imei => imei.toLowerCase().includes(term));
            renderImeiList(filtered);
        });

        // Search removed duplicated listener since it's already handled above


        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                const term = searchInput.value.trim();
                const exactMatch = products.find(p => p.barcode === term);
                if (exactMatch) {
                    addToCart(exactMatch.id);
                    searchInput.value = '';
                    filterProducts();
                }
            }
        });

        function handleCustomerChange() {
            const select = document.getElementById('customerSelect');
            const selectedOption = select.options[select.selectedIndex];
            currentCustomerPoints = parseInt(selectedOption.getAttribute('data-points')) || 0;
            currentCustomerGroupDiscount.value = parseFloat(selectedOption.getAttribute('data-group-discount')) || 0;
            currentCustomerGroupDiscount.type = selectedOption.getAttribute('data-group-discount-type') || 'percentage';

            if (!REWARD_POINTS_ENABLED) {
                updateTotals();
                return;
            }

            const rewardBox = document.getElementById('rewardPointBox');
            const pointsDisplay = document.getElementById('availablePointsDisplay');
            const toggle = document.getElementById('useRewardPointsToggle');

            if (currentCustomerPoints >= MIN_REDEEMABLE_POINTS) {
                rewardBox.style.display = 'flex';
                pointsDisplay.innerText = currentCustomerPoints;
            } else {
                rewardBox.style.display = 'none';
                toggle.checked = false;
                toggleRewardPoints();
            }
            updateTotals();
        }

        function toggleRewardPoints() {
            const isChecked = document.getElementById('useRewardPointsToggle').checked;
            const inputContainer = document.getElementById('rewardPointsInputContainer');
            const inputField = document.getElementById('usedPointsInput');

            if (isChecked) {
                // Check for minimum purchase amount requirement
                const subTotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
                if (subTotal < MIN_PURCHASE_FOR_POINTS) {
                    alert(@json(__('Minimum purchase of :amount required to use reward points.')).replace(':amount', MIN_PURCHASE_FOR_POINTS.toFixed(2)));
                    document.getElementById('useRewardPointsToggle').checked = false;
                    return;
                }

                inputContainer.style.setProperty('display', 'flex', 'important');
                inputField.value = currentCustomerPoints;
                usedRewardPoints = currentCustomerPoints;
            } else {
                inputContainer.style.setProperty('display', 'none', 'important');
                inputField.value = '';
                usedRewardPoints = 0;
            }
            updateTotals();
        }

        function validateAndUpdatePoints() {
            const inputField = document.getElementById('usedPointsInput');
            let val = parseInt(inputField.value) || 0;

            if (val < 0) val = 0;
            if (val > currentCustomerPoints) {
                val = currentCustomerPoints;
                inputField.value = val;
            }

            usedRewardPoints = val;
            updateTotals();
        }

        function isPhonePos() {
            return window.matchMedia('(max-width: 991.98px)').matches;
        }

        function phoneSearchPromptHtml() {
            const title = @json(__('Search or scan to add'));
            const text = @json(__('On phone, products are not listed all at once. Type a name/barcode or use the scanner, then tap to add — then open the cart to complete the sale.'));
            return `
                <div class="col-12" style="width:100%; flex:0 0 100%; max-width:100%;">
                    <div class="empty-state-box">
                        <div class="empty-state-icon"><i class="fas fa-barcode"></i></div>
                        <h6 class="empty-state-title">${title}</h6>
                        <p class="empty-state-text">${text}</p>
                    </div>
                </div>`;
        }

        // Filter Products (Unified)
        function filterProducts() {
            const selectedBranchId = branchSelect.value;
            const searchTerm = searchInput.value.toLowerCase().trim();

            if (!selectedBranchId) {
                const branchTitle = @json(__('Select a Branch to Get Started'));
                const branchText = @json(__('Choose a branch from the dropdown above to view its available products and start a new sale.'));
                productGrid.innerHTML = `
                    <div class="col-12" style="width:100%; flex:0 0 100%; max-width:100%;">
                        <div class="empty-state-box">
                            <div class="empty-state-icon"><i class="fas fa-store"></i></div>
                            <h6 class="empty-state-title">${branchTitle}</h6>
                            <p class="empty-state-text">${branchText}</p>
                        </div>
                    </div>`;
                return;
            }

            if (isPhonePos() && searchTerm.length < 2) {
                productGrid.innerHTML = phoneSearchPromptHtml();
                return;
            }

            const filtered = products.filter(p => {
                const hasStock = p.branch_stocks && p.branch_stocks[selectedBranchId] > 0;
                const matchesSearch = p.name.toLowerCase().includes(searchTerm) ||
                    p.barcode.toLowerCase().includes(searchTerm) ||
                    p.category.toLowerCase().includes(searchTerm);
                const matchesCategory = currentCategory === 'all' || p.category === currentCategory;

                return hasStock && (searchTerm === '' || matchesSearch) && matchesCategory;
            });

            renderProducts(filtered);
        }

        function updateCategoryTabs() {
            const selectedBranchId = branchSelect.value;
            if (!selectedBranchId) {
                document.querySelectorAll('.category-tab').forEach(btn => btn.style.display = 'none');
                return;
            }

            const availableCategories = new Set();
            products.forEach(p => {
                if (p.branch_stocks && p.branch_stocks[selectedBranchId] > 0) {
                    availableCategories.add(p.category);
                }
            });

            let currentCategoryStillVisible = false;
            document.querySelectorAll('.category-tab').forEach(btn => {
                const cat = btn.dataset.cat;
                if (cat === 'all') {
                    btn.style.display = 'block';
                    if (currentCategory === 'all') currentCategoryStillVisible = true;
                } else if (availableCategories.has(cat)) {
                    btn.style.display = 'block';
                    if (currentCategory === cat) currentCategoryStillVisible = true;
                } else {
                    btn.style.display = 'none';
                }
            });

            if (!currentCategoryStillVisible) {
                currentCategory = 'all';
                document.querySelectorAll('.category-tab').forEach(btn => {
                    if (btn.dataset.cat === 'all') btn.classList.add('active');
                    else btn.classList.remove('active');
                });
            }
        }

        function filterProductsByBranch() {
            updateCategoryTabs();
            filterProducts();
        }

        searchInput.addEventListener('input', () => {
            filterProducts();
        });

        // Add to Cart Logic
        function addToCart(id, specificImei = null, specificVariant = null) {
            const selectedBranchId = branchSelect.value;
            if (!selectedBranchId) {
                alert(@json(__('Please select a branch first!')));
                branchSelect.focus();
                return;
            }

            const product = products.find(p => p.id === id);

            const stockInBranch = (product.branch_stocks && product.branch_stocks[selectedBranchId]) ?
                product.branch_stocks[selectedBranchId] :
                0;

            if (stockInBranch <= 0) {
                alert(@json(__('Product is not available in the selected branch!')));
                return;
            }

            if (product.variants && product.variants.length > 0 && !specificVariant) {
                openVariantModal(product);
                return;
            }
            if (product.has_imei == 1 && !specificImei) {
                openImeiSelectModal(product, specificVariant);
                return;
            }

            let finalPrice = product.price;
            let variantId = null;
            let variantName = null;
            let availableStock = stockInBranch;

            if (specificVariant) {
                finalPrice = specificVariant.price;
                variantId = specificVariant.id;
                variantName = specificVariant.name;
                availableStock = (specificVariant.branch_stocks && specificVariant.branch_stocks[selectedBranchId]) ?
                    specificVariant.branch_stocks[selectedBranchId] : 0;
            }

            const uniqueKey = product.id + '_' + (variantId ? variantId : 'null');
            const existing = cart.find(item => item.unique_key === uniqueKey);

            if (existing) {
                if (existing.qty + 1 > availableStock) {
                    alert(@json(__('Cannot add more! Only :stock items available in stock.')).replace(':stock', availableStock));
                    return;
                }
                if (product.has_imei == 1 && specificImei) {
                    if (existing.imei_numbers.includes(specificImei)) {
                        alert(@json(__('This IMEI is already added to cart!')));
                        return;
                    }
                    existing.qty++;
                    existing.imei_numbers.push(specificImei);
                } else {
                    existing.qty++;
                }
            } else {
                if (1 > availableStock) {
                    alert(@json(__('Cannot add more! Only :stock items available in stock.')).replace(':stock', availableStock));
                    return;
                }
                cart.push({
                    ...product,
                    id: product.id,
                    price: finalPrice,
                    qty: 1,
                    variant_id: variantId,
                    variant_name: variantName,
                    unique_key: uniqueKey,
                    imei_numbers: specificImei ? [specificImei] : []
                });
            }
            renderCart();
        }

        function openVariantModal(product) {
            selectedProductForVariant = product;
            const selectedBranchId = document.getElementById('branchSelect').value;

            document.getElementById('variantProductName').innerText = product.name;
            const container = document.getElementById('variantButtons');
            container.innerHTML = '';

            let hasStockVariant = false;

            product.variants.forEach(variant => {
                const stock = variant.branch_stocks && variant.branch_stocks[selectedBranchId] ?
                    variant.branch_stocks[selectedBranchId] :
                    0;
                if (stock > 0) {
                    hasStockVariant = true;
                    const btn = document.createElement('button');
                    btn.className =
                        'btn btn-outline-secondary d-flex justify-content-between align-items-center py-2';
                    btn.innerHTML = `
                            <span>${variant.name} <small class="text-muted">(${stock} in stock)</small></span> 
                            <span class="fw-bold">${formatCurrency(variant.price.toFixed(2))}</span>
                        `;
                    btn.onclick = () => selectVariant(variant);
                    container.appendChild(btn);
                }
            });

            if (!hasStockVariant) {
                container.innerHTML = '<div class="text-center text-danger">No variants available in this branch!</div>';
            }

            variantModalInstance.show();
        }

        function selectVariant(variant) {
            if (!selectedProductForVariant) return;
            const pId = selectedProductForVariant.id;
            variantModalInstance.hide();

            const product = selectedProductForVariant;
            selectedProductForVariant = null;

            if (product.has_imei == 1) {
                openImeiSelectModal(product, variant);
            } else {
                addToCart(pId, null, variant);
            }
        }

        function openImeiSelectModal(product, variant = null) {
            selectedProductForImei = product;
            tempVariantForImei = variant;

            let title = `Select IMEI for ${product.name}`;
            if (variant) {
                title += ` (${variant.name})`;
            }
            document.getElementById('imeiSelectTitle').innerText = title;

            imeiSearchInput.value = '';
            let filteredImeis = [];

            if (variant) {
                filteredImeis = product.available_imeis
                    .filter(i => i.variant_id == variant.id)
                    .map(i => i.number);
            } else {
                filteredImeis = product.available_imeis
                    .filter(i => i.variant_id == null)
                    .map(i => i.number);
                if (filteredImeis.length === 0 && !variant && product.available_imeis.length > 0) {
                    filteredImeis = product.available_imeis.map(i => i.number);
                }
            }

            renderImeiList(filteredImeis);

            imeiSelectModalInstance.show();
            setTimeout(() => imeiSearchInput.focus(), 500);
        }

        function renderImeiList(imeiArray) {
            const container = document.getElementById('imeiListContainer');
            if (!imeiArray || imeiArray.length === 0) {
                container.innerHTML = '<div class="text-center text-muted p-3">No Available IMEI Found</div>';
                return;
            }
            container.innerHTML = imeiArray.map(imei => `
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                            onclick="confirmImeiSelection('${imei}')">
                            <span>${imei}</span>
                            <i class="fas fa-plus-circle text-primary"></i>
                        </button>
                    `).join('');
        }

        function confirmImeiSelection(imei) {
            if (selectedProductForImei) {
                const pId = selectedProductForImei.id;
                const variant = tempVariantForImei;
                imeiSelectModalInstance.hide();
                addToCart(pId, imei, variant);
                selectedProductForImei = null;
                tempVariantForImei = null;
            }
        }

        function renderCart() {
            if (cart.length === 0) {
                cartContainer.style.overflowY = 'hidden';
                cartContainer.innerHTML = `
                    <div class="m-auto text-center p-4" style="width: 100%;">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 72px; height: 72px; background-color: rgba(var(--primary-rgb), 0.1); border-radius: 50%;">
                            <i class="fas fa-shopping-basket" style="font-size: 28px; color: var(--primary-color);"></i>
                        </div>
                        <h6 class="fw-bold text-dark mb-2 text-center mx-auto" style="font-size: 1rem;">Your Cart is Empty</h6>
                        <p class="text-muted mb-0 text-center mx-auto" style="max-width: 260px; font-size: 0.85rem; line-height: 1.5;">Scan a barcode or add products from the catalog to get started.</p>
                    </div>`;
            } else {
                cartContainer.style.overflowY = 'auto';
                cartContainer.innerHTML = cart.map(item => `
                        <div class="cart-item d-flex p-2 animate__animated animate__fadeIn w-100" data-cart-key="${item.unique_key}">
                            <img src="${item.image}" alt="${item.name}" class="cart-item-img me-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;">
                            <div class="flex-grow-1 overflow-hidden d-flex flex-column w-100">
                                <div class="d-flex align-items-start mb-1 w-100">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 0.85rem; max-width: 85%; line-height: 1.2;">
                                        ${item.name} ${item.variant_name ? `<small class="text-muted">(${item.variant_name})</small>` : ``}
                                    </div>
                                    <i class="fas fa-times text-danger ms-auto" style="cursor:pointer; padding: 2px;" onclick="removeFromCart('${item.unique_key}')"></i>
                                </div>
                                ${item.imei_numbers && item.imei_numbers.length > 0 ? `<div class="text-primary small text-wrap mb-1" style="font-size:0.7rem;">SN: ${item.imei_numbers.join(', ')}</div>` : ''}

                                <div class="d-flex align-items-center mt-auto w-100">
                                    <div class="text-muted small cart-line-meta" style="font-size: 0.8rem;">${item.price.toFixed(2)} <span style="font-size: 0.7rem;">x</span> ${item.qty}</div>
                                    <div class="cart-qty-box mx-2" onclick="event.stopPropagation()">
                                        <button type="button" class="btn btn-sm" onclick="updateQty('${item.unique_key}', -1)" aria-label=@json(__('Decrease quantity'))>-</button>
                                        <input type="number" class="cart-qty-input" min="1" step="1" inputmode="numeric"
                                            data-cart-key="${item.unique_key}"
                                            value="${item.qty}"
                                            ${item.has_imei == 1 ? 'readonly tabindex="-1"' : ''}
                                            aria-label="Quantity"
                                            onfocus="this.select()"
                                            onkeydown="if(event.key==='Enter'){event.preventDefault(); this.blur();}">
                                        <button type="button" class="btn btn-sm" ${item.has_imei == 1 ? 'disabled style="opacity:0.35; cursor:not-allowed;"' : ''} onclick="updateQty('${item.unique_key}', 1)" aria-label=@json(__('Increase quantity'))>+</button>
                                    </div>
                                    <div class="fw-bold text-end text-primary-theme ms-auto cart-line-total" style="font-size: 0.9rem;">${formatCurrency((item.price * item.qty).toFixed(2))}</div>
                                </div>
                            </div>
                        </div>
                    `).join('');
            }
            updateTotals();
            updateMobileBadge();
            cartContainer.querySelectorAll('.cart-qty-input').forEach(fitCartQtyInput);
        }

        function getCartItemStock(item) {
            const selectedBranchId = document.getElementById('branchSelect').value;
            if (item.variant_id) {
                const variant = item.variants.find(v => v.id === item.variant_id);
                if (variant && variant.branch_stocks) {
                    return Number(variant.branch_stocks[selectedBranchId] || 0);
                }
                return 0;
            }
            if (item.branch_stocks) {
                return Number(item.branch_stocks[selectedBranchId] || 0);
            }
            return 0;
        }

        function fitCartQtyInput(input) {
            if (!input) return;
            const digits = String(input.value || '').replace(/\D/g, '');
            const len = Math.max(digits.length, 1);
            const ch = Math.min(Math.max(len + 1, 2), 9);
            input.style.width = ch + 'ch';
        }

        function refreshCartLine(uniqueKey) {
            const item = cart.find(i => i.unique_key === uniqueKey);
            const row = cartContainer.querySelector(`.cart-item[data-cart-key="${CSS.escape(String(uniqueKey))}"]`);
            if (!item || !row) return;
            const meta = row.querySelector('.cart-line-meta');
            const total = row.querySelector('.cart-line-total');
            const input = row.querySelector('.cart-qty-input');
            if (meta) {
                meta.innerHTML = `${item.price.toFixed(2)} <span style="font-size: 0.7rem;">x</span> ${item.qty}`;
            }
            if (total) {
                total.innerText = formatCurrency((item.price * item.qty).toFixed(2));
            }
            if (input && document.activeElement !== input) {
                input.value = item.qty;
            }
            if (input) fitCartQtyInput(input);
            updateTotals();
            updateMobileBadge();
        }

        function applyTypedCartQty(uniqueKey, rawValue, finalize) {
            const item = cart.find(i => i.unique_key === uniqueKey);
            if (!item || item.has_imei == 1) return;

            const qty = parseInt(String(rawValue).trim(), 10);
            if (!Number.isFinite(qty) || qty < 1) {
                if (finalize) removeFromCart(uniqueKey);
                return;
            }

            item.qty = qty;
            if (finalize) {
                renderCart();
            } else {
                refreshCartLine(uniqueKey);
            }
        }

        function updateQty(uniqueKey, change) {
            const item = cart.find(i => i.unique_key === uniqueKey);
            if (!item) return;

            if (change > 0) {
                const availableStock = getCartItemStock(item);
                if (item.qty + change > availableStock) {
                    alert(@json(__('Cannot add more! Only :stock items available in stock.')).replace(':stock', availableStock));
                    return;
                }
            }

            if (item.has_imei == 1 && change > 0) return;
            if (item.has_imei == 1 && change < 0) item.imei_numbers.pop();
            item.qty += change;
            if (item.qty <= 0) removeFromCart(uniqueKey);
            else renderCart();
        }

        if (!cartContainer.dataset.qtyBound) {
            cartContainer.dataset.qtyBound = '1';
            cartContainer.addEventListener('input', function(e) {
                const input = e.target.closest('.cart-qty-input');
                if (!input || input.readOnly) return;
                fitCartQtyInput(input);
                applyTypedCartQty(input.getAttribute('data-cart-key'), input.value, false);
            });
            cartContainer.addEventListener('change', function(e) {
                const input = e.target.closest('.cart-qty-input');
                if (!input || input.readOnly) return;
                applyTypedCartQty(input.getAttribute('data-cart-key'), input.value, true);
            });
            cartContainer.addEventListener('focusout', function(e) {
                const input = e.target.closest('.cart-qty-input');
                if (!input || input.readOnly) return;
                applyTypedCartQty(input.getAttribute('data-cart-key'), input.value, true);
            });
        }

        function removeFromCart(uniqueKey) {
            cart = cart.filter(i => i.unique_key !== uniqueKey);
            renderCart();
        }

        function clearCart() {
            if (cart.length === 0) return;
            clearOrderModalInstance.show();
        }

        document.getElementById('confirmClearOrderBtn').addEventListener('click', function() {
            cart = [];
            renderCart();
            clearOrderModalInstance.hide();
        });

        function renderProducts(items) {
            const selectedBranchId = document.getElementById('branchSelect').value;

            productGrid.innerHTML = items.map(p => {

                const currentStock = (p.branch_stocks && p.branch_stocks[selectedBranchId]) ?
                    p.branch_stocks[selectedBranchId] :
                    0;

                const stockClass = currentStock <= 5 ? 'bg-danger text-white' : 'bg-white text-dark border';

                let badgesHtml = '';
                if (p.variants.length > 0) badgesHtml += '<span class="badge-custom-variant">Variant</span>';
                if (p.has_imei) badgesHtml += '<span class="badge-custom-imei">IMEI</span>';

                return `
                            <div class="col">
                                <div class="product-card" onclick="addToCart(${p.id})">

                                    <div class="product-img-wrapper">
                                        <div class="pos-badge-top-left">
                                            ${badgesHtml}
                                        </div>
                                        <div class="pos-badge-top-right ${stockClass}" title="${currentStock} ${p.unit || 'Pcs'}">
                                            ${currentStock} ${p.unit || 'Pcs'}
                                        </div>
                                        <img src="${p.image}" alt="${p.name}">
                                    </div>

                                    <div class="text-center mt-1 mb-2 flex-grow-1 overflow-hidden">
                                        <h6 class="fw-bold text-dark mb-1 text-truncate" title="${p.name}" style="font-size: 0.85rem;">${p.name}</h6>
                                        <small class="text-muted fw-semibold d-block text-truncate" style="font-size: 0.75rem;">${p.category}</small>
                                    </div>

                                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bolder text-primary-theme mb-0 text-truncate me-2" style="font-size: 0.9rem;">${formatCurrency(p.price % 1 === 0 ? parseInt(p.price) : parseFloat(p.price).toFixed(2))}</h6>
                                        <small class="text-muted text-end text-truncate" style="font-size: 0.65rem;" title="Barcode: ${p.barcode}">
                                            <i class="fas fa-barcode"></i> ${p.barcode}
                                        </small>
                                    </div>

                                </div>
                            </div>
                            `;
            }).join('');
        }

        function updateDiscountType(type) {
            currentDiscountType = type;
            updateTotals();
        }

        function updateTotals() {
            globalSubTotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);

            // Calculate Group Discount
            if (currentCustomerGroupDiscount.value > 0) {
                if (currentCustomerGroupDiscount.type === 'percentage') {
                    globalGroupDiscountAmount = (globalSubTotal * currentCustomerGroupDiscount.value) / 100;
                } else {
                    globalGroupDiscountAmount = currentCustomerGroupDiscount.value;
                }
                if (globalGroupDiscountAmount > globalSubTotal) globalGroupDiscountAmount = globalSubTotal;
            } else {
                globalGroupDiscountAmount = 0;
            }

            const priceAfterGroupDiscount = globalSubTotal - globalGroupDiscountAmount;

            if (appliedCoupon) {
                if (appliedCoupon.type === 'percent') {
                    couponDiscountAmount = (priceAfterGroupDiscount * appliedCoupon.value) / 100;
                    if (appliedCoupon.max_discount && couponDiscountAmount > appliedCoupon.max_discount) {
                        couponDiscountAmount = appliedCoupon.max_discount;
                    }
                } else {
                    couponDiscountAmount = appliedCoupon.amount;
                }
                if (couponDiscountAmount > priceAfterGroupDiscount) couponDiscountAmount = priceAfterGroupDiscount;
            } else {
                couponDiscountAmount = 0;
            }

            const priceAfterCoupon = priceAfterGroupDiscount - couponDiscountAmount;

            let discVal = parseFloat(discountInput.value) || 0;

            if (currentDiscountType === 'percent') {
                globalManualDiscount = priceAfterCoupon * (discVal / 100);
            } else {
                globalManualDiscount = discVal;
            }
            if (globalManualDiscount > priceAfterCoupon) globalManualDiscount = priceAfterCoupon;

            const taxableAmount = Math.max(0, priceAfterCoupon - globalManualDiscount);

            let taxRate = parseFloat(taxInput.value) || 0;
            globalTaxAmount = taxableAmount * (taxRate / 100);

            const totalBeforeReward = taxableAmount + globalTaxAmount;

            if (totalBeforeReward < MIN_PURCHASE_FOR_POINTS) {
                usedRewardPoints = 0;
                pointDiscountAmount = 0;
                document.getElementById('useRewardPointsToggle').checked = false;
                document.getElementById('rewardPointsInputContainer').style.setProperty('display', 'none', 'important');
            } else {
                pointDiscountAmount = usedRewardPoints * POINT_REDEMPTION_RATE;

                if (pointDiscountAmount > totalBeforeReward) {
                    pointDiscountAmount = totalBeforeReward;
                    usedRewardPoints = totalBeforeReward / POINT_REDEMPTION_RATE;
                    document.getElementById('usedPointsInput').value = Math.floor(usedRewardPoints);
                }
            }

            finalGrandTotal = Math.max(0, totalBeforeReward - pointDiscountAmount);

            // UI Update Data
            document.getElementById('subtotalDisplay').innerText = `${formatCurrency(globalSubTotal.toFixed(2))}`;

            // Group Discount UI
            const groupDiscountRow = document.getElementById('groupDiscountRow');
            if (groupDiscountRow) {
                if (globalGroupDiscountAmount > 0) {
                    groupDiscountRow.style.setProperty('display', 'flex', 'important');
                    document.getElementById('groupDiscountDisplay').innerText = `${formatCurrency(globalGroupDiscountAmount.toFixed(2))}`;
                } else {
                    groupDiscountRow.style.setProperty('display', 'none', 'important');
                }
            }

            // Coupon UI
            if (appliedCoupon) {
                document.getElementById('couponRow').style.setProperty('display', 'flex', 'important');
                document.getElementById('displayCouponCode').innerText = appliedCoupon.code;
                document.getElementById('displayCouponAmount').innerText = `${formatCurrency(couponDiscountAmount.toFixed(2))}`;
                document.getElementById('couponCodeInput').parentElement.style.display = 'none';
                document.getElementById('couponMessage').style.display = 'none';
            } else {
                document.getElementById('couponRow').style.setProperty('display', 'none', 'important');
                document.getElementById('couponCodeInput').parentElement.style.display = 'flex';
            }

            // Manual Discount UI
            const discountRow = document.getElementById('discountRow');
            if (globalManualDiscount > 0) {
                discountRow.style.setProperty('display', 'flex', 'important');
                document.getElementById('discountDisplay').innerText = `${formatCurrency(globalManualDiscount.toFixed(2))}`;
            } else {
                discountRow.style.setProperty('display', 'none', 'important');
            }

            // Reward Point UI
            const rewardPointRow = document.getElementById('rewardPointRow');
            if (pointDiscountAmount > 0) {
                rewardPointRow.style.setProperty('display', 'flex', 'important');
                document.getElementById('usedPointsDisplay').innerText = usedRewardPoints;
                document.getElementById('pointDiscountDisplay').innerText = `${formatCurrency(pointDiscountAmount.toFixed(2))}`;
            } else {
                rewardPointRow.style.setProperty('display', 'none', 'important');
            }

            document.getElementById('taxDisplay').innerText = `${formatCurrency(globalTaxAmount.toFixed(2))}`;
            document.getElementById('totalDisplay').innerText = `${formatCurrency(finalGrandTotal.toFixed(2))}`;
            document.getElementById('modalTotalPayable').innerText = `${formatCurrency(finalGrandTotal.toFixed(2))}`;
        }

        function isFlagEnabled(value) {
            return value === true || value === 1 || value === '1';
        }

        function findAccountById(accountId) {
            return availableAccounts.find(acc => String(acc.id) === String(accountId)) || null;
        }

        function accountAllowsManualAmount(accountId) {
            if (!accountId || accountId === 'Gift Card') {
                return true;
            }
            const acc = findAccountById(accountId);
            return acc ? isFlagEnabled(acc.allow_manual_amount) : false;
        }

        function remainingPayableExcluding(amountInput) {
            let paid = 0;
            document.querySelectorAll('.payment-amount').forEach(input => {
                if (input !== amountInput) {
                    paid += parseFloat(input.value) || 0;
                }
            });
            return Math.max(0, parseFloat((finalGrandTotal - paid).toFixed(2)));
        }

        function syncPaymentAmountForRow(rowEl, options = {}) {
            if (!rowEl) {
                return;
            }
            const methodSelect = rowEl.querySelector('.payment-method');
            const amountInput = rowEl.querySelector('.payment-amount');
            if (!methodSelect || !amountInput) {
                return;
            }

            const accountId = methodSelect.value;
            const isManual = !accountId || accountAllowsManualAmount(accountId);

            if (isManual) {
                amountInput.value = '';
                amountInput.placeholder = @json(__('Amount'));
                if (options.focus) {
                    amountInput.focus();
                    amountInput.select();
                }
            } else {
                amountInput.value = remainingPayableExcluding(amountInput);
                amountInput.placeholder = '0.00';
            }
        }

        function getPaymentRowHtml(selectDefault = false) {
            const defaultId = String(defaultPaymentAccountId || '');
            const hasDefault = selectDefault && defaultId && availableAccounts.some(acc => String(acc.id) === defaultId);
            let optionsHtml = `<option value="" ${hasDefault ? '' : 'selected'} disabled>${@json(__('Select payment method'))}</option>`;

            if (availableAccounts.length > 0) {
                availableAccounts.forEach(acc => {
                    let icon = 'fas fa-wallet';
                    let nameLower = acc.name.toLowerCase();
                    let typeLower = (acc.type || '').toLowerCase();

                    if (nameLower.includes('cash') || typeLower.includes('cash')) icon = 'fas fa-money-bill';
                    else if (nameLower.includes('card') || nameLower.includes('bank') || typeLower.includes('bank'))
                        icon = 'fas fa-university';
                    else if (nameLower.includes('mobile') || nameLower.includes('bkash') || nameLower.includes(
                        'nagad'))
                        icon = 'fas fa-mobile-alt';

                    const selected = hasDefault && String(acc.id) === defaultId ? 'selected' : '';
                    const manualFlag = isFlagEnabled(acc.allow_manual_amount) ? '1' : '0';
                    const defaultFlag = isFlagEnabled(acc.is_default) ? '1' : '0';
                    optionsHtml += `<option value="${acc.id}" data-icon="${icon}" data-allow-manual-amount="${manualFlag}" data-is-default="${defaultFlag}" ${selected}>${acc.name}</option>`;
                });
            } else {
                optionsHtml += `<option value="" disabled>${@json(__('No Accounts Found'))}</option>`;
            }

            // একদম নিচে Gift Card অপশনটি ম্যানুয়ালি যোগ করে দিচ্ছি
            optionsHtml += `<option value="Gift Card" class="text-primary fw-bold">🎁 ${@json(__('Gift Card'))}</option>`;

            const addBtnHtml = canCreateAccounts
                ? `<button type="button" class="btn btn-outline-primary btn-qs-pay-add" onclick="openQsPaymentAccountModal(this.closest('.qs-pay-field-group').querySelector('.payment-method'))" title="${@json(__('Add Payment Method'))}"><i class="fas fa-plus"></i></button>`
                : '';

            return `
                        <div class="payment-row payment-row-card animate__animated animate__fadeIn">
                            <div class="payment-field payment-field-method">
                                <div class="input-group flex-nowrap qs-pay-field-group">
                                    <select class="form-select custom-payment-select payment-method" onchange="handlePaymentMethodChange(this)">
                                        ${optionsHtml}
                                    </select>
                                    ${addBtnHtml}
                                </div>
                            </div>

                            <div class="payment-field-row">
                                <div class="flex-grow-1 input-icon-group payment-field-amount">
                                    <span class="text-muted small fw-bold payment-currency-symbol">{{ \App\Helpers\CurrencyHelper::getSymbol() }}</span>
                                    <input type="number" class="form-control payment-amount"
                                        value="" placeholder="0.00" oninput="calculateSplitTotal()">
                                </div>

                                <div class="flex-grow-1 input-icon-group note-container payment-field-note">
                                    <i class="fas fa-receipt text-muted small note-icon"></i>
                                    <input type="text" class="form-control payment-note" placeholder="${@json(__('Trx ID'))}" title="${@json(__('Transaction ID (optional)'))}">
                                </div>

                                <button type="button" class="btn btn-icon btn-sm text-danger hover-bg-light rounded-circle payment-remove-btn"
                                        onclick="removePaymentRow(this)" title="${@json(__('Remove'))}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>`;
        }

        window.handlePaymentMethodChange = function (selectElement) {
            const row = $(selectElement).closest('.payment-row');
            const noteInput = row.find('.payment-note');
            const noteIcon = row.find('.note-icon');

            if (selectElement.value === 'Gift Card') {
                noteInput.attr('placeholder', @json(__('Enter Gift Card No')));
                noteInput.addClass('border-primary');
                noteIcon.removeClass('fa-receipt').addClass('fa-gift text-primary');

                noteInput.off('blur').on('blur', function () {
                    const cardNo = $(this).val().trim();
                    const amountInput = row.find('.payment-amount');

                    if (cardNo) {
                        fetch("{{ route('quick-sale.check-gift-card') }}", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                                "Accept": "application/json"
                            },
                            body: JSON.stringify({
                                card_no: cardNo
                            })
                        })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    showFloatingAlert('success', @json(__('Gift Card Balance: :balance TK')).replace(':balance', data.balance));
                                    let currentNeeded = parseFloat(amountInput.val()) || 0;
                                    if (data.balance < currentNeeded) {
                                        amountInput.val(data.balance);
                                        calculateSplitTotal();
                                    }
                                } else {
                                    alert(data.message);
                                    $(this).val('');
                                }
                            });
                    }
                });
            } else {
                noteInput.attr('placeholder', @json(__('Trx ID')));
                noteInput.attr('title', @json(__('Transaction ID (optional)')));
                noteInput.removeClass('border-primary');
                noteIcon.removeClass('fa-gift text-primary').addClass('fa-receipt');
                noteInput.off('blur');
            }

            syncPaymentAmountForRow(row[0], { focus: true });
            calculateSplitTotal();
        };

        function openPaymentModal() {
            if (cart.length === 0) {
                alert(@json(__('Cart is empty!')));
                return;
            }
            
            const sellerId = getSelectedSellerId();
            if (!sellerId) {
                alert(@json(__('Please select a Seller before completing the sale!')));
                return;
            }
            document.getElementById('modalTotalPayable').innerText = `${formatCurrency(finalGrandTotal.toFixed(2))}`;
            const pickupRadio = document.getElementById('qsFulfillPickup');
            const deliveryRadio = document.getElementById('qsFulfillDelivery');
            if (pickupRadio) pickupRadio.checked = true;
            if (deliveryRadio) deliveryRadio.checked = false;
            syncQsFulfillmentHint();
            const container = document.getElementById('paymentRowsContainer');
            container.innerHTML = getPaymentRowHtml(true);
            const firstRow = container.querySelector('.payment-row');
            syncPaymentAmountForRow(firstRow, {
                focus: firstRow ? accountAllowsManualAmount(firstRow.querySelector('.payment-method')?.value) : false
            });
            calculateSplitTotal();
            paymentModalInstance.show();
        }

        function getQsFulfillmentType() {
            const delivery = document.getElementById('qsFulfillDelivery');
            return delivery && delivery.checked ? 'delivery' : 'pickup';
        }

        function syncQsFulfillmentHint() {
            const hint = document.getElementById('qsDeliveryHint');
            if (!hint) return;
            hint.classList.toggle('d-none', getQsFulfillmentType() !== 'delivery');
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.name === 'qs_fulfillment') {
                syncQsFulfillmentHint();
            }
        });

        function addPaymentRow() {
            const container = document.getElementById('paymentRowsContainer');
            container.insertAdjacentHTML('beforeend', getPaymentRowHtml(false));
            syncPaymentAmountForRow(container.lastElementChild);
            calculateSplitTotal();
        }

        function removePaymentRow(btn) {
            const container = document.getElementById('paymentRowsContainer');
            if (container.children.length > 1) {
                btn.closest('.payment-row').remove();
                calculateSplitTotal();
            } else {
                alert(@json(__('At least one payment method is required!')));
            }
        }

        /* --- Quick Add Payment Account (nested over payment modal) --- */
        let qsAccountTargetSelect = null;
        let qsPausedPaymentModal = false;
        let qsResumePaymentScheduled = false;

        (function initQsAccountModal() {
            const el = document.getElementById('qsAddAccountModal');
            if (!el) return;
            if (el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
            el.addEventListener('hidden.bs.modal', function () {
                qsResumePaymentModal();
            });
            el.addEventListener('show.bs.modal', function () {
                const backdrop = document.querySelector('.modal-backdrop:last-of-type');
                if (backdrop) backdrop.classList.add('qs-quick-backdrop');
            });
        })();

        function qsGetBsModal(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) return null;
            return bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
        }

        function qsHideModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            const bs = qsGetBsModal(el);
            if (bs) {
                bs.hide();
            } else {
                $('#' + id).modal('hide');
            }
        }

        function qsShowModal(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (id === 'paymentModal' && paymentModalInstance) {
                paymentModalInstance.show();
                return;
            }
            const bs = qsGetBsModal(el);
            if (bs) {
                bs.show();
            } else {
                $('#' + id).modal('show');
            }
        }

        function qsPromptPrintReceipt(onPrint, onSkip) {
            const el = document.getElementById('saleSuccessPrintModal');
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                if (typeof onSkip === 'function') onSkip();
                return;
            }
            if (el.parentElement !== document.body) {
                document.body.appendChild(el);
            }
            const modal = bootstrap.Modal.getOrCreateInstance(el);
            const printBtn = document.getElementById('saleSuccessPrintBtn');
            let printChosen = false;

            printBtn.onclick = function () {
                printChosen = true;
                modal.hide();
            };
            el.addEventListener('shown.bs.modal', function () {
                printBtn.focus();
            }, { once: true });
            el.addEventListener('hidden.bs.modal', function () {
                printBtn.onclick = null;
                if (printChosen) {
                    if (typeof onPrint === 'function') onPrint();
                } else if (typeof onSkip === 'function') {
                    onSkip();
                }
            }, { once: true });

            modal.show();
            const backdrops = document.querySelectorAll('.modal-backdrop');
            if (backdrops.length > 1) {
                backdrops[backdrops.length - 1].classList.add('qs-quick-backdrop');
            }
        }

        function qsClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function qsResumePaymentModal() {
            if (!qsPausedPaymentModal || qsResumePaymentScheduled) return;
            qsPausedPaymentModal = false;
            qsResumePaymentScheduled = true;
            setTimeout(function () {
                qsResumePaymentScheduled = false;
                qsClearStrayBackdrops();
                qsShowModal('paymentModal');
            }, 200);
        }

        function qsOpenAccountOverPayment() {
            const parentEl = document.getElementById('paymentModal');
            const openQuick = function () {
                qsShowModal('qsAddAccountModal');
            };
            const parentVisible = parentEl && (
                parentEl.classList.contains('show') ||
                $(parentEl).is(':visible')
            );
            if (parentVisible) {
                qsPausedPaymentModal = true;
                qsResumePaymentScheduled = false;
                const onParentHidden = function () {
                    parentEl.removeEventListener('hidden.bs.modal', onParentHidden);
                    openQuick();
                };
                parentEl.addEventListener('hidden.bs.modal', onParentHidden);
                qsHideModal('paymentModal');
            } else {
                qsPausedPaymentModal = false;
                openQuick();
            }
        }

        function qsEscapeHtml(text) {
            return String(text == null ? '' : text)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function qsShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) { return m.trim(); }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html = '<div class="qs-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            if (messages.length === 1) {
                html += '<div>' + qsEscapeHtml(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) { html += '<li>' + qsEscapeHtml(m) + '</li>'; });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try { box.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch (e) {}
            }
        }

        function qsClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
                $(formEl).find('.qs-field-error').remove();
            }
        }

        function qsMarkFieldErrors(formEl, errors) {
            if (!formEl || !errors) return;
            Object.keys(errors).forEach(function (field) {
                const input = formEl.querySelector('[name="' + field + '"]');
                if (!input) return;
                input.classList.add('is-invalid');
                const msg = Array.isArray(errors[field]) ? errors[field][0] : errors[field];
                if (!msg) return;
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block qs-field-error';
                feedback.textContent = msg;
                input.insertAdjacentElement('afterend', feedback);
            });
        }

        function qsExtractErrorPayload(xhr) {
            let payload = xhr.responseJSON || null;
            if (!payload && xhr.responseText) {
                try { payload = JSON.parse(xhr.responseText); } catch (e) { payload = null; }
            }
            if (xhr.status === 419) {
                return { messages: [@json(__('Your session has expired. Please refresh the page and try again.'))], errors: null };
            }
            if (xhr.status === 403) {
                return { messages: [(payload && payload.message) || @json(__('You do not have permission to perform this action.'))], errors: null };
            }
            if (xhr.status === 422 && payload && payload.errors) {
                const messages = [];
                Object.keys(payload.errors).forEach(function (key) {
                    (payload.errors[key] || []).forEach(function (item) { messages.push(item); });
                });
                return { messages: messages.length ? messages : [@json(__('Validation failed.'))], errors: payload.errors };
            }
            if (payload && payload.message) {
                return { messages: [payload.message], errors: payload.errors || null };
            }
            if (xhr.status === 0) {
                return { messages: [@json(__('Network error. Please check your connection.'))], errors: null };
            }
            return { messages: [@json(__('Something went wrong. Please try again.'))], errors: null };
        }

        function qsHandleSaveFail(errorId, formEl, xhr) {
            const payload = qsExtractErrorPayload(xhr);
            qsClearFormError(errorId, formEl);
            if (payload.errors) qsMarkFieldErrors(formEl, payload.errors);
            qsShowFormError(errorId, payload.messages, formEl);
        }

        function syncQsAccountNumberRequirement() {
            const typeEl = document.getElementById('qs_account_type');
            const numberEl = document.getElementById('qs_account_number');
            const requiredMark = document.getElementById('qs_account_number_required');
            const hintEl = document.getElementById('qs_account_number_hint');
            if (!typeEl || !numberEl) return;
            const isCash = typeEl.value === 'cash';
            numberEl.required = !isCash;
            numberEl.placeholder = isCash ? @json(__('Not required for cash')) : @json(__('Account Number'));
            if (requiredMark) requiredMark.classList.toggle('d-none', isCash);
            if (hintEl) hintEl.classList.toggle('d-none', !isCash);
        }

        function qsBuildAccountOption(account) {
            const id = String(account.id);
            const name = account.name || ('#' + id);
            let icon = 'fas fa-wallet';
            const nameLower = name.toLowerCase();
            const typeLower = (account.type || '').toLowerCase();
            if (nameLower.includes('cash') || typeLower.includes('cash')) icon = 'fas fa-money-bill';
            else if (nameLower.includes('card') || nameLower.includes('bank') || typeLower.includes('bank')) icon = 'fas fa-university';
            else if (nameLower.includes('mobile') || nameLower.includes('bkash') || nameLower.includes('nagad')) icon = 'fas fa-mobile-alt';

            const opt = document.createElement('option');
            opt.value = id;
            opt.textContent = name;
            opt.setAttribute('data-icon', icon);
            opt.setAttribute('data-allow-manual-amount', isFlagEnabled(account.allow_manual_amount) ? '1' : '0');
            opt.setAttribute('data-is-default', isFlagEnabled(account.is_default) ? '1' : '0');
            return opt;
        }

        function qsSyncAccountToSelects(account, preferredSelect) {
            if (!account || account.id == null) return;

            if (!availableAccounts.some(function (acc) { return String(acc.id) === String(account.id); })) {
                availableAccounts.push({
                    id: account.id,
                    name: account.name,
                    type: account.type || '',
                    allow_manual_amount: account.allow_manual_amount ? 1 : 0,
                    is_default: account.is_default ? 1 : 0,
                    is_active: account.is_active ? 1 : 0
                });
            }

            document.querySelectorAll('.payment-method').forEach(function (selectEl) {
                if ($(selectEl).find('option[value="' + account.id + '"]').length) return;
                const opt = qsBuildAccountOption(account);
                const giftOpt = selectEl.querySelector('option[value="Gift Card"]');
                if (giftOpt) {
                    selectEl.insertBefore(opt, giftOpt);
                } else {
                    selectEl.appendChild(opt);
                }
            });

            const gcSelect = document.querySelector('#giftCardForm select[name="payment_method"]');
            if (gcSelect && $(gcSelect).find('option[value="' + account.id + '"]').length === 0) {
                gcSelect.appendChild(new Option(account.name, String(account.id), false, false));
            }

            if (preferredSelect) {
                preferredSelect.value = String(account.id);
                if (typeof handlePaymentMethodChange === 'function') {
                    handlePaymentMethodChange(preferredSelect);
                } else {
                    $(preferredSelect).trigger('change');
                }
            }
        }

        window.openQsPaymentAccountModal = function (selectEl) {
            const form = document.getElementById('qsAddAccountForm');
            if (!form) return;
            qsAccountTargetSelect = selectEl || null;
            form.reset();
            qsClearFormError('qsAccountFormError', form);
            const active = document.getElementById('qs_account_is_active');
            if (active) active.checked = true;
            const typeEl = document.getElementById('qs_account_type');
            if (typeEl) typeEl.value = 'cash';
            syncQsAccountNumberRequirement();
            qsOpenAccountOverPayment();
        };

        window.saveQsPaymentAccount = function (e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#qsAddAccountForm');
            const btn = $('#btnSaveQsAccount');
            if (!form.length) return false;

            qsClearFormError('qsAccountFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('accounts.store') }}",
                type: 'POST',
                data: form.serialize(),
                dataType: 'json',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .done(function (response) {
                    if (response && response.success && response.account) {
                        const a = response.account;
                        if (!a.is_active) {
                            qsShowFormError('qsAccountFormError',
                                @json(__('Account saved, but it is inactive so it was not selected.')), form[0]);
                            return;
                        }
                        qsSyncAccountToSelects(a, qsAccountTargetSelect);
                        qsHideModal('qsAddAccountModal');
                        form[0].reset();
                        const active = document.getElementById('qs_account_is_active');
                        if (active) active.checked = true;
                        return;
                    }
                    qsShowFormError('qsAccountFormError',
                        (response && response.message) || @json(__('Could not save account.')), form[0]);
                })
                .fail(function (xhr) {
                    qsHandleSaveFail('qsAccountFormError', form[0], xhr);
                })
                .always(function () {
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                });

            return false;
        };

        $(document).off('change.qsAccType', '#qs_account_type')
            .on('change.qsAccType', '#qs_account_type', syncQsAccountNumberRequirement);

        function getSumOfPayments() {
            let total = 0;
            document.querySelectorAll('.payment-amount').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            return total;
        }

        function calculateSplitTotal() {
            const totalPaid = getSumOfPayments();
            const balance = totalPaid - finalGrandTotal;
            document.getElementById('displayTotalPaid').innerText = `${formatCurrency(totalPaid.toFixed(2))}`;
            const balanceEl = document.getElementById('displayBalance');
            const labelEl = document.getElementById('balanceLabel');

            if (balance >= 0) {
                labelEl.innerText = @json(__('Change / Return:'));
                balanceEl.innerText = `${formatCurrency(balance.toFixed(2))}`;
                balanceEl.className = "fw-bold text-success";
            } else {
                labelEl.innerText = @json(__('Due Amount:'));
                balanceEl.innerText = `${formatCurrency(Math.abs(balance).toFixed(2))}`;
                balanceEl.className = "fw-bold text-danger";
            }
        }

        function processSplitPayment() {
            const totalPaid = getSumOfPayments();
            let payments = [];
            document.querySelectorAll('.payment-row').forEach(row => {
                const method = row.querySelector('.payment-method').value;
                const amount = parseFloat(row.querySelector('.payment-amount').value) || 0;
                const note = row.querySelector('.payment-note').value;

                // Allow 0 amount to support Fully Due Sales
                if (amount >= 0) {
                    payments.push({
                        method: method,
                        amount: amount,
                        note: note
                    });
                }
            });

            if (payments.length === 0) {
                alert(@json(__('Please add at least one payment method row!')));
                return;
            }

            if (payments.some(p => !p.method)) {
                alert(@json(__('Please select a payment method for each row!')));
                return;
            }

            const fulfillment = getQsFulfillmentType();
            // Sync Payload entirely with UI Global calculation variables
            const payload = {
                branch_id: getSelectedBranchId(),
                customer_id: document.querySelector('select[name="customer_id"]').value,
                seller_id: getSelectedSellerId(),
                sale_date: document.getElementById('date').value,
                items: cart,
                sub_total: globalSubTotal,
                grand_total: finalGrandTotal,
                payments: payments,
                use_reward_points: REWARD_POINTS_ENABLED && document.getElementById('useRewardPointsToggle')?.checked ?
                    1 : 0,
                used_points: REWARD_POINTS_ENABLED ? usedRewardPoints : 0,
                discount_type: currentDiscountType,
                discount_value: parseFloat(document.getElementById('discountInput').value) || 0,
                discount_amount: globalManualDiscount,
                group_discount_amount: globalGroupDiscountAmount,
                coupon_id: appliedCoupon ? appliedCoupon.id : null,
                coupon_discount_amount: couponDiscountAmount,
                tax_percent: parseFloat(document.getElementById('taxInput').value) || 0,
                tax_amount: globalTaxAmount,
                note: document.getElementById('paymentNote').value,
                quotation_id: "{{ isset($quotation) ? $quotation->id : '' }}",
                delivery_status: fulfillment === 'delivery' ? 'pending' : 'delivered',
                fulfillment_type: fulfillment
            };

            const btn = document.querySelector('button[onclick="processSplitPayment()"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            const showPrintingOverlay = function () {
                const el = document.getElementById('qsPrintingOverlay');
                if (el) {
                    el.classList.add('is-visible');
                }
            };
            const hidePrintingOverlay = function () {
                const el = document.getElementById('qsPrintingOverlay');
                if (el) {
                    el.classList.remove('is-visible');
                }
            };
            const printOpts = function (afterDone) {
                return {
                    onStart: showPrintingOverlay,
                    onDone: function () {
                        hidePrintingOverlay();
                        if (typeof afterDone === 'function') {
                            afterDone();
                        }
                    }
                };
            };

            fetch("{{ route('quick-sale.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const successMsg = fulfillment === 'delivery'
                            ? @json(__('Sale successful! Saved as Delivery (Pending). Open Sale List → Ship Order to assign courier.'))
                            : @json(__('Sale Successful!'));
                        sessionStorage.setItem('quick_sale_success', successMsg);
                        const isThermal = (data.pos_printer_type || 'thermal') === 'thermal';
                        const printUrl = isThermal
                            ? "{{ url('/quick-sale/print-thermal') }}/" + data.sale_id
                            : "{{ url('/quick-sale/print-a4') }}/" + data.sale_id;

                        const finishReload = function () {
                            window.location.reload();
                        };

                        // Thermal: hidden-iframe print (no about:blank popup)
                        if (isThermal && typeof printThermalFromUrl === 'function') {
                            if (data.pos_print_mode === 'auto') {
                                showPrintingOverlay();
                                printThermalFromUrl(printUrl, null, printOpts(finishReload));
                                return;
                            }
                            qsPromptPrintReceipt(function () {
                                printThermalFromUrl(printUrl, null, printOpts(finishReload));
                            }, finishReload);
                            return;
                        }

                        if (data.pos_print_mode === 'auto') {
                            sessionStorage.setItem('auto_print_url', printUrl);
                            sessionStorage.setItem('auto_print_type', data.pos_printer_type || 'thermal');
                        } else {
                            sessionStorage.setItem('manual_print_url', printUrl);
                            sessionStorage.setItem('manual_print_type', data.pos_printer_type || 'thermal');
                        }
                        finishReload();
                    } else {
                        hidePrintingOverlay();
                        alert(@json(__('Error:')) + ' ' + data.message);
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(err => {
                    hidePrintingOverlay();
                    alert(@json(__('Something went wrong')));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }

        /* --- Scanner --- */
        function openScannerModal() {
            scannerModalInstance.show();
            startCamera();
        }

        function startCamera() {
            if (html5QrcodeScanner) return;
            const onScanSuccess = (decodedText, decodedResult) => {
                const product = products.find(p => p.barcode === decodedText);
                if (product) {
                    addToCart(product.id);
                    return;
                }
                const productByImei = products.find(p => p.available_imeis.includes(decodedText));
                if (productByImei) {
                    addToCart(productByImei.id, decodedText);
                } else {
                    alert(@json(__('Product or IMEI not found!')));
                }
            };
            html5QrcodeScanner = new Html5Qrcode("reader");
            html5QrcodeScanner.start({
                facingMode: "environment"
            }, {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            }, onScanSuccess).catch(err => {
                document.getElementById('reader').innerHTML =
                    `<p class="text-danger p-3">Camera access denied.</p>`;
            });
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.stop().then(() => {
                    html5QrcodeScanner.clear();
                    html5QrcodeScanner = null;
                });
            }
        }
        document.getElementById('scannerModal').addEventListener('hidden.bs.modal', stopScanner);

        function toggleCart() {
            document.getElementById('cartSection').classList.toggle('show');
            document.getElementById('overlay').classList.toggle('show');
        }

        function updateMobileBadge() {
            const count = cart.reduce((acc, item) => acc + item.qty, 0);
            const badge = document.getElementById('mobileCartCount');
            if (!badge) return;
            badge.innerText = count > 99 ? '99+' : String(count);
            badge.classList.toggle('is-empty', count < 1);
        }

        document.querySelectorAll('.category-tab').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.category-tab').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                currentCategory = btn.dataset.cat;
                filterProducts();
            });
        });

        const categoryRail = document.querySelector('.category-rail');
        const categoryScroll = document.querySelector('.category-scroll');
        function syncCategoryNav() {
            if (!categoryRail || !categoryScroll) return;
            const overflows = categoryScroll.scrollWidth > categoryRail.clientWidth + 1;
            const max = categoryScroll.scrollWidth - categoryScroll.clientWidth;
            categoryRail.classList.toggle('can-scroll-left', overflows && categoryScroll.scrollLeft > 4);
            categoryRail.classList.toggle('can-scroll-right', overflows && max - categoryScroll.scrollLeft > 4);
        }
        if (categoryScroll && categoryRail) {
            categoryScroll.addEventListener('scroll', syncCategoryNav, { passive: true });
            window.addEventListener('resize', syncCategoryNav);
            if (window.ResizeObserver) {
                const categoryNavObserver = new ResizeObserver(syncCategoryNav);
                categoryNavObserver.observe(categoryRail);
                categoryNavObserver.observe(categoryScroll);
            }
            syncCategoryNav();
            requestAnimationFrame(function () { requestAnimationFrame(syncCategoryNav); });
            window.addEventListener('load', syncCategoryNav);
            function scrollCategoryBy(direction) {
                const tabs = [...categoryScroll.querySelectorAll('.category-tab')].filter(function (tab) {
                    return tab.offsetWidth > 0;
                });
                const box = categoryScroll.getBoundingClientRect();
                if (direction > 0) {
                    const max = categoryScroll.scrollWidth - categoryScroll.clientWidth;
                    const last = tabs[tabs.length - 1];
                    const next = tabs.find(function (tab) {
                        return tab.getBoundingClientRect().right > box.right - 4;
                    });
                    if (!next || next === last) {
                        categoryScroll.scrollTo({ left: max, behavior: 'smooth' });
                        return;
                    }
                    const delta = next.getBoundingClientRect().left - box.left - 4;
                    if (categoryScroll.scrollLeft + delta >= max - 4) {
                        categoryScroll.scrollTo({ left: max, behavior: 'smooth' });
                        return;
                    }
                    categoryScroll.scrollBy({ left: delta, behavior: 'smooth' });
                    return;
                }
                const prev = [...tabs].reverse().find(function (tab) {
                    return tab.getBoundingClientRect().right <= box.left + 2;
                });
                if (prev) {
                    categoryScroll.scrollBy({
                        left: prev.getBoundingClientRect().left - box.left - 4,
                        behavior: 'smooth'
                    });
                    return;
                }
                categoryScroll.scrollTo({ left: 0, behavior: 'smooth' });
            }
            categoryRail.querySelector('.category-nav-prev').addEventListener('click', function () {
                scrollCategoryBy(-1);
            });
            categoryRail.querySelector('.category-nav-next').addEventListener('click', function () {
                scrollCategoryBy(1);
            });
            categoryScroll.addEventListener('wheel', function (e) {
                if (this.scrollWidth <= this.clientWidth + 1) return;
                if (Math.abs(e.deltaX) > Math.abs(e.deltaY)) return;
                this.scrollLeft += e.deltaY;
                e.preventDefault();
                syncCategoryNav();
            }, { passive: false });
        }

        /* --- Gift Card --- */
        function openGiftCardModal() {
            document.getElementById('giftCardForm').reset();
            const nextYear = new Date();
            nextYear.setFullYear(nextYear.getFullYear() + 1);
            document.getElementById('gc_expiry').valueAsDate = nextYear;
            giftCardModalInstance.show();
        }

        function generateGiftCardNo() {
            const year = new Date().getFullYear();
            const random = Math.floor(1000 + Math.random() * 9000);
            document.getElementById('gc_card_no').value = `GC-${year}-${random}`;
        }

        function syncGcPrice() {
            const val = document.getElementById('gc_value').value;
            document.getElementById('gc_price').value = val;
        }

        function applyCoupon() {
            const code = document.getElementById('couponCodeInput').value.trim();
            const subtotal = cart.reduce((sum, i) => sum + (i.price * i.qty), 0);
            const msgDiv = document.getElementById('couponMessage');
            const btn = document.getElementById('btnApplyCoupon');

            if (!code) {
                msgDiv.innerText = @json(__('Please enter a coupon code!'));
                msgDiv.className = "small mt-1 text-danger fw-bold";
                msgDiv.style.display = 'block';
                return;
            }
            if (subtotal <= 0) {
                alert(@json(__('Add items to cart first!')));
                return;
            }

            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch("{{ route('quick-sale.check-coupon') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({
                    coupon_code: code,
                    sub_total: subtotal
                })
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        appliedCoupon = {
                            id: data.coupon_id,
                            code: data.code,
                            amount: data.discount_amount,
                            type: data.type,
                            value: data.value,
                            max_discount: data.max_discount
                        };
                        msgDiv.style.display = 'none';
                        document.getElementById('couponCodeInput').value = '';

                        updateTotals();

                    } else {
                        msgDiv.innerText = data.message;
                        msgDiv.className = "small mt-1 text-danger fw-bold";
                        msgDiv.style.display = 'block';
                    }
                })
                .catch(err => {
                    console.error(err);
                    msgDiv.innerText = @json(__('Something went wrong!'));
                    msgDiv.className = "small mt-1 text-danger fw-bold";
                    msgDiv.style.display = 'block';
                })
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function removeCoupon() {
            const clearCoupon = function () {
                appliedCoupon = null;
                couponDiscountAmount = 0;
                updateTotals();
            };
            if (typeof window.showAppConfirm !== 'function') {
                if (confirm(@json(__('Remove this coupon?')))) clearCoupon();
                return;
            }
            window.showAppConfirm({
                title: @json(__('Remove this coupon?')),
                text: @json(__('The coupon discount will be removed from this order.')),
                icon: 'fas fa-ticket-alt',
                confirmText: @json(__('Yes, remove it')),
                confirmIcon: 'fas fa-trash-alt'
            }).then(function (ok) {
                if (ok) clearCoupon();
            });
        }

        document.getElementById('giftCardForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + @json(__('Activating...'));
            btn.disabled = true;

            const formData = new FormData(this);
            const payload = Object.fromEntries(formData.entries());

            fetch("{{ route('quick-sale.gift-card.store') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify(payload)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        giftCardModalInstance.hide();
                        showFloatingAlert('success', @json(__('Success! Gift Card Active: :card')).replace(':card', data.card_no));
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => alert(@json(__('Something went wrong'))))
                .finally(() => {
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        });

        function openQuickCustomerModal() {
            const form = document.getElementById('addCustomerForm');
            if (!form) return;
            form.reset();
            if (typeof qsClearFormError === 'function') {
                qsClearFormError('qsCustomerFormError', form);
            }
            $('#addCustomerModal').modal('show');
        }

        function saveQuickCustomer(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addCustomerForm');
            const btn = $('#btnSaveCustomer');
            if (!form.length) return false;

            qsClearFormError('qsCustomerFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            qsMuteBranchSalesmanPopups(true);

            $.ajax({
                url: "{{ route('customers.store') }}",
                type: "POST",
                data: form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response && response.success && response.customer) {
                        const c = response.customer;
                        if (c.status === false) {
                            qsShowFormError('qsCustomerFormError',
                                @json(__('Customer saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            return;
                        }

                        const newOption = new Option(c.name, c.id, true, true);
                        $(newOption).attr('data-points', c.reward_points || 0)
                            .attr('data-group-discount', c.group_discount || 0)
                            .attr('data-group-discount-type', c.group_discount_type || 'percentage');

                        $('#customerSelect').append(newOption).trigger('change');
                        $('#addCustomerModal').modal('hide');
                        form[0].reset();
                        qsClearFormError('qsCustomerFormError', form[0]);
                        return;
                    }
                    qsShowFormError('qsCustomerFormError',
                        (response && response.message) || @json(__('Could not save customer.')), form[0]);
                },
                error: function (xhr) {
                    const payload = qsExtractFormErrors(xhr);
                    if (payload.errors) {
                        Object.keys(payload.errors).forEach(function (field) {
                            const input = form[0].querySelector('[name="' + field + '"]');
                            if (input) input.classList.add('is-invalid');
                        });
                    }
                    qsShowFormError('qsCustomerFormError', payload.messages, form[0]);
                },
                complete: function () {
                    qsMuteBranchSalesmanPopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                }
            });
            return false;
        }

        function qsMuteBranchSalesmanPopups(on) {
            window.__qsBranchSalesmanSilent = !!on;
            if (typeof toastr === 'undefined') return;
            if (on) {
                if (!window.__qsBsToastrBackup) {
                    window.__qsBsToastrBackup = {
                        success: toastr.success,
                        error: toastr.error,
                        info: toastr.info,
                        warning: toastr.warning
                    };
                }
                toastr.success = toastr.error = toastr.info = toastr.warning = function () {};
            } else if (window.__qsBsToastrBackup) {
                toastr.success = window.__qsBsToastrBackup.success;
                toastr.error = window.__qsBsToastrBackup.error;
                toastr.info = window.__qsBsToastrBackup.info;
                toastr.warning = window.__qsBsToastrBackup.warning;
            }
        }

        function qsClearFormError(errorId, formEl) {
            const box = document.getElementById(errorId);
            if (box) {
                box.classList.remove('is-visible');
                box.innerHTML = '';
            }
            if (formEl) {
                $(formEl).find('.is-invalid').removeClass('is-invalid');
            }
        }

        function qsShowFormError(errorId, message, formEl) {
            const box = document.getElementById(errorId);
            if (!box) return;
            let messages = [];
            if (Array.isArray(message)) {
                messages = message.filter(Boolean);
            } else if (message) {
                messages = String(message).split('\n').map(function (m) {
                    return m.trim();
                }).filter(Boolean);
            }
            if (!messages.length) messages = [@json(__('Something went wrong. Please try again.'))];

            let html =
                '<div class="qs-form-error-title nest-form-error-title"><i class="fas fa-exclamation-circle me-1"></i>Please fix the following:</div>';
            const esc = function (t) {
                return String(t == null ? '' : t)
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            };
            if (messages.length === 1) {
                html += '<div>' + esc(messages[0]) + '</div>';
            } else {
                html += '<ul>';
                messages.forEach(function (m) {
                    html += '<li>' + esc(m) + '</li>';
                });
                html += '</ul>';
            }
            box.innerHTML = html;
            box.classList.add('is-visible');
            if (formEl) {
                try {
                    box.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                } catch (e) {}
            }
        }

        function qsExtractFormErrors(xhr) {
            const payload = xhr.responseJSON || null;
            if (xhr.status === 422 && payload && payload.errors) {
                const messages = [];
                Object.keys(payload.errors).forEach(function (key) {
                    (payload.errors[key] || []).forEach(function (item) {
                        messages.push(item);
                    });
                });
                return {
                    messages: messages,
                    errors: payload.errors
                };
            }
            if (payload && payload.message) {
                return {
                    messages: [payload.message],
                    errors: null
                };
            }
            return {
                messages: [@json(__('Something went wrong. Please try again.'))],
                errors: null
            };
        }

        function openQuickSalesmanModal() {
            const form = document.getElementById('addSalesmanForm');
            if (!form) return;
            form.reset();
            qsClearFormError('qsSalesmanFormError', form);
            const status = document.getElementById('qs_salesman_status');
            if (status) status.checked = true;
            const joining = form.querySelector('[name="joining_date"]');
            if (joining && !joining.value) {
                joining.value = new Date().toISOString().slice(0, 10);
            }
            const currentBranch = document.getElementById('branchSelect')?.value || '';
            const salesmanBranch = document.getElementById('qsSalesmanBranch');
            if (salesmanBranch && currentBranch) {
                salesmanBranch.value = currentBranch;
            }
            $('#addSalesmanModal').modal('show');
        }

        let qsPausedSalesmanForBranch = false;
        let qsResumeSalesmanScheduled = false;

        function qsClearStrayBackdrops() {
            document.querySelectorAll('.modal-backdrop').forEach(function (b) {
                b.remove();
            });
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('padding-right');
            document.body.style.removeProperty('overflow');
        }

        function qsShowModalById(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                (bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el)).show();
            } else {
                $('#' + id).modal('show');
            }
        }

        function qsHideModalById(id) {
            const el = document.getElementById(id);
            if (!el) return;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bs = bootstrap.Modal.getInstance(el) || bootstrap.Modal.getOrCreateInstance(el);
                bs.hide();
            } else {
                $('#' + id).modal('hide');
            }
        }

        function qsResumeSalesmanModal() {
            if (!qsPausedSalesmanForBranch || qsResumeSalesmanScheduled) return;
            qsPausedSalesmanForBranch = false;
            qsResumeSalesmanScheduled = true;
            setTimeout(function () {
                qsResumeSalesmanScheduled = false;
                qsClearStrayBackdrops();
                qsShowModalById('addSalesmanModal');
            }, 200);
        }

        window.openQsSalesmanBranchModal = function () {
            const form = document.getElementById('addBranchForm');
            if (!form) return;
            form.reset();
            if (typeof qsClearFormError === 'function') qsClearFormError('qsBranchFormError', form);
            const active = document.getElementById('qs_branch_is_active');
            if (active) active.checked = true;

            const salesmanEl = document.getElementById('addSalesmanModal');
            const openBranch = function () {
                qsShowModalById('addBranchModal');
            };
            const salesmanVisible = salesmanEl && (
                salesmanEl.classList.contains('show') ||
                $(salesmanEl).is(':visible')
            );

            if (salesmanVisible) {
                qsPausedSalesmanForBranch = true;
                qsResumeSalesmanScheduled = false;
                const onHidden = function () {
                    salesmanEl.removeEventListener('hidden.bs.modal', onHidden);
                    openBranch();
                };
                salesmanEl.addEventListener('hidden.bs.modal', onHidden);
                qsHideModalById('addSalesmanModal');
            } else {
                qsPausedSalesmanForBranch = false;
                openBranch();
            }
        };

        (function bindQsBranchResume() {
            const branchEl = document.getElementById('addBranchModal');
            if (!branchEl || branchEl.dataset.qsSalesmanResumeBound === '1') return;
            branchEl.dataset.qsSalesmanResumeBound = '1';
            branchEl.addEventListener('hidden.bs.modal', function () {
                // Wait so nested Manager/Account can open over Branch without resuming Salesman early
                setTimeout(function () {
                    const nestOpen = document.querySelector(
                        '#nxNestManagerModal.show, #nxNestAccountModal.show, #nxNestRoleModal.show'
                    );
                    if (nestOpen) return;
                    if (document.getElementById('addBranchModal')?.classList.contains('show')) return;
                    qsResumeSalesmanModal();
                }, 280);
            });
        })();

        function saveQuickBranch(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addBranchForm');
            const btn = $('#btnSaveBranch');
            if (!form.length) return false;

            qsClearFormError('qsBranchFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            qsMuteBranchSalesmanPopups(true);

            $.ajax({
                url: "{{ route('branches.store') }}",
                type: "POST",
                data: new FormData(form[0]),
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success && response.branch) {
                        const b = response.branch;
                        if (!b.status) {
                            qsShowFormError('qsBranchFormError',
                                @json(__('Branch saved, but it is inactive so it was not selected.')), form[0]);
                            form[0].reset();
                            const active = document.getElementById('qs_branch_is_active');
                            if (active) active.checked = true;
                            return;
                        }
                        const branchSelect = document.getElementById('branchSelect');
                        if (branchSelect && !branchSelect.disabled) {
                            let existsMain = false;
                            for (let i = 0; i < branchSelect.options.length; i++) {
                                if (branchSelect.options[i].value === String(b.id)) {
                                    existsMain = true;
                                    break;
                                }
                            }
                            if (!existsMain) {
                                branchSelect.appendChild(new Option(b.name, b.id, false, false));
                            }
                            if (!qsPausedSalesmanForBranch) {
                                branchSelect.value = String(b.id);
                                $(branchSelect).trigger('change');
                            }
                        }
                        const salesmanBranch = document.getElementById('qsSalesmanBranch');
                        if (salesmanBranch) {
                            let existsSm = false;
                            for (let i = 0; i < salesmanBranch.options.length; i++) {
                                if (salesmanBranch.options[i].value === String(b.id)) {
                                    existsSm = true;
                                    break;
                                }
                            }
                            if (!existsSm) {
                                salesmanBranch.appendChild(new Option(b.name, b.id, false, false));
                            }
                            if (qsPausedSalesmanForBranch) {
                                salesmanBranch.value = String(b.id);
                                $(salesmanBranch).trigger('change');
                            }
                        }

                        $('#addBranchModal').modal('hide');
                        form[0].reset();
                        const active = document.getElementById('qs_branch_is_active');
                        if (active) active.checked = true;
                        qsClearFormError('qsBranchFormError', form[0]);
                        return;
                    }
                    qsShowFormError('qsBranchFormError', (response && response.message) || @json(__('Could not save branch.')),
                        form[0]);
                },
                error: function (xhr) {
                    const payload = qsExtractFormErrors(xhr);
                    if (payload.errors) {
                        Object.keys(payload.errors).forEach(function (field) {
                            const input = form[0].querySelector('[name="' + field + '"]');
                            if (input) input.classList.add('is-invalid');
                        });
                    }
                    qsShowFormError('qsBranchFormError', payload.messages, form[0]);
                },
                complete: function () {
                    qsMuteBranchSalesmanPopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                }
            });
            return false;
        }

        function saveQuickSalesman(e) {
            e.preventDefault();
            e.stopPropagation();
            const form = $('#addSalesmanForm');
            const btn = $('#btnSaveSalesman');
            if (!form.length) return false;

            qsClearFormError('qsSalesmanFormError', form[0]);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
            qsMuteBranchSalesmanPopups(true);

            $.ajax({
                url: "{{ route('employees.store') }}",
                type: "POST",
                data: form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function (response) {
                    if (response.success && response.employee) {
                        const emp = response.employee;
                        const employeeSelect = document.getElementById('employeeSelect');
                        if (emp.status && employeeSelect && !employeeSelect.disabled) {
                            const opt = new Option(emp.name, emp.id, true, true);
                            employeeSelect.appendChild(opt);
                            $(employeeSelect).trigger('change');
                        }

                        const branchSelect = document.getElementById('branchSelect');
                        if (emp.status && branchSelect && !branchSelect.disabled && emp.branch_id && !branchSelect.value) {
                            branchSelect.value = String(emp.branch_id);
                            $(branchSelect).trigger('change');
                        }

                        $('#addSalesmanModal').modal('hide');
                        form[0].reset();
                        const status = document.getElementById('qs_salesman_status');
                        if (status) status.checked = true;
                        qsClearFormError('qsSalesmanFormError', form[0]);
                        return;
                    }
                    qsShowFormError('qsSalesmanFormError', (response && response.message) ||
                        @json(__('Could not save salesman.')), form[0]);
                },
                error: function (xhr) {
                    const payload = qsExtractFormErrors(xhr);
                    if (payload.errors) {
                        Object.keys(payload.errors).forEach(function (field) {
                            const input = form[0].querySelector('[name="' + field + '"]');
                            if (input) input.classList.add('is-invalid');
                        });
                    }
                    qsShowFormError('qsSalesmanFormError', payload.messages, form[0]);
                },
                complete: function () {
                    qsMuteBranchSalesmanPopups(false);
                    btn.prop('disabled', false).html('<i class="fas fa-save me-1"></i> Save');
                }
            });
            return false;
        }

        function adjustMobileLayout() {
            if (window.innerWidth <= 991) {
                const mobileForm = document.getElementById('mobileFormPlaceholder');
                const mobileFooter = document.getElementById('mobileFooterPlaceholder');
                if (mobileForm && document.getElementById('cartForm')) mobileForm.appendChild(document.getElementById('cartForm'));
                if (mobileFooter && document.getElementById('cartFooter')) mobileFooter.appendChild(document.getElementById('cartFooter'));
            } else {
                const desktopForm = document.getElementById('desktopFormPlaceholder');
                const desktopFooter = document.getElementById('desktopFooterPlaceholder');
                if (desktopForm && document.getElementById('cartForm')) desktopForm.appendChild(document.getElementById('cartForm'));
                if (desktopFooter && document.getElementById('cartFooter')) desktopFooter.appendChild(document.getElementById('cartFooter'));
            }
        }
        window.addEventListener('resize', adjustMobileLayout);
        document.addEventListener('DOMContentLoaded', adjustMobileLayout);
        document.addEventListener('DOMContentLoaded', function () {
            if (sessionStorage.getItem('auto_print_url')) {
                let url = sessionStorage.getItem('auto_print_url');
                let type = sessionStorage.getItem('auto_print_type') || 'thermal';
                sessionStorage.removeItem('auto_print_url');
                sessionStorage.removeItem('auto_print_type');
                const overlay = document.getElementById('qsPrintingOverlay');
                const showOverlay = function () {
                    if (overlay) overlay.classList.add('is-visible');
                };
                const hideOverlay = function () {
                    if (overlay) overlay.classList.remove('is-visible');
                };
                if (type === 'thermal' && typeof printThermalFromUrl === 'function') {
                    printThermalFromUrl(url, null, {
                        onStart: showOverlay,
                        onDone: hideOverlay
                    });
                } else {
                    showOverlay();
                    window.open(url, '_blank', 'noopener,noreferrer,width=480,height=720');
                    setTimeout(hideOverlay, 4000);
                }
            }

            if (sessionStorage.getItem('manual_print_url')) {
                let url = sessionStorage.getItem('manual_print_url');
                let type = sessionStorage.getItem('manual_print_type') || 'thermal';
                sessionStorage.removeItem('manual_print_url');
                sessionStorage.removeItem('manual_print_type');
                const overlay = document.getElementById('qsPrintingOverlay');
                const showOverlay = function () {
                    if (overlay) overlay.classList.add('is-visible');
                };
                const hideOverlay = function () {
                    if (overlay) overlay.classList.remove('is-visible');
                };
                qsPromptPrintReceipt(function () {
                    if (type === 'thermal' && typeof printThermalFromUrl === 'function') {
                        printThermalFromUrl(url, null, {
                            onStart: showOverlay,
                            onDone: hideOverlay
                        });
                    } else {
                        showOverlay();
                        window.open(url, '_blank', 'noopener,noreferrer,width=480,height=720');
                        setTimeout(hideOverlay, 4000);
                    }
                });
            }
        });
    </script>
@endpush
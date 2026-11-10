<?php

namespace App\Helpers;

use App\Models\Account;
use App\Models\GeneralSettings;
use App\Models\ProductImei;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Transfer;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class InvoiceHelper
{
    public const DEVELOPER_CREDIT = 'Design & Developed by onetech.com.bd (+8801994433400)';

    public const DEFAULT_THERMAL_SIZE = '80mm';

    public const DEFAULT_THERMAL_LOGO_HEIGHT = 40;

    /**
     * Customer-facing labels for invoice / thermal documents.
     */
    public static function t(string $key, array $replace = []): string
    {
        $labels = [
            'invoice' => 'Invoice',
            'invoice_no' => 'Invoice No.',
            'invoice_hash' => 'INVOICE #',
            'document_invoice' => 'INVOICE',
            'document_receipt' => 'RECEIPT',
            'document_sales_receipt' => 'SALES RECEIPT',
            'document_quotation' => 'QUOTATION',
            'document_delivery' => 'DELIVERY NOTE',
            'document_purchase' => 'PURCHASE',
            'document_sale_return' => 'SALE RETURN',
            'document_purchase_return' => 'PURCHASE RETURN',
            'document_transfer' => 'TRANSFER',
            'thermal_delivery' => 'DELIVERY RECEIPT',
            'thermal_purchase' => 'PURCHASE RECEIPT',
            'thermal_transfer' => 'STOCK TRANSFER',
            'date' => 'Date',
            'status' => 'Status',
            'customer' => 'Customer',
            'walk_in' => 'Walk-in Customer',
            'phone' => 'Phone',
            'tel' => 'Tel.',
            'sold_by' => 'Sold by',
            'served_by' => 'Served by',
            'branch' => 'Branch',
            'payment' => 'Payment',
            'method' => 'Method',
            'item' => 'Item',
            'items_empty' => 'No items',
            'qty' => 'Qty',
            'total' => 'Total',
            'description' => 'Description',
            'warranty' => 'Warranty',
            'unit_price' => 'Unit Price',
            'product' => 'Product',
            'subtotal' => 'Subtotal',
            'discount' => 'Discount',
            'coupon_discount' => 'Coupon Discount',
            'reward_discount' => 'Reward Points Discount',
            'charge' => 'Charge',
            'tax' => 'Tax',
            'grand_total' => 'TOTAL',
            'customer_paid' => 'Customer Paid',
            'change' => 'Change',
            'due' => 'Due',
            'due_balance' => 'Due Balance',
            'terms' => 'Terms & Conditions',
            'terms_short' => 'TERMS',
            'thank_you' => 'Thank you for your visit!',
            'customer_signature' => 'CUSTOMER SIGNATURE',
            'company_signature' => 'COMPANY SIGNATURE & STAMP',
            'bill_to' => 'Bill To',
            'from' => 'From',
            'to' => 'To',
            'ship_to' => 'Ship To',
            'quote_to' => 'Quote To',
            'na' => 'N/A',
            'cash' => 'Cash',
            'print' => 'Print',
            'print_thermal' => 'Thermal Print',
            'close' => 'Close',
            'payment_history' => 'Payment History',
            'note' => 'Note',
            'information' => 'Information',
            'model' => 'Model',
            'variant' => 'Variant',
            'code' => 'Code',
            'batch' => 'Batch',
            'reference' => 'Reference',
            'valid_until' => 'Valid Until',
            'courier' => 'Courier',
            'tracking' => 'Tracking',
            'tracking_code' => 'Tracking Code',
            'delivery_charge' => 'Delivery Charge',
            'shipping' => 'Shipping',
            'order_discount' => 'Order Discount',
            'shipment_info' => 'Shipment Information',
            'purchase_info' => 'Purchase Information',
            'memo_no' => 'Memo No.',
            'original_invoice' => 'Original Invoice',
            'original_purchase' => 'Original Purchase',
            'return_note' => 'Return Note',
            'staff_note' => 'Staff Note',
            'return_from' => 'Returned By',
            'return_to' => 'Return To',
            'received_by' => 'Received By',
            'return_number' => 'Return No.',
            'sale_invoice' => 'Sale Invoice',
            'purchase_number' => 'Purchase No.',
            'return_success' => 'Return processed successfully!',
            'return_info' => 'Return Information',
            'from_branch' => 'From Branch',
            'to_branch' => 'To Branch',
            'created_by' => 'Created By',
            'transfer_info' => 'Transfer Information',
            'transfer_number' => 'Transfer No.',
            'transfer_thank_you' => 'Stock transfer document',
            'item_not_found' => 'Item not found',
            'unknown' => 'Unknown',
            'supplier' => 'Supplier',
            'branch_fallback' => 'Branch',
            'company_name' => 'Company',
        ];

        $text = $labels[$key] ?? $key;
        foreach ($replace as $search => $value) {
            $text = str_replace(':'.$search, (string) $value, $text);
        }

        return $text;
    }

    public static function paymentMethodLabel(?string $method): string
    {
        $raw = trim((string) $method);
        if ($raw === '') {
            return '';
        }

        $key = mb_strtolower($raw);
        $map = [
            'cash' => 'Cash',
            'card' => 'Card',
            'credit card' => 'Credit Card',
            'debit card' => 'Debit Card',
            'bank' => 'Bank Transfer',
            'bank transfer' => 'Bank Transfer',
            'transfer' => 'Transfer',
            'cheque' => 'Cheque',
            'check' => 'Cheque',
            'mobile' => 'Mobile',
            'gift card' => 'Gift Card',
            'due' => 'Due',
            'credit' => 'Credit',
        ];

        return $map[$key] ?? $raw;
    }

    public static function resolvePaymentMethodName(?string $method): string
    {
        $raw = trim((string) $method);
        if ($raw === '') {
            return '';
        }

        if (ctype_digit($raw)) {
            $account = Account::find((int) $raw);

            return $account?->name ?: $raw;
        }

        return self::paymentMethodLabel($raw);
    }

    public static function invoicePaymentMethodDisplay(array $invoice): string
    {
        $label = self::paymentMethodLabel($invoice['payment_method'] ?? '');
        if ($label !== '') {
            return $label;
        }

        $type = $invoice['type'] ?? '';
        if (in_array($type, ['sale', 'purchase'], true)
            && (float) ($invoice['due_amount'] ?? 0) > 0
            && (float) ($invoice['paid_amount'] ?? 0) <= 0) {
            return self::t('due');
        }

        return '';
    }

    public static function statusLabel(?string $status): string
    {
        $key = mb_strtolower(trim((string) $status));
        $map = [
            'paid' => 'Paid',
            'unpaid' => 'Unpaid',
            'partial' => 'Partial',
            'pending' => 'Pending',
            'due' => 'Due',
            'cancelled' => 'Cancelled',
            'canceled' => 'Cancelled',
            'expired' => 'Expired',
            'received' => 'Received',
            'delivered' => 'Delivered',
            'completed' => 'Completed',
            'approved' => 'Approved',
            'ordered' => 'Ordered',
            'shipped' => 'Shipped',
            'sent' => 'Sent',
            'sale_generated' => 'Sale Generated',
            'purchase_generated' => 'Purchase Generated',
            'request' => 'Request',
            'info' => 'Information',
        ];

        return $map[$key] ?? ($status ? mb_strtoupper((string) $status) : '');
    }

    public static function defaultFooterNote(?string $companyName = null, ?string $phone = null): string
    {
        $name = trim((string) $companyName);
        $note = $name !== ''
            ? 'Thank you for choosing '.$name.'. For support or warranty, please contact us anytime.'
            : 'Thank you for your business. For support or warranty, please contact us anytime.';

        $phone = trim((string) $phone);
        if ($phone !== '') {
            $note .= ' '.$phone;
        }

        return $note;
    }

    /**
     * Industry-standard thermal paper widths (value => label).
     */
    public static function thermalSizes(): array
    {
        return [
            '58mm' => '58mm (Small)',
            '72mm' => '72mm',
            '76mm' => '76mm',
            '80mm' => '80mm (Standard)',
            '100mm' => '100mm',
            '112mm' => '112mm (Large)',
        ];
    }

    /**
     * Default thermal printer size from General Settings.
     */
    public static function getThermalPrinterSize(?array $generalSettings = null): string
    {
        $generalSettings = $generalSettings ?? (view()->shared('general_settings') ?? []);

        if (empty($generalSettings) || ! is_array($generalSettings)) {
            $generalSettings = GeneralSettings::where('category', 'general')
                ->where('key_name', 'thermal_printer_size')
                ->pluck('value', 'key_name')
                ->toArray();
        }

        $size = $generalSettings['thermal_printer_size'] ?? self::DEFAULT_THERMAL_SIZE;
        $size = is_string($size) ? trim($size) : self::DEFAULT_THERMAL_SIZE;

        if (! array_key_exists($size, self::thermalSizes())) {
            return self::DEFAULT_THERMAL_SIZE;
        }

        return $size;
    }

    /**
     * Thermal receipt logo height in pixels (Print Settings).
     */
    public static function getThermalLogoHeight(?array $generalSettings = null): int
    {
        $generalSettings = $generalSettings ?? (view()->shared('general_settings') ?? []);

        if (empty($generalSettings) || ! is_array($generalSettings)) {
            $generalSettings = GeneralSettings::where('category', 'general')
                ->where('key_name', 'thermal_logo_height')
                ->pluck('value', 'key_name')
                ->toArray();
        }

        $height = (int) ($generalSettings['thermal_logo_height'] ?? self::DEFAULT_THERMAL_LOGO_HEIGHT);

        return max(24, min(80, $height));
    }

    public static function getShowBranchName(?array $generalSettings = null): bool
    {
        $generalSettings = $generalSettings ?? (view()->shared('general_settings') ?? []);

        if (empty($generalSettings) || ! is_array($generalSettings)) {
            $generalSettings = GeneralSettings::where('category', 'general')
                ->whereIn('key_name', ['print_show_branch_name', 'thermal_show_company_name'])
                ->pluck('value', 'key_name')
                ->toArray();
        }

        if (array_key_exists('print_show_branch_name', $generalSettings)) {
            return filter_var($generalSettings['print_show_branch_name'], FILTER_VALIDATE_BOOLEAN);
        }

        if (! array_key_exists('thermal_show_company_name', $generalSettings)) {
            return true;
        }

        return filter_var($generalSettings['thermal_show_company_name'], FILTER_VALIDATE_BOOLEAN);
    }

    public static function getThermalShowCompanyName(?array $generalSettings = null): bool
    {
        return self::getShowBranchName($generalSettings);
    }

    public static function thermalIdentityToggles(): array
    {
        return [
            'phone' => 'Mobile Number',
            'telephone' => 'Telephone Number',
            'email' => 'Email',
            'website' => 'Website',
            'bin' => 'BIN No',
        ];
    }

    public static function getThermalIdentityFlags(?array $generalSettings = null): array
    {
        $generalSettings = $generalSettings ?? (view()->shared('general_settings') ?? []);

        if (empty($generalSettings) || ! is_array($generalSettings)) {
            $generalSettings = GeneralSettings::where('category', 'general')
                ->whereIn('key_name', collect(self::thermalIdentityToggles())->keys()->map(fn ($k) => 'thermal_show_'.$k)->all())
                ->pluck('value', 'key_name')
                ->toArray();
        }

        $flags = [];
        foreach (self::thermalIdentityToggles() as $key => $label) {
            $settingKey = 'thermal_show_'.$key;
            $flags[$key] = array_key_exists($settingKey, $generalSettings)
                ? filter_var($generalSettings[$settingKey], FILTER_VALIDATE_BOOLEAN)
                : true;
        }

        return $flags;
    }

    /**
     * Normalize a thermal size string; falls back to settings default.
     */
    public static function normalizeThermalSize(?string $size = null): string
    {
        $size = $size ? trim($size) : null;

        if ($size && array_key_exists($size, self::thermalSizes())) {
            return $size;
        }

        return self::getThermalPrinterSize();
    }

    /**
     * Build a normalized invoice payload from a Sale.
     * Pass $overrides to replace any field (nested assoc arrays are merged).
     */
    public static function fromSale(Sale $sale, array $overrides = []): array
    {
        $sale->loadMissing(['branch', 'customer', 'seller', 'user', 'payments', 'productSales.product']);
        $settings = self::overlayBranchIdentity(self::companySettings(), $sale->branch);
        $servedBy = collect([$sale->seller?->name, $sale->user?->name])->filter()->first() ?? '';
        $cash = self::saleCashBreakdown($sale);
        $paidAmount = $cash['paid_amount'];
        $dueAmount = $cash['due_amount'];
        $paymentMethod = $sale->payments
            ->pluck('payment_method')
            ->map(fn ($method) => self::resolvePaymentMethodName($method))
            ->filter()
            ->unique()
            ->implode(' + ');

        $discounts = [];
        if ((float) $sale->discount_amount > 0) {
            $discounts[] = ['label' => self::t('discount'), 'amount' => (float) $sale->discount_amount];
        }
        if ((float) $sale->coupon_discount_amount > 0) {
            $discounts[] = ['label' => self::t('coupon_discount'), 'amount' => (float) $sale->coupon_discount_amount];
        }
        if ((float) $sale->point_discount_amount > 0) {
            $points = $sale->reward_points_used ?? 0;
            $discounts[] = [
                'label' => self::t('reward_discount').($points ? " ({$points} pts)" : ''),
                'amount' => (float) $sale->point_discount_amount,
            ];
        }

        $charges = [];
        if ((float) ($sale->shipping_cost ?? 0) > 0) {
            $charges[] = ['label' => self::t('shipping'), 'amount' => (float) $sale->shipping_cost];
        }

        $items = [];
        foreach ($sale->productSales as $item) {
            $imeis = ProductImei::where('sale_id', $sale->id)
                ->where('product_id', $item->product_id)
                ->pluck('imei_number')
                ->toArray();

            $metaParts = [];
            if (count($imeis) > 0) {
                $metaParts[] = 'IMEI: '.implode(', ', $imeis);
            }
            if (! empty($item->variant)) {
                $metaParts[] = self::t('variant').': '.(is_object($item->variant) ? ($item->variant->name ?? $item->variant->variant_name ?? '') : $item->variant);
            }

            $items[] = [
                'title' => $item->product->product_title ?? self::t('item_not_found'),
                'qty' => $item->qty,
                'unit_price' => (float) $item->unit_price,
                'total' => (float) $item->total_price,
                'warranty' => self::t('na'),
                'meta' => count($metaParts) ? implode(' | ', $metaParts) : null,
            ];
        }

        $payments = $sale->payments->map(function ($payment) {
            $applied = (float) $payment->amount;
            $change = (float) ($payment->change_amount ?? 0);

            return [
                'date' => $payment->payment_date
                    ? Carbon::parse($payment->payment_date)->format('d M Y')
                    : '',
                'method' => self::resolvePaymentMethodName($payment->payment_method ?? ''),
                'amount' => $applied,
                'received' => $applied + $change,
                'change' => $change,
            ];
        })->values()->all();

        $base = self::defaults([
            'type' => 'sale',
            'modal_title' => 'Invoice: '.$sale->invoice_no,
            'print_title' => 'Print Invoice',
            'document_label' => self::t('document_invoice'),
            'thermal_title' => self::t('document_sales_receipt'),
            'number' => $sale->invoice_no,
            'date' => $sale->sale_date,
            'date_short' => self::dateShort($sale->sale_date),
            'date_long' => self::dateLong($sale->sale_date),
            'status' => $sale->payment_status ?? '',
            'served_by' => $servedBy,
            'sold_by' => $servedBy,
            'company' => $settings,
            'from' => self::partyFromBranch($sale->branch, self::t('from')),
            'bill_to' => [
                'label' => self::t('bill_to'),
                'name' => $sale->customer->name ?? self::t('walk_in'),
                'address' => $sale->customer->address ?? self::t('na'),
                'phone' => $sale->customer->phone ?? null,
            ],
            'payment_method' => $paymentMethod,
            'items' => $items,
            'sub_total' => (float) $sale->sub_total,
            'discounts' => $discounts,
            'charges' => $charges,
            'tax_percent' => (float) ($sale->tax_percent ?? 0),
            'tax_amount' => (float) ($sale->tax_amount ?? 0),
            'grand_total' => (float) $sale->grand_total,
            'paid_amount' => $paidAmount,
            'received_amount' => $cash['received_amount'],
            'change_amount' => $cash['change_amount'],
            'due_amount' => $dueAmount,
            'payments' => $payments,
            'show_thermal' => true,
            'show_signatures' => true,
            'show_payment_history' => true,
        ]);

        return self::applyOverrides($base, $overrides);
    }

    /**
     * Delivery invoice built from Sale + Delivery relation.
     */
    public static function fromDelivery(Sale $sale, array $overrides = []): array
    {
        $delivery = $sale->delivery;
        $sale->loadMissing(['branch']);
        $deliveryCharge = (float) ($delivery->delivery_charge ?? 0);
        $codAmount = (float) ($delivery->cod_amount ?? 0);

        $items = [];
        foreach ($sale->productSales as $item) {
            $unit = (float) ($item->unit_price ?? $item->product->selling_price ?? 0);
            $qty = (float) $item->qty;
            $meta = null;
            if (! empty($item->variant)) {
                $meta = self::t('variant').': '.(is_object($item->variant) ? ($item->variant->name ?? '') : $item->variant);
            }

            $items[] = [
                'title' => $item->product->product_title ?? self::t('item'),
                'qty' => $item->qty,
                'unit_price' => $unit,
                'total' => (float) ($item->total_price ?? ($qty * $unit)),
                'warranty' => self::t('na'),
                'meta' => $meta,
            ];
        }

        $infoRows = [
            ['label' => self::t('courier'), 'value' => $delivery?->courier?->name ?? self::t('na')],
            ['label' => self::t('tracking_code'), 'value' => $delivery?->tracking_code ?? self::t('na')],
            ['label' => self::t('note'), 'value' => $delivery?->note ?: '-'],
        ];

        $extraRows = [
            ['label' => self::t('courier'), 'value' => $delivery?->courier?->name ?? self::t('na')],
            ['label' => self::t('tracking'), 'value' => $delivery?->tracking_code ?? self::t('na')],
        ];

        $charges = [];
        if ($deliveryCharge > 0) {
            $charges[] = ['label' => self::t('delivery_charge'), 'amount' => $deliveryCharge];
        }

        $grand = (float) $sale->grand_total + $deliveryCharge;
        $paid = (float) ($sale->paid_amount ?? $sale->payments?->sum('amount') ?? 0);
        if ($codAmount > 0) {
            $grand = $codAmount;
            $paid = 0;
        } else {
            $paid = $paid + $deliveryCharge;
        }

        $base = self::fromSale($sale, [
            'type' => 'delivery',
            'modal_title' => 'Delivery Note: '.$sale->invoice_no,
            'print_title' => 'Print Delivery Note',
            'document_label' => self::t('document_delivery'),
            'thermal_title' => self::t('thermal_delivery'),
            'status' => $delivery?->status ?? 'pending',
            'from' => self::partyFromBranch($sale->branch, self::t('from')),
            'bill_to' => [
                'label' => self::t('ship_to'),
                'name' => $delivery?->recipient_name ?? ($sale->customer->name ?? self::t('customer')),
                'address' => $delivery?->address ?? ($sale->customer->address ?? ''),
                'phone' => $delivery?->recipient_phone ?? ($sale->customer->phone ?? null),
            ],
            'items' => $items,
            'sub_total' => (float) $sale->grand_total,
            'discounts' => [],
            'tax_percent' => 0,
            'tax_amount' => 0,
            'charges' => $charges,
            'grand_total' => $grand,
            'paid_amount' => $paid,
            'due_amount' => max(0, $grand - $paid),
            'payments' => [],
            'info_blocks' => [
                ['title' => self::t('shipment_info'), 'rows' => $infoRows],
            ],
            'thermal_extra_rows' => $extraRows,
            'show_payment_history' => false,
            'show_thermal' => false,
            'show_signatures' => true,
            'show_thermal_terms' => false,
            'terms' => [],
        ]);

        return self::applyOverrides($base, $overrides);
    }

    public static function fromQuotation(Quotation $quotation, array $overrides = []): array
    {
        $status = $quotation->status ?? 'pending';
        if ($quotation->expiry_date && $quotation->expiry_date < date('Y-m-d') && $status === 'pending') {
            $status = 'expired';
        }

        $billToName = $quotation->customer->name ?? $quotation->supplier->name ?? self::t('walk_in');
        $billToPhone = $quotation->customer->phone ?? $quotation->supplier->phone ?? null;
        $billToAddress = $quotation->customer->address ?? $quotation->supplier->address ?? '';

        $items = [];
        foreach ($quotation->items as $item) {
            $meta = null;
            if ($item->variant) {
                $meta = self::t('variant').': '.($item->variant->name ?? $item->variant->variant_code ?? $item->variant->variant_name ?? '');
            }
            $items[] = [
                'title' => $item->product->product_title ?? self::t('unknown'),
                'qty' => $item->qty,
                'unit_price' => (float) $item->net_unit_price,
                'total' => (float) $item->total,
                'warranty' => self::t('na'),
                'meta' => $meta,
            ];
        }

        $discounts = [];
        if ((float) ($quotation->order_discount ?? 0) > 0) {
            $discounts[] = ['label' => self::t('order_discount'), 'amount' => (float) $quotation->order_discount];
        }

        $charges = [];
        if ((float) ($quotation->shipping_cost ?? 0) > 0) {
            $charges[] = ['label' => self::t('shipping'), 'amount' => (float) $quotation->shipping_cost];
        }

        $extra = [];
        if (! empty($quotation->expiry_date)) {
            $extra[] = ['label' => self::t('valid_until'), 'value' => self::dateShort($quotation->expiry_date)];
        }

        $base = self::defaults([
            'type' => 'quotation',
            'modal_title' => 'Quotation: '.$quotation->reference_no,
            'print_title' => 'Print Quotation',
            'document_label' => self::t('document_quotation'),
            'thermal_title' => self::t('document_quotation'),
            'thermal_number_label' => self::t('reference'),
            'number' => $quotation->reference_no,
            'date' => $quotation->date,
            'date_short' => self::dateShort($quotation->date),
            'date_long' => self::dateLong($quotation->date),
            'status' => $status,
            'served_by' => $quotation->seller->name ?? '',
            'sold_by' => $quotation->seller->name ?? '',
            'company' => self::overlayBranchIdentity(self::companySettings(), $quotation->branch),
            'from' => self::partyFromBranch($quotation->branch, self::t('from')),
            'bill_to' => [
                'label' => self::t('quote_to'),
                'name' => $billToName,
                'address' => $billToAddress,
                'phone' => $billToPhone,
            ],
            'items' => $items,
            'sub_total' => (float) ($quotation->total_price ?? $quotation->sub_total ?? 0),
            'discounts' => $discounts,
            'charges' => $charges,
            'tax_percent' => (float) ($quotation->order_tax_rate ?? 0),
            'tax_amount' => (float) ($quotation->order_tax ?? 0),
            'grand_total' => (float) ($quotation->grand_total ?? 0),
            'paid_amount' => 0,
            'due_amount' => (float) ($quotation->grand_total ?? 0),
            'payments' => [],
            'thermal_extra_rows' => $extra,
            'notes' => $quotation->note ?? null,
            'show_thermal' => false,
            'show_signatures' => true,
            'show_payment_history' => false,
        ]);

        return self::applyOverrides($base, $overrides);
    }

    public static function fromPurchase(Purchase $purchase, array $overrides = []): array
    {
        $items = [];
        $subTotal = 0;
        foreach ($purchase->details as $detail) {
            $lineTotal = (float) ($detail->total ?? (($detail->qty ?? 0) * ($detail->net_unit_cost ?? 0)));
            $subTotal += $lineTotal;
            $meta = null;
            if ($detail->variant) {
                $meta = self::t('variant').': '.($detail->variant->variant_name ?? $detail->variant->name ?? '');
            }
            $items[] = [
                'title' => $detail->product->product_title ?? self::t('item_not_found'),
                'qty' => $detail->qty,
                'unit_price' => (float) ($detail->net_unit_cost ?? 0),
                'total' => $lineTotal,
                'warranty' => self::t('na'),
                'meta' => $meta,
            ];
        }

        $discounts = [];
        if ((float) ($purchase->order_discount ?? 0) > 0) {
            $discounts[] = ['label' => self::t('order_discount'), 'amount' => (float) $purchase->order_discount];
        }

        $charges = [];
        if ((float) ($purchase->shipping_cost ?? 0) > 0) {
            $charges[] = ['label' => self::t('shipping'), 'amount' => (float) $purchase->shipping_cost];
        }

        $infoBlocks = [];
        if (! empty($purchase->memo_no) || ! empty($purchase->note)) {
            $rows = [];
            if (! empty($purchase->memo_no)) {
                $rows[] = ['label' => self::t('memo_no'), 'value' => $purchase->memo_no];
            }
            if (! empty($purchase->note)) {
                $rows[] = ['label' => self::t('note'), 'value' => $purchase->note];
            }
            $infoBlocks[] = ['title' => self::t('purchase_info'), 'rows' => $rows];
        }

        $base = self::defaults([
            'type' => 'purchase',
            'modal_title' => 'Purchase: '.$purchase->invoice_no,
            'print_title' => 'Print Purchase Invoice',
            'document_label' => self::t('document_purchase'),
            'thermal_title' => self::t('thermal_purchase'),
            'number' => $purchase->invoice_no,
            'date' => $purchase->date,
            'date_short' => self::dateShort($purchase->date),
            'date_long' => self::dateLong($purchase->date),
            'status' => $purchase->status ?? '',
            'served_by' => $purchase->creator->name ?? '',
            'sold_by' => $purchase->creator->name ?? '',
            'payment_method' => self::resolvePaymentMethodName($purchase->payment_method ?? ''),
            'company' => self::overlayBranchIdentity(self::companySettings(), $purchase->branch),
            'from' => [
                'label' => self::t('from'),
                'name' => $purchase->supplier->name ?? self::t('na'),
                'address' => trim(($purchase->supplier->company_name ?? '').' '.($purchase->supplier->address ?? '')),
                'phone' => $purchase->supplier->phone ?? self::t('na'),
            ],
            'bill_to' => self::partyFromBranch($purchase->branch, self::t('to')),
            'items' => $items,
            'sub_total' => $subTotal > 0 ? $subTotal : ((float) $purchase->grand_total - (float) ($purchase->shipping_cost ?? 0) + (float) ($purchase->order_discount ?? 0) - (float) ($purchase->order_tax ?? 0)),
            'discounts' => $discounts,
            'charges' => $charges,
            'tax_percent' => 0,
            'tax_amount' => (float) ($purchase->order_tax ?? 0),
            'grand_total' => (float) $purchase->grand_total,
            'paid_amount' => (float) ($purchase->paid_amount ?? 0),
            'due_amount' => (float) ($purchase->due_amount ?? max(0, $purchase->grand_total - $purchase->paid_amount)),
            'payments' => [],
            'info_blocks' => $infoBlocks,
            'show_thermal' => false,
            'show_signatures' => true,
            'show_payment_history' => false,
        ]);

        return self::applyOverrides($base, $overrides);
    }

    public static function fromSaleReturn(SaleReturn $return, array $overrides = []): array
    {
        $items = [];
        foreach ($return->details as $detail) {
            $metaParts = [];
            if ($detail->variant) {
                $metaParts[] = self::t('variant').': '.($detail->variant->name ?? $detail->variant->variant_name ?? '');
            }
            if (! empty($detail->imei_number)) {
                $imeis = array_filter(array_map('trim', explode(',', (string) $detail->imei_number)));
                if (count($imeis)) {
                    $metaParts[] = 'IMEI: '.implode(', ', $imeis);
                }
            }
            $items[] = [
                'title' => $detail->product->product_title ?? self::t('item'),
                'qty' => $detail->qty,
                'unit_price' => (float) ($detail->unit_price ?? 0),
                'total' => (float) ($detail->total ?? 0),
                'warranty' => self::t('na'),
                'meta' => count($metaParts) ? implode(' | ', $metaParts) : null,
            ];
        }

        $infoRows = [];
        if ($return->sale) {
            $infoRows[] = ['label' => self::t('original_invoice'), 'value' => $return->sale->invoice_no];
        }
        if (! empty($return->return_note)) {
            $infoRows[] = ['label' => self::t('return_note'), 'value' => $return->return_note];
        }
        if (! empty($return->staff_note)) {
            $infoRows[] = ['label' => self::t('staff_note'), 'value' => $return->staff_note];
        }

        $base = self::defaults([
            'type' => 'sale_return',
            'modal_title' => 'Sale Return: '.$return->reference_no,
            'print_title' => 'Print Sale Return',
            'document_label' => self::t('document_sale_return'),
            'thermal_title' => self::t('document_sale_return'),
            'thermal_number_label' => self::t('return_number'),
            'number' => $return->reference_no,
            'date' => $return->return_date ?? $return->created_at,
            'date_short' => self::dateShort($return->return_date ?? $return->created_at),
            'date_long' => self::dateLong($return->return_date ?? $return->created_at),
            'status' => $return->status ?? '',
            'served_by' => $return->user->name ?? '',
            'sold_by' => $return->user->name ?? '',
            'company' => self::overlayBranchIdentity(self::companySettings(), $return->branch),
            'from' => [
                'label' => self::t('return_from'),
                'name' => $return->customer->name ?? self::t('customer'),
                'address' => $return->customer->address ?? '',
                'phone' => $return->customer->phone ?? null,
            ],
            'bill_to' => self::partyFromBranch($return->branch, self::t('received_by')),
            'items' => $items,
            'sub_total' => (float) ($return->grand_total ?? 0),
            'discounts' => [],
            'tax_percent' => 0,
            'tax_amount' => 0,
            'grand_total' => (float) ($return->grand_total ?? 0),
            'paid_amount' => (float) ($return->grand_total ?? 0),
            'due_amount' => 0,
            'payments' => [],
            'info_blocks' => count($infoRows) ? [['title' => self::t('return_info'), 'rows' => $infoRows]] : [],
            'thermal_extra_rows' => $return->sale ? [['label' => self::t('sale_invoice'), 'value' => $return->sale->invoice_no]] : [],
            'thermal_thank_you' => self::t('return_success'),
            'show_thermal' => false,
            'show_signatures' => true,
            'show_payment_history' => false,
            'show_thermal_terms' => false,
            'terms' => [],
        ]);

        return self::applyOverrides($base, $overrides);
    }

    public static function fromPurchaseReturn(PurchaseReturn $return, array $overrides = []): array
    {
        $items = [];
        foreach ($return->details as $detail) {
            $metaParts = [];
            if ($detail->product?->product_code) {
                $metaParts[] = self::t('code').': '.$detail->product->product_code;
            }
            if ($detail->variant) {
                $metaParts[] = self::t('variant').': '.($detail->variant->variant_name ?? $detail->variant->name ?? '');
            }
            $items[] = [
                'title' => $detail->product->product_title ?? self::t('item'),
                'qty' => $detail->qty,
                'unit_price' => (float) ($detail->unit_price ?? 0),
                'total' => (float) ($detail->total ?? 0),
                'warranty' => self::t('na'),
                'meta' => count($metaParts) ? implode(' | ', $metaParts) : null,
            ];
        }

        $infoRows = [];
        if ($return->purchase) {
            $infoRows[] = ['label' => self::t('original_purchase'), 'value' => $return->purchase->invoice_no];
        }
        if (! empty($return->return_note)) {
            $infoRows[] = ['label' => self::t('return_note'), 'value' => $return->return_note];
        }
        if (! empty($return->staff_note)) {
            $infoRows[] = ['label' => self::t('staff_note'), 'value' => $return->staff_note];
        }

        $base = self::defaults([
            'type' => 'purchase_return',
            'modal_title' => 'Purchase Return: '.$return->reference_no,
            'print_title' => 'Print Purchase Return',
            'document_label' => self::t('document_purchase_return'),
            'thermal_title' => self::t('document_purchase_return'),
            'thermal_number_label' => self::t('return_number'),
            'number' => $return->reference_no,
            'date' => $return->return_date ?? $return->created_at,
            'date_short' => self::dateShort($return->return_date ?? $return->created_at),
            'date_long' => self::dateLong($return->return_date ?? $return->created_at),
            'status' => $return->status ?? '',
            'served_by' => $return->user->name ?? '',
            'sold_by' => $return->user->name ?? '',
            'company' => self::overlayBranchIdentity(self::companySettings(), $return->branch),
            'from' => self::partyFromBranch($return->branch, self::t('return_from')),
            'bill_to' => [
                'label' => self::t('return_to'),
                'name' => $return->supplier->name ?? self::t('supplier'),
                'address' => trim(($return->supplier->company_name ?? '').' '.($return->supplier->address ?? '')),
                'phone' => $return->supplier->phone ?? null,
            ],
            'items' => $items,
            'sub_total' => (float) ($return->grand_total ?? 0),
            'discounts' => [],
            'tax_percent' => 0,
            'tax_amount' => 0,
            'grand_total' => (float) ($return->grand_total ?? 0),
            'paid_amount' => (float) ($return->grand_total ?? 0),
            'due_amount' => 0,
            'payments' => [],
            'info_blocks' => count($infoRows) ? [['title' => self::t('return_info'), 'rows' => $infoRows]] : [],
            'thermal_extra_rows' => $return->purchase ? [['label' => self::t('purchase_number'), 'value' => $return->purchase->invoice_no]] : [],
            'thermal_thank_you' => self::t('return_success'),
            'show_thermal' => false,
            'show_signatures' => true,
            'show_payment_history' => false,
            'show_thermal_terms' => false,
            'terms' => [],
        ]);

        return self::applyOverrides($base, $overrides);
    }

    public static function fromTransfer(Transfer $transfer, array $overrides = []): array
    {
        $items = [];
        $subTotal = 0;
        foreach ($transfer->details as $detail) {
            $line = (float) ($detail->subtotal ?? $detail->total ?? 0);
            $subTotal += $line;
            $metaParts = [];
            if ($detail->product?->product_code) {
                $metaParts[] = self::t('code').': '.$detail->product->product_code;
            }
            if ($detail->variant) {
                $metaParts[] = self::t('variant').': '.($detail->variant->variant_name ?? $detail->variant->name ?? '');
            }
            if (! empty($detail->batch_no)) {
                $metaParts[] = self::t('batch').': '.$detail->batch_no;
            }
            $items[] = [
                'title' => $detail->product->product_title ?? self::t('item'),
                'qty' => $detail->qty,
                'unit_price' => (float) ($detail->net_unit_cost ?? 0),
                'total' => $line,
                'warranty' => self::t('na'),
                'meta' => count($metaParts) ? implode(' | ', $metaParts) : null,
            ];
        }

        $charges = [];
        if ((float) ($transfer->shipping_cost ?? 0) > 0) {
            $charges[] = ['label' => self::t('shipping'), 'amount' => (float) $transfer->shipping_cost];
        }

        $infoRows = [];
        if (! empty($transfer->note)) {
            $infoRows[] = ['label' => self::t('note'), 'value' => $transfer->note];
        }
        if ($transfer->user) {
            $infoRows[] = ['label' => self::t('created_by'), 'value' => $transfer->user->name];
        }

        $base = self::defaults([
            'type' => 'transfer',
            'modal_title' => 'Transfer: '.$transfer->reference_no,
            'print_title' => 'Print Transfer',
            'document_label' => self::t('document_transfer'),
            'thermal_title' => self::t('thermal_transfer'),
            'thermal_number_label' => self::t('transfer_number'),
            'number' => $transfer->reference_no,
            'date' => $transfer->created_at,
            'date_short' => self::dateShort($transfer->created_at),
            'date_long' => self::dateLong($transfer->created_at),
            'status' => $transfer->status ?? '',
            'served_by' => $transfer->user->name ?? '',
            'sold_by' => $transfer->user->name ?? '',
            'company' => self::overlayBranchIdentity(self::companySettings(), $transfer->fromBranch),
            'from' => self::partyFromBranch($transfer->fromBranch, self::t('from_branch')),
            'bill_to' => self::partyFromBranch($transfer->toBranch, self::t('to_branch')),
            'items' => $items,
            'sub_total' => $subTotal > 0 ? $subTotal : ((float) $transfer->grand_total - (float) ($transfer->shipping_cost ?? 0)),
            'discounts' => [],
            'charges' => $charges,
            'tax_percent' => 0,
            'tax_amount' => 0,
            'grand_total' => (float) ($transfer->grand_total ?? 0),
            'paid_amount' => (float) ($transfer->grand_total ?? 0),
            'due_amount' => 0,
            'payments' => [],
            'info_blocks' => count($infoRows) ? [['title' => self::t('transfer_info'), 'rows' => $infoRows]] : [],
            'show_thermal' => false,
            'show_signatures' => true,
            'show_payment_history' => false,
            'show_thermal_terms' => false,
            'terms' => [],
            'thermal_thank_you' => self::t('transfer_thank_you'),
        ]);

        return self::applyOverrides($base, $overrides);
    }

    /**
     * Start from defaults and override freely (for non-sale documents).
     */
    public static function make(array $overrides = []): array
    {
        return self::applyOverrides(self::defaults(), $overrides);
    }

    /**
     * Merge overrides into base. List keys are replaced wholesale.
     * Associative nested arrays (company, from, bill_to) are shallow-merged.
     */
    public static function applyOverrides(array $base, array $overrides): array
    {
        $listKeys = ['items', 'discounts', 'charges', 'payments', 'terms', 'thermal_extra_rows', 'info_blocks'];

        foreach ($overrides as $key => $value) {
            if (in_array($key, $listKeys, true)) {
                $base[$key] = $value;

                continue;
            }

            if (is_array($value) && isset($base[$key]) && is_array($base[$key]) && Arr::isAssoc($value)) {
                $base[$key] = array_merge($base[$key], $value);

                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    }

    public static function companySettings(?array $generalSettings = null): array
    {
        $generalSettings = $generalSettings ?? (view()->shared('general_settings') ?? []);

        if (empty($generalSettings) || ! is_array($generalSettings)) {
            $generalSettings = GeneralSettings::where('category', 'general')
                ->pluck('value', 'key_name')
                ->toArray();
        }

        $name = $generalSettings['company_name'] ?? self::t('company_name');
        $a4Logo = $generalSettings['a4_logo'] ?? null;
        $thermalLogo = $generalSettings['thermal_logo'] ?? null;

        return [
            'name' => $name,
            'phone' => $generalSettings['company_phone'] ?? '',
            'telephone' => $generalSettings['company_telephone'] ?? '',
            'bin_number' => $generalSettings['company_bin_number'] ?? '',
            'email' => $generalSettings['company_email'] ?? '',
            'website' => $generalSettings['company_website'] ?? '',
            'address' => $generalSettings['company_address'] ?? '',
            'logo' => $a4Logo,
            'a4_logo' => $a4Logo,
            'logo_url' => ImageHelper::publicUrl(is_string($a4Logo) ? $a4Logo : null, 'settings'),
            'thermal_logo' => $thermalLogo,
            'thermal_logo_url' => ImageHelper::publicUrl(is_string($thermalLogo) ? $thermalLogo : null, 'settings'),
            'initial' => mb_substr($name, 0, 1),
        ];
    }

    public static function terms(): array
    {
        $record = GeneralSettings::where('category', 'general')
            ->where('key_name', 'terms_and_conditions')
            ->first();

        $terms = $record?->value ?? [];

        return is_array($terms) ? array_values(array_filter(array_map('trim', $terms))) : [];
    }

    public static function totalDiscount(array $invoice): float
    {
        return (float) collect($invoice['discounts'] ?? [])->sum('amount');
    }

    public static function totalCharges(array $invoice): float
    {
        return (float) collect($invoice['charges'] ?? [])->sum('amount');
    }

    protected static function defaults(array $merge = []): array
    {
        $settings = self::companySettings();

        return array_merge([
            'type' => 'custom',
            'modal_title' => self::t('invoice'),
            'print_title' => 'Print Invoice',
            'document_label' => self::t('document_invoice'),
            'thermal_title' => self::t('document_receipt'),
            'thermal_number_label' => self::t('invoice'),
            'thermal_thank_you' => self::t('thank_you'),
            'thermal_extra_rows' => [],
            'thermal_width' => self::getThermalPrinterSize(),
            'thermal_logo_height' => self::getThermalLogoHeight(),
            'show_branch_name' => self::getShowBranchName(),
            'thermal_show_company_name' => self::getShowBranchName(),
            'thermal_identity' => self::getThermalIdentityFlags(),
            'show_thermal_terms' => true,
            'number' => '',
            'date' => null,
            'date_short' => '',
            'date_long' => '',
            'status' => '',
            'served_by' => '',
            'sold_by' => '',
            'company' => $settings,
            'from' => [
                'label' => self::t('from'),
                'name' => $settings['name'] ?? '',
                'address' => $settings['address'] ?? '',
                'phone' => $settings['phone'] ?? '',
                'telephone' => $settings['telephone'] ?? '',
                'bin_number' => $settings['bin_number'] ?? '',
                'website' => $settings['website'] ?? '',
                'email' => $settings['email'] ?? '',
            ],
            'bill_to' => [
                'label' => self::t('bill_to'),
                'name' => '',
                'address' => '',
                'phone' => null,
            ],
            'payment_method' => '',
            'items' => [],
            'sub_total' => 0,
            'discounts' => [],
            'charges' => [],
            'tax_percent' => 0,
            'tax_amount' => 0,
            'grand_total' => 0,
            'paid_amount' => 0,
            'received_amount' => 0,
            'change_amount' => 0,
            'due_amount' => 0,
            'payments' => [],
            'info_blocks' => [],
            'notes' => null,
            'terms' => self::terms(),
            'show_thermal' => false,
            'show_signatures' => true,
            'show_payment_history' => true,
            'footer_note' => null,
            'developer_credit' => self::DEVELOPER_CREDIT,
            'header_color' => 'var(--primary-color, #18a1f7)',
        ], $merge);
    }

    public static function overlayBranchIdentity(array $company, $branch): array
    {
        if (! $branch) {
            return $company;
        }

        $branchName = trim((string) ($branch->name ?? ''));
        if ($branchName !== '') {
            $company['name'] = $branchName;
            $company['branch_name'] = $branchName;
        }

        foreach (['bin_number', 'telephone', 'website', 'phone', 'email', 'address'] as $key) {
            $val = $branch->{$key} ?? null;
            if ($val !== null && $val !== '') {
                $company[$key] = $val;
            }
        }

        return $company;
    }

    public static function partyFromBranch($branch, string $label = ''): array
    {
        $label = $label !== '' ? $label : self::t('from');
        if (! $branch) {
            return [
                'label' => $label,
                'name' => self::t('branch_fallback'),
                'address' => '',
                'phone' => '',
                'telephone' => '',
                'bin_number' => '',
                'website' => '',
                'email' => '',
            ];
        }

        return [
            'label' => $label,
            'name' => $branch->name ?? self::t('branch_fallback'),
            'address' => $branch->address ?? '',
            'phone' => $branch->phone ?? '',
            'telephone' => $branch->telephone ?? '',
            'bin_number' => $branch->bin_number ?? '',
            'website' => $branch->website ?? '',
            'email' => $branch->email ?? '',
        ];
    }

    public static function printLayoutDefaults(string $format): array
    {
        $config = config('print_layout.'.$format, []);
        $blocks = [];
        foreach ($config as $id => $meta) {
            $blocks[] = [
                'id' => $id,
                'enabled' => (bool) ($meta['enabled'] ?? true),
                'label' => $meta['label'] ?? $id,
            ];
        }

        return $blocks;
    }

    public static function printBlocks(string $format, ?array $saved = null): array
    {
        $defaults = self::printLayoutDefaults($format);
        if ($saved === null) {
            $raw = GeneralSettings::getGeneralSetting('print_layout_'.$format);
            if (is_string($raw) && $raw !== '') {
                $decoded = json_decode($raw, true);
                $raw = is_array($decoded) ? $decoded : [];
            }
            $saved = is_array($raw) ? $raw : [];
        }

        $byId = [];
        foreach ($saved as $item) {
            if (! empty($item['id'])) {
                $byId[$item['id']] = $item;
            }
        }

        $ordered = [];
        $seen = [];
        foreach ($saved as $item) {
            $id = $item['id'] ?? null;
            $default = collect($defaults)->firstWhere('id', $id);
            if (! $id || ! $default) {
                continue;
            }
            $ordered[] = [
                'id' => $id,
                'enabled' => (bool) ($item['enabled'] ?? true),
                'label' => $default['label'],
            ];
            $seen[$id] = true;
        }
        foreach ($defaults as $block) {
            if (! isset($seen[$block['id']])) {
                $ordered[] = $block;
            }
        }

        return $ordered;
    }

    public static function enabledPrintBlocks(string $format, ?array $saved = null): array
    {
        return array_values(array_filter(
            self::printBlocks($format, $saved),
            fn ($block) => ! empty($block['enabled'])
        ));
    }

    public static function sampleInvoice(): array
    {
        $branch = class_exists(\App\Models\Branch::class)
            ? \App\Models\Branch::query()->orderBy('id')->first()
            : null;
        $settings = self::overlayBranchIdentity(self::companySettings(), $branch);
        if (empty($settings['branch_name'])) {
            $settings['name'] = 'Main Branch';
            $settings['branch_name'] = 'Main Branch';
        }
        $settings['bin_number'] = $settings['bin_number'] ?: 'BIN-000000';
        $settings['phone'] = $settings['phone'] ?: '+880 1700 000000';
        $settings['telephone'] = $settings['telephone'] ?: '+880 2 0000000';
        $settings['website'] = $settings['website'] ?: 'https://example.com';

        return self::defaults([
            'type' => 'sale',
            'modal_title' => 'Invoice Preview',
            'document_label' => self::t('document_invoice'),
            'thermal_title' => self::t('document_sales_receipt'),
            'number' => 'INV-PREVIEW',
            'date_short' => self::dateShort(now()),
            'date_long' => self::dateLong(now()),
            'status' => 'paid',
            'served_by' => 'Preview',
            'sold_by' => 'Preview',
            'company' => $settings,
            'from' => [
                'label' => self::t('from'),
                'name' => $settings['name'],
                'address' => $settings['address'] ?: '123 Main Street',
                'phone' => $settings['phone'],
                'telephone' => $settings['telephone'],
                'bin_number' => $settings['bin_number'],
                'website' => $settings['website'],
                'email' => $settings['email'] ?: 'info@example.com',
            ],
            'bill_to' => [
                'label' => self::t('bill_to'),
                'name' => self::t('walk_in'),
                'phone' => '+880 1800 000000',
            ],
            'payment_method' => 'Cash',
            'items' => [
                ['title' => 'Sample Product', 'qty' => 2, 'unit_price' => 10, 'total' => 20, 'warranty' => self::t('na')],
            ],
            'sub_total' => 20,
            'grand_total' => 20,
            'paid_amount' => 20,
            'received_amount' => 50,
            'change_amount' => 30,
            'due_amount' => 0,
            'show_thermal' => true,
            'show_signatures' => true,
        ]);
    }

    /**
     * Fill empty seller identity fields so print-settings preview shows how BIN / Tel / web look.
     */
    public static function withDemoIdentity(array $invoice): array
    {
        $demo = [
            'bin_number' => 'BIN-000000',
            'telephone' => '+880 2 5550101',
            'website' => 'https://www.example.com',
            'phone' => '+880 1700 123456',
            'email' => 'info@example.com',
            'address' => '123 Main Street',
        ];

        foreach (['company', 'from'] as $section) {
            $invoice[$section] = $invoice[$section] ?? [];
            foreach ($demo as $field => $value) {
                if (empty($invoice[$section][$field])) {
                    $invoice[$section][$field] = $value;
                }
            }
        }

        return $invoice;
    }

    protected static function dateShort(mixed $date): string
    {
        if (empty($date)) {
            return '';
        }

        return Carbon::parse($date)->format('d M Y');
    }

    protected static function dateLong(mixed $date): string
    {
        if (empty($date)) {
            return '';
        }

        return Carbon::parse($date)->format('d/m/Y H:i');
    }

    /**
     * Applied payment vs cash received vs change for a sale.
     * Works for new records (change_amount stored) and older overpaid sales.
     */
    public static function saleCashBreakdown(Sale $sale): array
    {
        $payments = $sale->relationLoaded('payments') ? $sale->payments : $sale->payments()->get();
        $grand = (float) ($sale->grand_total ?? 0);
        $applied = (float) ($sale->paid_amount ?? $payments->sum('amount') ?? 0);
        $storedChange = (float) $payments->sum('change_amount');
        $received = (float) $payments->sum(function ($payment) {
            return (float) $payment->amount + (float) ($payment->change_amount ?? 0);
        });

        if ($received <= 0) {
            $received = $applied;
        }

        $change = $storedChange;
        $due = (float) ($sale->due_amount ?? max(0, $grand - $applied));

        if ($change <= 0 && $due <= 0 && $received > $grand) {
            $change = round($received - $grand, 2);
            $applied = $grand;
        }

        return [
            'paid_amount' => round(max(0, $applied), 2),
            'received_amount' => round(max(0, $received), 2),
            'change_amount' => round(max(0, $change), 2),
            'due_amount' => round(max(0, $due), 2),
        ];
    }
}

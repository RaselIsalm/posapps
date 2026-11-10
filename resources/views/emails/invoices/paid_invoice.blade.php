<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paid Invoice</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f3f4f6; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #374151;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f3f4f6; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background-color: #10b981; padding: 40px 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold; letter-spacing: 1px;">
                                {{ __('PAYMENT RECEIVED') }}
                            </h1>
                            <p style="color: #ecfdf5; margin: 10px 0 0 0; font-size: 16px;">
                                {{ __('Thank you for your purchase!') }}
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 1.5;">
                                {{ __('Hello') }} <strong>{{ $sale->customer->name ?? __('Customer') }}</strong>,<br><br>
                                {{ __('We have received payment for invoice') }} <strong>#{{ $sale->invoice_no ?? $sale->id }}</strong>.
                                {{ __('Please find the transaction details below.') }}
                            </p>

                            <!-- Invoice Details -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 30px; border-top: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb;">
                                <tr>
                                    <td style="padding: 15px 0;">
                                        <strong>{{ __('Invoice Date:') }}</strong><br>
                                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
                                    </td>
                                    <td style="padding: 15px 0; text-align: right;">
                                        <strong>{{ __('Amount Paid:') }}</strong><br>
                                        <span style="color: #10b981; font-size: 18px; font-weight: bold;">
                                            {{ \App\Helpers\CurrencyHelper::format($sale->grand_total) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            <!-- Items Table -->
                            <h3 style="margin: 0 0 15px 0; font-size: 18px; color: #111827;">{{ __('Order Summary') }}</h3>
                            <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; margin-bottom: 30px;">
                                <thead>
                                    <tr>
                                        <th style="padding: 12px 10px; background-color: #f9fafb; text-align: left; border-bottom: 2px solid #e5e7eb; font-size: 14px; color: #6b7280;">{{ __('Item') }}</th>
                                        <th style="padding: 12px 10px; background-color: #f9fafb; text-align: center; border-bottom: 2px solid #e5e7eb; font-size: 14px; color: #6b7280;">{{ __('Qty') }}</th>
                                        <th style="padding: 12px 10px; background-color: #f9fafb; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 14px; color: #6b7280;">{{ __('Price') }}</th>
                                        <th style="padding: 12px 10px; background-color: #f9fafb; text-align: right; border-bottom: 2px solid #e5e7eb; font-size: 14px; color: #6b7280;">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sale->productSales as $item)
                                    <tr>
                                        <td style="padding: 12px 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px;">
                                            {{ $item->product->name ?? $item->product->product_title ?? __('Unknown item') }}
                                        </td>
                                        <td style="padding: 12px 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; text-align: center;">
                                            {{ $item->qty }}
                                        </td>
                                        <td style="padding: 12px 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; text-align: right;">
                                            {{ \App\Helpers\CurrencyHelper::format($item->unit_price) }}
                                        </td>
                                        <td style="padding: 12px 10px; border-bottom: 1px solid #f3f4f6; font-size: 14px; text-align: right;">
                                            {{ \App\Helpers\CurrencyHelper::format($item->total_price) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <!-- Totals -->
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%"></td>
                                    <td width="50%">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #6b7280;">{{ __('Subtotal:') }}</td>
                                                <td style="padding: 8px 0; font-size: 14px; text-align: right;">{{ \App\Helpers\CurrencyHelper::format($sale->sub_total) }}</td>
                                            </tr>
                                            @if($sale->discount_amount > 0)
                                            <tr>
                                                <td style="padding: 8px 0; font-size: 14px; color: #ef4444;">{{ __('Discount:') }}</td>
                                                <td style="padding: 8px 0; font-size: 14px; text-align: right; color: #ef4444;">-{{ \App\Helpers\CurrencyHelper::format($sale->discount_amount) }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <td style="padding: 12px 0; font-size: 16px; font-weight: bold; border-top: 2px solid #e5e7eb;">{{ __('TOTAL:') }}</td>
                                                <td style="padding: 12px 0; font-size: 16px; font-weight: bold; text-align: right; border-top: 2px solid #e5e7eb;">{{ \App\Helpers\CurrencyHelper::format($sale->grand_total) }}</td>
                                            </tr>
                                            @include('emails.invoices.partials.cash_breakdown')
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; font-size: 13px; color: #9ca3af;">
                                This is an automated email. Please do not reply to this message.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

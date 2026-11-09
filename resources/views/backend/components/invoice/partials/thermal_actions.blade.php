{{--
  Single Thermal Print button — always uses General Settings thermal_printer_size.
  Requires global-invoice.js (printThermalInvoiceSection).
--}}
@php
    $defaultThermalSize = \App\Helpers\InvoiceHelper::getThermalPrinterSize();
    $thermalLabel = \App\Helpers\InvoiceHelper::thermalSizes()[$defaultThermalSize] ?? $defaultThermalSize;
@endphp
<button type="button" class="btn btn-secondary px-4"
    onclick="printThermalInvoiceSection(); return false;"
    title="Print on {{ $thermalLabel }} (general settings)">
    <i class="fas fa-receipt me-2"></i> {{ \App\Helpers\InvoiceHelper::t('print_thermal') }}
    <span class="opacity-75 small ms-1">({{ $defaultThermalSize }})</span>
</button>

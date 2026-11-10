<div class="row g-3 mb-4">
    <div class="col-xl-7">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0">{{ __('Recent Transaction') }}</h6>
                <span class="badge bg-primary rounded-pill">{{ __('Latest 5') }}</span>
            </div>
            <div class="card-body px-4 pt-2">
                <ul class="nav nav-tabs nav-tabs-modern mb-3" id="trxTab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-sale">{{ __('Sale') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-purchase">{{ __('Purchase') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-quotation">{{ __('Quotation') }}</a></li>
                    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-payment">{{ __('Payment') }}</a></li>
                </ul>
                <div class="tab-content">
                    {{-- Sale Tab --}}
                    <div class="tab-pane fade show active" id="tab-sale">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                                <thead class="table-light"><tr><th>Date</th><th>Reference</th><th>Customer</th><th>Status</th><th class="text-end">Grand Total</th></tr></thead>
                                <tbody>
                                @forelse($recentSales as $s)
                                    <tr>
                                        <td>{{ $s->created_at->format('d/m/Y') }}</td>
                                        <td><span class="text-primary">{{ $s->invoice_no }}</span></td>
                                        <td>{{ $s->customer->name ?? 'Walk-in' }}</td>
                                        <td><span class="badge bg-success-subtle text-success rounded-pill">Completed</span></td>
                                        <td class="text-end fw-semibold">{{ \App\Helpers\CurrencyHelper::format($s->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No sales found</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Purchase Tab --}}
                    <div class="tab-pane fade" id="tab-purchase">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                                <thead class="table-light"><tr><th>Date</th><th>Reference</th><th>Supplier</th><th>Status</th><th class="text-end">Grand Total</th></tr></thead>
                                <tbody>
                                @forelse($recentPurchases as $p)
                                    <tr>
                                        <td>{{ $p->created_at->format('d/m/Y') }}</td>
                                        <td><span class="text-primary">{{ $p->invoice_no }}</span></td>
                                        <td>{{ $p->supplier->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-success-subtle text-success rounded-pill">{{ ucfirst($p->status ?? 'completed') }}</span></td>
                                        <td class="text-end fw-semibold">{{ \App\Helpers\CurrencyHelper::format($p->grand_total, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No purchases found</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Quotation Tab --}}
                    <div class="tab-pane fade" id="tab-quotation">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                                <thead class="table-light"><tr><th>Date</th><th>Reference</th><th>Customer</th><th>Status</th><th class="text-end">Grand Total</th></tr></thead>
                                <tbody>
                                @forelse($recentQuotations as $q)
                                    <tr>
                                        <td>{{ $q->created_at->format('d/m/Y') }}</td>
                                        <td><span class="text-primary">{{ $q->reference_no ?? '-' }}</span></td>
                                        <td>{{ $q->customer->name ?? 'N/A' }}</td>
                                        <td><span class="badge bg-warning-subtle text-warning rounded-pill">{{ ucfirst($q->status ?? 'pending') }}</span></td>
                                        <td class="text-end fw-semibold">{{ \App\Helpers\CurrencyHelper::format($q->grand_total ?? 0, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No quotations found</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{-- Payment Tab --}}
                    <div class="tab-pane fade" id="tab-payment">
                        <div class="table-responsive">
                            <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                                <thead class="table-light"><tr><th>Date</th><th>Account</th><th>Type</th><th>Note</th><th class="text-end">Amount</th></tr></thead>
                                <tbody>
                                @forelse($recentTransactions as $t)
                                    <tr>
                                        <td>{{ $t->created_at->format('d/m/Y') }}</td>
                                        <td>{{ $t->account->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($t->type=='credit')<span class="badge bg-success-subtle text-success rounded-pill">Credit</span>
                                            @else <span class="badge bg-danger-subtle text-danger rounded-pill">Debit</span>@endif
                                        </td>
                                        <td><small>{{ \Illuminate\Support\Str::limit($t->note,30) ?? '-' }}</small></td>
                                        <td class="text-end fw-semibold {{ $t->type=='credit'?'text-success':'text-danger' }}">{{ \App\Helpers\CurrencyHelper::format($t->amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted py-3">No transactions found</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Best Seller This Month --}}
    <div class="col-xl-5">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0">{{ __('Best Seller') }} {{ \Carbon\Carbon::now()->translatedFormat('F') }}</h6>
                <span class="badge bg-warning text-dark rounded-pill">{{ __('Top 5') }}</span>
            </div>
            <div class="card-body px-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                        <thead class="table-light"><tr><th>Product Details</th><th class="text-end">Qty</th></tr></thead>
                        <tbody>
                        @forelse($bestSellerMonth as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="product-thumb"><i class="fas fa-box text-primary"></i></div>
                                        <span>{{ $item->product->product_title ?? 'N/A' }} [{{ $item->product->id ?? '' }}]</span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">{{ $item->total_qty }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Best Seller Year (Qty) + Best Seller Year (Price) --}}
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0">{{ __('Best Seller') }} {{ \Carbon\Carbon::now()->year }} ({{ __('Qty') }})</h6>
                <span class="badge bg-info text-white rounded-pill">{{ __('Top 5') }}</span>
            </div>
            <div class="card-body px-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                        <thead class="table-light"><tr><th>Product Details</th><th class="text-end">Qty</th></tr></thead>
                        <tbody>
                        @forelse($bestSellerYearQty as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="product-thumb"><i class="fas fa-box text-info"></i></div>
                                        <span>{{ $item->product->product_title ?? 'N/A' }} [{{ $item->product->id ?? '' }}]</span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">{{ $item->total_qty }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-6">
        <div class="card border-0 shadow-sm h-100" style="border-radius:14px">
            <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between pt-3 px-4">
                <h6 class="fw-bold text-dark mb-0">{{ __('Best Seller') }} {{ \Carbon\Carbon::now()->year }} ({{ __('Price') }})</h6>
                <span class="badge bg-danger rounded-pill">{{ __('Top 5') }}</span>
            </div>
            <div class="card-body px-4 pt-2">
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 text-nowrap">
                        <thead class="table-light"><tr><th>Product Details</th><th class="text-end">Grand Total</th></tr></thead>
                        <tbody>
                        @forelse($bestSellerYearPrice as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="product-thumb"><i class="fas fa-box text-danger"></i></div>
                                        <span>{{ $item->product->product_title ?? 'N/A' }} [{{ $item->product->id ?? '' }}]</span>
                                    </div>
                                </td>
                                <td class="text-end fw-semibold">{{ \App\Helpers\CurrencyHelper::format($item->total_revenue, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No data</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded">
    <div>
        <h6 class="text-muted small mb-1">CARD NUMBER</h6>
        <h5 class="fw-bold text-dark mb-0" style="letter-spacing: 1px;">{{ $card->card_no }}</h5>
    </div>
    <div class="text-end">
        <h6 class="text-muted small mb-1">CURRENT BALANCE</h6>
        <h4 class="fw-bold text-success mb-0">{{ \App\Helpers\CurrencyHelper::format($card->balance, 2) }}</h4>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-bordered table-striped table-sm align-middle">
        <thead class="bg-white">
            <tr>
                <th class="text-uppercase small text-muted">Date</th>
                <th class="text-uppercase small text-muted">Invoice</th>
                <th class="text-uppercase small text-muted">Type</th>
                <th class="text-uppercase small text-muted">Note</th>
                <th class="text-end text-uppercase small text-muted">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($card->transactions as $trx)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M, Y') }}</td>
                    <td>
                        @if ($trx->sale)
                            <a href="#" class="text-primary fw-bold text-decoration-none small">
                                {{ $trx->sale->invoice_no ?? 'SALE-' . $trx->sale_id }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        <span
                            class="badge {{ $trx->transaction_type == 'debit' ? 'bg-danger' : 'bg-success' }} text-uppercase"
                            style="font-size: 10px;">
                            {{ $trx->transaction_type }}
                        </span>
                    </td>
                    <td><small class="text-muted">{{ $trx->note ?? '-' }}</small></td>
                    <td
                        class="text-end fw-bold {{ $trx->transaction_type == 'debit' ? 'text-danger' : 'text-success' }}">
                        {{ $trx->transaction_type == 'debit' ? '-' : '+' }}{{ \App\Helpers\CurrencyHelper::format($trx->amount, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="fas fa-history fa-2x mb-2 d-block opacity-25"></i>
                        No usage history found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

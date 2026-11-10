<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <tbody>
            <tr>
                <th style="width: 40%;">Coupon Code</th>
                <td class="fw-bold text-primary text-uppercase">{{ $coupon->code }}</td>
            </tr>
            <tr>
                <th>Type</th>
                <td>
                    <span class="badge {{ $coupon->type == 'percent' ? 'bg-info' : 'bg-primary' }}">
                        {{ ucfirst($coupon->type) }}
                    </span>
                </td>
            </tr>
            <tr>
                <th>Value / Amount</th>
                <td class="fw-bold">
                    {{ $coupon->type == 'percent' ? $coupon->amount . '%' : \App\Helpers\CurrencyHelper::format($coupon->amount) }}
                </td>
            </tr>
            <tr>
                <th>Min. Purchase Amount</th>
                <td>{{ $coupon->min_purchase > 0 ? \App\Helpers\CurrencyHelper::format($coupon->min_purchase) : 'N/A' }}</td>
            </tr>
            @if ($coupon->type == 'percent' && $coupon->max_discount > 0)
                <tr>
                    <th>Max. Discount Amount</th>
                    <td>{{ \App\Helpers\CurrencyHelper::format($coupon->max_discount) }}</td>
                </tr>
            @endif
            <tr>
                <th>Usage Limit</th>
                <td>{{ $coupon->usage_limit ?? 'Unlimited' }}</td>
            </tr>
            <tr>
                <th>Used Count</th>
                <td>{{ $coupon->used_count ?? 0 }}</td>
            </tr>
            <tr>
                <th>Start Date</th>
                <td>{{ date('d M, Y', strtotime($coupon->start_date)) }}</td>
            </tr>
            <tr>
                <th>Expiry Date</th>
                <td>{{ date('d M, Y', strtotime($coupon->end_date)) }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if ($coupon->status)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-danger">Inactive</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Created At</th>
                <td>{{ date('d M, Y h:i A', strtotime($coupon->created_at)) }}</td>
            </tr>
        </tbody>
    </table>
</div>
<div class="d-flex justify-content-end border-top pt-3">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
</div>

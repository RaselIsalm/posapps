<?php

namespace App\DataTables;

use App\Models\GiftCard;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class GiftCardDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            // Card Number
            ->editColumn('card_no', function ($row) {
                return '<span class="fw-bold text-dark" style="letter-spacing: 1px;">'.$row->card_no.'</span>';
            })

            // Customer Name
            ->addColumn('customer_name', function ($row) {
                if ($row->customer) {
                    return '<div>
                                <span class="fw-bold text-primary">'.$row->customer->name.'</span><br>
                                <small class="text-muted">'.$row->customer->phone.'</small>
                            </div>';
                }

                return '<span class="text-muted">Walk-in Customer</span>';
            })

            // Value & Balance
            ->editColumn('value', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->value);
            })
            ->editColumn('balance', function ($row) {
                return '<span class="fw-bold text-success">'.\App\Helpers\CurrencyHelper::format($row->balance).'</span>';
            })

            // Expiry Date Logic
            ->editColumn('expiry_date', function ($row) {
                return date('d M, Y', strtotime($row->expiry_date));
            })

            // Status Badge
            ->editColumn('status', function ($row) {
                $status = strtolower($row->status);

                if ($row->expiry_date < now()) {
                    return '<span class="badge badge-soft-danger">Expired</span>';
                }

                if ($row->balance <= 0) {
                    return '<span class="badge badge-soft-secondary">Used (Empty)</span>';
                }

                if ($status == 'active') {
                    return '<span class="badge badge-soft-success">Active</span>';
                } else {
                    return '<span class="badge badge-soft-warning">Inactive</span>';
                }
            })

            // Action Column
            ->addColumn('action', function ($row) {
                $deleteUrl = route('gift-card.destroy', $row->id);

                return '
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm btn-icon border" type="button" 
                                data-bs-toggle="dropdown" 
                                aria-expanded="false" 
                                data-bs-display="static"> 
                            <i class="fas fa-ellipsis text-dark"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li>
                                <a class="dropdown-item view-btn" href="javascript:void(0)" data-url="'.route('gift-card.show', $row->id).'">
                                    <i class="fas fa-eye me-2 text-info"></i> View Details
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item history-btn" href="javascript:void(0)" data-url="'.route('gift-card.history', $row->id).'">
                                    <i class="fas fa-history me-2 text-primary"></i> Usage History
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item recharge-btn" href="javascript:void(0)" data-url="'.route('gift-card.recharge-form', $row->id).'">
                                    <i class="fas fa-bolt me-2 text-success"></i> Recharge Balance
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item edit-btn" href="javascript:void(0)" data-url="'.route('gift-card.edit', $row->id).'">
                                    <i class="fas fa-edit me-2 text-warning"></i> Edit
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger delete-btn" data-url="'.$deleteUrl.'">
                                    <i class="fas fa-trash-alt me-2"></i> Delete
                                </button>
                            </li>
                        </ul>
                    </div>
                ';
            })

            ->filter(function ($instance) {
                if (! empty(request('search')['value'])) {
                    $instance->where(function ($w) {
                        $search = request('search')['value'];
                        $w->where('card_no', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            });
                    });
                }

                // Date Range Filter Logic
                if (request()->filled('start_date') && request()->filled('end_date')) {
                    $instance->whereBetween('created_at', [
                        request('start_date').' 00:00:00',
                        request('end_date').' 23:59:59',
                    ]);
                }

                // Status Filter Logic
                if (request()->filled('status') && request('status') !== 'all') {
                    $instance->where('status', request('status'));
                }
            })

            ->setRowAttr([
                'data-url' => function ($row) {
                    return route('gift-card.show', $row->id);
                },
                'style' => 'cursor: pointer;',
            ])
            ->setRowClass('clickable-row')

            ->rawColumns(['card_no', 'customer_name', 'balance', 'status', 'action']);
    }

    public function query(GiftCard $model): QueryBuilder
    {
        return $model->newQuery()->with('customer')->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('gift-card-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(0)
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',

                'buttons' => [
                    [
                        'extend' => 'excel',
                        'className' => 'btn btn-warning text-white btn-sm font-weight-bold',
                        'text' => '<i class="fas fa-file-excel"></i> '.__('Excel'),
                    ],
                    [
                        'extend' => 'pdf',
                        'className' => 'btn btn-danger text-white btn-sm font-weight-bold',
                        'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF'),
                    ],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-info text-white btn-sm font-weight-bold',
                        'text' => '<i class="fas fa-print"></i> '.__('Print'),
                    ],
                ],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search Card No...'),
                ],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('card_no')->title(__('Card Number')),
            Column::computed('customer_name')->title(__('Customer')),
            Column::make('value')->title(__('Original Value'))->addClass('text-center'),
            Column::make('balance')->title(__('Current Balance'))->addClass('text-center'),
            Column::make('expiry_date')->title(__('Valid Until')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(60)->addClass('text-end'),
        ];
    }
}

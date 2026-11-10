<?php

namespace App\DataTables;

use App\Models\StockHistory;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class StockHistoryDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->setRowAttr([
                'data-id' => function ($row) {
                    return $row->id;
                },
            ])
            ->addColumn('product_info', function ($row) {
                $name = $row->product->product_title ?? 'Unknown';
                $code = $row->product->product_code ?? '-';
                $variant = $row->variant ? ' ('.($row->variant->variant_name ?? $row->variant->item_code).')' : '';

                return '<div><span class="fw-bold text-dark">'.$name.$variant.'</span><br><small class="text-muted">'.$code.'</small></div>';
            })
            ->addColumn('branch_name', function ($row) {
                return $row->branch->name ?? 'N/A';
            })
            ->editColumn('qty', function ($row) {
                $color = $row->qty > 0 ? 'text-success' : 'text-danger';
                $prefix = $row->qty > 0 ? '+' : '';

                return '<span class="fw-bold '.$color.'">'.$prefix.number_format($row->qty, 2).'</span>';
            })
            ->editColumn('type', function ($row) {
                $types = [
                    'transfer_sent' => '<span class="badge bg-warning">Transfer Sent</span>',
                    'transfer_received' => '<span class="badge bg-success">Transfer Received</span>',
                    'transfer_restore' => '<span class="badge bg-info">Transfer Restored</span>',
                    'sale' => '<span class="badge bg-danger">Sale</span>',
                    'purchase' => '<span class="badge bg-primary">Purchase</span>',
                    'purchase_deleted' => '<span class="badge bg-dark">Purchase Deleted</span>',
                    'adjustment' => '<span class="badge bg-secondary">Adjustment</span>',
                ];

                return $types[$row->type] ?? '<span class="badge bg-secondary">'.ucfirst(str_replace('_', ' ', $row->type)).'</span>';
            })
            ->editColumn('created_at', function ($row) {
                return date('d M, Y h:i A', strtotime($row->created_at));
            })
            ->filter(function ($instance) {
                if (request()->has('branch_id') && ! empty(request('branch_id'))) {
                    $instance->where('branch_id', request('branch_id'));
                }

                if (request()->has('type') && ! empty(request('type'))) {
                    $instance->where('type', request('type'));
                }

                if (request()->has('start_date') && request()->has('end_date') && ! empty(request('start_date')) && ! empty(request('end_date'))) {
                    $instance->whereBetween('created_at', [
                        request('start_date').' 00:00:00',
                        request('end_date').' 23:59:59',
                    ]);
                }

                if (! empty(request('search')['value'])) {
                    $keyword = request('search')['value'];
                    $instance->where(function ($q) use ($keyword) {
                        $q->whereHas('product', function ($pq) use ($keyword) {
                            $pq->where('product_title', 'like', "%{$keyword}%")
                                ->orWhere('product_code', 'like', "%{$keyword}%");
                        })->orWhere('reference_no', 'like', "%{$keyword}%");
                    });
                }
            })
            ->rawColumns(['product_info', 'qty', 'type']);
    }

    public function query(StockHistory $model): QueryBuilder
    {
        return $model->newQuery()->with(['product', 'branch', 'variant'])->latest();
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('stock-history-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(6, 'desc')
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search History...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::computed('product_info')->title(__('Product')),
            Column::computed('branch_name')->title(__('Branch')),
            Column::make('qty')->title(__('Qty'))->addClass('text-center'),
            Column::make('type')->title(__('Type'))->addClass('text-center'),
            Column::make('reference_no')->title(__('Reference No')),
            Column::make('created_at')->title(__('Date')),
        ];
    }
}

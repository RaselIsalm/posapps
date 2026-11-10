<?php

namespace App\DataTables;

use App\Models\ProductBranch;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InventoryDataTable extends DataTable
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

                return '<div><span class="fw-bold text-dark">'.$name.'</span><br><small class="text-muted">'.$code.'</small></div>';
            })

            ->addColumn('variant_name', function ($row) {
                return $row->variant ? $row->variant->name : '<span class="badge bg-secondary">Single</span>';
            })

            ->addColumn('branch_name', function ($row) {
                return $row->branch->name ?? '<span class="text-danger">N/A</span>';
            })

            ->addColumn('stock_status', function ($row) {
                $alertQty = $row->product->alert_quantity ?? 0;
                if ($row->qty <= $alertQty) {
                    return '<span class="badge-stock-low">LOW STOCK ('.$row->qty.')</span>';
                }

                return '<span class="badge-stock-high">IN STOCK ('.$row->qty.')</span>';
            })

            ->editColumn('sell_price', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->sell_price);
            })

            ->addColumn('stock_value', function ($row) {
                return '<span class="text-primary">'.\App\Helpers\CurrencyHelper::format($row->qty * $row->sell_price).'</span>';
            })

            ->editColumn('updated_at', function ($row) {
                return date('d M, Y h:i A', strtotime($row->updated_at));
            })

            ->filter(function ($instance) {
                if (request()->has('branch_id') && ! empty(request('branch_id'))) {
                    $instance->where('branch_id', request('branch_id'));
                }

                if (request()->has('start_date') && request()->has('end_date') && ! empty(request('start_date')) && ! empty(request('end_date'))) {
                    $instance->whereBetween('product_branches.updated_at', [
                        request('start_date').' 00:00:00',
                        request('end_date').' 23:59:59',
                    ]);
                }

                if (request()->has('stock_filter') && ! empty(request('stock_filter'))) {
                    if (request('stock_filter') == 'low') {
                        $instance->whereHas('product', function ($q) {
                            $q->whereColumn('product_branches.qty', '<=', 'products.alert_quantity');
                        });
                    }
                }

                if (! empty(request('search')['value'])) {
                    $keyword = request('search')['value'];
                    $instance->whereHas('product', function ($q) use ($keyword) {
                        $q->where('product_title', 'like', "%{$keyword}%")
                            ->orWhere('product_code', 'like', "%{$keyword}%");
                    });
                }
            })

            ->rawColumns(['product_info', 'variant_name', 'branch_name', 'stock_status', 'stock_value', 'action']);
    }

    public function query(ProductBranch $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['product', 'branch', 'variant'])
            ->select('product_branches.*')
            ->latest('updated_at');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('inventory-table')
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
                'language' => ['search' => '', 'searchPlaceholder' => __('Search...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::computed('product_info')->title(__('Product Info')),
            Column::computed('variant_name')->title(__('Variant')),
            Column::computed('branch_name')->title(__('Branch')),
            Column::computed('stock_status')->title(__('Stock Status'))->addClass('text-center'),
            Column::make('sell_price')->title(__('Unit Price'))->addClass('text-end fw-bold'),
            Column::computed('stock_value')->title(__('Total Value'))->addClass('text-end fw-bold'),
            Column::make('updated_at')->title(__('Last Updated'))->width(100),
            // Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(50)->addClass('text-center'),
        ];
    }
}

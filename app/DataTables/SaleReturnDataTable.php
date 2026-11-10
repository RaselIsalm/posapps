<?php

namespace App\DataTables;

use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SaleReturnDataTable extends DataTable
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
            ->editColumn('date', function ($row) {
                return date('d M, Y', strtotime($row->date));
            })
            ->editColumn('reference_no', function ($row) {
                return '<span class="fw-bold text-primary">'.$row->reference_no.'</span>';
            })
            ->addColumn('sale_ref', function ($row) {
                return $row->sale ? '<span class="fw-bold text-dark">'.$row->sale->invoice_no.'</span>' : '<span class="text-danger">N/A</span>';
            })
            ->addColumn('customer_name', function ($row) {
                $name = $row->customer->name ?? 'Walk-in';
                $phone = $row->customer->phone ?? '';

                return $name.'<br><small class="text-muted">'.$phone.'</small>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'approved') {
                    return '<span class="badge bg-success">Approved</span>';
                } elseif ($row->status == 'rejected') {
                    return '<span class="badge bg-danger">Rejected</span>';
                } else {
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                }
            })
            ->editColumn('grand_total', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->grand_total);
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="dropdown">
                                <button class="btn btn-light btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-ellipsis-v text-black"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">';

                // View Details
                $action .= '<li>
                                <a class="dropdown-item view-details-btn" href="javascript:void(0)" data-id="'.$row->id.'">
                                    <i class="fas fa-eye text-info me-2"></i> View Details
                                </a>
                            </li>';

                if ($row->status == 'pending') {
                    $action .= '<li>
                                    <a class="dropdown-item approve-btn" href="javascript:void(0)" data-id="'.$row->id.'">
                                        <i class="fas fa-check-circle text-success me-2"></i> Approve & Stock In
                                    </a>
                                </li>';
                }

                // Edit
                if ($row->status == 'pending') {
                    $action .= '<li>
                                    <a class="dropdown-item" href="javascript:void(0)" onclick="editReturn('.$row->id.')">
                                        <i class="fas fa-edit text-warning me-2"></i> Edit
                                    </a>
                                </li>';
                }

                // Delete
                if (Auth::user()->can('delete_sale_return')) {
                    $action .= '<li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger delete-btn" href="javascript:void(0)" data-id="'.$row->id.'">
                                        <i class="fas fa-trash-alt me-2"></i> Delete
                                    </a>
                                </li>';
                }

                $action .= '</ul></div>';

                return $action;
            })
            ->rawColumns(['reference_no', 'sale_ref', 'customer_name', 'status', 'action']);
    }

    public function query(SaleReturn $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['sale', 'customer', 'branch']);

        if (request()->has('start_date') && request('start_date') && request()->has('end_date') && request('end_date')) {
            $query->whereBetween('date', [request('start_date'), request('end_date')]);
        }

        if (request()->has('branch_id') && request('branch_id')) {
            $query->where('branch_id', request('branch_id'));
        }

        if (request()->has('search_keyword') && request('search_keyword')) {
            $keyword = request('search_keyword');

            $query->where(function ($q) use ($keyword) {
                $q->where('reference_no', 'like', "%{$keyword}%")
                    ->orWhereHas('customer', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%")
                            ->orWhere('phone', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('sale', function ($q) use ($keyword) {
                        $q->where('invoice_no', 'like', "%{$keyword}%");
                    });
            });
        }

        return $query->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sale-return-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.branch_id = $("#branch_id").val(); data.start_date = $("#start_date").val(); data.end_date = $("#end_date").val();')
            ->orderBy(1)
            ->dom('<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip')
            ->parameters([
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning text-white btn-sm fw-bold', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm fw-bold', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info text-white btn-sm fw-bold', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search Return...'),
                ],
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30)->addClass('text-center'),
            Column::make('id')->visible(false)->printable(false)->exportable(false),
            Column::make('date')->title(__('Date')),
            Column::make('reference_no')->title(__('Return Ref')),
            Column::computed('sale_ref')->title(__('Sale Ref')),
            Column::make('branch.name')->title(__('Branch'))->name('branch.name'),
            Column::computed('customer_name')->title(__('Customer')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::make('total_qty')->title(__('Total Qty'))->addClass('text-center'),
            Column::make('grand_total')->title(__('Grand Total'))->addClass('text-center fw-bold'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(60)
                ->addClass('text-center'),
        ];
    }

    protected function filename(): string
    {
        return 'SaleReturn_'.date('YmdHis');
    }
}

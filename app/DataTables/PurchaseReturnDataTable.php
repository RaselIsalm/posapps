<?php

namespace App\DataTables;

use App\Models\PurchaseReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchaseReturnDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('return_date', function ($row) {
                return date('d M, Y', strtotime($row->return_date));
            })
            ->editColumn('reference_no', function ($row) {
                return '<span class="fw-bold text-primary">'.$row->reference_no.'</span>';
            })
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier->name ?? 'N/A';
            })
            ->addColumn('branch_name', function ($row) {
                return $row->branch->name ?? 'N/A';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'approved') {
                    return '<span class="badge bg-success">Approved</span>';
                } else {
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                }
            })
            ->editColumn('payment_status', function ($row) {
                if ($row->payment_status == 'paid') {
                    return '<span class="badge bg-success">Adjusted</span>';
                } else {
                    return '<span class="badge bg-danger">Due</span>';
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

                $action .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewReturnDetails('.$row->id.')"><i class="fas fa-eye text-info me-2"></i> Details</a></li>';

                if ($row->status == 'pending') {
                    $action .= '<li><a class="dropdown-item edit-btn" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-edit text-primary me-2"></i> Edit</a></li>';
                    $action .= '<li><a class="dropdown-item approve-btn" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-check-circle text-success me-2"></i> Approve</a></li>';
                }

                if (Auth::user()->can('delete_purchase_return')) {
                    $action .= '<li><hr class="dropdown-divider"></li>';
                    $action .= '<li><a class="dropdown-item text-danger delete-btn" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>';
                }

                $action .= '</ul></div>';

                return $action;
            })
            ->rawColumns(['reference_no', 'status', 'payment_status', 'action'])
            ->setRowId('id');
    }

    public function query(PurchaseReturn $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['supplier', 'branch'])->latest('id');

        if (request()->filled('branch_id') && request('branch_id') !== 'all') {
            $query->where('branch_id', request('branch_id'));
        }
        if (request()->filled('start_date')) {
            $query->whereDate('return_date', '>=', request('start_date'));
        }
        if (request()->filled('end_date')) {
            $query->whereDate('return_date', '<=', request('end_date'));
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('purchase-return-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.branch_id = $("#branch_id").val(); data.start_date = $("#start_date").val(); data.end_date = $("#end_date").val();')
            ->orderBy(0)
            ->dom('<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip')
            ->parameters([
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning text-white btn-sm fw-bold', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm fw-bold', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info text-white btn-sm fw-bold', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search Ref No...'),
                ],
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('return_date')->title(__('Date')),
            Column::make('reference_no')->title(__('Ref No')),
            Column::computed('supplier_name')->title(__('Supplier')),
            Column::computed('branch_name')->title(__('Branch')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::make('payment_status')->title(__('Payment'))->addClass('text-center'),
            Column::make('grand_total')->title(__('Total Amount'))->addClass('text-center fw-bold'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->addClass('text-center')->width(60),
        ];
    }

    protected function filename(): string
    {
        return 'PurchaseReturn_'.date('YmdHis');
    }
}

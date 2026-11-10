<?php

namespace App\DataTables;

use App\Models\Transfer;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TransferDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('created_at', function ($row) {
                return date('d M, Y', strtotime($row->created_at));
            })
            ->editColumn('reference_no', function ($row) {
                return '<span class="fw-bold text-primary">'.$row->reference_no.'</span>';
            })
            ->addColumn('from_branch', function ($row) {
                return $row->fromBranch->name ?? '<span class="text-danger">N/A</span>';
            })
            ->addColumn('to_branch', function ($row) {
                return $row->toBranch->name ?? '<span class="text-danger">N/A</span>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'completed') {
                    return '<span class="badge bg-success">Completed</span>';
                } elseif ($row->status == 'sent') {
                    return '<span class="badge bg-info text-dark">Sent (In Transit)</span>';
                } else {
                    return '<span class="badge bg-warning text-dark">Request</span>';
                }
            })
            ->editColumn('grand_total', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->grand_total);
            })
            ->addColumn('action', function ($row) {
                $action = '<div class="dropdown">
                            <button class="btn btn-light btn-sm btn-icon" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-ellipsis text-black"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">';

                // Details Button
                $action .= '<li><a class="dropdown-item" href="javascript:void(0)" onclick="viewTransferDetails('.$row->id.')"><i class="fas fa-eye text-info me-2"></i> Details</a></li>';

                // Workflow Logic Buttons
                if ($row->status == 'request') {
                    $action .= '<li><a class="dropdown-item status-btn" href="javascript:void(0)" data-id="'.$row->id.'" data-status="sent"><i class="fas fa-paper-plane text-warning me-2"></i> Approve & Send</a></li>';
                }

                if ($row->status == 'sent') {
                    $action .= '<li><a class="dropdown-item status-btn" href="javascript:void(0)" data-id="'.$row->id.'" data-status="completed"><i class="fas fa-check-circle text-success me-2"></i> Receive Stock</a></li>';
                }

                // Edit & Delete
                if ($row->status != 'completed') {
                    $action .= '<li><a class="dropdown-item" href="'.route('transfers.edit', $row->id).'"><i class="fas fa-edit text-primary me-2"></i> Edit</a></li>';

                    if (Auth::user()->can('delete_transfer')) {
                        $action .= '<li><hr class="dropdown-divider"></li>';
                        $action .= '<li><a class="dropdown-item text-danger delete-btn" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-trash-alt me-2"></i> Delete</a></li>';
                    }
                }

                $action .= '</ul></div>';

                return $action;
            })
            ->rawColumns(['reference_no', 'from_branch', 'to_branch', 'status', 'action'])
            ->setRowId('id');
    }

    public function query(Transfer $model): QueryBuilder
    {
        $query = $model->newQuery()->with(['fromBranch', 'toBranch'])->latest('id');

        // Filter Logic
        if (request()->filled('from_branch_id')) {
            $query->where('from_branch_id', request('from_branch_id'));
        }
        if (request()->filled('to_branch_id')) {
            $query->where('to_branch_id', request('to_branch_id'));
        }
        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }
        if (request()->filled('start_date')) {
            $query->whereDate('created_at', '>=', request('start_date'));
        }
        if (request()->filled('end_date')) {
            $query->whereDate('created_at', '<=', request('end_date'));
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('transfer-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', 'data.from_branch_id = $("#from_branch_id").val(); data.to_branch_id = $("#to_branch_id").val(); data.status = $("#status").val(); data.start_date = $("#start_date").val(); data.end_date = $("#end_date").val();')
            ->orderBy(0)
            ->dom('<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip')
            ->parameters([
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning text-white btn-sm fw-bold', 'text' => '<i class="fas fa-file-excel me-1"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm fw-bold', 'text' => '<i class="fas fa-file-pdf me-1"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info text-white btn-sm fw-bold', 'text' => '<i class="fas fa-print me-1"></i> '.__('Print')],
                ],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search Reference...'),
                ],
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('created_at')->title(__('Date')),
            Column::make('reference_no')->title(__('Ref No')),
            Column::computed('from_branch')->title(__('From Branch')),
            Column::computed('to_branch')->title(__('To Branch')),
            Column::make('total_qty')->title(__('Items Qty'))->addClass('text-center'),
            Column::make('grand_total')->title(__('Total Amount'))->addClass('text-end'),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->addClass('text-center')->width(60),
        ];
    }

    protected function filename(): string
    {
        return 'Transfers_'.date('YmdHis');
    }
}

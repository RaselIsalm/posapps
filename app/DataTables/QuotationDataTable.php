<?php

namespace App\DataTables;

use App\Models\Quotation;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class QuotationDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return date('d M, Y', strtotime($row->date ?? $row->created_at));
            })
            ->editColumn('reference_no', function ($row) {
                return '<span class="fw-bold" style="color: '.\App\Helpers\ThemeHelper::getPrimaryColor().';">'.$row->reference_no.'</span>';
            })
            ->addColumn('customer', function ($row) {
                if ($row->customer) {
                    return '<div><span class="fw-bold text-dark">'.$row->customer->name.'</span><br><small class="text-muted">'.($row->customer->phone ?? $row->customer->mobile ?? 'N/A').'</small></div>';
                }

                return '<span class="text-muted small">---</span>';
            })
            ->addColumn('supplier', function ($row) {
                if ($row->supplier) {
                    return '<div><span class="fw-bold text-dark">'.$row->supplier->name.'</span><br><small class="text-muted">'.($row->supplier->phone ?? $row->supplier->mobile ?? 'N/A').'</small></div>';
                }

                return '<span class="text-muted small">---</span>';
            })
            ->addColumn('branch_name', function ($row) {
                return $row->branch ? '<span class="text-dark">'.$row->branch->name.'</span>' : 'N/A';
            })
            ->editColumn('grand_total', function ($row) {
                return '<span class="fw-bold">'.\App\Helpers\CurrencyHelper::format($row->grand_total).'</span>';
            })
            ->editColumn('status', function ($row) {
                if ($row->expiry_date && $row->expiry_date < date('Y-m-d') && $row->status == 'pending') {
                    return '<span class="badge bg-secondary">Expired</span>';
                }

                $statusHtml = '';
                if ($row->status == 'pending') {
                    $statusHtml = '<span class="badge bg-warning">Pending</span>';
                } elseif ($row->status == 'sale_generated') {
                    $statusHtml = '<span class="badge bg-success">Sale Generated</span>';
                } elseif ($row->status == 'purchase_generated') {
                    $statusHtml = '<span class="badge bg-info">Purchase Generated</span>';
                } else {
                    $statusHtml = '<span class="badge bg-dark">'.ucfirst(str_replace('_', ' ', $row->status)).'</span>';
                }

                return $statusHtml;
            })
            ->addColumn('action', function ($row) {
                $action = '
                <div class="dropdown text-center">
                    <button class="btn btn-light btn-sm shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-ellipsis-h text-black"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                        <li>
                            <button type="button" class="dropdown-item view-quotation" data-id="'.$row->id.'">
                                <i class="fas fa-eye text-primary me-2"></i> View Details
                            </button>
                        </li>
                        <li>
                            <a class="dropdown-item" href="'.route('quotations.print', $row->id).'" target="_blank">
                                <i class="fas fa-print text-dark me-2"></i> Print Quotation
                            </a>
                        </li>';

                if ($row->status == 'pending' && (! $row->expiry_date || $row->expiry_date >= date('Y-m-d'))) {
                    $action .= '
                        <li>
                            <a class="dropdown-item" href="'.route('quotations.gen-invoice', $row->id).'">
                                <i class="fas fa-file-invoice text-success me-2"></i> Generate Sale
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="'.route('quotations.gen-purchase', $row->id).'">
                                <i class="fas fa-shopping-cart text-info me-2"></i> Generate Purchase
                            </a>
                        </li>';
                }

                $action .= '
                        <li>
                            <a class="dropdown-item" href="'.route('quotations.edit', $row->id).'">
                                <i class="fas fa-edit text-warning me-2"></i> Edit
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item py-2 text-danger delete-btn" data-url="'.route('quotations.destroy', $row->id).'">
                                <i class="far fa-trash-alt me-2"></i> Delete
                            </button>
                        </li>
                    </ul>
                </div>';

                return $action;
            })
            ->rawColumns(['reference_no', 'customer', 'supplier', 'branch_name', 'grand_total', 'status', 'action']);
    }

    public function query(Quotation $model)
    {
        return $model->newQuery()->select('quotations.*')->with(['customer', 'supplier', 'branch'])->latest();
    }

    public function html()
    {
        return $this->builder()
            ->setTableId('quotation-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1, 'desc')
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF')],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search Reference, Supplier...')],
            ]);
    }

    protected function getColumns()
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('date')->title(__('Date')),
            Column::make('reference_no')->title(__('Reference No')),
            Column::make('customer')->title(__('Customer'))->searchable(false),
            Column::make('supplier')->title(__('Supplier'))->searchable(false),
            Column::computed('branch_name')->title(__('Branch'))->searchable(false),
            Column::make('grand_total')->title(__('Total'))->className('text-end'),
            Column::make('status')->title(__('Status'))->className('text-center'),
            Column::computed('action')->title(__('Action'))
                ->exportable(false)
                ->printable(false)
                ->width(80)
                ->addClass('text-center'),
        ];
    }
}

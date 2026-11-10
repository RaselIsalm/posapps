<?php

namespace App\DataTables;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PurchasesDataTables extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()

            // Date formatting
            ->editColumn('date', function ($row) {
                return date('d M, Y', strtotime($row->date));
            })

            // Invoice No
            ->editColumn('invoice_no', function ($row) {
                return '<span class="fw-bold text-primary">'.$row->invoice_no.'</span>';
            })

            // Memo No
            ->editColumn('memo_no', function ($row) {
                return '<span class="fw-bold text-dark">'.$row->memo_no.'</span>';
            })

            // Supplier Info
            ->addColumn('supplier_info', function ($row) {
                $name = $row->supplier ? $row->supplier->name : 'Unknown';
                $phone = $row->supplier ? $row->supplier->phone : '';
                $initial = strtoupper(substr($name, 0, 1));

                $bgClass = 'bg-soft-primary text-primary';
                if (in_array($initial, ['A', 'C', 'E'])) {
                    $bgClass = 'bg-soft-info text-info';
                }
                if (in_array($initial, ['B', 'D', 'F'])) {
                    $bgClass = 'bg-soft-success text-success';
                }

                return '
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-2">
                            <div class="avatar-sm">
                                <span class="avatar-title rounded-circle fw-bold '.$bgClass.'" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; font-size: 14px;">
                                    '.$initial.'
                                </span>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 text-dark" style="font-size: 13px; font-weight: 600;">'.$name.'</h6>
                            <small class="text-muted" style="font-size: 11px;">'.$phone.'</small>
                        </div>
                    </div>
                ';
            })

            // Branch Name
            ->addColumn('branch_name', function ($row) {
                return $row->branch ? $row->branch->name : 'N/A';
            })

            // Status Badge
            ->editColumn('status', function ($row) {
                $status = trim(strtolower($row->status));
                $text = strtoupper($status);
                $badgeClass = 'badge bg-secondary fw-bold';

                if ($status == 'received') {
                    $badgeClass = 'badge badge-soft-success text-success fw-bold';
                } elseif ($status == 'pending') {
                    $badgeClass = 'badge badge-soft-warning text-warning fw-bold';
                } elseif ($status == 'ordered') {
                    $badgeClass = 'badge badge-soft-info text-info fw-bold';
                } elseif ($status == 'partial') {
                    $badgeClass = 'badge badge-soft-primary text-primary fw-bold';
                }

                return '<span class="'.$badgeClass.'">'.$text.'</span>';
            })

            // Amounts
            ->editColumn('grand_total', fn ($row) => \App\Helpers\CurrencyHelper::format($row->grand_total))
            ->editColumn('paid_amount', fn ($row) => '<span class="text-success">'.\App\Helpers\CurrencyHelper::format($row->paid_amount).'</span>')
            ->editColumn('due_amount', function ($row) {
                $class = $row->due_amount > 0 ? 'text-danger fw-bold' : 'text-muted';

                return '<span class="'.$class.'">'.\App\Helpers\CurrencyHelper::format($row->due_amount).'</span>';
            })

            // Action
            ->addColumn('action', function ($row) {
                $editUrl = route('purchases.edit', $row->id);
                $deleteUrl = route('purchases.destroy', $row->id);

                $addPaymentBtn = '';

                if ($row->due_amount > 0) {
                    $addPaymentBtn = '<li>
                        <a class="dropdown-item fw-bold text-black" href="javascript:void(0)" onclick="openPaymentModal('.$row->id.', '.$row->due_amount.')">
                            <i class="fas fa-money-bill-wave text-success me-2"></i> Add Payment
                        </a>
                    </li>';
                }

                return '
                    <div class="dropdown text-center">
                        <button class="btn btn-light btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #eee;">
                            <i class="fas fa-ellipsis text-black"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            
                            <li><a class="dropdown-item fw-bold text-black" href="'.$editUrl.'"><i class="far fa-edit me-2 text-warning fw-bold"></i> Edit</a></li>
                            
                            '.$addPaymentBtn.'

                            <li><hr class="dropdown-divider"></li>
                            
                            <li>
                                <a class="dropdown-item text-danger fw-bold" href="javascript:void(0)" onclick="openDeleteModal(\''.$deleteUrl.'\')">
                                    <i class="far fa-trash-alt me-2"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                ';
            })

            ->setRowAttr([
                'data-id' => function ($row) {
                    return $row->id;
                },
                'style' => 'cursor: pointer;',
            ])

            ->filter(function ($instance) {
                if (! empty(request('search')['value'])) {
                    $instance->where(function ($w) {
                        $keyword = request('search')['value'];

                        $w->orWhere('purchases.memo_no', 'like', "%$keyword%")
                            ->orWhereHas('supplier', function ($q) use ($keyword) {
                                $q->where('name', 'like', "%$keyword%")
                                    ->orWhere('phone', 'like', "%$keyword%");
                            })
                            ->orWhereHas('branch', function ($q) use ($keyword) {
                                $q->where('name', 'like', "%$keyword%");
                            });
                    });
                }

                if (request()->filled('branch_id')) {
                    $instance->where('purchases.branch_id', request('branch_id'));
                }

                if (request()->has('start_date') && request()->has('end_date') && ! empty(request('start_date')) && ! empty(request('end_date'))) {
                    $instance->whereBetween('purchases.date', [request('start_date'), request('end_date')]);
                }

                if (request()->has('status') && ! empty(request('status'))) {
                    $instance->where('purchases.status', request('status'));
                }
            })

            ->rawColumns(['invoice_no', 'memo_no', 'supplier_info', 'status', 'paid_amount', 'due_amount', 'action']);
    }

    public function query(Purchase $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['supplier', 'branch'])
            ->select('purchases.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('purchases-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
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
            Column::make('date')->title(__('Date')),
            Column::make('invoice_no')->title(__('Invoice No'))->addClass('text-center'),
            Column::make('memo_no')->title(__('Memo No')),
            Column::computed('supplier_info')->title(__('Supplier')),
            Column::computed('branch_name')->title(__('Branch')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::make('grand_total')->title(__('Total'))->addClass('fw-bold text-end'),
            Column::make('paid_amount')->title(__('Paid'))->addClass('fw-bold text-end'),
            Column::make('due_amount')->title(__('Due'))->addClass('fw-bold text-end'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(60)->addClass('text-center'),
        ];
    }
}

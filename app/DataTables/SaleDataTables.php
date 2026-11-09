<?php

namespace App\DataTables;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SaleDataTables extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('invoice_no', function ($row) {
                $inv = $row->invoice_no ?? 'INV-'.date('Ymd', strtotime($row->sale_date)).$row->id;

                return '<span class="fw-bold text-dark">'.$inv.'</span>
                    <i class="far fa-copy ms-1 text-muted copy-inv" 
                       data-inv="'.$inv.'" 
                       style="cursor:pointer; font-size:12px;" 
                       title="Copy Invoice"
                       onclick="copyInvoice(this, event)"></i>';
            })
            ->editColumn('sale_date', function ($row) {
                return date('d M, Y', strtotime($row->sale_date));
            })
            ->addColumn('customer_info', function ($row) {
                $name = $row->customer ? $row->customer->name : 'Walk-in Customer';
                $phone = $row->customer ? $row->customer->phone : '';
                $initial = strtoupper(substr($name, 0, 1));

                $bgClass = 'bg-soft-info text-info';
                if ($initial == 'D') {
                    $bgClass = 'bg-soft-primary text-primary';
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
            ->addColumn('branch_name', function ($row) {
                return $row->branch ? $row->branch->name : 'Main Branch';
            })
            ->addColumn('seller_name', function ($row) {
                return $row->seller ? $row->seller->name : 'N/A';
            })
            ->editColumn('grand_total', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->grand_total);
            })
            ->editColumn('payment_status', function ($row) {
                if ($row->isVoided()) {
                    return '<span class="badge badge-soft-secondary">'.e(__('VOIDED')).'</span>';
                }
                $status = strtolower($row->payment_status);
                if ($status == 'paid') {
                    return '<span class="badge badge-soft-success">PAID</span>';
                } elseif ($status == 'partial') {
                    return '<span class="badge badge-soft-warning">PARTIAL</span>';
                } else {
                    return '<span class="badge badge-soft-danger">DUE</span>';
                }
            })
            ->editColumn('delivery_status', function ($row) {
                if ($row->isVoided()) {
                    return '<span class="badge bg-secondary">'.e(__('Voided')).'</span>';
                }
                $status = strtolower((string) $row->delivery_status);
                if ($status === 'shipped') {
                    return '<span class="badge bg-info">Shipped</span>';
                }
                if (in_array($status, ['delivered', 'completed'], true)) {
                    return '<span class="badge bg-success">Delivered</span>';
                }
                if ($status === 'returned') {
                    return '<span class="badge bg-danger">Returned</span>';
                }

                return '<span class="badge bg-secondary">Pending</span>';
            })
            ->addColumn('action', function ($row) {
                $editUrl = route('sale-list.edit', $row->id);
                $voidUrl = route('sale-list.destroy', $row->id);
                $markDeliveredUrl = route('sale-list.markDelivered', $row->id);
                $csrf = csrf_field();

                if ($row->isVoided()) {
                    return '
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #eee;">
                            <i class="fas fa-ellipsis text-black"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item show-invoice" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-file-invoice me-2 text-info"></i> '.__('Invoice').'</a></li>
                        </ul>
                    </div>';
                }

                $paid = $row->payments->sum('amount');
                $due = $row->grand_total - $paid;

                $payDueButton = '';
                if (round($due, 2) > 0) {
                    $payDueButton = '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openDueModal('.$row->id.', '.$due.')"><i class="fas fa-money-bill-wave me-2 text-success"></i> '.__('Pay Due Amount').'</a></li>';
                }

                $status = strtolower((string) $row->delivery_status);
                $shipButton = '';
                $markDeliveredButton = '';
                if ($status === 'pending') {
                    $shipButton = '<li><a class="dropdown-item" href="javascript:void(0)" onclick="openShippingModal('.$row->id.')"><i class="fas fa-truck me-2 text-info"></i> '.__('Ship Order').'</a></li>';
                    $markDeliveredButton = '<li>
                        <form action="'.$markDeliveredUrl.'" method="POST" class="d-inline mark-delivered-form">
                            '.$csrf.'
                            <button type="submit" class="dropdown-item"><i class="fas fa-check-circle me-2 text-success"></i> '.__('Mark as Delivered').'</button>
                        </form>
                    </li>';
                }

                $phoneAttr = e((string) ($row->delivery?->recipient_phone ?: ($row->customer?->phone ?? '')));

                return '
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm btn-icon" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid #eee;">
                            <i class="fas fa-ellipsis text-black"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                            <li><a class="dropdown-item show-invoice" href="javascript:void(0)" data-id="'.$row->id.'"><i class="fas fa-file-invoice me-2 text-info"></i> '.__('Invoice').'</a></li>
                            <li><a class="dropdown-item" href="'.$editUrl.'"><i class="far fa-edit me-2 text-warning"></i> '.__('Edit').'</a></li>
                            
                            '.$payDueButton.' 
                            '.$shipButton.'
                            '.$markDeliveredButton.'
                            <li>
                                <a class="dropdown-item js-check-delivery-record" href="javascript:void(0)" data-phone="'.$phoneAttr.'">
                                    <i class="fas fa-shield-alt me-2 text-warning"></i> '.__('Check delivery record').'
                                </a>
                            </li>

                            '.(auth()->user()?->can('delete_sale_list') ? '
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button type="button" class="dropdown-item text-danger delete-btn" data-url="'.$voidUrl.'">
                                    <i class="fas fa-ban me-2"></i> '.__('Void').'
                                </button>
                            </li>
                            ' : '').'
                        </ul>
                    </div>
                ';
            })
            ->filter(function ($query) {
                if (request()->filled('branch_id')) {
                    $query->where('sales.branch_id', request('branch_id'));
                }
                if (request()->has('start_date') && request()->has('end_date') && ! empty(request('start_date'))) {
                    $query->whereBetween('sales.sale_date', [request('start_date'), request('end_date')]);
                }
                if (request()->has('payment_status') && ! empty(request('payment_status'))) {
                    $query->where('sales.payment_status', request('payment_status'));
                }
                if (request()->has('search') && ! empty(request('search')['value'])) {
                    $search = request('search')['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('sales.invoice_no', 'like', "%{$search}%")
                            ->orWhereHas('customer', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%")
                                    ->orWhere('phone', 'like', "%{$search}%");
                            }
                            )
                            ->orWhereHas('branch', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            }
                            );
                    }
                    );
                }
            })
            ->rawColumns(['invoice_no', 'customer_info', 'payment_status', 'action', 'delivery_status']);
    }

    public function query(Sale $model): QueryBuilder
    {
        // Include voided invoices for audit visibility; reports still use the default not_voided scope.
        return $model->newQuery()
            ->withoutGlobalScope('not_voided')
            ->with(['customer', 'branch', 'payments', 'seller', 'delivery'])
            ->select('sales.*')
            ->latest('sales.id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sale-table')
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
            Column::computed('invoice_no')->title(__('Invoice No')),
            Column::make('sale_date')->title(__('Date')),
            Column::computed('customer_info')->title(__('Customer')),
            Column::computed('branch_name')->title(__('Branch')),
            Column::computed('seller_name')->title(__('Seller')),
            Column::make('grand_total')->title(__('Total Amount'))->addClass('fw-bold text-center'),
            Column::make('payment_status')->title(__('Payment Status'))->addClass('text-center'),
            Column::make('delivery_status')->title(__('Delivery'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(60)->addClass('text-center'),
        ];
    }
}

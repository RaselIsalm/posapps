<?php

namespace App\DataTables;

use App\Models\Delivery;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DeliveryDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('invoice_no', function ($row) {
                return '<a href="javascript:void(0)" onclick="openInvoiceModal('.$row->sale_id.')" class="fw-bold text-primary">#'.$row->invoice_no.'</a>';
            })
            ->editColumn('created_at', function ($row) {
                return date('d M, Y', strtotime($row->created_at)).'<br><small class="text-muted">'.date('h:i A', strtotime($row->created_at)).'</small>';
            })
            ->addColumn('recipient_info', function ($row) {
                return '
                    <div class="d-flex flex-column">
                        <span class="fw-bold text-dark">'.$row->recipient_name.'</span>
                        <small class="text-muted"><i class="fas fa-phone-alt me-1" style="font-size:10px;"></i>'.$row->recipient_phone.'</small>
                        <small class="text-muted text-truncate" style="max-width: 150px;" title="'.$row->address.'">'.$row->address.'</small>
                    </div>
                ';
            })
            ->addColumn('courier_info', function ($row) {
                $courierName = $row->courier ? $row->courier->name : 'N/A';
                $tracking = $row->tracking_code ?? 'Not Generated';

                return '
                    <div>
                        <span class="badge bg-primary text-dark border">'.$courierName.'</span>
                        <div class="mt-1 small fw-bold text-primary">'.$tracking.'</div>
                    </div>
                ';
            })
            ->editColumn('cod_amount', function ($row) {
                if ($row->cod_amount > 0) {
                    return '<span class="text-danger fw-bold">'.\App\Helpers\CurrencyHelper::format($row->cod_amount).'</span>';
                }

                return '<span class="badge bg-primary border text-success fw-blod">Paid / Non-COD</span>';
            })
            ->editColumn('status', function ($row) {
                $status = $row->status;
                $badgeClass = 'bg-secondary';

                if ($status == 'delivered') {
                    $badgeClass = 'bg-success';
                } elseif ($status == 'shipped' || $status == 'processing') {
                    $badgeClass = 'bg-info text-dark';
                } elseif ($status == 'cancelled') {
                    $badgeClass = 'bg-danger';
                } elseif ($status == 'returned') {
                    $badgeClass = 'bg-warning text-dark';
                } elseif ($status == 'return_restocked') {
                    $badgeClass = 'bg-dark';
                }

                return '<span class="badge '.$badgeClass.' text-uppercase">'.str_replace('_', ' ', $status).'</span>';
            })
            ->addColumn('action', function ($row) {
                $deleteUrl = route('delivery.destroy', $row->id);
                $csrf = csrf_field();
                $method = method_field('DELETE');

                $syncBtn = '';
                if ($row->consignment_id && $row->courier && $row->courier->code === 'steadfast') {
                    $syncBtn = '
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="syncSteadfastStatus('.$row->id.')">
                                    <i class="fas fa-cloud-download-alt me-2 text-success"></i> Sync SteadFast Status
                                </a>
                            </li>';
                }

                $phoneAttr = e((string) ($row->recipient_phone ?? ''));

                return '
                    <div class="dropdown">
                        <button class="btn btn-light btn-sm btn-icon border" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis text-black"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                            '.$syncBtn.'
                            <li>
                                <a class="dropdown-item js-check-delivery-record" href="javascript:void(0)" data-phone="'.$phoneAttr.'">
                                    <i class="fas fa-shield-alt me-2 text-warning"></i> Check delivery record
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openStatusModal('.$row->id.', \''.$row->status.'\')">
                                    <i class="fas fa-sync-alt me-2 text-primary"></i> Update Status
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openEditModal('.$row->id.')">
                                    <i class="fas fa-edit me-2 text-info"></i> Edit Details
                                </a>
                            </li>
                            
                            <li>
                                <a class="dropdown-item" href="javascript:void(0)" onclick="openInvoiceModal('.$row->sale_id.')">
                                    <i class="fas fa-print me-2 text-secondary"></i> Invoice
                                </a>
                            </li>

                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="javascript:void(0)" onclick="openDeleteModal(\''.$deleteUrl.'\')">
                                    <i class="far fa-trash-alt me-2"></i> Delete
                                </a>
                            </li>
                        </ul>
                    </div>
                ';
            })
            ->filter(function ($query) {
                if (request()->has('courier_id') && ! empty(request('courier_id'))) {
                    $query->where('courier_id', request('courier_id'));
                }
                if (request()->has('status') && ! empty(request('status'))) {
                    $query->where('status', request('status'));
                }
                if (request()->has('date_range') && ! empty(request('date_range'))) {
                    $dates = explode(' to ', request('date_range'));
                    if (count($dates) == 2) {
                        $query->whereBetween('created_at', [$dates[0].' 00:00:00', $dates[1].' 23:59:59']);
                    }
                }

                // Search Bar
                if (request()->has('search') && ! empty(request('search')['value'])) {
                    $search = request('search')['value'];
                    $query->where(function ($q) use ($search) {
                        $q->where('invoice_no', 'like', "%{$search}%")
                            ->orWhere('tracking_code', 'like', "%{$search}%")
                            ->orWhere('recipient_name', 'like', "%{$search}%")
                            ->orWhere('recipient_phone', 'like', "%{$search}%");
                    });
                }
            })
            ->rawColumns(['invoice_no', 'created_at', 'recipient_info', 'courier_info', 'cod_amount', 'status', 'action']);
    }

    public function query(Delivery $model): QueryBuilder
    {
        return $model->newQuery()
            ->with(['sale', 'courier'])
            ->select('deliveries.*')
            ->latest('id');
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('delivery-table')
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
            Column::computed('created_at')->title(__('Date')),
            Column::computed('invoice_no')->title(__('Invoice')),
            Column::computed('recipient_info')->title(__('Recipient')),
            Column::computed('courier_info')->title(__('Courier & Tracking')),
            Column::computed('cod_amount')->title(__('COD Amount')),
            Column::computed('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')->title(__('Action'))->exportable(false)->printable(false)->width(60)->addClass('text-center'),
        ];
    }
}

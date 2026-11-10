<?php

namespace App\DataTables;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CouponDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->setRowAttr([
                'style' => 'cursor: pointer;',
                'onclick' => function ($coupon) {
                    return 'viewCoupon('.$coupon->id.')';
                },
            ])
            ->editColumn('amount', function ($coupon) {
                return $coupon->type == 'percent' ? $coupon->amount.'%' : \App\Helpers\CurrencyHelper::format($coupon->amount);
            })
            ->editColumn('min_purchase', function ($coupon) {
                return $coupon->min_purchase ? \App\Helpers\CurrencyHelper::format($coupon->min_purchase) : '-';
            })
            ->editColumn('type', function ($coupon) {
                return ucfirst($coupon->type);
            })
            ->editColumn('start_date', function ($coupon) {
                return date('d M, Y', strtotime($coupon->start_date));
            })
            ->editColumn('end_date', function ($coupon) {
                return date('d M, Y', strtotime($coupon->end_date));
            })
            ->editColumn('status', function ($coupon) {
                return $coupon->status
                    ? '<span class="badge bg-success">Active</span>'
                    : '<span class="badge bg-danger">Inactive</span>';
            })
            ->addColumn('action', function ($coupon) {
                $deleteUrl = route('coupons.destroy', $coupon->id);
                $csrf = csrf_token();
                $method = method_field('DELETE');
                $tealColor = \App\Helpers\ThemeHelper::getPrimaryColor();
                $redColor = '#e75132';

                return '
                <div class="d-flex justify-content-center">
                    <button type="button" class="btn btn-sm rounded text-white shadow-sm m-1" 
                            style="background-color: '.$tealColor.'; border-color: '.$tealColor.';" 
                            onclick="event.stopPropagation(); viewCoupon('.$coupon->id.')" title="View">
                        <i class="fas fa-eye"></i>
                    </button>

                    <button type="button" class="btn btn-sm rounded text-white shadow-sm m-1" 
                            style="background-color: '.$tealColor.'; border-color: '.$tealColor.';" 
                            onclick="event.stopPropagation(); editCoupon('.$coupon->id.')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>

                    <button type="button" class="btn btn-sm rounded text-white shadow-sm m-1 delete-btn" 
                            style="background-color: '.$redColor.'; border-color: '.$redColor.';" 
                            onclick="event.stopPropagation(); openDeleteModal(\''.$deleteUrl.'\')"
                            data-url="'.$deleteUrl.'"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>';
            })
            ->rawColumns(['amount', 'status', 'action']);
    }

    /**
     * Get the query source of dataTable.
     */
    public function query(Coupon $model): QueryBuilder
    {
        return $model->newQuery()->latest();
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('coupon-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1)
            ->selectStyleSingle()
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white shadow-sm', 'text' => '<i class="fas fa-file-excel me-1"></i> '.__('Excel')],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white shadow-sm', 'text' => '<i class="fas fa-file-pdf me-1"></i> '.__('PDF')],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-info btn-sm text-white shadow-sm',
                        'text' => '<i class="fas fa-print me-1"></i> '.__('Print'),
                        'title' => 'Coupon List',
                        'autoPrint' => true,

                        'customize' => 'function (win) {
                        $(win.document.body)
                            .css("font-family", "Arial, sans-serif")
                            .css("font-size", "10pt")
                            .css("background-color", "#fff")
                            .css("color", "#333");

                        $(win.document.body).find("h1")
                            .css("font-size", "18pt")
                            .css("text-align", "center")
                            .css("margin-bottom", "20px")
                            .css("text-transform", "uppercase")
                            .css("letter-spacing", "1px");

                        $(win.document.body).find("table")
                            .addClass("table table-bordered table-striped")
                            .css("width", "100%")
                            .css("border-collapse", "collapse")
                            .css("margin-bottom", "0");

                        $(win.document.body).find("thead th")
                            .css("background-color", "#f8f9fa")
                            .css("color", "#000")
                            .css("font-weight", "bold")
                            .css("border", "1px solid #dee2e6")
                            .css("text-align", "center")
                            .css("padding", "10px");

                        $(win.document.body).find("tbody td")
                            .css("border", "1px solid #dee2e6")
                            .css("padding", "8px")
                            .css("vertical-align", "middle");

                        $(win.document.body).find("table tbody tr td:nth-child(4)").css("text-align", "right"); // Value
                        $(win.document.body).find("table tbody tr td:nth-child(5)").css("text-align", "right"); // Min Spend
                        $(win.document.body).find("table tbody tr td:last-child").css("text-align", "center"); // Status
                    }',
                    ],
                ],

                'searching' => true,
                'lengthMenu' => [[10, 25, 50, 100], ['10', '25', '50', '100']],
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => __('Search by Code...'),
                ],
                'initComplete' => "function() {
                    $('.dataTables_filter input').addClass('form-control form-control-sm').css('margin-left', '0');
                    $('.dataTables_filter').addClass('d-inline-block');
                }",
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30),
            Column::make('code')->title(__('Coupon Code'))->addClass('fw-bold text-primary')->searchable(true),
            Column::make('type')->title(__('Type')),
            Column::make('amount')->title(__('Value'))->addClass('text-center'),
            Column::make('min_purchase')->title(__('Min Spend'))->addClass('text-center'),
            Column::make('start_date')->title(__('Start')),
            Column::make('end_date')->title(__('Expires')),
            Column::make('status')->title(__('Status'))->addClass('text-center'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-end'),
        ];
    }

    /**
     * Get the filename for export.
     */
    protected function filename(): string
    {
        return 'Coupon_'.date('YmdHis');
    }
}

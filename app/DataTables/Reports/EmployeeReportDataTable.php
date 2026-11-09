<?php

namespace App\DataTables\Reports;

use App\Models\Branch;
use App\Models\Employee;
use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class EmployeeReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('employee_info', function ($row) {
                return '<div class="d-flex flex-column">
                            <span class="fw-bold text-dark">'.$row->name.'</span>
                            <small class="text-muted"><i class="fas fa-phone-alt me-1"></i>'.$row->phone.'</small>
                        </div>';
            })
            ->addColumn('branch_name', function ($row) {
                $request = $this->request();
                if ($request->filled('branch_id')) {
                    return Branch::find($request->branch_id)->name ?? '-';
                }

                return '<span class="badge bg-info text-dark border">All / Mixed</span>';
            })
            ->addColumn('total_invoice', function ($row) {
                return '<span class="badge bg-info text-dark">'.($row->total_sales_count ?? 0).'</span>';
            })
            ->addColumn('total_sales', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->total_sales_amount ?? 0);
            })
            ->addColumn('sales_return', function ($row) {
                $amount = $row->total_return_amount ?? 0;

                return $amount > 0
                    ? '<span class="text-danger fw-bold">'.\App\Helpers\CurrencyHelper::format($amount).'</span>'
                    : '-';
            })
            ->addColumn('net_sales', function ($row) {
                $net = ($row->total_sales_amount ?? 0) - ($row->total_return_amount ?? 0);

                return '<span class="fw-bold text-dark">'.\App\Helpers\CurrencyHelper::format($net).'</span>';
            })
            ->addColumn('commission', function ($row) {
                return '<span class="fw-bold text-success">'.\App\Helpers\CurrencyHelper::format($row->total_commission ?? 0).'</span>';
            })
            ->setRowAttr([
                'data-id' => function ($row) {
                    return $row->id;
                },
                'data-name' => function ($row) {
                    return $row->name;
                },
                'style' => 'cursor: pointer;',
                'class' => 'clickable-row',
            ])
            ->rawColumns(['employee_info', 'branch_name', 'total_invoice', 'sales_return', 'net_sales', 'commission'])
            ->setRowId('id');
    }

    public function query(Employee $model): QueryBuilder
    {
        $request = $this->request();
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $branch_id = $request->get('branch_id');

        $query = $model->newQuery()->select('employees.*');

        $query->addSelect([
            'total_sales_amount' => Sale::selectRaw('SUM(grand_total)')
                ->whereColumn('seller_id', 'employees.id')
                ->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
                ->when($start_date, fn ($q) => $q->whereDate('sale_date', '>=', $start_date))
                ->when($end_date, fn ($q) => $q->whereDate('sale_date', '<=', $end_date))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id)),

            'total_sales_count' => Sale::selectRaw('COUNT(id)')
                ->whereColumn('seller_id', 'employees.id')
                ->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
                ->when($start_date, fn ($q) => $q->whereDate('sale_date', '>=', $start_date))
                ->when($end_date, fn ($q) => $q->whereDate('sale_date', '<=', $end_date))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id)),
        ]);

        $query->addSelect([
            'total_return_amount' => SaleReturn::selectRaw('SUM(grand_total)')
                ->where('status', 'approved')
                ->whereIn('sale_id', function ($q) use ($branch_id) {
                    $q->select('id')->from('sales')->whereColumn('seller_id', 'employees.id');
                    if ($branch_id) {
                        $q->where('branch_id', $branch_id);
                    }
                })
                ->when($start_date, fn ($q) => $q->whereDate('date', '>=', $start_date))
                ->when($end_date, fn ($q) => $q->whereDate('date', '<=', $end_date)),
        ]);

        $query->addSelect([
            'total_commission' => Sale::selectRaw('SUM(
                (SELECT SUM(
                    product_sales.qty * LEAST(
                        COALESCE(employees.max_commission, 0), 
                        COALESCE(products.max_comission, 0)
                    )
                ) FROM product_sales 
                JOIN products ON product_sales.product_id = products.id 
                WHERE product_sales.sale_id = sales.id
                )
            )')
                ->whereColumn('seller_id', 'employees.id')
                ->whereIn('delivery_status', ['delivered', 'completed', 'pending'])
                ->when($start_date, fn ($q) => $q->whereDate('sale_date', '>=', $start_date))
                ->when($end_date, fn ($q) => $q->whereDate('sale_date', '<=', $end_date))
                ->when($branch_id, fn ($q) => $q->where('branch_id', $branch_id)),
        ]);

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('employee-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(4, 'desc')
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel'), 'footer' => true],
                    ['extend' => 'pdf', 'className' => 'btn btn-danger btn-sm text-white', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF'), 'footer' => true],
                    [
                        'extend' => 'print',
                        'className' => 'btn btn-info btn-sm text-white',
                        'text' => '<i class="fas fa-print"></i> '.__('Print'),
                        'footer' => true,
                        'title' => 'Employee Performance Report',
                        'orientation' => 'landscape',
                        'customize' => 'function (win) {
                            $(win.document.body)
                                .css("font-size", "10pt")
                                .css("background-color", "#fff");

                            $(win.document.body).find("h1")
                                .css("font-size", "16pt")
                                .css("text-align", "center")
                                .css("margin-bottom", "20px")
                                .css("font-weight", "bold");

                            $(win.document.body).find("table")
                                .addClass("compact")
                                .css("font-size", "inherit")
                                .css("width", "100%")
                                .css("border-collapse", "collapse");

                            $(win.document.body).find("td, th")
                                .css("padding", "6px 8px") 
                                .css("border", "1px solid #ccc")
                                .css("vertical-align", "middle");

                            $(win.document.body).find("tr td:nth-child(4)").css("text-align", "center");
                            $(win.document.body).find("tr td:nth-child(5), tr td:nth-child(6), tr td:nth-child(7), tr td:nth-child(8)")
                                .css("text-align", "right");

                            $(win.document.body).find("tfoot th, tfoot td")
                                .css("font-weight", "bold")
                                .css("background-color", "#f2f2f2")
                                .css("border-top", "2px solid #000")
                                .css("font-size", "11pt");
                        }',
                    ],
                ],
                'searching' => false,
                'paging' => true,

                'footerCallback' => 'function ( row, data, start, end, display ) {
                    var api = this.api();

                    var intVal = function (i) {
                        if (typeof i === "number") return i;
                        var str = typeof i === "string" ? i.replace(/<[\s\S]*?>/g, "") : "";
                        str = str.replace(/[^\d.-]/g, "");
                        return parseFloat(str) || 0;
                    };

                    var totalInvoice = api.column(3, { page: "current"} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
                    var totalSales   = api.column(4, { page: "current"} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
                    var totalReturn  = api.column(5, { page: "current"} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
                    var netSales     = api.column(6, { page: "current"} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
                    var totalComm    = api.column(7, { page: "current"} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );

                    $( api.column(3).footer() ).html(totalInvoice);
                    $( api.column(4).footer() ).html(totalSales.toFixed(2));
                    $( api.column(5).footer() ).html(totalReturn.toFixed(2));
                    $( api.column(6).footer() ).html(netSales.toFixed(2));
                    $( api.column(7).footer() ).html(totalComm.toFixed(2));
                }',
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30),
            Column::make('employee_info')->title(__('Employee Info'))->width(200)->footer(__('Total Summary :')),
            Column::make('branch_name')->title(__('Branch'))->addClass('text-center'),
            Column::make('total_invoice')->title(__('Total Invoice'))->addClass('text-center fw-bold')->footer('0'),
            Column::make('total_sales')->title(__('Total Sales'))->addClass('text-end')->footer('0.00'),
            Column::make('sales_return')->title(__('Return'))->addClass('text-end text-danger')->footer('0.00'),
            Column::make('net_sales')->title(__('Net Sales'))->addClass('text-end fw-bold')->footer('0.00'),
            Column::make('commission')->title(__('Commission'))->addClass('text-end fw-bold text-success')->footer('0.00'),
        ];
    }

    protected function filename(): string
    {
        return 'EmployeeReport_'.date('YmdHis');
    }
}

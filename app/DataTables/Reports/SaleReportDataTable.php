<?php

namespace App\DataTables\Reports;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SaleReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('sale_date', function ($row) {
                return Carbon::parse($row->sale_date)->format('d M, Y');
            })
            ->addColumn('daily_invoices', function ($row) {
                return '<span class="text-black fs-6">'.$row->daily_invoices.'</span>';
            })
            ->editColumn('total_sales', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->total_sales);
            })
            ->editColumn('total_paid', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->total_paid);
            })
            ->editColumn('total_due', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->total_due);
            })
            ->rawColumns(['daily_invoices']);
    }

    public function query(Sale $model): QueryBuilder
    {
        $query = $model->newQuery();

        $query->selectRaw('
            sale_date, 
            COUNT(id) as daily_invoices,
            SUM(grand_total) as total_sales, 
            SUM(paid_amount) as total_paid, 
            SUM(due_amount) as total_due
        ');

        // Branch Filter
        if (request()->has('branch_id') && ! empty(request()->branch_id)) {
            $query->where('branch_id', request()->branch_id);
        }

        // Date Filter
        $fromDate = request()->get('from_date');
        $toDate = request()->get('to_date');

        if (! empty($fromDate) && ! empty($toDate)) {
            $query->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        $query->whereIn('delivery_status', ['delivered', 'completed', 'pending']);

        // Grouping
        $query->groupBy('sale_date');

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('salereport-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(1, 'desc')
            ->parameters([
                'dom' => '<"row align-items-center mb-3"<"col-md-6"l><"col-md-6 d-flex justify-content-end align-items-center gap-2"fB>>rtip',
                'buttons' => [
                    ['extend' => 'excel', 'className' => 'btn btn-warning btn-sm text-white', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel')],
                    [
                        'extend' => 'pdf',
                        'className' => 'btn btn-danger btn-sm text-white',
                        'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF'),
                        'title' => 'Sales Report',
                        'orientation' => 'portrait',
                        'pageSize' => 'A4',
                        'customize' => "function(doc) {
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0].length + 1).join('*').split('');
                            doc.defaultStyle.alignment = 'center';
                            doc.styles.tableHeader.alignment = 'center';
                        }",
                    ],
                    ['extend' => 'print', 'className' => 'btn btn-info btn-sm text-white', 'text' => '<i class="fas fa-print"></i> '.__('Print')],
                ],
                'language' => ['search' => '', 'searchPlaceholder' => __('Search...')],
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('sale_date')->title(__('Date')),
            Column::make('daily_invoices')->title(__('Invoice Qty'))->addClass('text-center'),
            Column::make('total_sales')->title(__('Total Sales (Tk)'))->addClass('text-right'),
            Column::make('total_paid')->title(__('Total Paid (Tk)'))->addClass('text-right'),
            Column::make('total_due')->title(__('Total Due (Tk)'))->addClass('text-right'),
        ];
    }

    protected function filename(): string
    {
        return 'Daily_Sales_Summary_'.date('YmdHis');
    }
}

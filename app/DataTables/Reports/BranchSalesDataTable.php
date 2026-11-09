<?php

namespace App\DataTables\Reports;

use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchSalesDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param  QueryBuilder  $query  Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->editColumn('sale_date', function ($row) {
                return date('d M, Y', strtotime($row->sale_date));
            })
            ->editColumn('total_invoices', function ($row) {
                return '<div class="text-center">'.$row->total_invoices.'</div>';
            })
            ->editColumn('total_sales', function ($row) {
                return '<div class="text-end fw-bold">'.\App\Helpers\CurrencyHelper::format($row->total_sales).'</div>';
            })
            ->editColumn('total_paid', function ($row) {
                return '<div class="text-end text-success">'.\App\Helpers\CurrencyHelper::format($row->total_paid).'</div>';
            })
            ->editColumn('total_due', function ($row) {
                return '<div class="text-end text-danger">'.\App\Helpers\CurrencyHelper::format($row->total_due).'</div>';
            })
            ->rawColumns(['total_invoices', 'total_sales', 'total_paid', 'total_due']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Sale $model): QueryBuilder
    {
        $request = request();

        $query = $model->newQuery()
            ->selectRaw('
                sale_date, 
                COUNT(id) as total_invoices, 
                SUM(grand_total) as total_sales, 
                SUM(paid_amount) as total_paid, 
                SUM(due_amount) as total_due
            ')
            ->whereIn('delivery_status', ['delivered', 'completed', 'pending']);

        if ($request->has('branch_id') && ! empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('start_date') && ! empty($request->start_date)) {
            $query->whereDate('sale_date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && ! empty($request->end_date)) {
            $query->whereDate('sale_date', '<=', $request->end_date);
        }

        return $query->groupBy('sale_date')->orderBy('sale_date', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('sales-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('reports.branch.sales'))
            ->dom('<"row"<"col-md-4"l><"col-md-8 d-flex justify-content-end align-items-center"fB>>rtip')
            ->orderBy(1)
            ->buttons([
                Button::make('excel')
                    ->text('<i class="fas fa-file-excel"></i> '.__('Excel'))
                    ->className('btn btn-orange btn-sm'),

                Button::make('pdf')
                    ->text('<i class="fas fa-file-pdf"></i> '.__('PDF'))
                    ->className('btn btn-red btn-sm'),

                Button::make('print')
                    ->text('<i class="fas fa-print"></i> '.__('Print'))
                    ->className('btn btn-teal btn-sm'),
            ]);

    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::computed('DT_RowIndex')->title('#'),
            Column::make('sale_date')->title(__('Date')),
            Column::make('total_invoices')->title(__('Total Invoices'))->addClass('text-center'),
            Column::make('total_sales')->title(__('Total Sales'))->addClass('text-end'),
            Column::make('total_paid')->title(__('Total Paid'))->addClass('text-end'),
            Column::make('total_due')->title(__('Total Due'))->addClass('text-end'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'Branch_Sales_'.date('YmdHis');
    }
}

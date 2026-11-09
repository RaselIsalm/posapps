<?php

namespace App\DataTables\Reports;

use App\Models\Expenses;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchExpenseDataTable extends DataTable
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
            ->editColumn('date', function ($row) {
                return date('d M, Y', strtotime($row->date));
            })
            ->editColumn('total_count', function ($row) {
                return '<div class="text-center">'.$row->total_count.'</div>';
            })
            ->editColumn('total_amount', function ($row) {
                return '<div class="text-end fw-bold text-danger">'.\App\Helpers\CurrencyHelper::format($row->total_amount).'</div>';
            })
            ->rawColumns(['total_count', 'total_amount']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(Expenses $model): QueryBuilder
    {
        $request = request();
        $query = $model->newQuery()
            ->selectRaw('
                date, 
                COUNT(id) as total_count, 
                SUM(amount) as total_amount
            ')
            ->where('status', 1);

        if ($request->has('branch_id') && ! empty($request->branch_id)) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->has('start_date') && ! empty($request->start_date)) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->has('end_date') && ! empty($request->end_date)) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        return $query->groupBy('date')->orderBy('date', 'desc');
    }

    /**
     * Optional method if you want to use the html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('expense-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('reports.branch.expenses'))
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
            Column::make('date')->title(__('Date')),
            Column::make('total_count')->title(__('Total Vouchers'))->addClass('text-center'),
            Column::make('total_amount')->title(__('Total Expense'))->addClass('text-end'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'Branch_Expenses_'.date('YmdHis');
    }
}

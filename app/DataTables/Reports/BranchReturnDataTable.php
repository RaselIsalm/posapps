<?php

namespace App\DataTables\Reports;

use App\Models\SaleReturn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BranchReturnDataTable extends DataTable
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
                // à¦¤à¦¾à¦°à¦¿à¦– à¦«à¦°à¦®à§à¦¯à¦¾à¦Ÿ
                return date('d M, Y', strtotime($row->date));
            })
            ->editColumn('total_returns', function ($row) {
                // à¦°à¦¿à¦Ÿà¦¾à¦°à§à¦¨ à¦¸à¦‚à¦–à§à¦¯à¦¾
                return '<div class="text-center">'.$row->total_returns.'</div>';
            })
            ->editColumn('total_amount', function ($row) {
                // à¦Ÿà¦¾à¦•à¦¾à¦° à¦…à§à¦¯à¦¾à¦®à¦¾à¦‰à¦¨à§à¦Ÿ (à¦°à¦¿à¦Ÿà¦¾à¦°à§à¦¨ à¦¤à¦¾à¦‡ à¦²à¦¾à¦² à¦°à¦™à§‡ à¦¦à§‡à¦–à¦¾à¦¨à§‹ à¦¹à¦²à§‹)
                return '<div class="text-end fw-bold text-danger">'.\App\Helpers\CurrencyHelper::format($row->total_amount).'</div>';
            })
            ->rawColumns(['total_returns', 'total_amount']);
    }

    /**
     * Get query source of dataTable.
     */
    public function query(SaleReturn $model): QueryBuilder
    {
        $request = request();

        // Query: à¦¤à¦¾à¦°à¦¿à¦– à¦…à¦¨à§à¦¯à¦¾à§Ÿà§€ à¦—à§à¦°à§à¦ª à¦•à¦°à¦¾ à¦à¦¬à¦‚ à¦®à§‹à¦Ÿ à¦°à¦¿à¦Ÿà¦¾à¦°à§à¦¨ à¦­à§à¦¯à¦¾à¦²à§ à¦¬à§‡à¦° à¦•à¦°à¦¾
        $query = $model->newQuery()
            ->selectRaw('
                date, 
                COUNT(id) as total_returns, 
                SUM(grand_total) as total_amount
            ')
            ->where('status', 'approved');

        // à¦«à¦¿à¦²à§à¦Ÿà¦¾à¦° à¦²à¦œà¦¿à¦•
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
            ->setTableId('return-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('reports.branch.returns'))
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
            Column::make('total_returns')->title(__('Total Returns'))->addClass('text-center'),
            Column::make('total_amount')->title(__('Total Amount'))->addClass('text-end'),
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'Branch_Returns_'.date('YmdHis');
    }
}

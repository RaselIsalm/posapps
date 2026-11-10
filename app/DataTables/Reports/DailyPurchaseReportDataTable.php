<?php

namespace App\DataTables\Reports;

use App\Models\Purchase;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DailyPurchaseReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->editColumn('date', fn ($row) => date('d M, Y', strtotime($row->date)))
            ->editColumn('total_amount', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_amount))
            ->editColumn('total_paid', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_paid))
            ->editColumn('total_due', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_due));
    }

    public function query(Purchase $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->select(
                'date',
                DB::raw('COUNT(id) as total_invoices'),
                DB::raw('SUM(grand_total) as total_amount'),
                DB::raw('SUM(paid_amount) as total_paid'),
                DB::raw('SUM(due_amount) as total_due')
            )
            ->groupBy('date')
            ->orderBy('date', 'desc');

        // Filters
        if ($this->request()->get('branch_id')) {
            $query->where('branch_id', $this->request()->get('branch_id'));
        }
        if ($this->request()->get('supplier_id')) {
            $query->where('supplier_id', $this->request()->get('supplier_id'));
        }
        if ($this->request()->get('start_date') && $this->request()->get('end_date')) {
            $query->whereBetween('date', [$this->request()->get('start_date'), $this->request()->get('end_date')]);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('table-daily')
            ->columns($this->getColumns())
            ->minifiedAjax(route('reports.purchase.daily'), '
                data.start_date = $("#start_date").val();
                data.end_date = $("#end_date").val();
                data.supplier_id = $("#supplier_id").val();
                data.branch_id = $("#branch_id").val();
            ')
            ->dom("<'row mb-3'<'col-sm-12 col-md-4 d-flex align-items-center'l><'col-sm-12 col-md-8 d-flex justify-content-end align-items-center'fB>>".
                  "<'row'<'col-sm-12'tr>>".
                  "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>")
            ->orderBy(1)
            ->buttons([
                Button::make('excel')->className('btn btn-custom-excel btn-sm')->text('<i class="fas fa-file-excel"></i> '.__('Excel')),
                Button::make('pdf')->className('btn btn-custom-pdf btn-sm')->text('<i class="fas fa-file-pdf"></i> '.__('PDF')),
                Button::make('print')->className('btn btn-custom-print btn-sm')->text('<i class="fas fa-print"></i> '.__('Print')),
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('date')->title(__('Date')),
            Column::make('total_invoices')->title(__('Invoices'))->addClass('text-center'),
            Column::make('total_amount')->title(__('Total Amount')),
            Column::make('total_paid')->title(__('Paid')),
            Column::make('total_due')->title(__('Due')),
        ];
    }

    protected function filename(): string
    {
        return 'DailyPurchaseReport_'.date('YmdHis');
    }
}

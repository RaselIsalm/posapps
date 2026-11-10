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

class SupplierPurchaseReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('supplier_name', function ($row) {
                return $row->supplier->name.' ('.($row->supplier->company_name ?? '').')';
            })
            ->addColumn('phone', function ($row) {
                return $row->supplier->phone ?? '-';
            })
            ->editColumn('total_amount', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_amount))
            ->editColumn('total_paid', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_paid))
            ->editColumn('total_due', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_due));
    }

    public function query(Purchase $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->join('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
            ->select(
                'purchases.supplier_id',
                'suppliers.name as supplier_name',
                'suppliers.company_name',
                'suppliers.phone',
                DB::raw('COUNT(purchases.id) as total_invoices'),
                DB::raw('SUM(purchases.grand_total) as total_amount'),
                DB::raw('SUM(purchases.paid_amount) as total_paid'),
                DB::raw('SUM(purchases.due_amount) as total_due')
            )
            ->groupBy('purchases.supplier_id', 'suppliers.name', 'suppliers.company_name', 'suppliers.phone');

        if ($this->request()->get('branch_id')) {
            $query->where('purchases.branch_id', $this->request()->get('branch_id'));
        }
        if ($this->request()->get('supplier_id')) {
            $query->where('purchases.supplier_id', $this->request()->get('supplier_id'));
        }
        if ($this->request()->get('start_date') && $this->request()->get('end_date')) {
            $query->whereBetween('purchases.date', [$this->request()->get('start_date'), $this->request()->get('end_date')]);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('table-supplier')
            ->columns($this->getColumns())
            ->minifiedAjax(route('reports.purchase.supplier'), '
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
            Column::make('DT_RowIndex')->title('#'),
            Column::make('supplier_name')->title(__('Supplier')),
            Column::make('phone')->title(__('Phone')),
            Column::make('total_invoices')->title(__('Invoices'))->addClass('text-center'),
            Column::make('total_amount')->title(__('Total Purchase')),
            Column::make('total_paid')->title(__('Paid')),
            Column::make('total_due')->title(__('Due'))->addClass('text-danger'),
        ];
    }

    protected function filename(): string
    {
        return 'SupplierPurchaseReport_'.date('YmdHis');
    }
}

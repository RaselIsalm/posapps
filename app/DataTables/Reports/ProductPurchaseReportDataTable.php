<?php

namespace App\DataTables\Reports;

use App\Models\PurchaseDetail;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductPurchaseReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('product_name', function ($row) {
                return $row->product->product_title ?? 'N/A';
            })
            ->addColumn('product_code', function ($row) {
                return $row->product->product_code ?? 'N/A';
            })
            ->addColumn('category', function ($row) {
                return $row->product->category->name ?? '-';
            })
            ->editColumn('avg_cost', fn ($row) => \App\Helpers\CurrencyHelper::format($row->avg_cost))
            ->editColumn('total_amount', fn ($row) => \App\Helpers\CurrencyHelper::format($row->total_amount))
            ->setRowId('product_id'); // Unique ID for row
    }

    public function query(PurchaseDetail $model): QueryBuilder
    {
        $query = $model->newQuery()
            ->join('purchases', 'purchase_details.purchase_id', '=', 'purchases.id')
            ->select(
                'purchase_details.product_id',
                DB::raw('SUM(purchase_details.qty) as total_qty'),
                DB::raw('SUM(purchase_details.total) as total_amount'),
                // Weighted Avg Cost calculation
                DB::raw('SUM(purchase_details.total) / SUM(purchase_details.qty) as avg_cost')
            )
            ->with(['product:id,product_title,product_code,category_id', 'product.category:id,name'])
            ->groupBy('purchase_details.product_id');

        // Apply Dynamic Filters from Request
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
            ->setTableId('table-product')
            ->columns($this->getColumns())
            ->minifiedAjax(route('reports.purchase.product'), '
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
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false),
            Column::make('product_name')->title(__('Product Name')),
            Column::make('product_code')->title(__('Code')),
            Column::make('category')->title(__('Category')),
            Column::make('total_qty')->title(__('Total Qty'))->addClass('text-center'),
            Column::make('avg_cost')->title(__('Avg Cost')),
            Column::make('total_amount')->title(__('Total Amount'))->addClass('text-end font-weight-bold'),
        ];
    }

    protected function filename(): string
    {
        return 'ProductPurchaseReport_'.date('YmdHis');
    }
}

<?php

namespace App\DataTables\Reports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InventoryReportDataTable extends DataTable
{
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('image', function ($row) {
                $url = $row->thumbnail
                    ? asset('storage/products/'.$row->thumbnail)
                    : asset('backend/assets/images/preview_image.png');

                return '<img src="'.$url.'" alt="Img" style="height: 40px; width: 40px; object-fit: cover; border-radius: 5px; border: 1px solid #ddd;">';
            })
            ->addColumn('product_info', function ($row) {
                $branch_name = $row->branch_name ?? 'All';

                return '<div>
                            <span class="fw-bold text-dark">'.$row->product_title.'</span><br>
                            <small class="text-muted">Code: '.$row->product_code.'</small> <br>
                            <small class="badge bg-primary text-dark border">'.$branch_name.'</small>
                        </div>';
            })
            ->addColumn('stock', function ($row) {
                $qty = $row->stock_qty ?? 0;
                if ($qty <= 5) {
                    return '<span class="badge bg-danger">'.$qty.' (Low)</span>';
                }

                return '<span class="badge bg-success">'.$qty.'</span>';
            })
            ->addColumn('stock_value_cost', function ($row) {
                $value = ($row->stock_qty ?? 0) * ($row->product_cost ?? 0);

                return \App\Helpers\CurrencyHelper::format($value);
            })
            ->addColumn('stock_value_sale', function ($row) {
                $value = ($row->stock_qty ?? 0) * ($row->selling_price ?? 0);

                return \App\Helpers\CurrencyHelper::format($value);
            })
            ->addColumn('potential_profit', function ($row) {
                $profit_per_unit = ($row->selling_price ?? 0) - ($row->product_cost ?? 0);
                $total_profit = $profit_per_unit * ($row->stock_qty ?? 0);
                $color = $total_profit >= 0 ? 'text-success' : 'text-danger';

                return '<span class="fw-bold '.$color.'">'.\App\Helpers\CurrencyHelper::format($total_profit).'</span>';
            })
            ->editColumn('product_cost', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->product_cost);
            })
            ->editColumn('selling_price', function ($row) {
                return \App\Helpers\CurrencyHelper::format($row->selling_price);
            })
            ->rawColumns(['image', 'product_info', 'stock', 'potential_profit'])
            ->setRowId('id');
    }

    public function query(Product $model): QueryBuilder
    {
        $request = $this->request();

        $query = $model->newQuery()
            ->join('product_branches', 'products.id', '=', 'product_branches.product_id')
            ->join('branches', 'product_branches.branch_id', '=', 'branches.id')
            ->select(
                'products.id',
                'products.product_title',
                'products.product_code',
                'products.thumbnail',
                'products.product_cost',
                'products.selling_price',
                'products.category_id',
                'products.created_at',
                'product_branches.qty as stock_qty',
                'product_branches.branch_id',
                'branches.name as branch_name'
            );

        if ($request->filled('branch_id')) {
            $query->where('product_branches.branch_id', $request->get('branch_id'));
        }

        if ($request->filled('category_id')) {
            $query->where('products.category_id', $request->get('category_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('products.created_at', '>=', $request->get('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('products.created_at', '<=', $request->get('end_date'));
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('inventory-report-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('Bfrtip')
            ->orderBy(2)
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
                        'title' => 'Inventory Valuation Report',
                        'orientation' => 'landscape',
                        'pageSize' => 'A4',

                        'customize' => 'function (win) {
                            $(win.document.body).css("font-size", "10pt").css("background-color", "#fff");
                            $(win.document.body).find("h1").css("font-size", "15pt").css("text-align", "center").css("margin-bottom", "20px");
                            
                            $(win.document.body).find("table")
                                .addClass("compact")
                                .css("font-size", "inherit")
                                .css("width", "100%")
                                .css("border-collapse", "collapse");

                            $(win.document.body).find("td, th")
                                .css("padding", "5px")
                                .css("border", "1px solid #ddd")
                                .css("vertical-align", "middle")
                                .css("text-align", "left");
                                
                            $(win.document.body).find("td:nth-child(4), td:nth-child(5), td:nth-child(6), td:nth-child(7), td:nth-child(8)")
                                .css("text-align", "right");

                            $(win.document.body).find("tfoot th, tfoot td")
                                .css("font-weight", "bold")
                                .css("background-color", "#f0f0f0")
                                .css("border-top", "2px solid #000");
                        }',
                    ],
                ],
                'searching' => false,
                'paging' => true,
                'footerCallback' => 'function ( row, data, start, end, display ) {
                    var api = this.api();

                    var parseNumber = function ( i ) {
                        if (typeof i === "string") {
                            i = i.replace(/<[^>]+>/g, "");
                            i = i.replace(/,/g, "");
                            return parseFloat(i) || 0;
                        }
                        return typeof i === "number" ? i : 0;
                    };

                    var totalQty = api.column(3, { page: "current"} ).data().reduce( function (a, b) {
                        var val = typeof b === "string" ? b.replace(/<[^>]+>/g, "").split("(")[0] : b;
                        return parseNumber(a) + parseNumber(val);
                    }, 0 );

                    var totalCostVal = api.column(6, { page: "current"} ).data().reduce( function (a, b) {
                        return parseNumber(a) + parseNumber(b);
                    }, 0 );

                    var totalSaleVal = api.column(7, { page: "current"} ).data().reduce( function (a, b) {
                        return parseNumber(a) + parseNumber(b);
                    }, 0 );

                    var totalProfit = api.column(8, { page: "current"} ).data().reduce( function (a, b) {
                        return parseNumber(a) + parseNumber(b);
                    }, 0 );

                    $( api.column(3).footer() ).html(totalQty + " Pcs");
                    $( api.column(6).footer() ).html(totalCostVal.toFixed(2));
                    $( api.column(7).footer() ).html(totalSaleVal.toFixed(2));
                    $( api.column(8).footer() ).html(totalProfit.toFixed(2));
                }',
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('#')->searchable(false)->orderable(false)->width(30),
            Column::make('image')->title(__('Image'))->width(50)->addClass('text-center'),
            Column::make('product_info')->title(__('Product Info'))->footer(__('Total Summary :')),
            Column::make('stock')->title(__('Stock (Qty)'))->addClass('text-center')->footer(__('Total Stock')),
            Column::make('product_cost')->title(__('Unit Cost')),
            Column::make('selling_price')->title(__('Unit Price')),
            Column::make('stock_value_cost')->title(__('Asset Value (Cost)'))->addClass('fw-bold text-primary')->footer(__('Total Asset')),
            Column::make('stock_value_sale')->title(__('Sales Value'))->footer(__('Total Sales')),
            Column::make('potential_profit')->title(__('Potential Profit'))->addClass('fw-bold')->footer(__('Total Profit')),
        ];
    }

    protected function filename(): string
    {
        return 'InventoryReport_'.date('YmdHis');
    }
}

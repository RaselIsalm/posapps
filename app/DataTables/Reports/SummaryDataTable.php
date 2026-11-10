<?php

namespace App\DataTables\Reports;

use App\Models\Expenses;
use App\Models\Payroll;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SummaryDataTable extends DataTable
{
    public function dataTable($query)
    {
        return datatables()
            ->collection($query)
            ->addColumn('date', function ($row) {
                return Carbon::parse($row['date'])->format('d M, Y');
            })
            ->editColumn('sales', fn ($row) => \App\Helpers\CurrencyHelper::format($row['sales']))
            ->editColumn('s_tax', fn ($row) => \App\Helpers\CurrencyHelper::format($row['s_tax']))
            ->editColumn('s_return', fn ($row) => \App\Helpers\CurrencyHelper::format($row['s_return']))
            ->editColumn('purchase', fn ($row) => \App\Helpers\CurrencyHelper::format($row['purchase']))
            ->editColumn('p_return', fn ($row) => \App\Helpers\CurrencyHelper::format($row['p_return']))
            ->editColumn('expense', fn ($row) => \App\Helpers\CurrencyHelper::format($row['expense']))
            ->editColumn('salary', fn ($row) => \App\Helpers\CurrencyHelper::format($row['salary']))
            ->editColumn('profit', function ($row) {
                $color = $row['profit'] >= 0 ? 'text-success' : 'text-danger';

                return '<span class="fw-bold '.$color.'">'.\App\Helpers\CurrencyHelper::format($row['profit']).'</span>';
            })
            ->rawColumns(['profit', 'date'])
            ->setRowId('date');
    }

    public function query(Request $request)
    {
        $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $end = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();
        $branchId = $request->branch_id;

        // 1. Basic Sales Data (Totals and Tax)
        $salesTotals = Sale::whereBetween('sale_date', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(sale_date) as sale_date_only, SUM(grand_total) as total_sales, SUM(tax_amount) as total_tax')
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->get()
            ->keyBy('sale_date_only');

        // 2. COGS Data
        $cogsData = ProductSale::join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->selectRaw('
                DATE(sales.sale_date) as cogs_date,
                SUM(product_sales.qty * (CASE WHEN product_sales.purchase_unit_cost > 0 THEN product_sales.purchase_unit_cost ELSE products.product_cost END)) as total_cogs
            ')
            ->groupBy(DB::raw('DATE(sales.sale_date)'))
            ->get()
            ->keyBy('cogs_date');

        // 3. Sales Returns and Return COGS
        $s_returns = SaleReturn::whereBetween('date', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'approved')
            ->join('sale_return_details', 'sale_returns.id', '=', 'sale_return_details.sale_return_id')
            ->join('products', 'sale_return_details.product_id', '=', 'products.id')
            ->selectRaw('
                DATE(sale_returns.date) as return_date_only, 
                SUM(sale_return_details.total) as total_return,
                SUM(sale_return_details.qty * (CASE WHEN sale_return_details.purchase_unit_cost > 0 THEN sale_return_details.purchase_unit_cost ELSE products.product_cost END)) as return_cogs
            ')
            ->groupBy(DB::raw('DATE(sale_returns.date)'))
            ->get()
            ->keyBy('return_date_only');

        // 4. Purchases
        $purchases = Purchase::whereBetween('date', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(date) as purchase_date, SUM(grand_total) as total_purchase, SUM(order_tax) as total_tax')
            ->groupBy(DB::raw('DATE(date)'))
            ->get()
            ->keyBy('purchase_date');

        // 5. Purchase Returns
        $p_returns = collect();
        if (class_exists('\App\Models\PurchaseReturn')) {
            $p_returns = PurchaseReturn::whereBetween('return_date', [$start, $end])
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->selectRaw('DATE(return_date) as p_return_date, SUM(grand_total) as total_return')
                ->groupBy(DB::raw('DATE(return_date)'))
                ->get()
                ->keyBy('p_return_date');
        }

        // 6. Expenses
        $expenses = Expenses::whereBetween('date', [$start, $end])
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->selectRaw('DATE(date) as expense_date, SUM(amount) as total_expense')
            ->groupBy(DB::raw('DATE(date)'))
            ->get()
            ->keyBy('expense_date');

        // 7. Salary
        $payrolls = collect();
        if (class_exists('\App\Models\Payroll')) {
            $payrolls = Payroll::whereBetween('payment_date', [$start, $end])
                ->selectRaw('DATE(payment_date) as payroll_date, SUM(net_salary) as total_salary')
                ->groupBy(DB::raw('DATE(payment_date)'))
                ->get()
                ->keyBy('payroll_date');
        }

        // Merge Data
        $period = CarbonPeriod::create($start, $end);
        $data = [];

        foreach ($period as $date) {
            $dateKey = $date->format('Y-m-d');

            $saleTotal = $salesTotals->get($dateKey);
            $cogsRow = $cogsData->get($dateKey);
            $sReturnData = $s_returns->get($dateKey);
            $purchaseData = $purchases->get($dateKey);
            $pReturnData = $p_returns->get($dateKey);
            $expenseData = $expenses->get($dateKey);
            $salaryData = $payrolls->get($dateKey);

            if ($saleTotal || $sReturnData || $purchaseData || $pReturnData || $expenseData || $salaryData) {
                $s_amount = $saleTotal ? $saleTotal->total_sales : 0;
                $s_tax = $saleTotal ? $saleTotal->total_tax : 0;
                $s_cogs = $cogsRow ? $cogsRow->total_cogs : 0;

                $s_return = $sReturnData ? $sReturnData->total_return : 0;
                $s_return_cogs = $sReturnData ? $sReturnData->return_cogs : 0;

                $p_amount = $purchaseData ? $purchaseData->total_purchase : 0;
                $p_tax = $purchaseData ? $purchaseData->total_tax : 0;
                $p_return = $pReturnData ? $pReturnData->total_return : 0;

                $e_amount = $expenseData ? $expenseData->total_expense : 0;
                $s_salary = $salaryData ? $salaryData->total_salary : 0;

                // Profit: (Net Sales - Net COGS) - Expenses - Salary
                $netSales = $s_amount - $s_return;
                $netCOGS = $s_cogs - $s_return_cogs;
                $grossProfit = $netSales - $netCOGS;
                $netProfit = $grossProfit - $e_amount - $s_salary;

                $data[] = [
                    'date' => $dateKey,
                    'sales' => $s_amount,
                    's_tax' => $s_tax,
                    's_return' => $s_return,
                    'purchase' => $p_amount,
                    'p_tax' => $p_tax,
                    'p_return' => $p_return,
                    'expense' => $e_amount,
                    'salary' => $s_salary,
                    'profit' => $netProfit,
                ];
            }
        }

        return collect($data);
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('summary-table')
            ->columns($this->getColumns())
            ->minifiedAjax('', '
                data.start_date = $("#start_date").val();
                data.end_date = $("#end_date").val();
                data.branch_id = $("#branch_id").val();
            ')
            ->dom('<"d-flex justify-content-end mb-2"B>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>')
            ->orderBy(0, 'desc')
            ->parameters([
                'buttons' => [
                    ['extend' => 'excel', 'text' => '<i class="fas fa-file-excel"></i> '.__('Excel'), 'className' => 'btn-excel'],
                    ['extend' => 'pdf', 'text' => '<i class="fas fa-file-pdf"></i> '.__('PDF'), 'className' => 'btn-pdf'],
                    ['extend' => 'print', 'text' => '<i class="fas fa-print"></i> '.__('Print'), 'className' => 'btn-print'],
                ],
                'footerCallback' => "function ( row, data, start, end, display ) {
                    var api = this.api();
                    var intVal = function ( i ) {
                        if (typeof i === 'string') {
                            var clean = i.replace(/<[^>]+>/g, '').replace(/[$,\\s]/g, '');
                            return parseFloat(clean) || 0;
                        }
                        return typeof i === 'number' ? i : 0;
                    };
                    
                    var sales = api.column(1, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var tax = api.column(2, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var s_returns = api.column(3, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var purchases = api.column(4, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var p_returns = api.column(5, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var expenses = api.column(6, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var salary = api.column(7, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );
                    var profit = api.column(8, { page: 'current'} ).data().reduce( (a, b) => intVal(a) + intVal(b), 0 );

                    $( api.column(1).footer() ).html(sales.toFixed(2));
                    $( api.column(2).footer() ).html(tax.toFixed(2));
                    $( api.column(3).footer() ).html(s_returns.toFixed(2));
                    $( api.column(4).footer() ).html(purchases.toFixed(2));
                    $( api.column(5).footer() ).html(p_returns.toFixed(2));
                    $( api.column(6).footer() ).html(expenses.toFixed(2));
                    $( api.column(7).footer() ).html(salary.toFixed(2));
                    
                    var pClass = profit >= 0 ? 'text-success' : 'text-danger';
                    $( api.column(8).footer() ).html('<span class=\"' + pClass + '\">' + profit.toFixed(2) + '</span>');
                }",
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('date')->title(__('DATE'))->addClass('text-start text-muted fw-bold small')->footer(__('Total Summary')),
            Column::make('sales')->title(__('SALES'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('s_tax')->title(__('SALES TAX'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('s_return')->title(__('S.RETURN'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('purchase')->title(__('PURCHASE'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('p_return')->title(__('P.RETURN'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('expense')->title(__('EXPENSE'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('salary')->title(__('SALARY'))->addClass('text-end text-muted fw-bold small')->footer('0.00'),
            Column::make('profit')->title(__('PROFIT'))->addClass('text-end fw-bold small')->footer('0.00'),
        ];
    }

    protected function filename(): string
    {
        return 'Summary_'.date('YmdHis');
    }
}

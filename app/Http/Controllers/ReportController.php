<?php

namespace App\Http\Controllers;

use App\DataTables\Reports\CustomerLedgerDataTable;
use App\DataTables\Reports\EmployeeReportDataTable;
use App\DataTables\Reports\InventoryReportDataTable;
use App\DataTables\Reports\SaleReportDataTable;
use App\DataTables\Reports\SummaryDataTable;
use App\DataTables\Reports\SupplierLedgerDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expenses;
use App\Models\Payroll;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class ReportController extends Controller
{
    protected function nestQuickAddData(): array
    {
        $employees = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::where('is_active', 1)->orderBy('name')->get(['id', 'name']);
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        return compact('employees', 'accounts', 'employeeRoles');
    }

    public function salesReport(SaleReportDataTable $dataTable, Request $request)
    {
        $branches = Branch::all();
        extract($this->nestQuickAddData());
        $summaryQuery = Sale::query();

        if ($request->has('branch_id') && ! empty($request->branch_id)) {
            $summaryQuery->where('branch_id', $request->branch_id);
        }

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        if (! empty($fromDate) && ! empty($toDate)) {
            $summaryQuery->whereBetween('sale_date', [$fromDate, $toDate]);
        }

        $summaryQuery->whereIn('delivery_status', ['delivered', 'completed', 'pending']);

        $summary = $summaryQuery->selectRaw('
            SUM(grand_total) as total_sales,
            SUM(paid_amount) as total_paid,
            SUM(due_amount) as total_due,
            COUNT(id) as total_invoices
        ')->first();

        if (! $summary) {
            $summary = (object) ['total_sales' => 0, 'total_paid' => 0, 'total_due' => 0, 'total_invoices' => 0];
        }

        return $dataTable->render('backend.reports.sales', compact('summary', 'fromDate', 'toDate', 'branches', 'employees', 'accounts', 'employeeRoles'));
    }

    public function summaryReport(Request $request, SummaryDataTable $dataTable)
    {
        $branches = Branch::all();
        extract($this->nestQuickAddData());

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth();
        $branchId = $request->branch_id;

        $salesQuery = Sale::whereBetween('sale_date', [$startDate, $endDate]);
        if ($branchId) {
            $salesQuery->where('branch_id', $branchId);
        }
        $totalSales = (clone $salesQuery)->sum('grand_total');
        $totalTax = (clone $salesQuery)->sum('tax_amount');

        $sReturnQuery = SaleReturn::whereBetween('date', [$startDate, $endDate])->where('status', 'approved');
        if ($branchId) {
            $sReturnQuery->where('branch_id', $branchId);
        }
        $totalSaleReturn = $sReturnQuery->sum('grand_total');

        $purchasesQuery = Purchase::whereBetween('date', [$startDate, $endDate]);
        if ($branchId) {
            $purchasesQuery->where('branch_id', $branchId);
        }
        $totalPurchase = $purchasesQuery->sum('grand_total');

        $pReturnQuery = PurchaseReturn::whereBetween('return_date', [$startDate, $endDate]);
        if ($branchId) {
            $pReturnQuery->where('branch_id', $branchId);
        }
        $totalPurchaseReturn = $pReturnQuery->sum('grand_total');

        $expenseQuery = Expenses::query();
        $expensesQuery = $expenseQuery->whereBetween('date', [$startDate, $endDate]);
        if ($branchId) {
            $expensesQuery->where('branch_id', $branchId);
        }
        $totalExpense = $expensesQuery->sum('amount');

        $totalSalary = 0;
        if (class_exists('\App\Models\Payroll')) {
            $salaryQuery = Payroll::whereBetween('payment_date', [$startDate, $endDate]);
            $totalSalary = $salaryQuery->sum('net_salary');
        }

        $totals = [
            'total_sales' => $totalSales,
            'total_sales_return' => $totalSaleReturn,
            'total_tax' => $totalTax,
            'total_purchase' => $totalPurchase,
            'total_purchase_return' => $totalPurchaseReturn,
            'total_expense' => $totalExpense,
            'total_salary' => $totalSalary,
        ];

        $totalCOGS = \App\Models\ProductSale::join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->when($branchId, fn ($q) => $q->where('sales.branch_id', $branchId))
            ->sum(\Illuminate\Support\Facades\DB::raw('product_sales.qty * (CASE WHEN product_sales.purchase_unit_cost > 0 THEN product_sales.purchase_unit_cost ELSE products.product_cost END)'));

        $totalReturnCOGS = \App\Models\SaleReturnDetail::join('sale_returns', 'sale_return_details.sale_return_id', '=', 'sale_returns.id')
            ->join('products', 'sale_return_details.product_id', '=', 'products.id')
            ->where('sale_returns.status', 'approved')
            ->whereBetween('sale_returns.date', [$startDate, $endDate])
            ->when($branchId, fn ($q) => $q->where('sale_returns.branch_id', $branchId))
            ->sum(\Illuminate\Support\Facades\DB::raw('sale_return_details.qty * (CASE WHEN sale_return_details.purchase_unit_cost > 0 THEN sale_return_details.purchase_unit_cost ELSE products.product_cost END)'));

        $totals['total_profit'] = ($totalSales - $totalSaleReturn) - ($totalCOGS - $totalReturnCOGS) - $totalExpense - $totalSalary;

        return $dataTable->render('backend.reports.summary', compact('totals', 'startDate', 'endDate', 'branches', 'employees', 'accounts', 'employeeRoles'));
    }

    public function supplierLedger(SupplierLedgerDataTable $dataTable)
    {
        $suppliers = Supplier::select('id', 'name', 'phone')->get();
        $branches = Branch::select('id', 'name')->get();
        extract($this->nestQuickAddData());

        return $dataTable->render('backend.reports.supplier_ledger', compact('suppliers', 'branches', 'employees', 'accounts', 'employeeRoles'));
    }

    public function customerLedger(CustomerLedgerDataTable $dataTable)
    {
        $customers = Customer::select('id', 'name', 'phone')->get();
        $branches = Branch::select('id', 'name')->get();
        extract($this->nestQuickAddData());

        return $dataTable->render('backend.reports.customer_ledger', compact('customers', 'branches', 'employees', 'accounts', 'employeeRoles'));
    }

    public function inventoryReport(InventoryReportDataTable $dataTable)
    {
        $categories = Category::select('id', 'name')->get();
        $branches = Branch::select('id', 'name')->get();
        extract($this->nestQuickAddData());

        return $dataTable->render('backend.reports.inventory', compact('categories', 'branches', 'employees', 'accounts', 'employeeRoles'));
    }

    public function employeesReport(EmployeeReportDataTable $dataTable)
    {
        $branches = Branch::select('id', 'name')->get();
        extract($this->nestQuickAddData());

        return $dataTable->render('backend.reports.employees', compact('branches', 'employees', 'accounts', 'employeeRoles'));
    }

    public function getEmployeeSaleDetails($id, Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $branch_id = $request->branch_id;

        $employee = Employee::findOrFail($id);
        $emp_max_comm = $employee->max_commission ?? 0;

        $details = Sale::query()
            ->select(
                'sales.sale_date',
                'sales.invoice_no',
                'products.product_title as product_name',
                'products.product_code',
                'product_sales.qty',
                'products.selling_price',
                'products.max_comission as prod_comm'
            )
            ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
            ->join('products', 'product_sales.product_id', '=', 'products.id')
            ->where('sales.seller_id', $id)
            ->whereIn('sales.delivery_status', ['delivered', 'completed', 'pending'])
            ->when($start_date, fn ($q) => $q->whereDate('sales.sale_date', '>=', $start_date))
            ->when($end_date, fn ($q) => $q->whereDate('sales.sale_date', '<=', $end_date))
            ->when($branch_id, fn ($q) => $q->where('sales.branch_id', $branch_id))
            ->orderBy('sales.sale_date', 'desc')
            ->get();

        $total_commission = 0;
        $total_qty = 0;
        $formatted_data = $details->map(function ($item) use ($emp_max_comm, &$total_commission, &$total_qty) {
            $prod_rate = $item->prod_comm ?? 0;
            $applied_rate = min($emp_max_comm, $prod_rate);
            $row_comm = $item->qty * $applied_rate;
            $total_commission += $row_comm;
            $total_qty += $item->qty;

            return [
                'date' => date('d M, Y', strtotime($item->sale_date)),
                'invoice' => $item->invoice_no,
                'product_name' => $item->product_name,
                'product_code' => $item->product_code ?? '-',
                'qty' => $item->qty,
                'prod_rate' => number_format($prod_rate, 2),
                'emp_limit' => number_format($emp_max_comm, 2),
                'applied_rate' => number_format($applied_rate, 2),
                'row_comm' => number_format($row_comm, 2),
                'selling_price' => number_format($item->selling_price, 2),
                'is_limited' => ($emp_max_comm < $prod_rate),
            ];
        });

        return response()->json([
            'data' => $formatted_data,
            'summary' => [
                'total_qty' => $total_qty,
                'total_comm' => number_format($total_commission, 2),
            ],
        ]);
    }
}

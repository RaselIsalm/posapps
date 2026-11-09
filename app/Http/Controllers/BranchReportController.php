<?php

namespace App\Http\Controllers;

use App\DataTables\Reports\BranchExpenseDataTable;
use App\DataTables\Reports\BranchPurchaseDataTable;
use App\DataTables\Reports\BranchReturnDataTable;
use App\DataTables\Reports\BranchSalesDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Expenses;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class BranchReportController extends Controller
{
    public function index(BranchSalesDataTable $salesDT, BranchPurchaseDataTable $purchaseDT, BranchExpenseDataTable $expenseDT, BranchReturnDataTable $returnDT)
    {
        $branches = Branch::select('id', 'name')->get();
        $employees = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::where('is_active', 1)->orderBy('name')->get(['id', 'name']);
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();
        $salesTable = $salesDT->html();
        $purchaseTable = $purchaseDT->html();
        $expenseTable = $expenseDT->html();
        $returnTable = $returnDT->html();

        return view('backend.reports.branch.index', compact('branches', 'employees', 'accounts', 'employeeRoles', 'salesTable', 'purchaseTable', 'expenseTable', 'returnTable'));
    }

    public function getBranchSummary(Request $request)
    {
        $branch_id = $request->branch_id;
        $start_date = $request->start_date;
        $end_date = $request->end_date;

        $applyFilter = function ($query) use ($branch_id, $start_date, $end_date) {
            return $query->when($branch_id, function ($q) use ($branch_id) {
                return $q->where('branch_id', $branch_id);
            })
                ->when($start_date, function ($q) {
                    return $q->whereDate('created_at', '>=', request('start_date'));
                })
                ->when($end_date, function ($q) {
                    return $q->whereDate('created_at', '<=', request('end_date'));
                });
        };

        $salesQuery = Sale::query()->whereIn('delivery_status', ['delivered', 'completed', 'pending']);
        if ($branch_id) {
            $salesQuery->where('branch_id', $branch_id);
        }
        if ($start_date) {
            $salesQuery->whereDate('sale_date', '>=', $start_date);
        }
        if ($end_date) {
            $salesQuery->whereDate('sale_date', '<=', $end_date);
        }
        $total_sales = $salesQuery->sum('grand_total');

        $purchaseQuery = Purchase::query();
        if ($branch_id) {
            $purchaseQuery->where('branch_id', $branch_id);
        }
        if ($start_date) {
            $purchaseQuery->whereDate('date', '>=', $start_date);
        }
        if ($end_date) {
            $purchaseQuery->whereDate('date', '<=', $end_date);
        }
        $total_purchase = $purchaseQuery->sum('grand_total');

        $expenseQuery = Expenses::query()->where('status', 1);
        if ($branch_id) {
            $expenseQuery->where('branch_id', $branch_id);
        }
        if ($start_date) {
            $expenseQuery->whereDate('date', '>=', $start_date);
        }
        if ($end_date) {
            $expenseQuery->whereDate('date', '<=', $end_date);
        }
        $total_expense = $expenseQuery->sum('amount');

        $returnQuery = SaleReturn::query()->where('status', 'approved');
        if ($branch_id) {
            $returnQuery->where('branch_id', $branch_id);
        }
        if ($start_date) {
            $returnQuery->whereDate('date', '>=', $start_date);
        }
        if ($end_date) {
            $returnQuery->whereDate('date', '<=', $end_date);
        }
        $total_return = $returnQuery->sum('grand_total');

        $net_profit = ($total_sales - $total_return) - $total_expense;

        return response()->json([
            'total_sales' => number_format($total_sales, 2),
            'total_purchase' => number_format($total_purchase, 2),
            'total_expense' => number_format($total_expense, 2),
            'total_return' => number_format($total_return, 2),
            'net_profit' => number_format($net_profit, 2),
        ]);
    }

    public function getBranchSales(BranchSalesDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function getBranchPurchases(BranchPurchaseDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function getBranchExpenses(BranchExpenseDataTable $dataTable)
    {
        return $dataTable->ajax();
    }

    public function getBranchReturns(BranchReturnDataTable $dataTable)
    {
        return $dataTable->ajax();
    }
}

<?php

namespace App\Http\Controllers;

use App\DataTables\Reports\DailyPurchaseReportDataTable;
use App\DataTables\Reports\ProductPurchaseReportDataTable;
use App\DataTables\Reports\SupplierPurchaseReportDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class PurchaseReportController extends Controller
{
    public function index(ProductPurchaseReportDataTable $productDT, SupplierPurchaseReportDataTable $supplierDT, DailyPurchaseReportDataTable $dailyDT)
    {
        $suppliers = Supplier::select('id', 'name', 'company_name')->get();
        $branches = Branch::select('id', 'name')->get();
        $employees = User::orderBy('name')->get(['id', 'name']);
        $accounts = Account::where('is_active', 1)->orderBy('name')->get(['id', 'name']);
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();
        $productTable = $productDT->html();
        $supplierTable = $supplierDT->html();
        $dailyTable = $dailyDT->html();

        return view('backend.reports.purchase.index', compact('suppliers', 'branches', 'employees', 'accounts', 'employeeRoles', 'productTable', 'supplierTable', 'dailyTable'));
    }

    public function productWise(ProductPurchaseReportDataTable $dataTable)
    {
        return $dataTable->render(null);
    }

    public function supplierWise(SupplierPurchaseReportDataTable $dataTable)
    {
        return $dataTable->render(null);
    }

    public function dailyData(DailyPurchaseReportDataTable $dataTable)
    {
        return $dataTable->render(null);
    }

    public function getSummary(Request $request)
    {
        $purchaseQuery = Purchase::query();

        if ($request->branch_id) {
            $purchaseQuery->where('branch_id', $request->branch_id);
        }
        if ($request->supplier_id) {
            $purchaseQuery->where('supplier_id', $request->supplier_id);
        }
        if ($request->start_date && $request->end_date) {
            $purchaseQuery->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $purchaseData = $purchaseQuery->select(
            DB::raw('COALESCE(SUM(grand_total), 0) as total_purchase'),
            DB::raw('COALESCE(SUM(paid_amount), 0) as total_paid'),
            DB::raw('COALESCE(SUM(due_amount), 0) as total_due')
        )->first();

        $returnQuery = DB::table('purchase_returns');

        if ($request->branch_id) {
            $returnQuery->where('branch_id', $request->branch_id);
        }
        if ($request->supplier_id) {
            $returnQuery->where('supplier_id', $request->supplier_id);
        }
        if ($request->start_date && $request->end_date) {
            $returnQuery->whereBetween('return_date', [$request->start_date, $request->end_date]);
        }

        $totalReturn = $returnQuery->sum('grand_total');

        return response()->json([
            'total_purchase' => $purchaseData->total_purchase,
            'total_paid' => $purchaseData->total_paid,
            'total_due' => $purchaseData->total_due,
            'total_return' => $totalReturn,
        ]);
    }
}

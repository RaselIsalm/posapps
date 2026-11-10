<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\Expenses;
use App\Models\Payroll;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        return redirect('dashboard');
    }

    public function dashboard(Request $request)
    {
        $filter = $request->get('filter', 'this_month');
        [$startDate, $endDate, $filterLabel] = $this->resolveDateFilter($filter);
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $metrics = $this->getDashboardMetrics($startDate, $endDate);
        $totalCustomers = $metrics['totalCustomers'];
        $totalSuppliers = $metrics['totalSuppliers'];
        $totalSale = $metrics['totalSale'];
        $totalSaleReturn = $metrics['totalSaleReturn'];
        $totalPurchase = $metrics['totalPurchase'];
        $totalPurchaseReturn = $metrics['totalPurchaseReturn'];
        $totalPaid = $metrics['totalPaid'];
        $totalDue = $metrics['totalDue'];
        $totalDueReceive = $metrics['totalDueReceive'];
        $totalPurchasePaid = $metrics['totalPurchasePaid'];
        $totalPurchaseDue = $metrics['totalPurchaseDue'];
        $totalExpense = $metrics['totalExpense'];
        $profit = $metrics['profit'];
        $totalSalary = $metrics['totalSalary'];
        $receivableCustomerDue = $metrics['receivableCustomerDue'];
        $totalAccountBalance = $metrics['totalAccountBalance'];
        $supplierDue = $metrics['supplierDue'];
        $customerAdvance = $metrics['customerAdvance'];
        $totalCashIn = $metrics['totalCashIn'];
        $totalCashOut = $metrics['totalCashOut'];

        $recentSales = Sale::with('customer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentPurchases = Purchase::with('supplier')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentQuotations = Quotation::with('customer')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
        $recentTransactions = Transaction::with('account')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $bestSellerMonth = ProductSale::with('product')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereMonth('sales.created_at', $thisMonth)
            ->whereYear('sales.created_at', $thisYear)
            ->selectRaw('product_sales.product_id, SUM(product_sales.qty) as total_qty')
            ->groupBy('product_sales.product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $bestSellerYearQty = ProductSale::with('product')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereYear('sales.created_at', $thisYear)
            ->selectRaw('product_sales.product_id, SUM(product_sales.qty) as total_qty')
            ->groupBy('product_sales.product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $bestSellerYearPrice = ProductSale::with('product')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereYear('sales.created_at', $thisYear)
            ->selectRaw('product_sales.product_id, SUM(product_sales.qty * product_sales.unit_price) as total_revenue')
            ->groupBy('product_sales.product_id')
            ->orderByDesc('total_revenue')
            ->limit(5)
            ->get();

        return view('backend.index', compact(
            'filter',
            'filterLabel',
            'totalCustomers',
            'totalSuppliers',
            'totalSale',
            'totalSaleReturn',
            'totalPurchase',
            'totalPurchaseReturn',
            'totalPaid',
            'totalDue',
            'totalDueReceive',
            'totalPurchasePaid',
            'totalPurchaseDue',
            'totalExpense',
            'profit',
            'totalSalary',
            'receivableCustomerDue',
            'totalAccountBalance',
            'supplierDue',
            'customerAdvance',
            'totalCashIn',
            'totalCashOut',
            'recentSales',
            'recentPurchases',
            'recentQuotations',
            'recentTransactions',
            'bestSellerMonth',
            'bestSellerYearQty',
            'bestSellerYearPrice'
        ));
    }

    public function dashboardData(Request $request)
    {
        $filter = $request->get('filter', 'this_month');
        [$startDate, $endDate, $filterLabel] = $this->resolveDateFilter($filter);

        $metrics = $this->getDashboardMetrics($startDate, $endDate);

        return response()->json([
            'filterLabel' => __($filterLabel),
            'totalCustomers' => number_format($metrics['totalCustomers']),
            'totalSuppliers' => number_format($metrics['totalSuppliers']),
            'totalSale' => number_format($metrics['totalSale'], 2),
            'totalSaleReturn' => number_format($metrics['totalSaleReturn'], 2),
            'totalPurchase' => number_format($metrics['totalPurchase'], 2),
            'totalPurchaseReturn' => number_format($metrics['totalPurchaseReturn'], 2),
            'totalPaid' => number_format($metrics['totalPaid'], 2),
            'totalDue' => number_format($metrics['totalDue'], 2),
            'totalDueReceive' => number_format($metrics['totalDueReceive'], 2),
            'totalPurchasePaid' => number_format($metrics['totalPurchasePaid'], 2),
            'totalPurchaseDue' => number_format($metrics['totalPurchaseDue'], 2),
            'totalExpense' => number_format($metrics['totalExpense'], 2),
            'profit' => number_format($metrics['profit'], 2),
            'totalSalary' => number_format($metrics['totalSalary'], 2),
            'receivableCustomerDue' => number_format($metrics['receivableCustomerDue'], 2),
            'totalAccountBalance' => number_format($metrics['totalAccountBalance'], 2),
            'supplierDue' => number_format($metrics['supplierDue'], 2),
            'customerAdvance' => number_format($metrics['customerAdvance'], 2),
            'totalCashIn' => number_format($metrics['totalCashIn'], 2),
            'totalCashOut' => number_format($metrics['totalCashOut'], 2),
        ]);
    }

    public function onboarding()
    {
        $announcement = null;
        $videoLink = '#';
        $docsLink = '#';

        return view('backend.onboarding.index', compact('announcement', 'videoLink', 'docsLink'));
    }

    /**
     * Resolve the start date, end date and label for a dashboard date filter.
     */
    private function resolveDateFilter(string $filter): array
    {
        switch ($filter) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                $filterLabel = 'Today';
                break;
            case 'last_7_days':
                $startDate = Carbon::now()->subDays(7)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                $filterLabel = 'Last 7 Days';
                break;
            case 'this_month':
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $filterLabel = 'This Month';
                break;
            case 'this_year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                $filterLabel = 'This Year';
                break;
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                $filterLabel = 'This Month';
        }

        return [$startDate, $endDate, $filterLabel];
    }

    /**
     * Compute all dashboard summary metrics for the given date range in one pass,
     * so callers never re-run the same aggregate query twice.
     */
    private function getDashboardMetrics($startDate, $endDate): array
    {
        $totalSale = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('grand_total');
        $totalSaleReturn = SaleReturn::whereBetween('created_at', [$startDate, $endDate])->sum('grand_total');
        $totalPurchase = Purchase::whereBetween('created_at', [$startDate, $endDate])->sum('grand_total');
        $totalPurchaseReturn = PurchaseReturn::whereBetween('created_at', [$startDate, $endDate])->sum('grand_total');
        $totalExpense = Expenses::whereBetween('created_at', [$startDate, $endDate])->sum('amount');

        return [
            'totalCustomers' => Customer::count(),
            'totalSuppliers' => Supplier::count(),
            'totalSale' => $totalSale,
            'totalSaleReturn' => $totalSaleReturn,
            'totalPurchase' => $totalPurchase,
            'totalPurchaseReturn' => $totalPurchaseReturn,
            'totalPaid' => Sale::whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount'),
            'totalDue' => Sale::whereBetween('created_at', [$startDate, $endDate])->sum('due_amount'),
            'totalDueReceive' => 0,
            'totalPurchasePaid' => Purchase::whereBetween('created_at', [$startDate, $endDate])->sum('paid_amount'),
            'totalPurchaseDue' => Purchase::whereBetween('created_at', [$startDate, $endDate])->sum('due_amount'),
            'totalExpense' => $totalExpense,
            'profit' => ($totalSale - $totalSaleReturn) - ($totalPurchase - $totalPurchaseReturn) - $totalExpense,
            'totalSalary' => Payroll::whereBetween('created_at', [$startDate, $endDate])->sum('net_salary'),
            'receivableCustomerDue' => Sale::where('due_amount', '>', 0)->sum('due_amount'),
            'totalAccountBalance' => Account::active()->sum('current_balance'),
            'supplierDue' => Purchase::where('due_amount', '>', 0)->sum('due_amount'),
            'customerAdvance' => 0,
            'totalCashIn' => Transaction::where('type', 'credit')->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
            'totalCashOut' => Transaction::where('type', 'debit')->whereBetween('created_at', [$startDate, $endDate])->sum('amount'),
        ];
    }
}

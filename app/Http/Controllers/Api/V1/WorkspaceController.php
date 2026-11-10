<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Account;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Expenses;
use App\Models\GeneralSettings;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\ProductBranch;
use App\Models\ProductSale;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Quotation;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\SimplePdf;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkspaceController extends ApiController
{
    public function generalSettings(): JsonResponse
    {
        $settings = GeneralSettings::where('category', 'general')->pluck('value', 'key_name');
        $symbol = $settings->get('currency_symbol', $settings->get('currency', '$'));

        return $this->success([
            'symbol' => is_string($symbol) ? $symbol : '$',
            'currency_position' => $settings->get('currency_position', 'Prefix'),
            'currency_code' => $settings->get('currency_code', 'USD'),
            'default_tax' => (float) ($settings->get('default_tax') ?? 5),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $this->requirePermission('view_dashboard');
        [$start, $end] = $this->period((string) $request->query('type', 'this_month'));

        // Dashboard KPIs intentionally follow the website's created_at date
        // semantics. Operational report endpoints continue to use sale_date/date.
        $sales = $this->dashboardPeriodQuery(Sale::query(), $request, $start, $end);
        $purchases = $this->dashboardPeriodQuery(Purchase::query(), $request, $start, $end);
        $saleTotal = (float) (clone $sales)->sum('grand_total');
        $purchaseTotal = (float) (clone $purchases)->sum('grand_total');
        $saleReturnTotal = (float) $this->dashboardPeriodQuery(SaleReturn::query(), $request, $start, $end)->sum('grand_total');
        $purchaseReturnTotal = (float) $this->dashboardPeriodQuery(PurchaseReturn::query(), $request, $start, $end)->sum('grand_total');
        $expenseTotal = (float) $this->dashboardPeriodQuery(Expenses::query(), $request, $start, $end)->sum('amount');
        $accountIds = $this->accessibleAccountQuery($request)->select('accounts.id');
        $customerDue = (float) $this->applyBranchScope(Sale::query(), $request)->where('due_amount', '>', 0)->sum('due_amount');
        $supplierDue = (float) $this->applyBranchScope(Purchase::query(), $request)->where('due_amount', '>', 0)->sum('due_amount');
        $totalAccountBalance = (float) $this->accessibleAccountQuery($request)->sum('current_balance');

        $restrictedBranches = $request->filled('branch_id')
            || Employee::query()->where('user_id', $request->user()->id)->where('status', 1)->exists()
            || (! $request->user()->hasAnyRole(['Super-Admin', 'Admin']) && ! $request->user()->can('view_branches'));
        $customerCount = $restrictedBranches
            ? Customer::query()->whereHas('sales', fn ($query) => $this->applyBranchScope($query, $request))->count()
            : Customer::query()->count();
        $supplierCount = $restrictedBranches
            ? Supplier::query()->whereHas('purchases', fn ($query) => $this->applyBranchScope($query, $request))->count()
            : Supplier::query()->count();

        $payrolls = Payroll::query()->whereBetween('created_at', [$start, $end]);
        $transactions = Transaction::query();
        if ($restrictedBranches) {
            $payrolls->whereHas('employee', fn ($query) => $this->applyBranchScope($query, $request, 'branch_id'));
            $transactions->whereIn('account_id', $accountIds);
        }
        $totalCashIn = (float) (clone $transactions)->where('type', 'credit')->whereBetween('created_at', [$start, $end])->sum('amount');
        $totalCashOut = (float) (clone $transactions)->where('type', 'debit')->whereBetween('created_at', [$start, $end])->sum('amount');

        $chart = [];
        for ($offset = 5; $offset >= 0; $offset--) {
            $month = now()->startOfMonth()->subMonths($offset);
            $monthEnd = $month->copy()->endOfMonth();
            $chart[] = [
                'month' => (int) $month->month,
                'year' => (int) $month->year,
                'purchase' => (float) $this->applyBranchScope(Purchase::query(), $request)->whereBetween('date', [$month->toDateString(), $monthEnd->toDateString()])->sum('grand_total'),
                'sale' => (float) $this->applyBranchScope(Sale::query(), $request)->whereBetween('sale_date', [$month->toDateString(), $monthEnd->toDateString()])->sum('grand_total'),
            ];
        }

        $recentSales = $this->recentDashboardRecords(
            $this->applyBranchScope(Sale::query()->with('customer'), $request)->orderByDesc('created_at')->limit(5)->get(),
            fn (Sale $sale) => [
                'id' => (int) $sale->id,
                'date' => $this->formatDate($sale->created_at),
                'reference' => (string) ($sale->invoice_no ?? ''),
                'name' => (string) ($sale->customer?->name ?? 'Walk-in customer'),
                'status' => (string) ($sale->payment_status ?? ''),
                'amount' => (float) $sale->grand_total,
            ],
        );
        $recentPurchases = $this->recentDashboardRecords(
            $this->applyBranchScope(Purchase::query()->with('supplier'), $request)->orderByDesc('created_at')->limit(5)->get(),
            fn (Purchase $purchase) => [
                'id' => (int) $purchase->id,
                'date' => $this->formatDate($purchase->created_at),
                'reference' => (string) ($purchase->invoice_no ?: $purchase->memo_no ?: ''),
                'name' => (string) ($purchase->supplier?->name ?? ''),
                'status' => (string) ($purchase->status ?? ''),
                'amount' => (float) $purchase->grand_total,
            ],
        );
        $recentQuotations = $this->recentDashboardRecords(
            $this->applyBranchScope(Quotation::query()->with('customer'), $request)->orderByDesc('created_at')->limit(5)->get(),
            fn (Quotation $quotation) => [
                'id' => (int) $quotation->id,
                'date' => $this->formatDate($quotation->created_at),
                'reference' => (string) ($quotation->reference_no ?? ''),
                'name' => (string) ($quotation->customer?->name ?? ''),
                'status' => (string) ($quotation->status ?? ''),
                'amount' => (float) $quotation->grand_total,
            ],
        );
        $recentTransactions = $this->recentDashboardRecords(
            (clone $transactions)->with('account')->orderByDesc('created_at')->limit(5)->get(),
            fn (Transaction $transaction) => [
                'id' => (int) $transaction->id,
                'date' => $this->formatDate($transaction->created_at),
                'reference' => (string) ($transaction->reason ?? ''),
                'name' => (string) ($transaction->account?->name ?? ''),
                'status' => (string) $transaction->type,
                'note' => (string) ($transaction->note ?? ''),
                'amount' => (float) $transaction->amount,
            ],
        );

        $monthStart = now()->startOfMonth();
        $yearStart = now()->startOfYear();
        $bestSellerMonth = $this->dashboardBestSellers($request, $monthStart, now()->endOfMonth(), 'qty');
        $bestSellerYearQty = $this->dashboardBestSellers($request, $yearStart, now()->endOfYear(), 'qty');
        $bestSellerYearRevenue = $this->dashboardBestSellers($request, $yearStart, now()->endOfYear(), 'revenue');

        $metrics = [
            'total_customers' => $customerCount,
            'total_suppliers' => $supplierCount,
            'total_sale' => $saleTotal,
            'sale_return' => $saleReturnTotal,
            'total_purchase' => $purchaseTotal,
            'purchase_return' => $purchaseReturnTotal,
            'total_paid' => (float) (clone $sales)->sum('paid_amount'),
            'total_due' => (float) (clone $sales)->sum('due_amount'),
            'total_due_receive' => 0.0,
            'total_purchase_paid' => (float) (clone $purchases)->sum('paid_amount'),
            'total_purchase_due' => (float) (clone $purchases)->sum('due_amount'),
            'total_expense' => $expenseTotal,
            'profit' => ($saleTotal - $saleReturnTotal) - ($purchaseTotal - $purchaseReturnTotal) - $expenseTotal,
            'total_salary' => (float) $payrolls->sum('net_salary'),
            'receivable_customer_due' => $customerDue,
            'total_account_balance' => $totalAccountBalance,
            'supplier_due' => $supplierDue,
            'customer_advance' => 0.0,
            'total_cash_in' => $totalCashIn,
            'total_cash_out' => $totalCashOut,
        ];

        return $this->success([
            // Legacy keys stay available to older app builds.
            'sale' => $saleTotal,
            'purchase' => $purchaseTotal,
            'profit' => $metrics['profit'],
            'purchase_due' => $metrics['total_purchase_due'],
            'max_chart_amount' => (int) ceil(max(1, collect($chart)->flatMap(fn ($row) => [$row['purchase'], $row['sale']])->max() ?? 1)),
            'purchase_and_sale_chart' => $chart,
            ...$metrics,
            'assets' => [
                'receivable_customer_due' => $customerDue,
                'total_account_balance' => $totalAccountBalance,
            ],
            'liabilities' => [
                'supplier_due' => $supplierDue,
                'customer_advance' => 0.0,
            ],
            'cash' => [
                'total_cash_in' => $totalCashIn,
                'total_cash_out' => $totalCashOut,
            ],
            'recent_sales' => $recentSales,
            'recent_purchases' => $recentPurchases,
            'recent_quotations' => $recentQuotations,
            'recent_transactions' => $recentTransactions,
            'best_seller_month' => $bestSellerMonth,
            'best_seller_year_qty' => $bestSellerYearQty,
            'best_seller_year_revenue' => $bestSellerYearRevenue,
        ]);
    }

    private function dashboardPeriodQuery($query, Request $request, Carbon $start, Carbon $end)
    {
        return $this->applyBranchScope($query, $request)->whereBetween('created_at', [$start, $end]);
    }

    private function recentDashboardRecords($records, callable $map): array
    {
        return $records->map($map)->values()->all();
    }

    private function dashboardBestSellers(Request $request, Carbon $start, Carbon $end, string $metric): array
    {
        $query = ProductSale::query()
            ->with('product')
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereBetween('sales.created_at', [$start, $end]);
        $this->applyBranchScope($query, $request, 'sales.branch_id');

        if ($metric === 'revenue') {
            $query->select('product_sales.product_id')
                ->selectRaw('SUM(product_sales.qty * product_sales.unit_price) as total_revenue')
                ->groupBy('product_sales.product_id')
                ->orderByDesc('total_revenue');
        } else {
            $query->select('product_sales.product_id')
                ->selectRaw('SUM(product_sales.qty) as total_qty')
                ->groupBy('product_sales.product_id')
                ->orderByDesc('total_qty');
        }

        return $query->limit(5)->get()->map(fn (ProductSale $item) => [
            'product_id' => (int) $item->product_id,
            'product_name' => (string) ($item->product?->product_title ?? ''),
            'quantity' => (float) ($item->total_qty ?? 0),
            'revenue' => (float) ($item->total_revenue ?? 0),
        ])->values()->all();
    }

    public function reports(Request $request): JsonResponse
    {
        $this->requirePermission('view_summary_report');
        [$start, $end] = $this->period((string) $request->query('type', 'this_month'));
        $purchases = $this->applyBranchScope(Purchase::query(), $request)->whereBetween('date', [$start->toDateString(), $end->toDateString()]);
        $sales = $this->applyBranchScope(Sale::query(), $request)->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()]);
        $payments = Payment::query()->whereBetween('payment_date', [$start->toDateString(), $end->toDateString()])
            ->whereHas('sale', fn ($sale) => $this->applyBranchScope($sale, $request));

        $purchaseTotal = (float) (clone $purchases)->sum('grand_total');
        $saleTotal = (float) (clone $sales)->sum('grand_total');
        $purchaseTax = (float) (clone $purchases)->sum('order_tax');
        $saleTax = (float) (clone $sales)->sum('tax_amount');
        $purchaseDiscount = (float) (clone $purchases)->sum('order_discount');
        $saleDiscount = (float) (clone $sales)->sum(DB::raw('discount_amount + coupon_discount_amount + group_discount_amount + point_discount_amount'));
        $costQuery = ProductSale::query()
            ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$start->toDateString(), $end->toDateString()]);
        $saleCosts = (float) $this->applyBranchScope($costQuery, $request, 'sales.branch_id')
            ->sum(DB::raw('product_sales.qty * product_sales.purchase_unit_cost'));
        $expenses = (float) $this->applyBranchScope(Expenses::query(), $request)->whereBetween('date', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $availableProduct = (int) floor((float) $this->applyBranchScope(ProductBranch::query(), $request)->sum('qty'));

        return $this->success([
            'purchases' => [
                'total' => $purchaseTotal,
                'paid' => (float) (clone $purchases)->sum('paid_amount'),
                'tax' => $purchaseTax,
                'discount' => $purchaseDiscount,
                'due' => (float) (clone $purchases)->sum('due_amount'),
                'products' => (int) (clone $purchases)->sum('total_qty'),
            ],
            'sales' => [
                'total' => $saleTotal,
                'tax' => $saleTax,
                'discount' => $saleDiscount,
                'selling_product' => (int) ProductSale::query()->whereIn('sale_id', (clone $sales)->select('id'))->sum('qty'),
                'available_product' => $availableProduct,
            ],
            'payment_received' => [
                'total' => (float) (clone $payments)->sum('amount'),
                'cash' => (float) (clone $payments)->where('payment_method', 'like', '%cash%')->sum('amount'),
                'bank' => (float) (clone $payments)->where('payment_method', 'not like', '%cash%')->sum('amount'),
                'cash_count' => (int) (clone $payments)->where('payment_method', 'like', '%cash%')->count(),
                'bank_count' => (int) (clone $payments)->where('payment_method', 'not like', '%cash%')->count(),
            ],
            'profit' => $saleTotal - $saleCosts - $expenses,
        ]);
    }

    public function purchases(Request $request): JsonResponse
    {
        $this->requirePermission('view_purchases');
        $query = $this->applyBranchScope(Purchase::query(), $request)->with('supplier')->when($request->filled('search'), function ($q) use ($request) {
            $search = trim((string) $request->query('search'));
            $q->where(function ($nested) use ($search) {
                $nested->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"));
            });
        });
        $query = $this->dateFilters($query, $request, 'date');

        // Add a unique tie-breaker so a row cannot move between pages when
        // multiple purchases share the same date.
        $data = $this->page($request, $query->orderByDesc('date')->orderByDesc('id'), fn (Purchase $purchase) => [
            'id' => (int) $purchase->id,
            'date' => $this->formatDate($purchase->date),
            'reference_no' => $purchase->invoice_no ?: $purchase->memo_no,
            'supplier' => $purchase->supplier?->name ?? '',
            'grand_total' => (float) $purchase->grand_total,
            'paid_amount' => (float) $purchase->paid_amount,
            'due_amount' => (float) $purchase->due_amount,
            'status' => ((float) $purchase->due_amount <= 0 ? 'Paid' : ((float) $purchase->paid_amount > 0 ? 'Partial' : 'Due')),
        ], 'purchases');

        return $this->success($data);
    }

    public function sales(Request $request): JsonResponse
    {
        $this->requirePermission('view_sale_list');
        $query = $this->applyBranchScope(Sale::query(), $request)->with(['customer', 'branch', 'seller.user', 'productSales', 'payments'])->when($request->filled('search'), function ($q) use ($request) {
            $search = trim((string) $request->query('search'));
            $q->where(function ($nested) use ($search) {
                $nested->where('invoice_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
            });
        })
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->query('payment_status')))
            ->when($request->filled('delivery_status'), fn ($q) => $q->where('delivery_status', $request->query('delivery_status')));
        $query = $this->dateFilters($query, $request, 'sale_date');

        $data = $this->page($request, $query->orderByDesc('sale_date')->orderByDesc('id'), fn (Sale $sale) => [
            'id' => (int) $sale->id,
            'date' => $this->formatDate($sale->sale_date),
            'reference_no' => $sale->invoice_no,
            'biller' => $sale->seller?->user?->name ?? $sale->customer?->name ?? '',
            'customer_id' => $sale->customer_id ? (int) $sale->customer_id : null,
            'customer' => $sale->customer?->name ?? 'Walk-in customer',
            'branch_id' => $sale->branch_id ? (int) $sale->branch_id : null,
            'branch' => $sale->branch?->name ?? '',
            'qty' => (int) $sale->productSales->sum('qty'),
            'discount' => (float) ($sale->discount_amount + $sale->coupon_discount_amount + $sale->group_discount_amount + $sale->point_discount_amount),
            'tax' => (float) $sale->tax_amount,
            'total_price' => (float) $sale->sub_total,
            'grand_total' => (float) $sale->grand_total,
            'paid_amount' => (float) $sale->paid_amount,
            'due_amount' => (float) $sale->due_amount,
            'payment_status' => $sale->payment_status,
            'delivery_status' => $sale->delivery_status,
            'reward_points_earned' => (int) $sale->reward_points_earned,
            'reward_points_used' => (int) $sale->reward_points_used,
            'invoice_pdf_url' => url('/api/v1/invoice/'.$sale->id),
        ], 'sales');
        $data['branches'] = app(CatalogController::class)->accessibleBranchesForUser($request->user())->orderBy('name')->get(['id', 'name']);

        return $this->success($data);
    }

    public function expenses(Request $request): JsonResponse
    {
        $this->requirePermission('view_expenses');
        $query = $this->applyBranchScope(Expenses::query(), $request)->with(['category', 'branch'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim((string) $request->query('search'));
                $q->where(function ($nested) use ($search) {
                    $nested->where('note', 'like', "%{$search}%")
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', "%{$search}%"));
                });
            });
        $query = $this->dateFilters($query, $request, 'date');
        $amount = (float) (clone $query)->sum('amount');
        $data = $this->page($request, $query->orderByDesc('date')->orderByDesc('id'), fn (Expenses $expense) => [
            'id' => (int) $expense->id,
            'date' => $this->formatDate($expense->date),
            'reference_no' => 'EXP-'.str_pad((string) $expense->id, 4, '0', STR_PAD_LEFT),
            'wearehouse' => $expense->branch?->name ?? '',
            'category' => $expense->category?->name ?? '',
            'amount' => (float) $expense->amount,
            'note' => $expense->note,
        ], 'expenses');
        $data['total_expense_amount'] = $amount;

        return $this->success($data);
    }

    public function salesPdf(Request $request): JsonResponse
    {
        $this->requirePermission('view_sale_list');

        return $this->success(['invoice_pdf_url' => url('/api/v1/sale/export').'?'.http_build_query($request->query())]);
    }

    public function purchasesPdf(Request $request): JsonResponse
    {
        $this->requirePermission('view_purchases');

        return $this->success(['invoice_pdf_url' => url('/api/v1/purchase/export').'?'.http_build_query($request->query())]);
    }

    public function salesExport(Request $request)
    {
        $this->requirePermission('view_sale_list');
        $query = $this->dateFilters($this->applyBranchScope(Sale::query(), $request), $request, 'sale_date');
        $sales = $query->with(['customer', 'branch'])->latest('sale_date')->limit(5000)->get();
        $lines = ['ONE POS - SALES REPORT', 'Generated: '.now()->format('Y-m-d H:i'), '', 'DATE | INVOICE | CUSTOMER | TOTAL | PAID | DUE'];
        foreach ($sales as $sale) {
            $lines[] = implode(' | ', [
                (string) $sale->sale_date,
                (string) ($sale->invoice_no ?: $sale->id),
                (string) ($sale->customer?->name ?? 'Walk-in'),
                number_format((float) $sale->grand_total, 2),
                number_format((float) $sale->paid_amount, 2),
                number_format((float) $sale->due_amount, 2),
            ]);
        }
        $lines[] = '';
        $lines[] = 'Total sales: '.number_format((float) $sales->sum('grand_total'), 2);

        return SimplePdf::response($lines, 'sales-report.pdf');
    }

    public function purchasesExport(Request $request)
    {
        $this->requirePermission('view_purchases');
        $query = $this->dateFilters($this->applyBranchScope(Purchase::query(), $request), $request, 'date');
        $purchases = $query->with('supplier')->latest('date')->limit(5000)->get();
        $lines = ['ONE POS - PURCHASE REPORT', 'Generated: '.now()->format('Y-m-d H:i'), '', 'DATE | INVOICE | SUPPLIER | TOTAL | PAID | DUE'];
        foreach ($purchases as $purchase) {
            $lines[] = implode(' | ', [
                (string) $purchase->date,
                (string) ($purchase->invoice_no ?: $purchase->memo_no),
                (string) ($purchase->supplier?->name ?? ''),
                number_format((float) $purchase->grand_total, 2),
                number_format((float) $purchase->paid_amount, 2),
                number_format((float) $purchase->due_amount, 2),
            ]);
        }
        $lines[] = '';
        $lines[] = 'Total purchases: '.number_format((float) $purchases->sum('grand_total'), 2);

        return SimplePdf::response($lines, 'purchase-report.pdf');
    }

    public function accounts(Request $request): JsonResponse
    {
        $this->requirePermission('view_accounts');
        $type = (string) $request->query('type', 'account');
        if ($type === 'money_transfer') {
            $query = Transaction::query()->with('account')
                ->whereIn('account_id', $this->accessibleAccounts($request)->select('id'))
                ->where('reason', 'like', '%transfer%')
                ->when($request->filled('search'), fn ($q) => $q->where('reason', 'like', '%'.trim((string) $request->query('search')).'%'));
            $data = $this->page($request, $query->orderByDesc('date')->orderByDesc('id'), fn (Transaction $transaction) => [
                'id' => (int) $transaction->id,
                'date' => $this->formatDate($transaction->date),
                'reference_no' => 'TR-'.str_pad((string) $transaction->id, 4, '0', STR_PAD_LEFT),
                'from' => $transaction->type === 'debit' ? ($transaction->account?->name ?? '') : '',
                'to' => $transaction->type === 'credit' ? ($transaction->account?->name ?? '') : '',
                'amount' => (float) $transaction->amount,
            ], 'money_transfers');

            return $this->success($data);
        }
        if ($type === 'balance_sheet') {
            $accounts = $this->accessibleAccounts($request)->orderBy('name')->get();
            $rows = $accounts->map(function (Account $account) {
                $credits = (float) $account->transactions()->where('type', 'credit')->sum('amount');
                $debits = (float) $account->transactions()->where('type', 'debit')->sum('amount');

                return [
                    'id' => (int) $account->id,
                    'name' => $account->name,
                    'account_no' => (string) $account->account_number,
                    'credit' => $credits,
                    'debit' => $debits,
                    'balance' => (float) $account->current_balance,
                ];
            });

            return $this->success(['balance_sheet' => $rows]);
        }

        $query = $this->accessibleAccounts($request)->when($request->filled('search'), function ($q) use ($request) {
            $search = trim((string) $request->query('search'));
            $q->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$search}%")
                ->orWhere('account_number', 'like', "%{$search}%"));
        });

        return $this->success(['accounts' => $query->orderBy('name')->get()->map(fn (Account $account) => [
            'id' => (int) $account->id,
            'account_no' => (string) $account->account_number,
            'name' => $account->name,
            'initial_balance' => (float) $account->initial_balance,
            'total_balance' => (float) $account->current_balance,
            'note' => $account->note,
        ])]);
    }

    public function balance(Request $request): JsonResponse
    {
        $this->requirePermission('view_accounts');
        $today = $this->applyBranchScope(Sale::query(), $request)->whereDate('sale_date', today())->sum('grand_total');

        return $this->success([
            'balance' => (float) $this->accessibleAccounts($request)->sum('current_balance'),
            'todaySale' => (float) $today,
        ]);
    }

    public function addBalance(Request $request): JsonResponse
    {
        $this->requirePermission('create_accounts');
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0'],
            'account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'date' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);
        $account = isset($validated['account_id'])
            ? $this->accessibleAccounts($request)->find($validated['account_id'])
            : $this->accessibleAccounts($request)->where('type', 'cash')->orderByDesc('is_default')->first();
        if (! $account) {
            return response()->json(['message' => 'Choose an active account before recording a deposit.'], 422);
        }

        DB::transaction(function () use ($validated, $account) {
            TransactionService::createTransaction(
                $account->id,
                'credit',
                (float) $validated['amount'],
                $validated['note'] ?: 'POS cash deposit',
                $validated['date'] ?? today()->toDateString()
            );
        });

        return $this->success([], 'Balance deposited successfully.', 201);
    }

    private function dateFilters($query, Request $request, string $column)
    {
        $fromDate = $request->query('from_date', $request->query('start_date'));
        $toDate = $request->query('to_date', $request->query('end_date'));
        if (filled($fromDate)) {
            $query->whereDate($column, '>=', $this->normalizeFilterDate($fromDate));
        }
        if (filled($toDate)) {
            $query->whereDate($column, '<=', $this->normalizeFilterDate($toDate));
        }

        return $this->applyBranchScope($query, $request);
    }

    private function normalizeFilterDate(mixed $value): string
    {
        $value = trim((string) $value);
        try {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
                $date = Carbon::createFromFormat('!d-m-Y', $value);
                if ($date->format('d-m-Y') !== $value) {
                    abort(422, 'A filter date is invalid.');
                }

                return $date->toDateString();
            }

            return Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            abort(422, 'A filter date is invalid.');
        }
    }

    private function accessibleAccounts(Request $request)
    {
        $user = $request->user();
        $catalog = app(CatalogController::class);
        $branches = $catalog->accessibleBranchesForUser($user);

        if ($request->filled('branch_id')) {
            $branchId = $request->integer('branch_id');
            abort_unless($branches->whereKey($branchId)->exists(), 403, 'You do not have access to this branch.');
            $accountIds = Branch::query()->whereKey($branchId)->pluck('account_id')->filter();

            return Account::active()->whereIn('id', $accountIds);
        }

        if ($user->hasAnyRole(['Super-Admin', 'Admin']) || $user->can('view_branches')) {
            return Account::active();
        }

        return Account::active()->whereIn('id', $branches->pluck('account_id')->filter());
    }

    private function period(string $filter): array
    {
        $now = Carbon::now();
        $filter = match ($filter) {
            'daily' => 'today',
            'weekly' => 'last_7_days',
            'monthly' => 'this_month',
            'yearly' => 'this_year',
            default => $filter,
        };

        return match ($filter) {
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'last_7_days' => [$now->copy()->subDays(7)->startOfDay(), $now->copy()->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_month' => [$now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
        };
    }

    private function formatDate(mixed $date): string
    {
        if ($date instanceof Carbon) {
            return $date->format('Y-m-d');
        }

        return $date ? Carbon::parse($date)->format('Y-m-d') : '';
    }
}

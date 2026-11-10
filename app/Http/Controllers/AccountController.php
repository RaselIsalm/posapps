<?php

namespace App\Http\Controllers;

use App\DataTables\AccountDataTable;
use App\DataTables\StatementDataTable;
use App\Models\Account;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(AccountDataTable $dataTable)
    {
        return $dataTable->render('backend.account.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            return view('backend.account.ajax.create');
        } else {
            abort(404);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('accounts', 'name')->whereNull('deleted_at'),
            ],
            'account_number' => $this->accountNumberRules(),
            'type' => 'required',
            'initial_balance' => 'nullable|numeric|min:0|max:9999999999999.99',
        ], [
            'initial_balance.max' => 'Initial balance is too large. Please enter an amount up to 9,999,999,999,999.99.',
            'initial_balance.numeric' => 'Initial balance must be a valid number.',
            'initial_balance.min' => 'Initial balance cannot be negative.',
        ]);

        $accountNumber = $this->normalizedAccountNumber($request);

        // Soft-deleted rows still occupy the DB unique index on name —
        // permanently remove matching trashed accounts so recreate works.
        Account::onlyTrashed()
            ->where(function ($query) use ($request, $accountNumber) {
                $query->where('name', $request->name);
                if ($accountNumber) {
                    $query->orWhere('account_number', $accountNumber);
                }
            })
            ->forceDelete();

        $is_active = $request->is_active == 'on' ? 1 : 0;
        $is_default = $request->is_default == 'on' ? 1 : 0;
        $allow_manual_amount = $request->allow_manual_amount == 'on' ? 1 : 0;
        $initialBalance = $request->initial_balance ?? 0;

        try {
            $account = new Account;
            $account->name = $request->name;
            $account->account_number = $accountNumber;
            $account->initial_balance = $initialBalance;
            $account->current_balance = $initialBalance;
            $account->type = $request->type;
            $account->note = $request->note;
            $account->is_active = $is_active;
            $account->is_default = 0;
            $account->allow_manual_amount = $allow_manual_amount;
            $account->created_by = Auth::user()->id;
            $account->save();

            if ($is_default) {
                Account::setAsDefault($account->id);
            }
        } catch (\Throwable $e) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not save account. Please check the balance amount and try again.',
                ], 422);
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Could not save account. Please check the balance amount and try again.');
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Account Created Successfully',
                'account' => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'account_number' => $account->account_number,
                    'is_active' => (bool) $account->is_active,
                    'is_default' => (bool) $account->is_default,
                    'allow_manual_amount' => (bool) $account->allow_manual_amount,
                ],
            ]);
        }

        return redirect()->route('accounts.index')->with('success', 'Account Created Successfully');
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $account = Account::findOrFail($id);

            return view('backend.account.ajax.edit', compact('account'));
        } else {
            abort(404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => [
                'required',
                Rule::unique('accounts', 'name')->whereNull('deleted_at')->ignore($id),
            ],
            'account_number' => $this->accountNumberRules($id),
            'type' => 'required',
            'initial_balance' => 'nullable|numeric|min:0|max:9999999999999.99',
        ], [
            'initial_balance.max' => 'Initial balance is too large. Please enter an amount up to 9,999,999,999,999.99.',
            'initial_balance.numeric' => 'Initial balance must be a valid number.',
            'initial_balance.min' => 'Initial balance cannot be negative.',
        ]);

        $is_active = $request->is_active == 'on' ? 1 : 0;
        $is_default = $request->is_default == 'on' ? 1 : 0;
        $allow_manual_amount = $request->allow_manual_amount == 'on' ? 1 : 0;

        $account = Account::findOrFail($id);

        // Sync current_balance when initial_balance changes
        $newInitialBalance = $request->initial_balance ?? 0;
        $oldInitialBalance = $account->initial_balance;
        $balanceDiff = $newInitialBalance - $oldInitialBalance;

        $account->name = $request->name;
        $account->account_number = $this->normalizedAccountNumber($request);
        $account->initial_balance = $newInitialBalance;
        $account->current_balance += $balanceDiff;
        $account->type = $request->type;
        $account->note = $request->note;
        $account->is_active = $is_active;
        $account->allow_manual_amount = $allow_manual_amount;
        $account->is_default = 0;
        $account->save();

        if ($is_default) {
            Account::setAsDefault($account->id);
        } else {
            Account::clearDefaultIfMatches($account->id);
        }

        return redirect()->route('accounts.index')->with('success', 'Account Updated Successfully');
    }

    public function destroy(Request $request, $id)
    {
        $account = Account::findOrFail($id);

        if ($account->transactions()->exists()) {
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Warning: This account has transaction history and cannot be deleted! You can deactivate it instead.'
                ], 400);
            }
            return redirect()->route('accounts.index')
                ->with('error', 'Warning: This account has transaction history and cannot be deleted! You can deactivate it instead.');
        }

        Account::clearDefaultIfMatches($account->id);
        $account->forceDelete();

        if ($request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Account Deleted Successfully']);
        }

        return redirect()->route('accounts.index')->with('success', 'Account Deleted Successfully');
    }

    public function makeDefault($id)
    {
        // Server-side authorization check (Double-security)
        if (! auth()->user()?->can('update_accounts')) {
            return redirect()->back()->with('error', 'Unauthorized Access: You do not have permission to modify accounts.');
        }

        $account = Account::findOrFail($id);
        Account::setAsDefault($account->id);

        return redirect()->route('accounts.index')->with('success', $account->name.' is now the default account.');
    }

    public function statement(Request $request, StatementDataTable $dataTable)
    {
        $accounts = Account::where('is_active', 1)->get();

        $account = null;
        $opening_balance = 0;

        if ($request->filled('account_id')) {
            $account = Account::find($request->account_id);

            if ($account) {
                $opening_balance = $account->initial_balance;

                if ($request->filled('start_date')) {
                    $startDate = $request->start_date;

                    $history = Transaction::where('account_id', $account->id)
                        ->where('date', '<', $startDate)
                        ->selectRaw("SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END) as total_credit")
                        ->selectRaw("SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END) as total_debit")
                        ->first();

                    $opening_balance += ($history->total_credit - $history->total_debit);
                }
            }
        }

        return $dataTable->with('balance', $opening_balance)
            ->render('backend.account.statement', compact('accounts', 'account', 'opening_balance'));
    }

    public function balanceSheet(Request $request)
    {
        $filter_date = $request->input('date', date('Y-m-d'));

        $accounts = Account::where('is_active', 1)->get();
        $account_balances = [];
        $total_cash_bank = 0;

        foreach ($accounts as $acc) {
            $credit = Transaction::where('account_id', $acc->id)
                ->whereDate('date', '<=', $filter_date)
                ->where('type', 'credit')
                ->sum('amount');

            $debit = Transaction::where('account_id', $acc->id)
                ->whereDate('date', '<=', $filter_date)
                ->where('type', 'debit')
                ->sum('amount');

            $balance = $acc->initial_balance + $credit - $debit;

            if ($balance != 0) {
                $account_balances[] = [
                    'id' => $acc->id,
                    'name' => $acc->name,
                    'number' => $acc->account_number,
                    'balance' => $balance,
                ];
                $total_cash_bank += $balance;
            }
        }

        $total_customer_due = Sale::whereDate('sale_date', '<=', $filter_date)->sum('due_amount');

        $total_assets = $total_cash_bank + $total_customer_due;

        $total_supplier_due = Purchase::whereDate('date', '<=', $filter_date)->sum('due_amount');

        $total_liabilities = $total_supplier_due;

        $net_equity = $total_assets - $total_liabilities;

        return view('backend.account.balance_sheet', compact(
            'account_balances',
            'total_cash_bank',
            'total_customer_due',
            'total_assets',
            'total_supplier_due',
            'total_liabilities',
            'net_equity',
            'filter_date'
        ));
    }

    protected function accountNumberRules(?int $ignoreId = null): array
    {
        $unique = Rule::unique('accounts', 'account_number')->whereNull('deleted_at');
        if ($ignoreId) {
            $unique = $unique->ignore($ignoreId);
        }

        return [
            'required_unless:type,cash',
            'nullable',
            'string',
            'max:255',
            $unique,
        ];
    }

    protected function normalizedAccountNumber(Request $request): ?string
    {
        $number = trim((string) $request->input('account_number', ''));

        if ($number === '') {
            return null;
        }

        return $number;
    }
}

<?php

namespace App\Http\Controllers;

use App\DataTables\ExpensesDataTable;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Expenses;
use App\Models\ExpensesCategory;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ExpensesController extends Controller
{
    public function index(ExpensesDataTable $dataTable)
    {
        return $dataTable->render('backend.expenses.index');
    }

    public function create()
    {
        if (request()->ajax()) {
            $category = ExpensesCategory::where('status', 1)->orderBy('name')->get();
            $accounts = Account::where('is_active', 1)->orderBy('name')->get();
            $branches = Branch::where('status', 1)->orderBy('name')->get();
            $branchManagers = User::orderBy('name')->get();
            $employeeRoles = Role::query()
                ->orderBy('name')
                ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                    $query->where('name', '!=', 'Super-Admin');
                })
                ->get();

            return view('backend.expenses.ajax.create', compact(
                'category',
                'accounts',
                'branches',
                'branchManagers',
                'employeeRoles'
            ));
        }

        return abort(404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'category_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $account = Account::find($request->account_id);
            if ($account && $account->current_balance < $request->amount) {
                throw new \Exception('Insufficient balance in '.$account->name.'! Current balance is: '.$account->current_balance);
            }

            $expense = Expenses::create([
                'date' => $request->date,
                'category_id' => $request->category_id,
                'branch_id' => $request->branch_id,
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'note' => $request->note,
                'status' => 1,
                'created_by' => Auth::id(),
            ]);

            TransactionService::createTransaction(
                $request->account_id,
                'debit',
                $request->amount,
                'Expense: '.($request->note ?? 'Office Expense'),
                $request->date,
                $expense
            );

            DB::commit();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Expense created successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Expense created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function edit($id)
    {
        if (request()->ajax()) {
            $expense = Expenses::findOrFail($id);
            $categories = ExpensesCategory::where('status', 1)->get();
            $branches = Branch::where('status', 1)->get();
            $accounts = Account::where('is_active', 1)->get();
            $branchManagers = User::orderBy('name')->get();
            $employeeRoles = Role::query()
                ->orderBy('name')
                ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                    $query->where('name', '!=', 'Super-Admin');
                })
                ->get();

            return view('backend.expenses.ajax.edit', compact('expense', 'categories', 'branches', 'accounts', 'branchManagers', 'employeeRoles'));
        }

        return abort(404);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'category_id' => 'required|integer',
            'branch_id' => 'required|integer',
            'account_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $expense = Expenses::findOrFail($id);
            $oldAmount = 0;
            $oldAccountId = null;

            $oldTransaction = Transaction::where('reference_type', get_class($expense))
                ->where('reference_id', $expense->id)
                ->first();

            if ($oldTransaction) {
                $oldAmount = $oldTransaction->amount;
                $oldAccountId = $oldTransaction->account_id;
            }

            $account = Account::find($request->account_id);
            if ($account) {
                $availableBalance = $account->current_balance;
                if ($oldAccountId == $request->account_id) {
                    $availableBalance += $oldAmount;
                }

                if ($availableBalance < $request->amount) {
                    throw new \Exception('Insufficient balance in '.$account->name.'! Available balance to update is: '.$availableBalance);
                }
            }

            if ($oldTransaction) {
                $oldAccount = Account::find($oldTransaction->account_id);
                if ($oldAccount) {
                    $oldAccount->increment('current_balance', $oldTransaction->amount);
                }
                $oldTransaction->delete();
            }

            $expense->update([
                'date' => $request->date,
                'category_id' => $request->category_id,
                'branch_id' => $request->branch_id,
                'account_id' => $request->account_id,
                'amount' => $request->amount,
                'note' => $request->note,
                'status' => $request->has('status') ? 1 : 0,
                'updated_by' => Auth::id(),
            ]);

            TransactionService::createTransaction(
                $request->account_id,
                'debit',
                $request->amount,
                'Expense Updated: '.($request->note ?? 'Office Expense'),
                $request->date,
                $expense
            );

            DB::commit();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Expense updated successfully.',
                ]);
            }

            return redirect()->back()->with('success', 'Expense updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect()->back()->with('error', 'Failed: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $expense = Expenses::findOrFail($id);

            $transaction = Transaction::where('reference_type', get_class($expense))
                ->where('reference_id', $expense->id)
                ->first();

            if ($transaction) {
                $account = Account::find($transaction->account_id);
                if ($account) {
                    $account->increment('current_balance', $transaction->amount);
                }
                $transaction->delete();
            }

            $expense->delete();

            DB::commit();

            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Expense deleted and account balance restored successfully.']);
            }

            return redirect()->route('expenses.index')->with('success', 'Expense deleted and account balance restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Something went wrong: ' . $e->getMessage()], 500);
            }

            return redirect()->back()->with('error', 'Something went wrong: '.$e->getMessage());
        }
    }
}

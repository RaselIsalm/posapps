<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    /**
     * Handle account transaction and update balance.
     *
     * IMPORTANT: This method does NOT manage its own transaction.
     * The caller MUST wrap this call inside a DB::beginTransaction/commit/rollBack block.
     *
     * @param  int  $accountId
     * @param  string  $type  'credit' or 'debit'
     * @param  float  $amount
     * @param  string  $reason
     * @param  string  $date
     * @param  object|null  $relatedModel  (Optional) Polymorphic relation
     * @return bool
     *
     * @throws Exception
     */
    public static function createTransaction($accountId, $type, $amount, $reason, $date, $relatedModel = null)
    {
        $account = Account::findOrFail($accountId);

        // Update Account Balance (Atomic update to prevent race conditions)
        if ($type === 'credit') {
            $account->increment('current_balance', $amount);
        } else {
            $account->decrement('current_balance', $amount);
        }
        $account->refresh(); // Get updated balance for transaction log

        // Create Transaction Record
        Transaction::create([
            'account_id' => $accountId,
            'type' => $type,
            'amount' => $amount,
            'balance' => $account->current_balance,
            'reason' => $reason,
            'date' => $date,
            'reference_type' => $relatedModel ? get_class($relatedModel) : null,
            'reference_id' => $relatedModel ? $relatedModel->id : null,
            'created_by' => auth()->id() ?? 1,
        ]);

        return true;
    }
}

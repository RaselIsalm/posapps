<?php

namespace App\Http\Controllers;

use App\DataTables\GiftCardDataTable;
use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\GiftCard;
use App\Models\GiftCardTransaction;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GiftCardController extends Controller
{
    public function index(GiftCardDataTable $dataTable)
    {
        $customers = Customer::where('status', 1)->select('id', 'name', 'phone')->orderBy('name')->get();
        $accounts = Account::where('is_active', 1)->orderBy('name')->get();
        $customer_groups = CustomerGroup::where('status', 1)->orderBy('name')->get();

        return $dataTable->render('backend.gift_card.index', compact('customers', 'accounts', 'customer_groups'));
    }

    public function show($id)
    {
        $giftCard = GiftCard::with(['customer', 'createdBy'])->findOrFail($id);

        return view('backend.gift_card.show_modal', compact('giftCard'));
    }

    public function history($id)
    {
        $card = GiftCard::with(['transactions.sale', 'customer'])->findOrFail($id);

        return view('backend.gift_card.history_modal', compact('card'));
    }

    public function edit($id)
    {
        $giftCard = GiftCard::findOrFail($id);
        $customers = Customer::where('status', 1)->select('id', 'name', 'phone')->get();

        return view('backend.gift_card.edit_modal', compact('giftCard', 'customers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'card_no' => ['required', Rule::unique('gift_cards')->ignore($id)],
            'expiry_date' => 'required|date',
            'status' => 'required',
        ]);

        try {
            $card = GiftCard::findOrFail($id);
            $customerId = $request->customer_id ? $request->customer_id : null;
            $card->update([
                'card_no' => $request->card_no,
                'customer_id' => $customerId,
                'expiry_date' => $request->expiry_date,
                'status' => $request->status,
            ]);

            return response()->json(['status' => 'success', 'message' => 'Gift Card updated successfully!']);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            GiftCard::findOrFail($id)->delete();

            return response()->json(['status' => 'success', 'message' => 'Deleted successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong!']);
        }
    }

    public function rechargeForm($id)
    {
        $giftCard = GiftCard::with('customer')->findOrFail($id);
        $accounts = Account::where('is_active', 1)->orderBy('name')->get();

        return view('backend.gift_card.recharge_modal', compact('giftCard', 'accounts'));
    }

    public function recharge(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'account_id' => 'required|exists:accounts,id',
        ]);

        try {
            DB::beginTransaction();

            $giftCard = GiftCard::findOrFail($id);
            $amount = $request->amount;

            // Increment balance and value
            $giftCard->balance += $amount;
            $giftCard->value += $amount;

            // Reactivate if it was used/empty
            if ($giftCard->status === 'used' || $giftCard->status === 'inactive') {
                $giftCard->status = 'active';
            }

            // Extend expiry if provided
            if ($request->filled('new_expiry_date')) {
                $giftCard->expiry_date = $request->new_expiry_date;
            }

            $giftCard->save();

            // Record account transaction
            $account = Account::findOrFail($request->account_id);
            TransactionService::createTransaction(
                $account->id,
                'credit',
                $amount,
                'Gift Card Recharge: '.$giftCard->card_no.($request->note ? ' - '.$request->note : ''),
                date('Y-m-d'),
                $giftCard
            );

            // Record gift card transaction history
            GiftCardTransaction::create([
                'gift_card_id' => $giftCard->id,
                'transaction_type' => 'credit',
                'amount' => $amount,
                'transaction_date' => date('Y-m-d'),
                'note' => 'Recharge'.($request->note ? ': '.$request->note : ''),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Gift Card recharged successfully! New balance: $'.number_format($giftCard->balance, 2),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Error: '.$e->getMessage(),
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Helpers\InvoiceHelper;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Department;
use App\Models\Employee;
use App\Models\GiftCard;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\RewardPointSetting;
use App\Models\User;
use App\Services\SaleService;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class QuickSaleController extends Controller
{
    protected $saleService;

    public function __construct(SaleService $saleService)
    {
        $this->saleService = $saleService;
    }

    public function index(Request $request)
    {
        $products = Product::with([
            'category',
            'unit',
            'sellingUnit',
            'productVariants.productBranches',
            'productBranches',
            'imeis' => function ($q) {
                $q->select('id', 'product_id', 'variant_id', 'imei_number', 'status', 'is_sold')
                    ->where('status', 'in_stock')
                    ->where('is_sold', 0);
            },
        ])
            ->where('is_active', 1)
            ->whereHas('productBranches', function ($q) {
                $q->where('qty', '>', 0);
            })
            ->get();

        $customers = Customer::with('customerGroup')->where('status', 1)->get();
        $customer_groups = CustomerGroup::where('status', 1)->orderBy('name')->get();
        $categories = Category::where('status', 1)->get();
        $accounts = Account::where('is_active', 1)->get();
        $rewardSetting = RewardPointSetting::first();
        $branchManagers = User::orderBy('name')->get();
        $departments = Department::where('status', 1)->orderBy('name')->get();
        $employeeRoles = Role::query()
            ->orderBy('name')
            ->when(! Auth::user()?->hasRole('Super-Admin'), function ($query) {
                $query->where('name', '!=', 'Super-Admin');
            })
            ->get();

        // Logged-in employee: lock to their own branch + salesman identity
        $lockedEmployee = Employee::with('branch')
            ->where('user_id', auth()->id())
            ->where('status', 1)
            ->first();

        if ($lockedEmployee) {
            $branches = $lockedEmployee->branch_id
                ? Branch::where('id', $lockedEmployee->branch_id)->where('status', 1)->get()
                : collect();
            $sellers = collect([$lockedEmployee]);
        } else {
            $branches = Branch::where('status', 1)->get();
            $sellers = Employee::where('status', 1)->get();
        }

        $quotation = null;
        if ($request->has('quotation_id')) {
            $quotation = Quotation::with(['items.product', 'items.variant'])->findOrFail($request->quotation_id);
        }

        return view('backend.quick_sale.index', compact(
            'products',
            'customers',
            'customer_groups',
            'branches',
            'categories',
            'sellers',
            'accounts',
            'quotation',
            'rewardSetting',
            'lockedEmployee',
            'branchManagers',
            'departments',
            'employeeRoles'
        ));
    }

    public function store(Request $request)
    {
        // Employee users cannot override branch / salesman via request tampering
        $lockedEmployee = Employee::where('user_id', auth()->id())->where('status', 1)->first();
        if ($lockedEmployee) {
            if (! $lockedEmployee->branch_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Your employee profile has no branch assigned. Contact admin.',
                ], 422);
            }

            $request->merge([
                'branch_id' => $lockedEmployee->branch_id,
                'seller_id' => $lockedEmployee->id,
            ]);
        }

        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'customer_id' => 'nullable|exists:customers,id',
            'seller_id' => 'required|exists:employees,id',
            'sale_date' => 'required|date',
            'items' => 'required|array|min:1',
            'payments' => 'required|array|min:1',
            'grand_total' => 'required|numeric',
        ]);

        try {
            $sale = $this->saleService->createSale($request->all());

            $general_settings = \App\Models\GeneralSettings::where('category', 'general')->pluck('value', 'key_name')->toArray();
            $pos_print_mode = $general_settings['pos_print_mode'] ?? 'manual';
            $pos_printer_type = $general_settings['pos_printer_type'] ?? 'thermal';

            return response()->json([
                'success' => true,
                'message' => 'Sale Completed Successfully!',
                'sale_id' => $sale->id,
                'points_earned' => $sale->reward_points_earned,
                'points_used' => $sale->reward_points_used,
                'pos_print_mode' => $pos_print_mode,
                'pos_printer_type' => $pos_printer_type,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function storeGiftCard(Request $request)
    {
        $request->validate([
            'card_no' => 'required|unique:gift_cards,card_no',
            'value' => 'required|numeric|min:1',
            'price' => 'required|numeric|min:0',
            'expiry_date' => 'required|date|after:today',
            'customer_id' => 'nullable|exists:customers,id',
            'payment_method' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $giftCard = new GiftCard;
            $giftCard->card_no = $request->card_no;
            $giftCard->customer_id = $request->customer_id;
            $giftCard->value = $request->value;
            $giftCard->price = $request->price;
            $giftCard->balance = $request->value;
            $giftCard->expiry_date = $request->expiry_date;
            $giftCard->created_by = auth()->id();
            $giftCard->status = 'active';
            $giftCard->save();

            if ($request->payment_method) {
                $account = Account::find($request->payment_method);
                if ($account) {
                    $account->increment('current_balance', $request->price);

                    TransactionService::createTransaction(
                        $account->id,
                        'credit',
                        $request->price,
                        'Gift Card Sold: '.$giftCard->card_no,
                        date('Y-m-d'),
                        $giftCard
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Gift Card Activated Successfully',
                'card_no' => $giftCard->card_no,
            ]);
        } catch (\Exception $error) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$error->getMessage(),
            ], 500);
        }
    }

    public function checkCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'sub_total' => 'required|numeric|min:0',
        ]);

        $code = strtoupper(trim($request->coupon_code));
        $subTotal = $request->sub_total;
        $today = date('Y-m-d');

        $coupon = Coupon::where('code', $code)->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid coupon code!']);
        }
        if (! $coupon->status) {
            return response()->json(['success' => false, 'message' => 'This coupon is inactive!']);
        }
        if ($coupon->start_date > $today) {
            return response()->json(['success' => false, 'message' => 'Coupon not started yet!']);
        }
        if ($coupon->end_date < $today) {
            return response()->json(['success' => false, 'message' => 'Coupon expired!']);
        }
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json(['success' => false, 'message' => 'Coupon usage limit exceeded!']);
        }
        if ($subTotal < $coupon->min_purchase) {
            return response()->json([
                'success' => false,
                'message' => 'Minimum purchase required: '.number_format($coupon->min_purchase, 2),
            ]);
        }

        $discountAmount = 0;
        if ($coupon->type == 'fixed') {
            $discountAmount = $coupon->amount;
        } else {
            $discountAmount = ($subTotal * $coupon->amount) / 100;
            if ($coupon->max_discount && $discountAmount > $coupon->max_discount) {
                $discountAmount = $coupon->max_discount;
            }
        }

        if ($discountAmount > $subTotal) {
            $discountAmount = $subTotal;
        }

        return response()->json([
            'success' => true,
            'message' => 'Coupon Applied Successfully!',
            'coupon_id' => $coupon->id,
            'code' => $coupon->code,
            'discount_amount' => round($discountAmount, 2),
            'type' => $coupon->type,
            'value' => $coupon->amount,
        ]);
    }

    public function checkGiftCard(Request $request)
    {
        $request->validate([
            'card_no' => 'required|string',
        ]);

        $card = GiftCard::where('card_no', $request->card_no)->first();

        if (! $card) {
            return response()->json(['success' => false, 'message' => 'Gift Card not found!']);
        }

        if (! $card->isValid()) {
            return response()->json(['success' => false, 'message' => 'Gift Card is expired or inactive!']);
        }

        return response()->json([
            'success' => true,
            'balance' => $card->balance,
            'message' => 'Gift Card applied successfully!',
        ]);
    }

    public function printThermal($id)
    {
        $sale = \App\Models\Sale::with([
            'customer',
            'branch',
            'productSales.product',
            'payments',
            'seller',
            'user',
        ])->findOrFail($id);

        // Same InvoiceHelper + thermal_body design as Sale List invoice modal
        $invoice = InvoiceHelper::fromSale($sale, [
            'thermal_width' => InvoiceHelper::getThermalPrinterSize(),
        ]);

        // Fragment = raw body HTML for printThermalFromUrl() (identical to Sale List)
        if (request()->boolean('fragment') || request()->ajax()) {
            return response(
                view('backend.components.invoice.partials.thermal_body', compact('invoice'))->render()
            )->header('Content-Type', 'text/html; charset=UTF-8');
        }

        return view('backend.components.invoice.thermal_page', compact('invoice'));
    }

    public function printA4($id)
    {
        $sale = \App\Models\Sale::with(['customer', 'branch', 'productSales.product', 'payments', 'seller'])->findOrFail($id);

        $invoice = InvoiceHelper::fromSale($sale);

        return view('backend.components.invoice.a4_page', compact('invoice'));
    }
}

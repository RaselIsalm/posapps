<?php

namespace App\Http\Controllers;

use App\DataTables\DeliveryDataTable;
use App\Models\Courier;
use App\Models\Delivery;
use App\Models\Sale;
use App\Services\PathaoCourierService;
use App\Services\SteadfastCourierService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeliveryController extends Controller
{
    public function index(DeliveryDataTable $dataTable)
    {
        $couriers = Courier::where('is_active', 1)->get();

        return $dataTable->render('backend.delivery.index', compact('couriers'));
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required',
            'status' => 'required',
        ]);

        $delivery = Delivery::findOrFail($request->delivery_id);
        $delivery->status = $request->status;
        $delivery->save();

        if ($delivery->sale) {
            $delivery->sale->delivery_status = $request->status;
            $delivery->sale->save();
        }

        return response()->json(['success' => true, 'message' => 'Status updated successfully']);
    }

    public function getDeliveryInvoice($id)
    {
        $sale = Sale::with([
            'customer',
            'branch',
            'productSales.product',
            'payments',
            'seller',
            'delivery.courier',
        ])->findOrFail($id);

        $invoice = \App\Helpers\InvoiceHelper::fromDelivery($sale);

        return view('backend.components.invoice.content', compact('invoice'));
    }

    public function getSaleInfo($id)
    {
        $sale = Sale::with(['customer', 'branch'])->findOrFail($id);
        $codAmount = ($sale->payment_status === 'paid') ? 0 : $sale->due_amount;
        $couriers = Courier::active()->get(['id', 'name', 'default_charge']);

        return response()->json([
            'sale_id' => $sale->id,
            'invoice_no' => $sale->invoice_no,
            'recipient_name' => $sale->customer->name ?? 'Walk-in Customer',
            'recipient_phone' => $sale->customer->phone ?? '',
            'address' => $sale->customer->address ?? '',
            'cod_amount' => $codAmount,
            'couriers' => $couriers,
            'grand_total' => $sale->grand_total,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'courier_id' => 'required|exists:couriers,id',
            'recipient_name' => 'required',
            'recipient_phone' => 'required',
            'address' => 'required',
            'cod_amount' => 'required|numeric|min:0',
            'delivery_charge' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $sale = Sale::findOrFail($request->sale_id);
            $courier = Courier::findOrFail($request->courier_id);

            $delivery = new Delivery;
            $delivery->sale_id = $sale->id;
            $delivery->courier_id = $courier->id;
            $delivery->invoice_no = $sale->invoice_no;
            $delivery->recipient_name = $request->recipient_name;
            $delivery->recipient_phone = $request->recipient_phone;
            $delivery->address = $request->address;

            $delivery->tracking_code = $request->tracking_code ?? 'TRK-'.time().rand(1000, 9999);

            $delivery->order_amount = $sale->grand_total;
            $delivery->cod_amount = $request->cod_amount;
            $delivery->delivery_charge = $request->delivery_charge;
            $delivery->note = $request->note;
            $delivery->status = 'pending';

            if ($courier->has_api && $courier->is_active) {
                if ($courier->code === 'steadfast') {
                    $steadfast = new SteadfastCourierService($courier);

                    if ($steadfast->hasCredentials()) {
                        $orderData = [
                            'invoice' => $sale->invoice_no,
                            'recipient_name' => $request->recipient_name,
                            'recipient_phone' => $request->recipient_phone,
                            'recipient_address' => $request->address,
                            'cod_amount' => $request->cod_amount,
                            'note' => $request->note ?? '',
                        ];

                        $apiResponse = $steadfast->placeOrder($orderData);

                        if ($apiResponse['success'] && isset($apiResponse['data']['consignment'])) {
                            $consignment = $apiResponse['data']['consignment'];
                            $delivery->consignment_id = $consignment['consignment_id'] ?? null;
                            $delivery->tracking_code = $consignment['tracking_code'] ?? $delivery->tracking_code;
                            $delivery->status = SteadfastCourierService::mapStatus($consignment['status'] ?? 'pending');
                            $delivery->courier_response = $apiResponse['data'];
                        } else {
                            $delivery->courier_response = [
                                'error' => true,
                                'message' => $apiResponse['data']['message'] ?? 'SteadFast API request failed',
                                'status' => $apiResponse['status'],
                            ];
                        }
                    }
                } elseif ($courier->code === 'pathao') {
                    $pathao = new PathaoCourierService($courier);

                    if ($pathao->hasCredentials()) {
                        $orderData = [
                            'invoice' => $sale->invoice_no,
                            'recipient_name' => $request->recipient_name,
                            'recipient_phone' => $request->recipient_phone,
                            'recipient_address' => $request->address,
                            'cod_amount' => $request->cod_amount,
                            'note' => $request->note ?? '',
                            'city_id' => 1,
                            'zone_id' => 1,
                            'area_id' => 1,
                        ];

                        $apiResponse = $pathao->placeOrder($orderData);

                        if ($apiResponse['success'] && isset($apiResponse['data']['data']['consignment_id'])) {
                            $consignment = $apiResponse['data']['data'];
                            $delivery->consignment_id = $consignment['consignment_id'] ?? null;
                            $delivery->tracking_code = $consignment['tracking_code'] ?? $delivery->tracking_code;
                            $delivery->status = PathaoCourierService::mapStatus($consignment['order_status'] ?? 'pending');
                            $delivery->courier_response = $apiResponse['data'];
                        } else {
                            $delivery->courier_response = [
                                'error' => true,
                                'message' => $apiResponse['data']['message'] ?? 'Pathao API request failed',
                                'status' => $apiResponse['status'] ?? 500,
                            ];
                        }
                    }
                }
            }

            $delivery->save();

            $sale->delivery_status = 'shipped';
            $sale->save();

            DB::commit();

            $message = 'Delivery request created successfully!';
            if (isset($apiResponse) && ! $apiResponse['success']) {
                $message .= ' (Warning: SteadFast API returned an error — delivery saved locally)';
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'tracking_code' => $delivery->tracking_code,
                'consignment_id' => $delivery->consignment_id,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        $delivery = Delivery::findOrFail($id);

        return response()->json($delivery);
    }

    public function update(Request $request)
    {
        $request->validate([
            'delivery_id' => 'required|exists:deliveries,id',
            'courier_id' => 'required|exists:couriers,id',
            'recipient_name' => 'required|string',
            'recipient_phone' => 'required|string',
            'address' => 'required|string',
            'cod_amount' => 'numeric|min:0',
            'tracking_code' => 'nullable|string',
        ]);

        try {
            $delivery = Delivery::findOrFail($request->delivery_id);

            $delivery->courier_id = $request->courier_id;
            $delivery->recipient_name = $request->recipient_name;
            $delivery->recipient_phone = $request->recipient_phone;
            $delivery->address = $request->address;
            $delivery->tracking_code = $request->tracking_code;
            $delivery->cod_amount = $request->cod_amount;
            $delivery->delivery_charge = $request->delivery_charge;
            $delivery->note = $request->note;

            $delivery->save();

            return response()->json(['success' => true, 'message' => 'Delivery details updated successfully!']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function syncSteadfastStatus(Request $request)
    {
        $request->validate(['delivery_id' => 'required|exists:deliveries,id']);

        $delivery = Delivery::with('courier')->findOrFail($request->delivery_id);

        if (! $delivery->courier || $delivery->courier->code !== 'steadfast') {
            return response()->json(['success' => false, 'message' => 'This delivery is not using SteadFast courier.'], 400);
        }

        if (! $delivery->consignment_id && ! $delivery->tracking_code) {
            return response()->json(['success' => false, 'message' => 'No consignment ID or tracking code available to check status.'], 400);
        }

        $steadfast = new SteadfastCourierService($delivery->courier);

        if (! $steadfast->hasCredentials()) {
            return response()->json(['success' => false, 'message' => 'SteadFast API credentials not configured for this courier.'], 400);
        }

        if ($delivery->consignment_id) {
            $apiResponse = $steadfast->checkStatusByConsignmentId($delivery->consignment_id);
        } else {
            $apiResponse = $steadfast->checkStatusByTrackingCode($delivery->tracking_code);
        }

        if ($apiResponse['success'] && isset($apiResponse['data']['delivery_status'])) {
            $sfStatus = $apiResponse['data']['delivery_status'];
            $mappedStatus = SteadfastCourierService::mapStatus($sfStatus);

            $delivery->status = $mappedStatus;
            $delivery->courier_response = array_merge(
                $delivery->courier_response ?? [],
                ['last_sync' => now()->toDateTimeString(), 'steadfast_status' => $sfStatus]
            );
            $delivery->save();

            if ($delivery->sale) {
                $delivery->sale->delivery_status = $mappedStatus;
                $delivery->sale->save();
            }

            return response()->json([
                'success' => true,
                'message' => "Status synced: {$sfStatus} → {$mappedStatus}",
                'steadfast_status' => $sfStatus,
                'mapped_status' => $mappedStatus,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $apiResponse['data']['message'] ?? 'Failed to fetch status from SteadFast.',
        ], 400);
    }

    public function getSteadfastBalance(Request $request)
    {
        $request->validate(['courier_id' => 'required|exists:couriers,id']);

        $courier = Courier::findOrFail($request->courier_id);

        if ($courier->code !== 'steadfast') {
            return response()->json(['success' => false, 'message' => 'Not a SteadFast courier.'], 400);
        }

        $steadfast = new SteadfastCourierService($courier);

        if (! $steadfast->hasCredentials()) {
            return response()->json(['success' => false, 'message' => 'SteadFast API credentials not configured.'], 400);
        }

        $apiResponse = $steadfast->getCurrentBalance();

        if ($apiResponse['success']) {
            return response()->json([
                'success' => true,
                'balance' => $apiResponse['data']['current_balance'] ?? 0,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $apiResponse['data']['message'] ?? 'Failed to fetch balance.',
        ], 400);
    }

    public function steadfastWebhook(Request $request)
    {
        $payload = $request->all();
        Log::channel('single')->info('Steadfast Webhook Received', $payload);

        if (empty($payload['status'])) {
            return response()->json(['success' => false, 'message' => 'Status is required'], 400);
        }

        if (empty($payload['consignment_id']) && empty($payload['tracking_code'])) {
            return response()->json(['success' => false, 'message' => 'Consignment ID or Tracking Code is required'], 400);
        }

        $query = Delivery::query();

        if (! empty($payload['consignment_id'])) {
            $query->where('consignment_id', $payload['consignment_id']);
        } else {
            $query->where('tracking_code', $payload['tracking_code']);
        }

        $delivery = $query->first();

        if ($delivery) {
            $newStatus = SteadfastCourierService::mapStatus($payload['status']);

            $delivery->status = $newStatus;

            $responses = is_array($delivery->courier_response) ? $delivery->courier_response : [];
            $responses['last_webhook'] = $payload;
            $delivery->courier_response = $responses;

            $delivery->save();

            if ($newStatus === 'delivered') {
                $sale = Sale::find($delivery->sale_id);
                if ($sale) {
                    $sale->delivery_status = 'delivered';
                    $sale->save();
                }
            } elseif ($newStatus === 'cancelled') {
                $sale = Sale::find($delivery->sale_id);
                if ($sale) {
                    $sale->delivery_status = 'pending';
                    $sale->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Status updated']);
        }

        return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
    }

    public function pathaoWebhook(Request $request)
    {
        $payload = $request->all();

        Log::channel('single')->info('Pathao Webhook Received', $payload);

        $identifier = $payload['consignment_id'] ?? $payload['tracking_code'] ?? null;

        if (! $identifier || empty($payload['status'])) {
            return response()->json(['success' => false, 'message' => 'Invalid payload'], 400);
        }

        $delivery = Delivery::where('consignment_id', $identifier)
            ->orWhere('tracking_code', $identifier)
            ->orWhere('tracking_code', 'LIKE', '%'.$identifier.'%')
            ->first();

        if ($delivery) {
            $newStatus = PathaoCourierService::mapStatus($payload['status']);

            $delivery->status = $newStatus;

            $responses = is_array($delivery->courier_response) ? $delivery->courier_response : [];
            $responses['last_webhook'] = $payload;
            $delivery->courier_response = $responses;

            $delivery->save();

            if ($newStatus === 'delivered') {
                $sale = Sale::find($delivery->sale_id);
                if ($sale) {
                    $sale->delivery_status = 'delivered';
                    $sale->save();
                }
            }

            return response()->json(['success' => true, 'message' => 'Status updated']);
        }

        return response()->json(['success' => false, 'message' => 'Delivery not found'], 404);
    }

    public function destroy($id)
    {
        try {
            $delivery = Delivery::findOrFail($id);
            $delivery->delete();

            return response()->json([
                'success' => true,
                'message' => 'Delivery Deleted Successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete delivery: ' . $e->getMessage()
            ], 500);
        }
    }
}

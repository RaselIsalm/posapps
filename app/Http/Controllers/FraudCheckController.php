<?php

namespace App\Http\Controllers;

use App\Services\BdCourierFraudService;
use Illuminate\Http\Request;

class FraudCheckController extends Controller
{
    public function check(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $this->releaseSession($request);

        $service = new BdCourierFraudService;
        $result = $service->checkPhone($request->phone);

        if ($result['success'] && ! empty($result['data'])) {
            $result['risk'] = BdCourierFraudService::assessRisk($result['data']);
        }

        return response()->json($result);
    }

    public function test(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
        ]);

        $this->releaseSession($request);

        $service = new BdCourierFraudService;

        if (! $service->hasCredentials()) {
            return response()->json([
                'success' => false,
                'message' => 'Save an API key, URL, and turn fraud check on before testing.',
            ], 422);
        }

        $result = $service->checkPhoneFresh($request->phone);

        if ($result['success'] && ! empty($result['data'])) {
            $result['risk'] = BdCourierFraudService::assessRisk($result['data']);
        }

        return response()->json($result);
    }

    protected function releaseSession(Request $request): void
    {
        $request->session()->save();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
    }
}

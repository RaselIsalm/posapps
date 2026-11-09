<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Courier;
use App\Models\FraudCheckSetting;
use App\Models\SmsLog;
use App\Models\SmsSetting;
use App\Models\SmsSettingParam;
use App\Models\SmsTemplate;
use App\Services\BdCourierFraudService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommunicationController extends ApiController
{
    private const PERMISSIONS = [
        'sms-logs' => 'view_sms_log', 'sms-templates' => 'view_sms_template',
        'sms-settings' => 'manage_api_integrations', 'couriers' => 'manage_api_integrations',
        'fraud-check-settings' => 'manage_api_integrations',
    ];

    public function index(Request $request, string $resource): JsonResponse
    {
        $this->requireResource($resource);
        $search = trim((string) $request->query('search', ''));
        $query = match ($resource) {
            'sms-logs' => SmsLog::query(), 'sms-templates' => SmsTemplate::query(),
            'sms-settings' => SmsSetting::query()->with('params'), 'couriers' => Courier::query(),
            'fraud-check-settings' => FraudCheckSetting::query(),
        };
        if ($search !== '' && $resource === 'sms-logs') $query->where(fn ($q) => $q->where('phone_number', 'like', '%'.$search.'%')->orWhere('message', 'like', '%'.$search.'%')->orWhere('status', 'like', '%'.$search.'%'));
        if ($search !== '' && $resource === 'sms-templates') $query->where('template_name', 'like', '%'.$search.'%');
        if ($search !== '' && $resource === 'couriers') $query->where('name', 'like', '%'.$search.'%');
        if ($search !== '' && $resource === 'sms-settings') $query->where('provider_name', 'like', '%'.$search.'%');
        if ($resource === 'sms-logs') {
            $query->when($request->filled('status'), fn ($q) => $q->where('status', $request->query('status')))
                ->when($request->filled('from_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from_date')))
                ->when($request->filled('to_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to_date')));
        }
        $serializer = fn ($row) => $this->serialize($resource, $row);
        if ($resource === 'fraud-check-settings') return $this->success(['item' => $serializer($query->first())]);
        return $this->success($this->page($request, $query->latest('id'), $serializer, $resource));
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        $this->requireResource($resource);
        $model = match ($resource) {
            'sms-logs' => SmsLog::findOrFail($id), 'sms-templates' => SmsTemplate::findOrFail($id),
            'sms-settings' => SmsSetting::with('params')->findOrFail($id), 'couriers' => Courier::findOrFail($id),
            default => abort(404),
        };
        return $this->success(['item' => $this->serialize($resource, $model)]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $this->requireMutation($resource, 'create');
        $item = match ($resource) {
            'sms-templates' => $this->saveTemplate($request), 'sms-settings' => $this->saveSmsSetting($request),
            'couriers' => $this->saveCourier($request), 'fraud-check-settings' => $this->saveFraudSetting($request),
            default => abort(405),
        };
        return $this->success(['item' => $this->serialize($resource, $item)], 'Saved successfully.', 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $this->requireMutation($resource, 'update');
        $item = match ($resource) {
            'sms-templates' => $this->saveTemplate($request, SmsTemplate::findOrFail($id)),
            'sms-settings' => $this->saveSmsSetting($request, SmsSetting::findOrFail($id)),
            'couriers' => $this->saveCourier($request, Courier::findOrFail($id)),
            'fraud-check-settings' => $this->saveFraudSetting($request, FraudCheckSetting::findOrFail($id)),
            default => abort(405),
        };
        return $this->success(['item' => $this->serialize($resource, $item)], 'Updated successfully.');
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $this->requireMutation($resource, 'delete');
        $item = match ($resource) {
            'sms-templates' => SmsTemplate::findOrFail($id), 'sms-settings' => SmsSetting::with('params')->findOrFail($id),
            'couriers' => Courier::findOrFail($id), default => abort(405),
        };
        if ($resource === 'sms-settings') { abort_if($item->is_active, 409, 'Activate another SMS gateway before deleting this one.'); $item->params()->delete(); }
        if ($resource === 'couriers') abort_if($item->deliveries()->exists(), 409, 'This courier is linked to existing deliveries.');
        $item->delete(); return $this->success([], 'Deleted successfully.');
    }

    public function testSms(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('manage_api_integrations');
        $data = $request->validate(['test_phone' => ['required', 'string', 'max:20']]);
        $setting = SmsSetting::with('params')->findOrFail($id);
        $params = $setting->params_array + ['number' => $data['test_phone'], 'message' => 'Test SMS from ONE POS.'];
        try {
            $response = Http::timeout(10)->get($setting->api_url, $params);
            return $this->success(['sent' => $response->successful(), 'provider_status' => $response->status(), 'message' => $response->successful() ? 'Test SMS sent.' : 'SMS provider returned an error.']);
        } catch (\Throwable $e) { Log::warning('Mobile SMS gateway test failed: '.$e->getMessage()); return $this->success(['sent' => false, 'message' => 'Could not reach the SMS provider.'], 'Gateway test failed.', 502); }
    }

    public function smsBalance(int $id): JsonResponse
    {
        $this->requirePermission('manage_api_integrations');
        $setting = SmsSetting::with('params')->findOrFail($id);
        abort_if(! $setting->balance_api_url, 422, 'No balance URL is configured.');
        try { $response = Http::timeout(10)->get($setting->balance_api_url, $setting->params_array); return $this->success(['balance' => $response->body(), 'provider_status' => $response->status()]); }
        catch (\Throwable $e) { Log::warning('Mobile SMS balance check failed: '.$e->getMessage()); return $this->success(['message' => 'Could not reach the SMS provider.'], 'Balance check failed.', 502); }
    }

    public function testFraudCheck(Request $request): JsonResponse
    {
        $this->requirePermission('manage_api_integrations');
        $data = $request->validate(['phone' => ['required', 'string', 'max:20']]);
        $service = new BdCourierFraudService();
        abort_unless($service->hasCredentials(), 422, 'Save an API key and URL and activate fraud check before testing.');
        $result = $service->checkPhoneFresh($data['phone']);
        if (($result['success'] ?? false) && ! empty($result['data'])) $result['risk'] = BdCourierFraudService::assessRisk($result['data']);
        return $this->success(['result' => $result]);
    }

    private function saveTemplate(Request $request, ?SmsTemplate $template = null): SmsTemplate
    {
        $data = $request->validate(['template_name' => ['required', 'string', 'max:255'], 'message_body' => ['required', 'string'], 'is_active' => ['sometimes', 'boolean']]);
        $data['template_name'] = Str::lower(str_replace(' ', '_', trim($data['template_name'])));
        if ($request->boolean('is_active')) SmsTemplate::when($template, fn ($q) => $q->whereKeyNot($template->id))->update(['is_active' => 0]);
        if ($template) $template->update($data); else $template = SmsTemplate::create($data);
        return $template->fresh();
    }

    private function saveSmsSetting(Request $request, ?SmsSetting $setting = null): SmsSetting
    {
        $data = $request->validate(['provider_name' => ['required', 'string', 'max:255'], 'api_url' => ['required', 'url', 'max:500'], 'balance_api_url' => ['nullable', 'url', 'max:500'], 'is_active' => ['sometimes', 'boolean'], 'params' => ['nullable', 'array'], 'params.*.key' => ['required', 'string', 'max:100'], 'params.*.value' => ['nullable', 'string', 'max:500']]);
        if ($request->boolean('is_active')) SmsSetting::when($setting, fn ($q) => $q->whereKeyNot($setting->id))->update(['is_active' => 0]);
        $values = ['provider_name' => $data['provider_name'], 'api_url' => $data['api_url'], 'balance_api_url' => $data['balance_api_url'] ?? null, 'is_active' => $request->boolean('is_active')];
        if ($setting) $setting->update($values); else $setting = SmsSetting::create($values);
        if (array_key_exists('params', $data)) {
            $previous = $setting->params()->get()->keyBy('param_key');
            $setting->params()->delete();
            foreach ($data['params'] ?? [] as $param) {
                $key = $param['key']; $value = $param['value'] ?? '';
                if (preg_match('/^•+$/u', $value) && isset($previous[$key])) $value = $previous[$key]->param_value;
                SmsSettingParam::create(['sms_setting_id' => $setting->id, 'param_key' => $key, 'param_value' => $value]);
            }
        }
        return $setting->fresh('params');
    }

    private function saveCourier(Request $request, ?Courier $courier = null): Courier
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'phone' => ['required', 'string', 'max:50'], 'address' => ['required', 'string'], 'base_url' => ['nullable', 'url', 'max:500'], 'default_charge' => ['nullable', 'numeric', 'min:0'], 'is_active' => ['sometimes', 'boolean'], 'params' => ['nullable', 'array'], 'params.*.key' => ['required', 'string', 'max:100'], 'params.*.value' => ['nullable', 'string', 'max:500']]);
        $previous = collect($courier?->api_config['params'] ?? [])->keyBy('key');
        $params = array_map(function ($param) use ($previous) {
            $value = $param['value'] ?? '';
            if (preg_match('/^•+$/u', $value) && isset($previous[$param['key']])) $value = $previous[$param['key']]['value'] ?? '';
            return ['key' => $param['key'], 'value' => $value];
        }, $data['params'] ?? []);
        $config = ['base_url' => $data['base_url'] ?? null, 'params' => array_values($params)];
        $values = ['name' => $data['name'], 'code' => Str::slug($data['name']), 'phone' => $data['phone'], 'address' => $data['address'], 'api_config' => $config, 'has_api' => 1, 'default_charge' => $data['default_charge'] ?? 0, 'is_active' => $request->boolean('is_active')];
        if ($courier) $courier->update($values); else $courier = Courier::create($values);
        return $courier->fresh();
    }

    private function saveFraudSetting(Request $request, ?FraudCheckSetting $setting = null): FraudCheckSetting
    {
        $data = $request->validate(['api_key' => ['nullable', 'string', 'max:255'], 'api_url' => ['nullable', 'url', 'max:255'], 'is_active' => ['sometimes', 'boolean']]);
        $setting ??= FraudCheckSetting::first() ?? new FraudCheckSetting();
        if (! empty($data['api_key']) && preg_match('/^[•*]+$/u', $data['api_key'])) unset($data['api_key']);
        $setting->fill($data + ['is_active' => $request->boolean('is_active')]); $setting->save(); return $setting->fresh();
    }

    private function requireResource(string $resource): void
    {
        abort_unless(isset(self::PERMISSIONS[$resource]), 404);
        $this->requirePermission(self::PERMISSIONS[$resource]);
    }

    private function requireMutation(string $resource, string $action): void
    {
        $this->requireResource($resource);
        if ($resource === 'sms-templates') {
            $permission = match ($action) { 'create' => 'create_sms_template', 'update' => 'update_sms_template', default => 'delete_sms_template' };
            $this->requirePermission($permission);
        } elseif ($resource === 'sms-logs') abort(405);
        else $this->requirePermission('manage_api_integrations');
    }

    private function serialize(string $resource, mixed $item): ?array
    {
        if (! $item) return null;
        return match ($resource) {
            'sms-logs' => ['id' => $item->id, 'phone' => $item->phone_number, 'message' => $item->message, 'status' => $item->status, 'response' => $item->response, 'created_at' => $item->created_at?->toIso8601String()],
            'sms-templates' => ['id' => $item->id, 'template_name' => $item->template_name, 'message_body' => $item->message_body, 'is_active' => (bool) $item->is_active],
            'sms-settings' => ['id' => $item->id, 'provider_name' => $item->provider_name, 'api_url' => $item->api_url, 'balance_api_url' => $item->balance_api_url, 'is_active' => (bool) $item->is_active, 'params' => $item->params?->map(fn ($param) => ['key' => $param->param_key, 'value' => $param->param_value === '' ? '' : str_repeat('•', min(12, max(4, mb_strlen($param->param_value)))), 'has_value' => $param->param_value !== ''])->values()],
            'couriers' => ['id' => $item->id, 'name' => $item->name, 'phone' => $item->phone, 'address' => $item->address, 'base_url' => $item->api_config['base_url'] ?? null, 'params' => collect($item->api_config['params'] ?? [])->map(fn ($p) => ['key' => $p['key'], 'value' => empty($p['value']) ? '' : str_repeat('•', min(12, max(4, mb_strlen($p['value']))))])->values(), 'default_charge' => (float) $item->default_charge, 'is_active' => (bool) $item->is_active],
            'fraud-check-settings' => ['id' => $item->id, 'api_key' => $item->api_key ? str_repeat('•', min(16, max(4, mb_strlen($item->api_key)))) : '', 'has_api_key' => (bool) $item->api_key, 'api_url' => $item->api_url, 'is_active' => (bool) $item->is_active],
        };
    }
}

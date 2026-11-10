<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ImageHelper;
use App\Helpers\InvoiceHelper;
use App\Helpers\ThemeHelper;
use App\Models\Account;
use App\Models\BackupRun;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\GeneralSettings;
use App\Models\MailSetting;
use App\Models\Unit;
use App\Models\User;
use App\Services\BackupService;
use App\Services\MailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SystemController extends ApiController
{
    private const PERMISSIONS = [
        'branches' => ['view_branches', 'create_branches', 'update_branches', 'delete_branches'],
        'roles' => ['manage_roles', 'manage_roles', 'manage_roles', 'manage_roles'],
        'users' => ['manage_users', 'manage_users', 'manage_users', 'manage_users'],
        'units' => ['view_units', 'create_units', 'update_units', 'delete_units'],
        'currencies' => ['view_currencies', 'create_currencies', 'update_currencies', 'delete_currencies'],
        'activity-logs' => ['view_activity_log', 'view_activity_log', 'view_activity_log', 'view_activity_log'],
    ];

    public function index(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($resource, 0);
        $query = match ($resource) {
            'branches' => Branch::query()->with(['manager', 'account']),
            'roles' => Role::query()->with('permissions'), 'users' => User::query()->with('roles'),
            'units' => Unit::query()->with('baseUnit'), 'currencies' => Currency::query(),
            'activity-logs' => Activity::query()->with('causer'),
        };
        $term = trim((string) $request->query('search', ''));
        if ($term !== '') {
            $query->where(function ($q) use ($resource, $term) {
                if ($resource === 'activity-logs') {
                    $q->where('description', 'like', '%'.$term.'%')
                        ->orWhere('log_name', 'like', '%'.$term.'%')
                        ->orWhereHas('causer', fn ($user) => $user->where('name', 'like', '%'.$term.'%'));
                } else {
                    $q->where('name', 'like', '%'.$term.'%');
                    if ($resource === 'branches') $q->orWhere('email', 'like', '%'.$term.'%')->orWhere('phone', 'like', '%'.$term.'%');
                }
            });
        }
        if ($resource === 'activity-logs') {
            $query->when($request->filled('log_name'), fn ($q) => $q->where('log_name', $request->query('log_name')))
                ->when($request->filled('user_id'), fn ($q) => $q->where('causer_id', $request->integer('user_id')))
                ->when($request->filled('subject_type'), fn ($q) => $q->where('subject_type', $request->query('subject_type')))
                ->when($request->filled('from_date'), fn ($q) => $q->whereDate('created_at', '>=', $request->query('from_date')))
                ->when($request->filled('to_date'), fn ($q) => $q->whereDate('created_at', '<=', $request->query('to_date')));
        }
        $data = $this->page($request, $query->latest('id'), fn ($item) => $this->serialize($resource, $item), $resource);
        if ($resource === 'roles') $data['permissions'] = Permission::query()->orderBy('name')->get(['id', 'name', 'guard_name']);
        if ($resource === 'branches') $data['options'] = $this->branchOptions();
        if ($resource === 'users') $data['options'] = $this->userOptions($request);
        if ($resource === 'units') $data['options'] = ['base_units' => Unit::active()->orderBy('name')->get(['id', 'name'])];
        if ($resource === 'activity-logs') $data['filters'] = ['users' => User::orderBy('name')->get(['id', 'name']), 'modules' => Activity::select('log_name')->distinct()->pluck('log_name'), 'subject_types' => Activity::whereNotNull('subject_type')->select('subject_type')->distinct()->pluck('subject_type')];
        return $this->success($data);
    }

    public function show(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($resource, 0);
        $item = match ($resource) {
            'branches' => Branch::with(['manager', 'account'])->findOrFail($id), 'roles' => Role::with('permissions')->findOrFail($id),
            'users' => User::with('roles')->findOrFail($id), 'units' => Unit::with('baseUnit')->findOrFail($id),
            'currencies' => Currency::findOrFail($id), 'activity-logs' => Activity::with('causer')->findOrFail($id),
        };
        return $this->success(['item' => $this->serialize($resource, $item)]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $this->authorizeResource($resource, 1);
        $item = match ($resource) {
            'branches' => $this->saveBranch($request), 'roles' => $this->saveRole($request), 'users' => $this->saveUser($request),
            'units' => $this->saveUnit($request), 'currencies' => $this->saveCurrency($request), default => abort(405),
        };
        return $this->success(['item' => $this->serialize($resource, $item)], 'Created successfully.', 201);
    }

    public function update(Request $request, string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($resource, 2);
        $item = match ($resource) {
            'branches' => $this->saveBranch($request, Branch::findOrFail($id)), 'roles' => $this->saveRole($request, Role::findOrFail($id)),
            'users' => $this->saveUser($request, User::findOrFail($id)), 'units' => $this->saveUnit($request, Unit::findOrFail($id)),
            'currencies' => $this->saveCurrency($request, Currency::findOrFail($id)), default => abort(405),
        };
        return $this->success(['item' => $this->serialize($resource, $item)], 'Updated successfully.');
    }

    public function destroy(string $resource, int $id): JsonResponse
    {
        $this->authorizeResource($resource, 3);
        $item = match ($resource) { 'branches' => Branch::findOrFail($id), 'roles' => Role::findOrFail($id), 'users' => User::findOrFail($id), 'units' => Unit::findOrFail($id), 'currencies' => Currency::findOrFail($id), default => abort(405) };
        if ($resource === 'branches') abort_if($item->sales()->exists() || $item->purchases()->exists() || $item->productStocks()->exists() || \App\Models\Expenses::where('branch_id', $id)->exists() || \App\Models\SaleReturn::where('branch_id', $id)->exists() || \App\Models\PurchaseReturn::where('branch_id', $id)->exists(), 409, 'This branch has linked operational records and cannot be deleted.');
        if ($resource === 'roles') abort_if(in_array($item->name, ['Super-Admin', 'Admin'], true), 409, 'System roles cannot be deleted.');
        if ($resource === 'roles') abort_if($item->users()->exists(), 409, 'This role is assigned to users and cannot be deleted.');
        if ($resource === 'currencies') abort_if($item->is_default, 409, 'The default currency cannot be deleted.');
        if ($resource === 'users') {
            abort_if($item->hasRole('Super-Admin') || $item->id === auth()->id(), 403, 'You cannot remove this user.');
            if ($item->hasOperationalHistory()) { $item->deactivateAccount(); return $this->success(['mode' => 'deactivated'], 'User has history and was deactivated.'); }
            $item->syncRoles([]);
        }
        if ($resource === 'units') abort_if($item->childUnits()->exists() || \App\Models\Product::where('unit_id', $id)->exists(), 409, 'This unit is in use.');
        $item->delete(); return $this->success([], 'Deleted successfully.');
    }

    public function userRemovalPreview(int $id): JsonResponse
    {
        $this->requirePermission('manage_users'); $user = User::findOrFail($id);
        abort_if($user->hasRole('Super-Admin') || $user->id === auth()->id(), 403, 'You cannot remove this user.');
        return $this->success(['mode' => $user->hasOperationalHistory() ? 'deactivate' : 'delete', 'message' => $user->hasOperationalHistory() ? 'User has records; the account will be deactivated and records retained.' : 'This user can be permanently deleted.']);
    }

    public function activateUser(int $id): JsonResponse
    {
        $this->requirePermission('manage_users'); $user = User::findOrFail($id);
        abort_if($user->hasRole('Super-Admin') && ! auth()->user()->hasRole('Super-Admin'), 403);
        $user->activateAccount(); return $this->success(['item' => $this->serialize('users', $user->fresh('roles'))], 'User activated.');
    }

    public function settings(Request $request, string $section): JsonResponse
    {
        $this->requirePermission('manage_general_settings');
        $values = GeneralSettings::where('category', 'general')->pluck('value', 'key_name')->all();
        foreach ($values as $key => $value) {
            if (is_string($value) && str_starts_with(trim($value), '[')) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) $values[$key] = $decoded;
            }
        }
        $data = match ($section) {
            'general' => ['values' => $this->publicSettings($values), 'currencies' => Currency::active()->orderBy('name')->get(['id', 'name', 'code', 'symbol', 'exchange_rate']), 'accounts' => Account::active()->orderBy('name')->get(['id', 'name', 'type', 'is_default']), 'timezones' => \DateTimeZone::listIdentifiers()],
            'color' => ['values' => collect(['primary_color', 'primary_hover_color', 'link_color', 'toast_success_color', 'primary_light_color'])->mapWithKeys(fn ($key) => [$key => $values[$key] ?? match ($key) {'primary_color' => ThemeHelper::DEFAULT_PRIMARY, 'primary_hover_color' => ThemeHelper::DEFAULT_HOVER, 'primary_light_color' => ThemeHelper::DEFAULT_LIGHT, default => ThemeHelper::DEFAULT_PRIMARY}])],
            'print' => ['values' => $this->printSettings($values), 'thermal_fields' => InvoiceHelper::thermalIdentityToggles(), 'thermal_blocks' => config('print_layout.thermal', []), 'a4_blocks' => config('print_layout.a4', [])],
            'terms' => ['terms' => is_array($values['terms_and_conditions'] ?? null) ? $values['terms_and_conditions'] : []],
            'mail' => ['mail' => $this->mailSettings()],
            default => abort(404),
        };
        return $this->success($data);
    }

    public function updateSettings(Request $request, string $section): JsonResponse
    {
        $this->requirePermission('manage_general_settings');
        match ($section) {
            'general' => $this->updateGeneralSettings($request), 'color' => $this->updateColorSettings($request),
            'print' => $this->updatePrintSettings($request), 'terms' => $this->updateTerms($request),
            'mail' => $this->updateMailSettings($request), default => abort(404),
        };
        return $this->settings($request, $section);
    }

    public function testMail(Request $request): JsonResponse
    {
        $this->requirePermission('manage_general_settings'); $data = $request->validate(['test_email' => ['required', 'email', 'max:255']]);
        $result = MailService::sendTestMail($data['test_email']); return $this->success(['result' => $result]);
    }

    public function resetColorSettings(): JsonResponse
    {
        $this->requirePermission('manage_general_settings');
        foreach (['primary_color' => ThemeHelper::DEFAULT_PRIMARY, 'primary_hover_color' => ThemeHelper::DEFAULT_HOVER, 'link_color' => ThemeHelper::DEFAULT_PRIMARY, 'toast_success_color' => ThemeHelper::DEFAULT_PRIMARY, 'primary_light_color' => ThemeHelper::DEFAULT_LIGHT] as $key => $value) GeneralSettings::setGeneralSetting($key, $value);
        return $this->settings(request(), 'color');
    }

    public function backups(Request $request): JsonResponse
    {
        $this->requirePermission('manage_general_settings');
        $service = app(BackupService::class); $service->syncDiskFiles(); $service->forgetMissingLocalFiles();
        $rows = BackupRun::latest('id')->limit(100)->get()->map(fn ($run) => ['id' => $run->id, 'filename' => $run->filename, 'status' => $run->status, 'size' => $run->sizeLabel(), 'trigger' => $run->triggerLabel(), 'started_at' => $run->started_at?->toIso8601String(), 'message' => $run->message]);
        return $this->success(['runs' => $rows, 'settings' => $this->backupSettings(), 'next_run' => BackupService::nextRunDescription(), 'encryption_available' => BackupService::encryptionAvailable()]);
    }

    public function updateBackupSettings(Request $request): JsonResponse
    {
        $this->requirePermission('manage_general_settings');
        $data = $request->validate(['backup_enabled' => ['required', Rule::in(['0', '1'])], 'backup_interval' => ['required', Rule::in(array_keys(BackupService::INTERVALS))], 'backup_daily_time' => ['required', 'date_format:H:i'], 'backup_weekly_day' => ['required', 'integer', 'between:0,6'], 'backup_keep_count' => ['required', 'integer', 'between:3,30'], 'backup_notify' => ['required', Rule::in(['0', '1'])], 'backup_notify_email' => ['nullable', 'email', 'required_if:backup_notify,1'], 'backup_include_files' => ['required', Rule::in(['0', '1'])], 'backup_storage' => ['required', Rule::in(['local', 'both', 'remote'])], 'backup_remote_host' => ['nullable', 'string', 'max:255'], 'backup_remote_port' => ['nullable', 'integer', 'between:1,65535'], 'backup_remote_username' => ['nullable', 'string', 'max:190'], 'backup_remote_password' => ['nullable', 'string', 'max:190'], 'backup_remote_path' => ['nullable', 'string', 'max:255']]);
        foreach (['host', 'port', 'username', 'path'] as $required) if ($data['backup_storage'] !== 'local') abort_if(empty($data['backup_remote_'.$required]), 422, 'Remote backup host, port, username and path are required.');
        foreach ($data as $key => $value) if (! str_starts_with($key, 'backup_remote_')) GeneralSettings::setGeneralSetting($key, $value);
        foreach (['host', 'username', 'path'] as $key) {
            $value = $data['backup_remote_'.$key] ?? null;
            if ($value !== null && ! preg_match('/^•+$/u', $value)) BackupService::storeSecret('backup_remote_'.$key, (string) $value);
        }
        if (isset($data['backup_remote_port'])) GeneralSettings::setGeneralSetting('backup_remote_port', $data['backup_remote_port']);
        if (! empty($data['backup_remote_password'])) BackupService::storeSecret('backup_remote_password', $data['backup_remote_password']);
        return $this->backups($request);
    }

    public function testBackupRemote(Request $request): JsonResponse
    {
        $this->requirePermission('manage_general_settings');
        $data = $request->validate(['backup_remote_host' => ['nullable', 'string', 'max:255'], 'backup_remote_port' => ['nullable', 'integer', 'between:1,65535'], 'backup_remote_username' => ['nullable', 'string', 'max:190'], 'backup_remote_password' => ['nullable', 'string', 'max:190'], 'backup_remote_path' => ['nullable', 'string', 'max:255']]);
        $host = $data['backup_remote_host'] ?? BackupService::readSecret('backup_remote_host');
        $port = (int) ($data['backup_remote_port'] ?? BackupService::setting('backup_remote_port', 21));
        $username = $data['backup_remote_username'] ?? BackupService::readSecret('backup_remote_username');
        $path = $data['backup_remote_path'] ?? BackupService::readSecret('backup_remote_path');
        $password = $data['backup_remote_password'] ?? BackupService::readSecret('backup_remote_password');
        abort_if(! $host || ! $username || ! $path || ! $password, 422, 'Complete remote backup credentials in settings first.');
        app(BackupService::class)->testRemote($host, $port, $username, $password, $path);
        return $this->success([], 'Remote backup connection succeeded.');
    }

    public function runBackup(): JsonResponse
    {
        $this->requirePermission('manage_general_settings'); $run = app(BackupService::class)->run('manual');
        return $this->success(['id' => $run->id, 'filename' => $run->filename, 'status' => $run->status, 'size' => $run->sizeLabel()], $run->message ?: 'Backup completed.');
    }

    public function deleteBackup(int $id): JsonResponse
    {
        $this->requirePermission('manage_general_settings'); app(BackupService::class)->delete(BackupRun::findOrFail($id)); return $this->success([], 'Backup deleted.');
    }

    public function downloadBackup(int $id, BackupService $service)
    {
        $this->requirePermission('manage_general_settings');
        $backup = BackupRun::findOrFail($id);
        abort_unless($backup->status === 'success' && $backup->filename, 409, 'This backup is not ready to download.');
        $path = storage_path('app/backups/'.basename($backup->filename));
        $temporary = ! is_file($path);
        if ($temporary && str_contains((string) $backup->message, 'other server')) $service->pullFromRemote($backup->filename);
        abort_unless(is_file($path), 404, 'Backup file is unavailable.');
        return response()->download($path, basename($backup->filename))->deleteFileAfterSend($temporary);
    }

    public function restoreBackup(Request $request, int $id): JsonResponse
    {
        $this->requirePermission('manage_general_settings'); $data = $request->validate(['confirm' => ['required', Rule::in(['RESTORE'])]]); $backup = BackupRun::findOrFail($id); $service = app(BackupService::class);
        $service->withLocalCopy($backup, fn () => $service->restore($backup, $data['confirm'])); return $this->success([], 'Backup restored; a safety copy was saved.');
    }

    private function saveBranch(Request $request, ?Branch $branch = null): Branch
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'address' => ['required', 'string'], 'account_id' => ['required', 'integer', 'exists:accounts,id'], 'manager_id' => ['required', 'integer', 'exists:users,id'], 'phone' => ['nullable', 'string', 'max:50'], 'telephone' => ['nullable', 'string', 'max:50'], 'bin_number' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'website' => ['nullable', 'string', 'max:255'], 'status' => ['sometimes', 'boolean'], 'branch_logo' => ['nullable', 'image', 'max:2048'], 'remove_branch_logo' => ['sometimes', 'boolean']]);
        $data['website'] = trim((string) ($data['website'] ?? '')); if ($data['website'] && ! preg_match('#^https?://#i', $data['website'])) $data['website'] = 'https://'.$data['website'];
        $data['status'] = $request->boolean('status'); unset($data['branch_logo'], $data['remove_branch_logo']);
        if ($request->file('branch_logo')) { $name = 'branch_'.Str::uuid().'.'.$request->file('branch_logo')->extension(); $data['branch_logo'] = ImageHelper::uploadImage($name, $request->file('branch_logo'), 'branch'); }
        if ($branch) { $branch->update($data); if ($request->boolean('remove_branch_logo') && ! $request->file('branch_logo')) { ImageHelper::deleteImage($branch->branch_logo, 'branch'); $branch->branch_logo = null; $branch->save(); } }
        else $branch = Branch::create($data + ['created_by' => auth()->id()]);
        return $branch->fresh(['manager', 'account']);
    }

    private function saveRole(Request $request, ?Role $role = null): Role
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->where('guard_name', 'web')->ignore($role?->id)], 'description' => ['nullable', 'string', 'max:1000'], 'permissions' => ['nullable', 'array'], 'permissions.*' => ['string', 'exists:permissions,name']]);
        abort_if(($data['name'] ?? $role?->name) === 'Super-Admin' && ! auth()->user()->hasRole('Super-Admin'), 403, 'Only Super Admin can manage the Super-Admin role.');
        $role ??= new Role(['guard_name' => 'web']); $role->name = $data['name']; $role->description = $data['description'] ?? null; $role->save();
        if (array_key_exists('permissions', $data)) {
            $permissions = array_values(array_unique($data['permissions']));
            foreach ($permissions as $permission) {
                if (preg_match('/^(create|update|delete)_(.+)$/', $permission, $matches)) {
                    $view = 'view_'.$matches[2];
                    if (! in_array($view, $permissions, true) && Permission::where('name', $view)->where('guard_name', 'web')->exists()) $permissions[] = $view;
                }
            }
            $role->syncPermissions($permissions);
        }
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); return $role->fresh('permissions');
    }

    private function saveUser(Request $request, ?User $user = null): User
    {
        $rules = ['name' => ['required', 'string', 'max:255'], 'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user?->id)], 'phone' => ['nullable', 'string', 'max:30'], 'role' => ['required', 'string', 'exists:roles,name'], 'password' => [$user ? 'nullable' : 'required', 'string', 'min:6', 'confirmed'], 'is_active' => ['sometimes', 'boolean']];
        $data = $request->validate($rules); $role = Role::findByName($data['role'], 'web');
        abort_if($role->name === 'Super-Admin' && ! auth()->user()->hasRole('Super-Admin'), 403, 'You cannot assign Super-Admin.');
        if ($user?->hasRole('Super-Admin')) abort_unless(auth()->user()->hasRole('Super-Admin'), 403, 'You cannot edit Super-Admin.');
        $user ??= new User(['username' => $this->uniqueUsername($data['email'])]); $user->fill(['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null, 'is_active' => $request->boolean('is_active', true)]);
        if (! empty($data['password'])) $user->password = $data['password']; $user->save(); $user->syncRoles([$role]);
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); return $user->fresh('roles');
    }

    private function saveUnit(Request $request, ?Unit $unit = null): Unit
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:255', Rule::unique('units', 'name')->ignore($unit?->id)], 'short_code' => ['required', 'string', 'max:50'], 'base_unit_id' => ['nullable', 'integer', 'exists:units,id', Rule::notIn([$unit?->id])], 'operator' => ['nullable', Rule::in(['*', '/'])], 'conversion_rate' => ['required', 'numeric', 'gt:0'], 'is_active' => ['sometimes', 'boolean']]);
        $data['base_unit_id'] = $data['base_unit_id'] ?? null; $data['operator'] = $data['operator'] ?? '*'; $data['is_active'] = $request->boolean('is_active', true);
        if ($unit) { $data['updated_by'] = auth()->id(); $unit->update($data); } else { $data['created_by'] = auth()->id(); $unit = Unit::create($data); }
        return $unit->fresh('baseUnit');
    }

    private function saveCurrency(Request $request, ?Currency $currency = null): Currency
    {
        $data = $request->validate(['name' => ['required', 'string', Rule::unique('currencies', 'name')->ignore($currency?->id)], 'code' => ['required', 'string', 'max:10', Rule::unique('currencies', 'code')->ignore($currency?->id)], 'symbol' => ['required', 'string', 'max:20'], 'exchange_rate' => ['required', 'numeric', 'gt:0'], 'position' => ['nullable', 'string', 'max:20'], 'is_active' => ['sometimes', 'boolean'], 'is_default' => ['sometimes', 'boolean']]);
        if ($request->boolean('is_default')) Currency::query()->when($currency, fn ($q) => $q->whereKeyNot($currency->id))->update(['is_default' => 0]);
        $data['is_active'] = $request->boolean('is_active', true); $data['is_default'] = $request->boolean('is_default');
        if ($currency) { $data['updated_by'] = auth()->id(); $currency->update($data); } else { $data['created_by'] = auth()->id(); $currency = Currency::create($data); }
        return $currency->fresh();
    }

    private function updateGeneralSettings(Request $request): void
    {
        $request->validate(['timezone' => ['required', 'timezone'], 'default_tax' => ['nullable', 'numeric', 'min:0'], 'thermal_printer_size' => ['nullable', Rule::in(['58mm', '72mm', '76mm', '80mm', '100mm', '112mm'])], 'default_payment_method' => ['nullable', 'integer', 'exists:accounts,id'], 'currency_symbol' => ['nullable', 'string', 'max:20'], 'currency_code' => ['nullable', 'string', 'max:10'], 'currency_position' => ['nullable', 'string', 'max:20'], 'logo' => ['nullable', 'image', 'max:2048'], 'favicon' => ['nullable', 'image', 'max:1024']]);
        $allowed = ['timezone', 'default_tax', 'thermal_printer_size', 'default_payment_method', 'currency_symbol', 'currency_code', 'currency_position', 'company_name', 'company_title', 'phone', 'telephone', 'email', 'address', 'website', 'vat_number', 'footer_text'];
        foreach ($request->only($allowed) as $key => $value) GeneralSettings::setGeneralSetting($key, $value);
        foreach (['logo', 'favicon'] as $key) if ($request->file($key)) { $old = GeneralSettings::getGeneralSetting($key); $filename = ImageHelper::updateImage($key.'_'.time().'.'.$request->file($key)->extension(), $request->file($key), 'settings', is_string($old) ? $old : null); GeneralSettings::setGeneralSetting($key, $filename); }
        foreach (['logo', 'favicon'] as $key) if ($request->boolean('remove_'.$key) && ! $request->file($key)) { ImageHelper::deleteImage(GeneralSettings::getGeneralSetting($key), 'settings'); GeneralSettings::setGeneralSetting($key, null); }
        if ($request->exists('default_payment_method')) Account::setAsDefault($request->filled('default_payment_method') ? (int) $request->input('default_payment_method') : null);
    }

    private function updateColorSettings(Request $request): void
    {
        $data = $request->validate(['primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'primary_hover_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'link_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'toast_success_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'], 'primary_light_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/']]);
        foreach ($data as $key => $value) GeneralSettings::setGeneralSetting($key, strtolower($value));
    }

    private function updatePrintSettings(Request $request): void
    {
        $request->validate(['print_layout_thermal' => ['required', 'array'], 'print_layout_a4' => ['required', 'array'], 'thermal_logo_height' => ['required', 'integer', 'between:24,80'], 'print_show_branch_name' => ['sometimes', 'boolean'], 'thermal_logo' => ['nullable', 'image', 'max:2048'], 'a4_logo' => ['nullable', 'image', 'max:2048']]);
        foreach (['thermal', 'a4'] as $format) {
            $allowed = array_keys(config('print_layout.'.$format, [])); $seen = []; $layout = [];
            foreach ($request->input('print_layout_'.$format, []) as $row) { $id = $row['id'] ?? null; if (! $id || ! in_array($id, $allowed, true) || isset($seen[$id])) continue; $seen[$id] = true; $layout[] = ['id' => $id, 'enabled' => filter_var($row['enabled'] ?? true, FILTER_VALIDATE_BOOLEAN)]; }
            foreach ($allowed as $id) if (! isset($seen[$id])) $layout[] = ['id' => $id, 'enabled' => true];
            GeneralSettings::setGeneralSetting('print_layout_'.$format, $layout);
        }
        GeneralSettings::setGeneralSetting('thermal_logo_height', (string) $request->integer('thermal_logo_height')); GeneralSettings::setGeneralSetting('print_show_branch_name', $request->boolean('print_show_branch_name') ? '1' : '0');
        foreach (array_keys(InvoiceHelper::thermalIdentityToggles()) as $field) GeneralSettings::setGeneralSetting('thermal_show_'.$field, $request->boolean('thermal_show_'.$field) ? '1' : '0');
        foreach (['thermal_logo', 'a4_logo'] as $key) if ($request->file($key)) { $old = GeneralSettings::getGeneralSetting($key); GeneralSettings::setGeneralSetting($key, ImageHelper::updateImage($key.'_'.time().'.'.$request->file($key)->extension(), $request->file($key), 'settings', is_string($old) ? $old : null)); }
        foreach (['thermal_logo', 'a4_logo'] as $key) if ($request->boolean('remove_'.$key) && ! $request->file($key)) { ImageHelper::deleteImage(GeneralSettings::getGeneralSetting($key), 'settings'); GeneralSettings::setGeneralSetting($key, null); }
    }

    private function updateTerms(Request $request): void
    {
        $data = $request->validate(['terms' => ['required', 'array'], 'terms.*' => ['nullable', 'string', 'max:2000']]);
        GeneralSettings::setGeneralSetting('terms_and_conditions', array_values(array_filter($data['terms'], fn ($x) => trim((string) $x) !== '')));
    }

    private function updateMailSettings(Request $request): void
    {
        $old = MailSetting::first();
        $data = $request->validate(['mail_host' => ['required', 'string', 'max:255'], 'mail_port' => ['required', 'integer', 'between:1,65535'], 'mail_username' => ['required', 'email', 'max:255'], 'mail_password' => [$old ? 'nullable' : 'required', 'string', 'max:255'], 'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'null'])], 'mail_from_address' => ['required', 'email', 'max:255'], 'mail_from_name' => ['required', 'string', 'max:255'], 'is_active' => ['sometimes', 'boolean']]);
        if ($old && (empty($data['mail_password']) || $data['mail_password'] === str_repeat('•', 16))) unset($data['mail_password']);
        $data['mail_mailer'] = 'smtp'; $data['is_active'] = $request->boolean('is_active');
        if ($old) $old->update($data); else $old = MailSetting::create($data);
    }

    private function saveBackupSettings(array $values): array { return $values; }
    private function mailSettings(): ?array { $m = MailSetting::first(); return $m ? ['mail_host' => $m->mail_host, 'mail_port' => $m->mail_port, 'mail_username' => $m->mail_username, 'mail_password' => $m->mail_password ? str_repeat('•', 16) : '', 'has_password' => (bool) $m->mail_password, 'mail_encryption' => $m->mail_encryption, 'mail_from_address' => $m->mail_from_address, 'mail_from_name' => $m->mail_from_name, 'is_active' => (bool) $m->is_active] : null; }
    private function publicSettings(array $values): array
    {
        $settings = $this->onlySettings($values, ['timezone', 'default_tax', 'thermal_printer_size', 'default_payment_method', 'currency_symbol', 'currency_code', 'currency_position', 'company_name', 'company_title', 'phone', 'telephone', 'email', 'address', 'website', 'vat_number', 'footer_text', 'logo', 'favicon']);
        foreach (['logo', 'favicon'] as $key) $settings[$key] = ImageHelper::publicUrl($settings[$key], 'settings');
        return $settings;
    }

    private function printSettings(array $values): array
    {
        $settings = $this->onlySettings($values, ['print_layout_thermal', 'print_layout_a4', 'thermal_logo_height', 'print_show_branch_name', 'thermal_logo', 'a4_logo']);
        foreach (['thermal', 'a4'] as $format) {
            $key = 'print_layout_'.$format;
            if (! is_array($settings[$key])) $settings[$key] = collect(config('print_layout.'.$format, []))->map(fn ($block, $id) => ['id' => $id, 'enabled' => (bool) ($block['enabled'] ?? true)])->values()->all();
        }
        $settings['thermal_logo_height'] ??= 40;
        $settings['print_show_branch_name'] = (bool) ($settings['print_show_branch_name'] ?? true);
        foreach (['thermal_logo', 'a4_logo'] as $key) $settings[$key] = ImageHelper::publicUrl($settings[$key], 'settings');
        return $settings;
    }
    private function onlySettings(array $values, array $keys): array { return collect($keys)->mapWithKeys(fn ($key) => [$key => $values[$key] ?? null])->all(); }
    private function backupSettings(): array
    {
        $username = BackupService::readSecret('backup_remote_username');

        return [
            'backup_enabled' => (string) BackupService::setting('backup_enabled', '0'),
            'backup_interval' => (string) BackupService::setting('backup_interval', 'daily'),
            'backup_daily_time' => substr((string) BackupService::setting('backup_daily_time', '02:00'), 0, 5),
            'backup_weekly_day' => (int) BackupService::setting('backup_weekly_day', '1'),
            'backup_keep_count' => (int) BackupService::setting('backup_keep_count', 10),
            'backup_notify' => (string) BackupService::setting('backup_notify', '0'),
            'backup_notify_email' => (string) BackupService::setting('backup_notify_email', ''),
            'backup_include_files' => (string) BackupService::setting('backup_include_files', '1'),
            'backup_storage' => BackupService::storageMode(),
            'backup_remote_host' => BackupService::readSecret('backup_remote_host'),
            'backup_remote_port' => (int) BackupService::setting('backup_remote_port', 21),
            'backup_remote_username' => $username === '' ? '' : str_repeat('•', min(12, max(4, mb_strlen($username)))),
            'backup_remote_username_set' => $username !== '',
            'backup_remote_password' => '',
            'backup_remote_password_set' => BackupService::readSecret('backup_remote_password') !== '',
            'backup_remote_path' => BackupService::readSecret('backup_remote_path'),
        ];
    }

    private function branchOptions(): array { return ['accounts' => Account::active()->orderBy('name')->get(['id', 'name']), 'managers' => User::orderBy('name')->get(['id', 'name'])]; }
    private function userOptions(Request $request): array { return ['roles' => Role::when(! $request->user()->hasRole('Super-Admin'), fn ($q) => $q->where('name', '!=', 'Super-Admin'))->orderBy('name')->pluck('name')]; }
    private function uniqueUsername(string $email): string { $base = explode('@', $email)[0]; $name = $base; $i = 1; while (User::where('username', $name)->exists()) $name = $base.$i++; return $name; }

    private function authorizeResource(string $resource, int $action): void { abort_unless(isset(self::PERMISSIONS[$resource]), 404); $this->requirePermission(self::PERMISSIONS[$resource][$action]); }

    private function serialize(string $resource, mixed $m): array
    {
        return match ($resource) {
            'branches' => ['id' => $m->id, 'name' => $m->name, 'address' => $m->address, 'phone' => $m->phone, 'telephone' => $m->telephone, 'email' => $m->email, 'website' => $m->website, 'bin_number' => $m->bin_number, 'account_id' => $m->account_id, 'account' => $m->account?->name, 'manager_id' => $m->manager_id, 'manager' => $m->manager?->name, 'logo' => $m->branch_logo ? ImageHelper::publicUrl($m->branch_logo, 'branch') : null, 'status' => (bool) $m->status],
            'roles' => ['id' => $m->id, 'name' => $m->name, 'description' => $m->description, 'permissions' => $m->permissions->pluck('name')->values()],
            'users' => ['id' => $m->id, 'name' => $m->name, 'email' => $m->email, 'phone' => $m->phone, 'role' => $m->roles->first()?->name, 'is_active' => (bool) $m->is_active],
            'units' => ['id' => $m->id, 'name' => $m->name, 'short_code' => $m->short_code, 'base_unit_id' => $m->base_unit_id, 'base_unit' => $m->baseUnit?->name, 'operator' => $m->operator, 'conversion_rate' => (float) $m->conversion_rate, 'is_active' => (bool) $m->is_active],
            'currencies' => ['id' => $m->id, 'name' => $m->name, 'code' => $m->code, 'symbol' => $m->symbol, 'exchange_rate' => (float) $m->exchange_rate, 'position' => $m->position, 'is_active' => (bool) $m->is_active, 'is_default' => (bool) $m->is_default],
            'activity-logs' => ['id' => $m->id, 'date' => $m->created_at?->toIso8601String(), 'module' => $m->log_name, 'user' => $m->causer?->name ?? 'System', 'description' => $m->description, 'subject_type' => $m->subject_type, 'subject_id' => $m->subject_id, 'properties' => $m->properties],
        };
    }
}

<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class ApiController extends Controller
{
    protected function requirePermission(string ...$permissions): void
    {
        abort_unless(auth()->user()?->hasAnyPermission($permissions), 403, 'You do not have permission to perform this action.');
    }

    protected function success(mixed $data = [], string $message = 'Request completed successfully.', int $status = 200): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], $status);
    }

    protected function page(Request $request, Builder $query, callable $map, string $key): array
    {
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);
        $result = $query->paginate($perPage);

        return [
            $key => $result->getCollection()->map($map)->values(),
            'total' => $result->total(),
            'current_page' => $result->currentPage(),
            'per_page' => $result->perPage(),
            'last_page' => $result->lastPage(),
        ];
    }

    protected function applyBranchScope(Builder $query, Request $request, string $column = 'branch_id'): Builder
    {
        $user = $request->user();
        $employee = Employee::query()->where('user_id', $user->id)->where('status', 1)->first();
        if ($employee) {
            abort_unless($employee->branch_id, 422, 'Your employee profile has no branch assigned.');

            return $query->where($column, $employee->branch_id);
        }

        $catalog = app(CatalogController::class);
        $accessible = $catalog->accessibleBranchesForUser($user);
        $branchId = $this->requestedBranchId($request);
        if ($branchId !== null) {
            abort_unless($accessible->whereKey($branchId)->exists(), 403, 'You do not have access to this branch.');

            return $query->where($column, $branchId);
        }

        if (! $user->hasAnyRole(['Super-Admin', 'Admin']) && ! $user->can('view_branches')) {
            return $query->whereIn($column, $accessible->select('id'));
        }

        return $query;
    }

    protected function requestedBranchId(Request $request): ?int
    {
        $value = $request->query('branch_id', $request->query('warehouse_id'));
        if (! is_numeric($value) || (int) $value <= 0) {
            return null;
        }

        return (int) $value;
    }

    /** Accounts are global records, but branch users may only see accounts assigned to accessible branches. */
    protected function accessibleAccountQuery(Request $request)
    {
        $user = $request->user();
        $branches = app(CatalogController::class)->accessibleBranchesForUser($user);

        if ($request->filled('branch_id')) {
            $branchId = (int) $request->input('branch_id');
            abort_unless($branches->whereKey($branchId)->exists(), 403, 'You do not have access to this branch.');
            $accountIds = Branch::query()->whereKey($branchId)->pluck('account_id')->filter();

            return Account::active()->whereIn('id', $accountIds);
        }

        if ($user->hasAnyRole(['Super-Admin', 'Admin']) || $user->can('view_branches')) {
            return Account::active();
        }

        return Account::active()->whereIn('id', $branches->pluck('account_id')->filter());
    }
}

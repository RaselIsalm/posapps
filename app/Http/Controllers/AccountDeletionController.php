<?php

namespace App\Http\Controllers;

use App\Models\AccountDeletionRequest;
use App\Models\User;
use App\Services\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class AccountDeletionController extends Controller
{
    public function __construct(private readonly AccountDeletionService $deletionService) {}

    public function privacy(): View
    {
        return view('legal.privacy');
    }

    public function create(): View
    {
        return view('legal.account-deletion');
    }

    public function requestDeletion(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        try {
            $this->deletionService->requestFromEmail(mb_strtolower(trim($validated['email'])));
        } catch (Throwable $exception) {
            Log::warning('ONEPOS external account deletion request could not be processed.', [
                'exception' => $exception::class,
            ]);
        }

        return redirect()->route('account-deletion.create')->with(
            'status',
            'If an eligible ONEPOS account uses that email address, a confirmation link will be sent to it.'
        );
    }

    public function confirm(AccountDeletionRequest $deletionRequest, string $token): View
    {
        return view('legal.account-deletion-confirm', [
            'deletionRequest' => $deletionRequest,
            'token' => $token,
            'isValid' => $this->deletionService->verificationIsValid($deletionRequest, $token),
        ]);
    }

    public function verify(Request $request, AccountDeletionRequest $deletionRequest, string $token): RedirectResponse
    {
        if (! $this->deletionService->verify($deletionRequest, $token)) {
            return redirect()->route('account-deletion.create')->withErrors([
                'email' => 'This confirmation link has expired or was already used. Please request a new one.',
            ]);
        }

        return redirect()->route('account-deletion.create')->with(
            'status',
            'Your request was verified. ONEPOS administrators will review it.'
        );
    }

    public function index(): View
    {
        $requests = AccountDeletionRequest::query()
            ->with(['user', 'reviewer'])
            ->where('status', AccountDeletionRequest::STATUS_PENDING)
            ->whereNotNull('verified_at')
            ->latest('verified_at')
            ->paginate(20);

        return view('backend.user.deletion-requests', compact('requests'));
    }

    public function approve(Request $request, AccountDeletionRequest $deletionRequest): RedirectResponse
    {
        $this->requireSuperAdmin($request->user());
        $this->deletionService->approve($deletionRequest, $request->user());

        return redirect()->route('users.deletionRequests.index')->with(
            'success',
            'The account profile data was removed and the request was completed.'
        );
    }

    public function reject(Request $request, AccountDeletionRequest $deletionRequest): RedirectResponse
    {
        $this->requireSuperAdmin($request->user());

        if ($deletionRequest->status !== AccountDeletionRequest::STATUS_PENDING) {
            return redirect()->route('users.deletionRequests.index')->withErrors([
                'request' => 'Only pending verified requests can be rejected.',
            ]);
        }

        $this->deletionService->reject($deletionRequest, $request->user());

        return redirect()->route('users.deletionRequests.index')->with(
            'success',
            'The request was rejected.'
        );
    }

    private function requireSuperAdmin(?User $user): void
    {
        abort_unless($user?->hasRole('Super-Admin'), 403);
    }
}

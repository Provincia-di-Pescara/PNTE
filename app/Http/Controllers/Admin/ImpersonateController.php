<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ImpersonateController extends Controller
{
    public function take(Request $request, User $user): RedirectResponse
    {
        $admin = $request->user();

        abort_unless($admin->hasRole(UserRole::SuperAdmin->value), 403);
        abort_unless($admin->canImpersonate(), 403);
        abort_unless($user->canBeImpersonated(), 403);

        ImpersonationLog::create([
            'impersonator_id' => $admin->id,
            'impersonated_id' => $user->id,
            'started_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $admin->impersonate($user);

        return redirect()->route('dashboard')
            ->with('info', 'Stai operando come '.$user->name.'.');
    }

    public function leave(Request $request): RedirectResponse
    {
        $currentUser = $request->user();

        /** @var int|null $originalId */
        $originalId = session('impersonated_by');

        if ($originalId) {
            ImpersonationLog::where('impersonator_id', $originalId)
                ->where('impersonated_id', $currentUser->id)
                ->whereNull('ended_at')
                ->latest('started_at')
                ->first()
                ?->update(['ended_at' => now()]);
        }

        $currentUser->leaveImpersonation();

        return redirect()->route('admin.settings.users.index')
            ->with('info', 'Impersonazione terminata.');
    }
}

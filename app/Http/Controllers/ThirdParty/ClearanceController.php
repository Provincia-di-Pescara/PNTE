<?php

declare(strict_types=1);

namespace App\Http\Controllers\ThirdParty;

use App\Contracts\ApplicationServiceInterface;
use App\Enums\ClearanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Clearance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ClearanceController extends Controller
{
    public function __construct(
        private readonly ApplicationServiceInterface $applicationService,
    ) {}

    public function index(): View
    {
        $user = auth()->user();

        $clearances = Clearance::query()
            ->where('entity_id', $user->entity_id)
            ->with(['application.company', 'application.vehicle'])
            ->latest()
            ->paginate(20);

        return view('third-party.clearances.index', compact('clearances'));
    }

    public function show(Clearance $clearance): View
    {
        $this->authorize('view', $clearance);
        $clearance->load(['application.company', 'application.vehicle.axles', 'application.route', 'entity']);

        return view('third-party.clearances.show', compact('clearance'));
    }

    public function approve(Request $request, Clearance $clearance): RedirectResponse
    {
        $this->authorize('decide', $clearance);

        $clearance->update([
            'stato' => ClearanceStatus::Approved,
            'decided_at' => now(),
            'decided_by' => auth()->id(),
            'note' => $request->input('note'),
        ]);

        $this->checkApplicationClearances($clearance);

        return redirect()->route('third-party.clearances.show', $clearance)
            ->with('success', 'Nulla osta approvato.');
    }

    public function reject(Request $request, Clearance $clearance): RedirectResponse
    {
        $this->authorize('decide', $clearance);

        $reason = $request->input('note', 'Nulla osta negato.');

        $clearance->update([
            'stato' => ClearanceStatus::Rejected,
            'decided_at' => now(),
            'decided_by' => auth()->id(),
            'note' => $reason,
        ]);

        $this->applicationService->reject($clearance->application, $reason);

        return redirect()->route('third-party.clearances.show', $clearance)
            ->with('success', 'Nulla osta negato.');
    }

    private function checkApplicationClearances(Clearance $clearance): void
    {
        $application = $clearance->application;
        $allClearances = $application->clearances;

        if ($allClearances->every(fn (Clearance $c) => $c->stato->isDecided())) {
            if ($allClearances->every(fn (Clearance $c) => $c->stato->isPositive())) {
                $this->applicationService->markPaymentReady($application);
            }
        }
    }
}

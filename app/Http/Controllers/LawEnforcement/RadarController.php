<?php

declare(strict_types=1);

namespace App\Http\Controllers\LawEnforcement;

use App\Enums\ApplicationStatus;
use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\View\View;

final class RadarController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $applications = Application::query()
            ->where('stato', ApplicationStatus::Approved)
            ->where('valida_da', '<=', $today)
            ->where('valida_fino', '>=', $today)
            ->with(['company', 'vehicle', 'trips' => fn ($q) => $q->where('stato', TripStatus::Active)])
            ->latest()
            ->paginate(50);

        return view('law-enforcement.radar.index', compact('applications'));
    }

    public function show(Application $application): View
    {
        $application->load(['company', 'vehicle.axles', 'route', 'trips']);

        return view('law-enforcement.radar.show', compact('application'));
    }
}

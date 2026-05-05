<?php

declare(strict_types=1);

namespace App\Http\Controllers\Citizen;

use App\Enums\TripStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Trip;
use Illuminate\Http\RedirectResponse;

final class TripController extends Controller
{
    public function store(Application $application): RedirectResponse
    {
        $this->authorize('create', Trip::class);

        $trip = Trip::create([
            'application_id' => $application->id,
            'driver_user_id' => auth()->id(),
            'stato' => TripStatus::Active,
            'started_at' => now(),
        ]);

        $application->increment('viaggi_effettuati');

        return redirect()->route('my.applications.show', $application)
            ->with('success', 'Check-in viaggio avviato.');
    }

    public function end(Trip $trip): RedirectResponse
    {
        $this->authorize('update', $trip);

        $trip->update([
            'stato' => TripStatus::Completed,
            'ended_at' => now(),
        ]);

        return redirect()->route('my.applications.show', $trip->application_id)
            ->with('success', 'Viaggio completato.');
    }
}

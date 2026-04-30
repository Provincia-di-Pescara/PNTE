<?php

declare(strict_types=1);

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\StoreVehicleRequest;
use App\Http\Requests\Citizen\UpdateVehicleRequest;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class VehicleController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Vehicle::class);

        $companies = auth()->user()->companies()
            ->whereNotNull('company_user.approved_at')
            ->with('vehicles.axles')
            ->get();

        $vehicles = $companies->flatMap->vehicles->sortBy('targa');

        return view('citizen.vehicles.index', compact('vehicles'));
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', Vehicle::class);

        $companies = auth()->user()->companies()
            ->whereNotNull('company_user.approved_at')
            ->get();

        if ($companies->isEmpty()) {
            return redirect()->route('my.vehicles.index')
                ->with('error', 'Nessuna delega aziendale approvata. Richiedi prima una delega.');
        }

        return view('citizen.vehicles.create', compact('companies'));
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        /** @var array<int, array<string, mixed>> $axles */
        $axles = $request->validated('axles');

        $vehicle = Vehicle::create(array_merge(
            $request->safe()->except('axles'),
            ['numero_assi' => count($axles)]
        ));

        $vehicle->axles()->createMany($axles);

        return redirect()->route('my.vehicles.show', $vehicle)
            ->with('success', 'Veicolo aggiunto al garage.');
    }

    public function show(Vehicle $vehicle): View
    {
        $this->authorize('view', $vehicle);
        $vehicle->load('axles', 'company');

        return view('citizen.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorize('update', $vehicle);
        $vehicle->load('axles', 'company');

        $companies = auth()->user()->companies()
            ->whereNotNull('company_user.approved_at')
            ->get();

        return view('citizen.vehicles.edit', compact('vehicle', 'companies'));
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        /** @var array<int, array<string, mixed>> $axles */
        $axles = $request->validated('axles');

        $vehicle->update(array_merge(
            $request->safe()->except('axles'),
            ['numero_assi' => count($axles)]
        ));

        $vehicle->axles()->delete();
        $vehicle->axles()->createMany($axles);

        return redirect()->route('my.vehicles.show', $vehicle)
            ->with('success', 'Veicolo aggiornato.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);
        $vehicle->delete();

        return redirect()->route('my.vehicles.index')
            ->with('success', 'Veicolo rimosso dal garage.');
    }
}

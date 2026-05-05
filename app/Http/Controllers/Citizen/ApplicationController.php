<?php

declare(strict_types=1);

namespace App\Http\Controllers\Citizen;

use App\Contracts\ApplicationServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreApplicationRequest;
use App\Http\Requests\Admin\UpdateApplicationRequest;
use App\Models\Application;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class ApplicationController extends Controller
{
    public function __construct(
        private readonly ApplicationServiceInterface $applicationService,
    ) {}

    public function index(): View
    {
        $this->authorize('viewAny', Application::class);

        $applications = Application::query()
            ->where('user_id', auth()->id())
            ->with(['company', 'vehicle'])
            ->latest()
            ->paginate(20);

        return view('citizen.applications.index', compact('applications'));
    }

    public function create(): View|RedirectResponse
    {
        $this->authorize('create', Application::class);

        $user = auth()->user();
        $companies = $user->companies()->whereNotNull('company_user.approved_at')->get();

        if ($companies->isEmpty()) {
            return redirect()->route('my.delegations.index')
                ->with('error', 'Nessuna delega aziendale approvata. Richiedi prima una delega.');
        }

        $companyIds = $companies->pluck('id');
        $vehicles = Vehicle::query()->whereIn('company_id', $companyIds)->get();

        return view('citizen.applications.create', compact('companies', 'vehicles'));
    }

    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $application = Application::create(array_merge(
            $request->validated(),
            ['user_id' => auth()->id()],
        ));

        $this->applicationService->submit($application);

        return redirect()->route('my.applications.show', $application)
            ->with('success', 'Istanza inviata con successo.');
    }

    public function show(Application $application): View
    {
        $this->authorize('view', $application);
        $application->load(['company', 'vehicle.axles', 'route', 'clearances.entity', 'trips']);

        return view('citizen.applications.show', compact('application'));
    }

    public function edit(Application $application): View
    {
        $this->authorize('update', $application);
        $application->load(['company', 'vehicle', 'route']);

        $user = auth()->user();
        $companies = $user->companies()->whereNotNull('company_user.approved_at')->get();
        $companyIds = $companies->pluck('id');
        $vehicles = Vehicle::query()->whereIn('company_id', $companyIds)->get();

        return view('citizen.applications.edit', compact('application', 'companies', 'vehicles'));
    }

    public function update(UpdateApplicationRequest $request, Application $application): RedirectResponse
    {
        $this->authorize('update', $application);

        $application->update($request->validated());

        return redirect()->route('my.applications.show', $application)
            ->with('success', 'Istanza aggiornata.');
    }
}

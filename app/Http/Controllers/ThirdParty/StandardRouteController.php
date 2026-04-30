<?php

declare(strict_types=1);

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\StoreStandardRouteRequest;
use App\Http\Requests\ThirdParty\UpdateStandardRouteRequest;
use App\Models\Entity;
use App\Models\StandardRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class StandardRouteController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', StandardRoute::class);

        $user = auth()->user();
        $query = StandardRoute::query()->with('entity')->latest();

        if ($user->isThirdParty() && $user->entity_id) {
            $query->where('entity_id', $user->entity_id);
        }

        $standardRoutes = $query->paginate(20);

        return view('third-party.standard-routes.index', compact('standardRoutes'));
    }

    public function create(): View
    {
        $this->authorize('create', StandardRoute::class);

        $user = auth()->user();

        if ($user->isThirdParty()) {
            $entities = Entity::query()->where('id', $user->entity_id)->get();
        } else {
            $entities = Entity::query()->orderBy('nome')->get();
        }

        return view('third-party.standard-routes.create', compact('entities'));
    }

    public function store(StoreStandardRouteRequest $request): RedirectResponse
    {
        $this->authorize('create', StandardRoute::class);

        $data = $request->validated();
        $wkt = $data['geometry'];

        $user = auth()->user();
        if ($user->isThirdParty() && (int) $data['entity_id'] !== $user->entity_id) {
            abort(403);
        }

        DB::statement(
            'INSERT INTO standard_routes (entity_id, nome, geometry, vehicle_types, max_massa_kg, max_lunghezza_mm, max_larghezza_mm, max_altezza_mm, active, note, created_at, updated_at) VALUES (?, ?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['entity_id'],
                $data['nome'],
                $wkt,
                json_encode($data['vehicle_types']),
                $data['max_massa_kg'] ?? null,
                $data['max_lunghezza_mm'] ?? null,
                $data['max_larghezza_mm'] ?? null,
                $data['max_altezza_mm'] ?? null,
                isset($data['active']) ? (int) $data['active'] : 1,
                $data['note'] ?? null,
            ]
        );

        return redirect()->route('third-party.standard-routes.index')
            ->with('success', 'Strada standard creata.');
    }

    public function show(StandardRoute $standardRoute): View
    {
        $this->authorize('view', $standardRoute);

        $standardRoute->load('entity');

        return view('third-party.standard-routes.show', compact('standardRoute'));
    }

    public function edit(StandardRoute $standardRoute): View
    {
        $this->authorize('update', $standardRoute);

        $user = auth()->user();

        if ($user->isThirdParty()) {
            $entities = Entity::query()->where('id', $user->entity_id)->get();
        } else {
            $entities = Entity::query()->orderBy('nome')->get();
        }

        return view('third-party.standard-routes.edit', compact('standardRoute', 'entities'));
    }

    public function update(UpdateStandardRouteRequest $request, StandardRoute $standardRoute): RedirectResponse
    {
        $this->authorize('update', $standardRoute);

        $data = $request->validated();
        $wkt = $data['geometry'];

        $user = auth()->user();
        if ($user->isThirdParty() && (int) $data['entity_id'] !== $user->entity_id) {
            abort(403);
        }

        DB::statement(
            'UPDATE standard_routes SET entity_id = ?, nome = ?, geometry = ST_GeomFromText(?, 4326), vehicle_types = ?, max_massa_kg = ?, max_lunghezza_mm = ?, max_larghezza_mm = ?, max_altezza_mm = ?, active = ?, note = ?, updated_at = NOW() WHERE id = ?',
            [
                $data['entity_id'],
                $data['nome'],
                $wkt,
                json_encode($data['vehicle_types']),
                $data['max_massa_kg'] ?? null,
                $data['max_lunghezza_mm'] ?? null,
                $data['max_larghezza_mm'] ?? null,
                $data['max_altezza_mm'] ?? null,
                isset($data['active']) ? (int) $data['active'] : 1,
                $data['note'] ?? null,
                $standardRoute->id,
            ]
        );

        return redirect()->route('third-party.standard-routes.index')
            ->with('success', 'Strada standard aggiornata.');
    }

    public function destroy(StandardRoute $standardRoute): RedirectResponse
    {
        $this->authorize('delete', $standardRoute);

        $standardRoute->delete();

        return redirect()->route('third-party.standard-routes.index')
            ->with('success', 'Strada standard eliminata.');
    }
}

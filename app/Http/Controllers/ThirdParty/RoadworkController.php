<?php

declare(strict_types=1);

namespace App\Http\Controllers\ThirdParty;

use App\Http\Controllers\Controller;
use App\Http\Requests\ThirdParty\StoreRoadworkRequest;
use App\Http\Requests\ThirdParty\UpdateRoadworkRequest;
use App\Models\Entity;
use App\Models\Roadwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class RoadworkController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Roadwork::class);

        $user = auth()->user();
        $query = Roadwork::query()->with('entity')->latest();

        if ($user->isThirdParty() && $user->entity_id) {
            $query->where('entity_id', $user->entity_id);
        }

        $roadworks = $query->paginate(20);

        return view('third-party.roadworks.index', compact('roadworks'));
    }

    public function create(): View
    {
        $this->authorize('create', Roadwork::class);

        $user = auth()->user();

        if ($user->isThirdParty()) {
            $entities = Entity::query()->where('id', $user->entity_id)->get();
        } else {
            $entities = Entity::query()->orderBy('nome')->get();
        }

        return view('third-party.roadworks.create', compact('entities'));
    }

    public function store(StoreRoadworkRequest $request): RedirectResponse
    {
        $this->authorize('create', Roadwork::class);

        $data = $request->validated();
        $wkt = $data['geometry'];

        $user = auth()->user();
        if ($user->isThirdParty() && (int) $data['entity_id'] !== $user->entity_id) {
            abort(403);
        }

        DB::statement(
            'INSERT INTO roadworks (entity_id, title, geometry, valid_from, valid_to, severity, status, note, created_at, updated_at) VALUES (?, ?, ST_GeomFromText(?, 4326), ?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $data['entity_id'],
                $data['title'],
                $wkt,
                $data['valid_from'],
                $data['valid_to'] ?? null,
                $data['severity'],
                $data['status'],
                $data['note'] ?? null,
            ]
        );

        return redirect()->route('third-party.roadworks.index')
            ->with('success', 'Cantiere creato.');
    }

    public function show(Roadwork $roadwork): View
    {
        $this->authorize('view', $roadwork);

        $roadwork->load('entity');

        return view('third-party.roadworks.show', compact('roadwork'));
    }

    public function edit(Roadwork $roadwork): View
    {
        $this->authorize('update', $roadwork);

        $user = auth()->user();

        if ($user->isThirdParty()) {
            $entities = Entity::query()->where('id', $user->entity_id)->get();
        } else {
            $entities = Entity::query()->orderBy('nome')->get();
        }

        return view('third-party.roadworks.edit', compact('roadwork', 'entities'));
    }

    public function update(UpdateRoadworkRequest $request, Roadwork $roadwork): RedirectResponse
    {
        $this->authorize('update', $roadwork);

        $data = $request->validated();
        $wkt = $data['geometry'];

        DB::statement(
            'UPDATE roadworks SET entity_id = ?, title = ?, geometry = ST_GeomFromText(?, 4326), valid_from = ?, valid_to = ?, severity = ?, status = ?, note = ?, updated_at = NOW() WHERE id = ?',
            [
                $data['entity_id'],
                $data['title'],
                $wkt,
                $data['valid_from'],
                $data['valid_to'] ?? null,
                $data['severity'],
                $data['status'],
                $data['note'] ?? null,
                $roadwork->id,
            ]
        );

        return redirect()->route('third-party.roadworks.index')
            ->with('success', 'Cantiere aggiornato.');
    }

    public function destroy(Roadwork $roadwork): RedirectResponse
    {
        $this->authorize('delete', $roadwork);

        $roadwork->delete();

        return redirect()->route('third-party.roadworks.index')
            ->with('success', 'Cantiere eliminato.');
    }
}

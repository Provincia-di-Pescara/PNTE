<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\EntityType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEntityRequest;
use App\Http\Requests\Admin\UpdateEntityRequest;
use App\Models\Entity;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class EntityController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Entity::class);

        $entities = Entity::query()
            ->orderBy('tipo')
            ->orderBy('nome')
            ->paginate(50);

        return view('admin.entities.index', compact('entities'));
    }

    public function create(): View
    {
        $this->authorize('create', Entity::class);

        $types = EntityType::cases();

        return view('admin.entities.form', ['entity' => new Entity, 'types' => $types]);
    }

    public function store(StoreEntityRequest $request): RedirectResponse
    {
        Entity::create($request->validated());

        return redirect()->route('admin.entities.index')
            ->with('success', 'Ente creato con successo.');
    }

    public function show(Entity $entity): View
    {
        $this->authorize('view', $entity);

        return view('admin.entities.show', compact('entity'));
    }

    public function edit(Entity $entity): View
    {
        $this->authorize('update', $entity);

        $types = EntityType::cases();

        return view('admin.entities.form', compact('entity', 'types'));
    }

    public function update(UpdateEntityRequest $request, Entity $entity): RedirectResponse
    {
        $entity->update($request->validated());

        return redirect()->route('admin.entities.show', $entity)
            ->with('success', 'Ente aggiornato.');
    }

    public function destroy(Entity $entity): RedirectResponse
    {
        $this->authorize('delete', $entity);

        $entity->delete();

        return redirect()->route('admin.entities.index')
            ->with('success', 'Ente eliminato.');
    }
}

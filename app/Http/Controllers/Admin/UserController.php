<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateUserRoleRequest;
use App\Models\Entity;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class UserController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdmin->value), 403);

        $users = User::query()
            ->with(['roles', 'entity'])
            ->orderBy('name')
            ->paginate(25);

        return view('admin.settings.users.index', compact('users'));
    }

    public function show(Request $request, User $user): View
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdmin->value), 403);

        $user->load(['roles', 'entity', 'companies']);

        $entities = Entity::query()->orderBy('nome')->get(['id', 'nome', 'tipo']);
        $roles = UserRole::cases();

        return view('admin.settings.users.show', compact('user', 'entities', 'roles'));
    }

    public function updateRole(UpdateUserRoleRequest $request, User $user): RedirectResponse
    {
        abort_if($user->hasRole(UserRole::SuperAdmin->value), 403);

        $user->syncRoles([$request->input('role')]);

        return redirect()->route('admin.settings.users.show', $user)
            ->with('success', 'Ruolo aggiornato.');
    }

    public function updateEntity(Request $request, User $user): RedirectResponse
    {
        abort_unless($request->user()->hasRole(UserRole::SuperAdmin->value), 403);

        $request->validate([
            'entity_id' => ['nullable', 'integer', 'exists:entities,id'],
        ]);

        $user->update(['entity_id' => $request->input('entity_id')]);

        return redirect()->route('admin.settings.users.show', $user)
            ->with('success', 'Ente aggiornato.');
    }
}

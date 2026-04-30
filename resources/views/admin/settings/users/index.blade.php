@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.settings.index') }}" class="text-ink-3 hover:text-ink transition-colors">
            <x-icon name="chevron" size="14" class="rotate-180" />
        </a>
        <div>
            <h1 class="text-xl font-bold tracking-tight">Gestione utenti</h1>
            <p class="text-sm text-ink-2 mt-0.5">{{ $users->total() }} utenti registrati.</p>
        </div>
    </div>
</div>

@if(session('info'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-amber-50 text-amber-700 text-[13px] font-medium border border-amber-200">{{ session('info') }}</div>
@endif

<div class="card overflow-hidden">
    <table class="w-full text-[13px]">
        <thead>
            <tr class="border-b border-line bg-surface-2">
                <th class="text-left px-4 py-3 font-semibold text-ink-2 text-xs uppercase tracking-wider">Utente</th>
                <th class="text-left px-4 py-3 font-semibold text-ink-2 text-xs uppercase tracking-wider">Ruolo</th>
                <th class="text-left px-4 py-3 font-semibold text-ink-2 text-xs uppercase tracking-wider hidden sm:table-cell">Ente</th>
                <th class="text-left px-4 py-3 font-semibold text-ink-2 text-xs uppercase tracking-wider hidden md:table-cell">Accesso</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @forelse($users as $user)
            <tr class="hover:bg-surface-2/50 transition-colors">
                <td class="px-4 py-3">
                    <div class="font-medium text-ink">{{ $user->name }}</div>
                    <div class="text-ink-3 text-[11px]">{{ $user->email ?? $user->codice_fiscale }}</div>
                </td>
                <td class="px-4 py-3">
                    @foreach($user->roles as $role)
                    <x-chip tone="{{ match($role->name) {
                        'super-admin' => 'danger',
                        'operator' => 'warning',
                        'third-party' => 'info',
                        'citizen' => 'neutral',
                        'law-enforcement' => 'success',
                        default => 'neutral',
                    } }}">{{ $role->name }}</x-chip>
                    @endforeach
                </td>
                <td class="px-4 py-3 hidden sm:table-cell text-ink-2">
                    {{ $user->entity?->nome ?? '—' }}
                </td>
                <td class="px-4 py-3 hidden md:table-cell text-ink-3 text-[11px]">
                    {{ $user->auth_provider?->value ?? 'local' }}
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('admin.settings.users.show', $user) }}" class="btn btn-sm">
                            Gestisci
                        </a>
                        @if($user->canBeImpersonated())
                        <form method="POST" action="{{ route('admin.users.impersonate', $user) }}">
                            @csrf
                            <button type="submit" class="btn btn-sm" title="Impersona utente">
                                <x-icon name="user" size="13" />
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-ink-3">Nessun utente trovato.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $users->links() }}
</div>
@endsection

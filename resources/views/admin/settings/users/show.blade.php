@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.settings.users.index') }}" class="text-ink-3 hover:text-ink transition-colors">
        <x-icon name="chevron" size="14" class="rotate-180" />
    </a>
    <div>
        <h1 class="text-xl font-bold tracking-tight">{{ $user->name }}</h1>
        <p class="text-sm text-ink-2 mt-0.5">{{ $user->email ?? $user->codice_fiscale }}</p>
    </div>
    @if($user->canBeImpersonated())
    <form method="POST" action="{{ route('admin.users.impersonate', $user) }}" class="ml-auto">
        @csrf
        <button type="submit" class="btn">
            <x-icon name="user" size="14" /> Impersona
        </button>
    </form>
    @endif
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    {{-- Ruolo --}}
    <div class="card p-5">
        <h2 class="text-sm font-semibold mb-4">Ruolo</h2>
        <form method="POST" action="{{ route('admin.settings.users.role', $user) }}" class="flex items-end gap-3">
            @csrf
            @method('PATCH')
            <div class="flex-1">
                <label for="role" class="block text-xs font-semibold text-ink-2 mb-1.5">Ruolo assegnato</label>
                <select id="role" name="role"
                        class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors"
                        {{ $user->hasRole('super-admin') ? 'disabled' : '' }}>
                    @foreach($roles as $role)
                    <option value="{{ $role->value }}" @selected($user->hasRole($role->value))>{{ $role->value }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary h-9 shrink-0" {{ $user->hasRole('super-admin') ? 'disabled' : '' }}>
                Salva
            </button>
        </form>
        @if($user->hasRole('super-admin'))
        <p class="mt-2 text-[11px] text-ink-3">Il ruolo super-admin non può essere modificato da qui.</p>
        @endif
    </div>

    {{-- Ente --}}
    <div class="card p-5">
        <h2 class="text-sm font-semibold mb-4">Ente territoriale</h2>
        <form method="POST" action="{{ route('admin.settings.users.entity', $user) }}" class="flex items-end gap-3">
            @csrf
            @method('PATCH')
            <div class="flex-1">
                <label for="entity_id" class="block text-xs font-semibold text-ink-2 mb-1.5">Ente assegnato</label>
                <select id="entity_id" name="entity_id"
                        class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    <option value="">— nessuno —</option>
                    @foreach($entities as $entity)
                    <option value="{{ $entity->id }}" @selected($user->entity_id === $entity->id)>
                        {{ $entity->nome }} ({{ $entity->tipo }})
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary h-9 shrink-0">Salva</button>
        </form>
        <p class="mt-2 text-[11px] text-ink-3">Necessario per utenti con ruolo third-party.</p>
    </div>

    {{-- Info --}}
    <div class="card p-5">
        <h2 class="text-sm font-semibold mb-4">Informazioni</h2>
        <dl class="space-y-2 text-[13px]">
            <div class="flex gap-3">
                <dt class="text-ink-3 w-32 shrink-0">Provider auth</dt>
                <dd class="text-ink font-medium">{{ $user->auth_provider?->value ?? 'local' }}</dd>
            </div>
            @if($user->codice_fiscale)
            <div class="flex gap-3">
                <dt class="text-ink-3 w-32 shrink-0">Codice fiscale</dt>
                <dd class="text-ink font-mono">{{ $user->codice_fiscale }}</dd>
            </div>
            @endif
            @if($user->nome_verificato)
            <div class="flex gap-3">
                <dt class="text-ink-3 w-32 shrink-0">Nome verificato</dt>
                <dd class="text-ink">{{ $user->nome_verificato }} {{ $user->cognome_verificato }}</dd>
            </div>
            @endif
            <div class="flex gap-3">
                <dt class="text-ink-3 w-32 shrink-0">Aziende</dt>
                <dd class="text-ink">{{ $user->companies->count() }}</dd>
            </div>
        </dl>
    </div>
</div>
@endsection

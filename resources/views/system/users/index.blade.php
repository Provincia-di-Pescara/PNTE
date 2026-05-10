@extends('layouts.system')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold">Utenti di sistema</h1>
            <p class="text-sm text-ink-2 mt-0.5">Account con ruolo <code class="text-xs bg-surface-2 px-1 rounded">system-admin</code>. Login locale, nessun accesso ai dati delle pratiche.</p>
        </div>
        <button x-data @click="$dispatch('open-modal', 'create-system-user')"
                class="btn btn-primary btn-sm flex items-center gap-1.5">
            <x-icon name="plus" size="14" /> Nuovo utente
        </button>
    </div>

    @if(session('success'))
        <x-alert tone="success">{{ session('success') }}</x-alert>
    @endif

    @if(session('temp_password'))
        <x-alert tone="warning">
            <strong>Password temporanea:</strong>
            <code class="ml-1 font-mono">{{ session('temp_password') }}</code>
            — Annotarla subito, non verrà mostrata di nuovo.
        </x-alert>
    @endif

    <div class="bg-surface border border-line rounded-lg divide-y divide-line">
        @forelse($users as $user)
        <div class="flex items-center gap-4 px-4 py-3">
            <x-avatar :name="$user->name" tone="amber" />
            <div class="flex-1 min-w-0">
                <div class="text-sm font-medium truncate">{{ $user->name }}</div>
                <div class="text-xs text-ink-2 truncate">{{ $user->email }}</div>
            </div>
            <div class="flex items-center gap-2">
                <form method="POST" action="{{ route('system.users.reset-password', $user) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            onclick="return confirm('Reimpostare la password di {{ $user->name }}?')"
                            class="btn btn-sm btn-ghost text-xs">
                        Reset password
                    </button>
                </form>
                @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('system.users.disable', $user) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            onclick="return confirm('Disabilitare {{ $user->name }}?')"
                            class="btn btn-sm btn-ghost text-red-600 text-xs">
                        Disabilita
                    </button>
                </form>
                @endif
            </div>
        </div>
        @empty
        <div class="px-4 py-8 text-center text-sm text-ink-2">Nessun utente di sistema trovato.</div>
        @endforelse
    </div>

    {{ $users->links() }}
</div>

{{-- Create system user modal --}}
<x-modal name="create-system-user" title="Nuovo utente di sistema">
    <form method="POST" action="{{ route('system.users.store') }}" class="space-y-4">
        @csrf
        <x-form.field label="Nome completo" name="name" required />
        <x-form.field label="Email" name="email" type="email" required />
        <x-form.field label="Password" name="password" type="password" required
                      hint="Minimo 12 caratteri." />
        <x-form.field label="Conferma password" name="password_confirmation" type="password" required />
        <div class="flex justify-end gap-2 pt-2">
            <button type="button" x-on:click="$dispatch('close-modal', 'create-system-user')"
                    class="btn btn-ghost btn-sm">Annulla</button>
            <button type="submit" class="btn btn-primary btn-sm">Crea utente</button>
        </div>
    </form>
</x-modal>
@endsection

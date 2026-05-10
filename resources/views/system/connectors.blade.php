@extends('layouts.system')

@section('content')
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Vault & connettori</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Vault connettori</h1>
            <p class="text-xs text-ink-3 mt-0.5">Credenziali e certificati delle integrazioni PA. Le chiavi non sono mai mostrate in chiaro.</p>
        </div>
        <div class="flex-1"></div>
        <button class="btn"><x-icon name="refresh" size="12" /> Verifica tutte</button>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-primary"><x-icon name="plus" size="12" /> Impostazioni</a>
    </div>

    {{-- Credential cards --}}
    <div class="space-y-3">
        @foreach($connectors as $c)
        <div class="card p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg border border-line flex items-center justify-center shrink-0
                        {{ $c['configured'] ? 'bg-accent-bg text-accent-ink' : 'bg-surface-2 text-ink-3' }}">
                <x-icon name="layers" size="18" />
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-[13.5px] font-semibold">{{ $c['name'] }}</span>
                    <x-chip tone="{{ $c['configured'] ? 'success' : 'default' }}" dot="true">
                        {{ $c['configured'] ? 'configurato' : 'non configurato' }}
                    </x-chip>
                </div>
                <div class="text-[11.5px] text-ink-3 mt-0.5">{{ $c['org'] }} · {{ $c['type'] }}</div>
            </div>

            <a href="{{ route($c['route']) }}" class="btn btn-sm">Configura</a>
        </div>
        @endforeach
    </div>

    <p class="text-xs text-ink-3 px-1">
        Le credenziali di bootstrap (chiavi private, token PDND) vanno nelle variabili d'ambiente — mai nel database.
        Usa le impostazioni qui sopra per i parametri pubblici (host, client ID, issuer URL).
    </p>

</div>
@endsection

@extends('layouts.system-sidebar')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Tenant</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Tenant · enti aderenti</h1>
            <p class="text-xs text-ink-3 mt-0.5">Il system-admin abilita o disabilita il tenant. Nessun accesso ai dati di pratica.</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        <div class="grid text-[10.5px] text-ink-3 uppercase tracking-[0.08em] font-medium bg-surface-2 border-b border-line"
             style="grid-template-columns: 130px 1fr 100px 80px 120px 100px; padding: 10px 16px;">
            <div>Cod. ISTAT</div>
            <div>Denominazione</div>
            <div>Ruolo</div>
            <div>Utenti</div>
            <div>Abilitazione</div>
            <div></div>
        </div>

        @forelse($tenants as $tenant)
            <div class="grid items-center text-[12.5px] border-b border-line last:border-0 row-hover"
                 style="grid-template-columns: 130px 1fr 100px 80px 120px 100px; padding: 12px 16px;">
                <div class="mono text-ink-3">{{ $tenant['codice_istat'] ?: '—' }}</div>
                <div class="font-medium">{{ $tenant['nome'] }}</div>
                <div>
                    @if($tenant['is_capofila'])
                        <x-chip tone="amber">capofila</x-chip>
                    @else
                        <x-chip>ente</x-chip>
                    @endif
                </div>
                <div class="num">{{ number_format($tenant['users']) }}</div>
                <div>
                    <x-chip tone="{{ $tenant['is_tenant'] ? 'success' : 'default' }}" dot="true">
                        {{ $tenant['is_tenant'] ? 'abilitato' : 'disabilitato' }}
                    </x-chip>
                </div>
                <form method="POST" action="{{ route('system.tenants.toggle', $tenant['id']) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $tenant['is_tenant'] ? 'btn-ghost' : 'btn-primary' }}">
                        {{ $tenant['is_tenant'] ? 'Disabilita' : 'Abilita' }}
                    </button>
                </form>
            </div>
        @empty
            <div class="px-4 py-8 text-sm text-ink-2 text-center">Nessun ente disponibile.</div>
        @endforelse
    </div>
</div>
@endsection

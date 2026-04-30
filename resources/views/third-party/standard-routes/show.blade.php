@extends('layouts.third-party')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
            <a href="{{ route('third-party.standard-routes.index') }}" class="hover:text-ink transition-colors">Strade Standard</a>
            <span class="mx-1">/</span>
            <span>Dettaglio</span>
        </nav>
        <h1 class="text-xl font-bold tracking-tight">{{ $standardRoute->nome }}</h1>
    </div>
    @can('update', $standardRoute)
    <a href="{{ route('third-party.standard-routes.edit', $standardRoute) }}" class="btn btn-primary">Modifica</a>
    @endcan
</div>

<div class="card p-6 max-w-2xl space-y-4">
    <dl class="grid grid-cols-2 gap-x-8 gap-y-4 text-[13px]">
        <div>
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Ente</dt>
            <dd class="text-ink">{{ $standardRoute->entity->nome }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Stato</dt>
            <dd>
                <x-chip :tone="$standardRoute->active ? 'success' : 'default'" dot="true">{{ $standardRoute->active ? 'Attiva' : 'Inattiva' }}</x-chip>
            </dd>
        </div>
        <div class="col-span-2">
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Tipi di veicolo</dt>
            <dd class="flex flex-wrap gap-2">
                @foreach($standardRoute->vehicle_types as $vt)
                <x-chip>{{ \App\Enums\VehicleType::from($vt)->label() }}</x-chip>
                @endforeach
            </dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Massa max</dt>
            <dd class="text-ink">{{ $standardRoute->max_massa_kg ? number_format($standardRoute->max_massa_kg).' kg' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Lunghezza max</dt>
            <dd class="text-ink">{{ $standardRoute->max_lunghezza_mm ? number_format($standardRoute->max_lunghezza_mm).' mm' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Larghezza max</dt>
            <dd class="text-ink">{{ $standardRoute->max_larghezza_mm ? number_format($standardRoute->max_larghezza_mm).' mm' : '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Altezza max</dt>
            <dd class="text-ink">{{ $standardRoute->max_altezza_mm ? number_format($standardRoute->max_altezza_mm).' mm' : '—' }}</dd>
        </div>
        @if($standardRoute->note)
        <div class="col-span-2">
            <dt class="text-xs font-semibold text-ink-3 uppercase tracking-wider mb-1">Note</dt>
            <dd class="text-ink">{{ $standardRoute->note }}</dd>
        </div>
        @endif
    </dl>
</div>
@endsection

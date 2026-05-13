@extends('layouts.citizen')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            @auth
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">
                {{ auth()->user()->companies->first()?->ragione_sociale ?? auth()->user()->name }}
            </div>
            @endauth
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Le mie pratiche</h1>
        </div>
        <div class="flex-1"></div>
        <a href="{{ route('my.delegations.index') }}" class="btn">
            <x-icon name="user" size="12" /> Deleghe
        </a>
        <a href="{{ route('my.applications.create') }}" class="btn btn-primary">
            <x-icon name="plus" size="12" /> Nuova domanda
        </a>
    </div>

    {{-- KPI Grid --}}
    <div class="grid grid-cols-4 gap-3">
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">In corso</div>
            <div class="num text-[24px] font-semibold mt-1">{{ $activeCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Approvate (totali)</div>
            <div class="num text-[24px] font-semibold mt-1">{{ $approvedCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Mezzi in garage</div>
            <div class="num text-[24px] font-semibold mt-1">{{ $vehicleCount ?? 0 }}</div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">{{ $delegationCount ?? 0 }} {{ ($delegationCount ?? 0) === 1 ? 'azienda' : 'aziende' }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Percorsi salvati</div>
            <div class="num text-[24px] font-semibold mt-1">{{ $routeCount ?? 0 }}</div>
        </div>
    </div>

    <div class="grid grid-cols-[1.3fr_1fr] gap-4">
        {{-- Pratiche recenti --}}
        <div class="card overflow-hidden">
            <div class="px-4 py-3 border-b border-line">
                <div class="text-[13px] font-semibold">Pratiche recenti</div>
            </div>

            @if(($recentApplications ?? collect())->isEmpty())
            <div class="py-12 flex flex-col items-center justify-center text-center">
                <div class="w-10 h-10 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-3">
                    <x-icon name="doc" size="20" stroke="1.5" />
                </div>
                <p class="text-sm font-semibold">Nessuna pratica presentata</p>
                <p class="text-xs text-ink-2 mt-1">
                    <a href="{{ route('my.applications.create') }}" class="text-accent hover:underline">Crea la tua prima richiesta →</a>
                </p>
            </div>
            @else
            @foreach($recentApplications as $i => $app)
            <a href="{{ route('my.applications.show', $app) }}"
               class="flex items-center gap-3 px-4 py-3.5 {{ $i < count($recentApplications) - 1 ? 'border-b border-line' : '' }} row-hover cursor-pointer">
                <div class="w-9 h-9 rounded-lg bg-surface-2 border border-line flex items-center justify-center text-ink-2 shrink-0">
                    <x-icon name="truck" size="16" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="mono text-[11px] text-ink-3">{{ sprintf('PNTE-%04d', $app->id) }}</span>
                        <x-status-pill :state="$app->stato->value" />
                    </div>
                    <div class="text-[13px] font-medium mt-0.5">
                        {{ $app->company?->ragione_sociale ?? 'Pratica #'.$app->id }}
                    </div>
                    <div class="text-[11.5px] text-ink-3 mt-0.5">
                        {{ $app->vehicle?->targa ?? '—' }}
                        @if($app->route)
                            · {{ number_format($app->route->distance_km ?? 0, 1) }} km
                        @endif
                        · {{ ($app->selected_entity_ids ? count($app->selected_entity_ids) : 0) }} enti
                    </div>
                </div>
                <div class="text-right shrink-0">
                    @if($app->wear_calculation && isset($app->wear_calculation['total']))
                    <div class="num text-[13px] font-semibold">€ {{ number_format($app->wear_calculation['total'], 2) }}</div>
                    @else
                    <div class="text-[13px] text-ink-3">—</div>
                    @endif
                    <div class="text-[11px] text-ink-3">{{ $app->created_at->format('d M Y') }}</div>
                </div>
            </a>
            @endforeach
            <div class="px-4 py-2.5 border-t border-line">
                <a href="{{ route('my.applications.index') }}" class="text-[12px] text-accent hover:underline">Tutte le pratiche →</a>
            </div>
            @endif
        </div>

        {{-- Garage Virtuale --}}
        <div class="card flex flex-col overflow-hidden">
            <div class="px-4 py-3 border-b border-line flex items-center">
                <div class="text-[13px] font-semibold">Garage Virtuale</div>
                <div class="flex-1"></div>
                <a href="{{ route('my.vehicles.create') }}" class="btn btn-sm">
                    <x-icon name="plus" size="11" /> Aggiungi
                </a>
            </div>

            @if(($recentVehicles ?? collect())->isEmpty())
            <div class="flex-1 flex flex-col items-center justify-center py-10 text-center">
                <div class="w-10 h-10 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-3">
                    <x-icon name="truck" size="20" stroke="1.5" />
                </div>
                <p class="text-sm font-semibold">Nessun veicolo</p>
                <p class="text-xs text-ink-2 mt-1">
                    <a href="{{ route('my.vehicles.create') }}" class="text-accent hover:underline">Aggiungi il primo mezzo →</a>
                </p>
            </div>
            @else
            <div class="flex-1 overflow-auto">
                @foreach($recentVehicles as $i => $vehicle)
                <a href="{{ route('my.vehicles.show', $vehicle) }}"
                   class="flex items-center gap-3 px-4 py-3 {{ $i < count($recentVehicles) - 1 ? 'border-b border-line' : '' }} row-hover">
                    <div class="w-8 h-8 rounded-lg bg-surface-2 border border-line flex items-center justify-center text-ink-2 shrink-0">
                        <x-icon name="{{ $vehicle->tipo?->value === 'trailer' ? 'layers' : 'truck' }}" size="14" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-[12.5px] font-semibold truncate">{{ $vehicle->marca }} {{ $vehicle->modello }}</div>
                        <div class="text-[11px] text-ink-3 flex gap-2">
                            <span>{{ $vehicle->tipo?->label() ?? '—' }}</span>
                            <span class="mono">{{ $vehicle->targa }}</span>
                            @if($vehicle->axles->count() > 0)
                            · <span>{{ $vehicle->axles->count() }} assi</span>
                            @endif
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="px-4 py-2.5 border-t border-line">
                <a href="{{ route('my.vehicles.index') }}" class="text-[12px] text-accent hover:underline">Tutti i mezzi →</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Quick links --}}
    <div class="grid grid-cols-3 gap-3">
        <a href="{{ route('my.routes.create') }}" class="card p-4 flex items-center gap-3 hover:border-accent transition-colors group">
            <div class="w-9 h-9 rounded-lg bg-accent-bg flex items-center justify-center text-accent-ink shrink-0 group-hover:bg-accent group-hover:text-white transition-colors">
                <x-icon name="map" size="16" />
            </div>
            <div>
                <div class="text-[13px] font-semibold">Nuovo percorso</div>
                <div class="text-[11px] text-ink-3">Calcola itinerario con OSRM</div>
            </div>
        </a>
        <a href="{{ route('my.vehicles.index') }}" class="card p-4 flex items-center gap-3 hover:border-accent transition-colors group">
            <div class="w-9 h-9 rounded-lg bg-surface-2 flex items-center justify-center text-ink-2 shrink-0 group-hover:text-accent transition-colors">
                <x-icon name="axles" size="16" />
            </div>
            <div>
                <div class="text-[13px] font-semibold">Garage Virtuale</div>
                <div class="text-[11px] text-ink-3">Gestione flotta veicoli</div>
            </div>
        </a>
        <a href="{{ route('my.delegations.index') }}" class="card p-4 flex items-center gap-3 hover:border-accent transition-colors group">
            <div class="w-9 h-9 rounded-lg bg-surface-2 flex items-center justify-center text-ink-2 shrink-0 group-hover:text-accent transition-colors">
                <x-icon name="users" size="16" />
            </div>
            <div>
                <div class="text-[13px] font-semibold">Deleghe</div>
                <div class="text-[11px] text-ink-3">Aziende rappresentate</div>
            </div>
        </a>
    </div>
</div>
@endsection

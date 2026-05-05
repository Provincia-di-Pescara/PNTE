@extends('layouts.third-party')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            @if($entity ?? null)
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">{{ $entity->nome }} · {{ $entity->tipo?->label() }}</div>
            @endif
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Pareri e Nulla Osta</h1>
        </div>
        <div class="flex-1"></div>
        <a href="{{ route('third-party.roadworks.create') }}" class="btn btn-primary">
            <x-icon name="plus" size="12" /> Nuovo cantiere
        </a>
    </div>

    {{-- KPI Grid --}}
    <div class="grid grid-cols-4 gap-3">
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Pareri in attesa</div>
            <div class="num text-[24px] font-semibold mt-1 {{ ($pendingClearancesCount ?? 0) > 0 ? 'text-accent-ink' : 'text-ink' }}">
                {{ $pendingClearancesCount ?? 0 }}
            </div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">nulla osta da rilasciare</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Cantieri attivi</div>
            <div class="num text-[24px] font-semibold mt-1 {{ ($activeRoadworkCount ?? 0) > 0 ? 'text-accent-ink' : 'text-ink' }}">
                {{ $activeRoadworkCount ?? 0 }}
            </div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">su {{ $roadworkCount ?? 0 }} totali</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Strade ARS</div>
            <div class="num text-[24px] font-semibold mt-1">{{ $standardRouteCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Ente</div>
            @if($entity ?? null)
            <div class="text-[13px] font-semibold mt-1 truncate">{{ $entity->nome }}</div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">{{ $entity->codice_istat ?? '—' }}</div>
            @else
            <div class="text-[13px] text-ink-3 mt-1">—</div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-[1.3fr_1fr] gap-4">
        {{-- Clearances inbox --}}
        <div class="card overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-line flex items-center gap-2">
                <div class="text-[13px] font-semibold">Pareri da rilasciare</div>
                @if(($pendingClearancesCount ?? 0) > 0)
                <x-chip tone="amber" :dot="true">{{ $pendingClearancesCount }}</x-chip>
                @endif
            </div>

            @if(($pendingClearances ?? collect())->isEmpty())
            <div class="flex-1 flex flex-col items-center justify-center py-12 text-center">
                <div class="w-10 h-10 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-3">
                    <x-icon name="check" size="20" stroke="1.5" />
                </div>
                <p class="text-sm font-semibold text-success">Nessun parere in attesa</p>
                <p class="text-xs text-ink-2 mt-1">Tutte le richieste di nulla osta sono state evase.</p>
            </div>
            @else
            <div class="flex-1 overflow-auto">
                @foreach($pendingClearances as $i => $clearance)
                <div class="flex items-center gap-3 px-4 py-3.5 {{ $i < count($pendingClearances) - 1 ? 'border-b border-line' : '' }} row-hover">
                    <div class="w-8 h-8 rounded-lg bg-accent-bg flex items-center justify-center text-accent-ink shrink-0">
                        <x-icon name="doc" size="14" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="mono text-[11px] text-ink-3">
                                {{ sprintf('GTE-%04d', $clearance->application_id) }}
                            </span>
                            <x-chip tone="amber" :dot="true">In attesa</x-chip>
                        </div>
                        <div class="text-[13px] font-medium mt-0.5">
                            {{ $clearance->application?->company?->ragione_sociale ?? 'Azienda non nota' }}
                        </div>
                        <div class="text-[11.5px] text-ink-3 mt-0.5">
                            {{ $clearance->application?->vehicle?->targa ?? '—' }}
                            @if($clearance->application?->route)
                                · {{ number_format($clearance->application->route->distance_km ?? 0, 1) }} km
                            @endif
                            · ricevuto {{ $clearance->created_at->diffForHumans() }}
                        </div>
                    </div>
                    <a href="{{ route('third-party.clearances.show', $clearance) }}" class="btn btn-sm btn-primary">
                        Esamina
                    </a>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Right column --}}
        <div class="space-y-4">
            {{-- Cantieri attivi --}}
            <div class="card overflow-hidden">
                <div class="px-4 py-3 border-b border-line flex items-center justify-between">
                    <div class="text-[13px] font-semibold">Cantieri attivi</div>
                    <a href="{{ route('third-party.roadworks.index') }}" class="text-xs text-accent hover:underline">Tutti →</a>
                </div>

                @if(($activeRoadworks ?? collect())->isEmpty())
                <div class="py-6 flex flex-col items-center text-center text-ink-3">
                    <x-icon name="cone" size="18" stroke="1.5" />
                    <p class="text-xs mt-2">Nessun cantiere attivo</p>
                </div>
                @else
                <div>
                    @foreach($activeRoadworks as $i => $rw)
                    <div class="flex items-center gap-3 px-4 py-2.5 {{ $i < count($activeRoadworks) - 1 ? 'border-b border-line' : '' }}">
                        <div class="w-2 h-2 rounded-full bg-accent shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-[12.5px] font-medium truncate">{{ $rw->title ?? 'Cantiere #'.$rw->id }}</div>
                            <div class="text-[10.5px] text-ink-3">
                                {{ $rw->valid_from?->format('d/m/Y') }} – {{ $rw->valid_to?->format('d/m/Y') ?? 'indefinito' }}
                            </div>
                        </div>
                        <a href="{{ route('third-party.roadworks.show', $rw) }}" class="text-ink-3 hover:text-accent">
                            <x-icon name="arrow" size="12" />
                        </a>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Scheda ente --}}
            @if($entity ?? null)
            <div class="card p-4">
                <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase mb-3">Scheda ente</div>
                <dl class="space-y-2 text-[12.5px]">
                    <div class="flex gap-3">
                        <dt class="text-ink-3 w-16 shrink-0">Tipo</dt>
                        <dd>{{ $entity->tipo?->label() }}</dd>
                    </div>
                    @if($entity->codice_istat)
                    <div class="flex gap-3">
                        <dt class="text-ink-3 w-16 shrink-0">ISTAT</dt>
                        <dd class="mono">{{ $entity->codice_istat }}</dd>
                    </div>
                    @endif
                    @if($entity->pec)
                    <div class="flex gap-3">
                        <dt class="text-ink-3 w-16 shrink-0">PEC</dt>
                        <dd class="truncate text-[11.5px]">{{ $entity->pec }}</dd>
                    </div>
                    @endif
                    <div class="flex gap-3">
                        <dt class="text-ink-3 w-16 shrink-0">Geometria</dt>
                        <dd>
                            @if($entity->geom)
                                <x-chip tone="success" :dot="true">Presente</x-chip>
                            @else
                                <x-chip tone="amber" :dot="true">Assente</x-chip>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
            @endif

            {{-- Quick links --}}
            <div class="card p-4">
                <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase mb-3">Azioni rapide</div>
                <div class="space-y-2">
                    <a href="{{ route('third-party.roadworks.create') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-7 h-7 rounded bg-accent-bg flex items-center justify-center text-accent-ink group-hover:bg-accent group-hover:text-white transition-colors">
                            <x-icon name="plus" size="13" />
                        </div>
                        <div>
                            <div class="text-[12.5px] font-semibold">Nuovo cantiere</div>
                            <div class="text-[10.5px] text-ink-2">Segnala chiusura stradale</div>
                        </div>
                    </a>
                    <a href="{{ route('third-party.standard-routes.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-7 h-7 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent transition-colors">
                            <x-icon name="map" size="13" />
                        </div>
                        <div>
                            <div class="text-[12.5px] font-semibold">Strade ARS</div>
                            <div class="text-[10.5px] text-ink-2">Archivio regionale strade</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

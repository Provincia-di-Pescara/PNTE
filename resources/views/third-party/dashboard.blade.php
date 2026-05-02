@extends('layouts.third-party')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight">Cruscotto Ente</h1>
            <p class="text-sm text-ink-2 mt-1">Gestione pareri e cantieri stradali</p>
        </div>
        <a href="{{ route('third-party.roadworks.create') }}" class="btn btn-primary">
            <x-icon name="plus" size="14" /> Nuovo cantiere
        </a>
    </div>

    <!-- KPI Grid -->
    <div class="grid grid-cols-4 gap-4">
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Cantieri totali</div>
            <div class="text-2xl font-bold mt-1 num">{{ $roadworkCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Cantieri attivi</div>
            <div class="text-2xl font-bold mt-1 num text-amber-500">{{ $activeRoadworkCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Strade ARS</div>
            <div class="text-2xl font-bold mt-1 num">{{ $standardRouteCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Pareri da rilasciare</div>
            <div class="text-2xl font-bold mt-1 num">0</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <div class="col-span-2 space-y-6">
            @if(($activeRoadworks ?? collect())->isNotEmpty())
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold">Cantieri attivi</h3>
                    <a href="{{ route('third-party.roadworks.index') }}" class="text-xs text-accent hover:underline">Tutti →</a>
                </div>
                <div class="divide-y divide-line">
                    @foreach($activeRoadworks as $rw)
                    <div class="flex items-center gap-3 py-2.5">
                        <div class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-medium truncate">{{ $rw->title ?? 'Cantiere #'.$rw->id }}</div>
                            <div class="text-[10px] text-ink-3">
                                {{ $rw->valid_from?->format('d/m/Y') }} – {{ $rw->valid_to?->format('d/m/Y') ?? 'indefinito' }}
                            </div>
                        </div>
                        <a href="{{ route('third-party.roadworks.show', $rw) }}" class="text-ink-3 hover:text-accent">
                            <x-icon name="arrow-right" size="12" />
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <div class="card p-6 border border-dashed border-line-2 bg-surface flex flex-col items-center justify-center text-center py-12">
                <div class="w-12 h-12 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-4">
                    <x-icon name="doc" size="24" stroke="1.5" />
                </div>
                <h3 class="text-sm font-semibold">Pareri (Nulla Osta) · In arrivo con v0.5.x</h3>
                <p class="text-xs text-ink-2 mt-1 max-w-sm">Le richieste di nulla osta per i transiti sul tuo territorio appariranno qui.</p>
            </div>
        </div>
        
        <div class="col-span-1 space-y-6">
            @if($entity ?? null)
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-3">Scheda ente</h3>
                <div class="space-y-2 text-xs">
                    <div class="flex items-start gap-2">
                        <span class="text-ink-3 w-20 shrink-0">Nome</span>
                        <span class="font-medium">{{ $entity->nome }}</span>
                    </div>
                    <div class="flex items-start gap-2">
                        <span class="text-ink-3 w-20 shrink-0">Tipo</span>
                        <span>{{ $entity->tipo?->label() }}</span>
                    </div>
                    @if($entity->codice_istat)
                    <div class="flex items-start gap-2">
                        <span class="text-ink-3 w-20 shrink-0">ISTAT</span>
                        <span class="font-mono">{{ $entity->codice_istat }}</span>
                    </div>
                    @endif
                    @if($entity->pec)
                    <div class="flex items-start gap-2">
                        <span class="text-ink-3 w-20 shrink-0">PEC</span>
                        <span class="truncate">{{ $entity->pec }}</span>
                    </div>
                    @endif
                    <div class="flex items-start gap-2">
                        <span class="text-ink-3 w-20 shrink-0">Geometria</span>
                        @if($entity->geom)
                            <span class="text-green-600 font-medium">✓ Presente</span>
                        @else
                            <span class="text-amber-500 font-medium">✗ Assente</span>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Azioni rapide</h3>
                <div class="space-y-2">
                    <a href="{{ route('third-party.roadworks.create') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-accent/40 hover:border-accent transition-colors bg-accent/5 group">
                        <div class="w-8 h-8 rounded bg-accent/10 flex items-center justify-center text-accent">
                            <x-icon name="plus" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Nuovo cantiere</div>
                            <div class="text-[10px] text-ink-2">Segnala chiusura stradale</div>
                        </div>
                    </a>
                    <a href="{{ route('third-party.roadworks.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="cone" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">I tuoi cantieri</div>
                            <div class="text-[10px] text-ink-2">Gestione chiusure stradali</div>
                        </div>
                    </a>
                    <a href="{{ route('third-party.standard-routes.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="road" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Strade ARS</div>
                            <div class="text-[10px] text-ink-2">Archivio regionale strade</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

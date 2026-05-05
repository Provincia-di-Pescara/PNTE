@extends('layouts.law-enforcement')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Forze dell'Ordine · Verifica</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Verifica trasporti</h1>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3 max-w-lg">
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Cantieri attivi</div>
            <div class="num text-[24px] font-semibold mt-1 {{ ($activeRoadworkCount ?? 0) > 0 ? 'text-accent-ink' : 'text-ink' }}">
                {{ $activeRoadworkCount ?? 0 }}
            </div>
        </div>
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">Strade ARS censite</div>
            <div class="num text-[24px] font-semibold mt-1">{{ $arsRouteCount ?? 0 }}</div>
        </div>
    </div>

    {{-- Search --}}
    <div class="card p-5 max-w-2xl" x-data="{ plate: '', loading: false }">
        <label class="block text-[11px] text-ink-3 uppercase tracking-[0.08em] font-medium mb-2">
            Targa veicolo
        </label>
        <div class="flex gap-3">
            <input type="text"
                   x-model="plate"
                   placeholder="Es. AB 123 CD"
                   @keydown.enter="$refs.searchBtn.click()"
                   class="flex-1 h-12 px-4 rounded-lg border border-line-2 bg-bg text-xl font-mono uppercase tracking-widest focus:border-accent focus:ring-1 focus:ring-accent outline-none transition-all">
            <a href="{{ route('law-enforcement.radar.index') }}"
               x-ref="searchBtn"
               :href="plate ? '{{ route('law-enforcement.radar.index') }}?plate=' + plate : '{{ route('law-enforcement.radar.index') }}'"
               class="btn btn-primary h-12 px-8 text-base">
                <x-icon name="search" size="18" /> Cerca
            </a>
        </div>
        <p class="text-[11px] text-ink-3 mt-2">Inserisci la targa per verificare autorizzazione, validità e prescrizioni.</p>
    </div>

    {{-- QR scan --}}
    <div class="card p-5 max-w-2xl">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-surface-2 rounded-lg flex items-center justify-center text-ink-2">
                <x-icon name="qr" size="20" />
            </div>
            <div>
                <div class="text-[13px] font-semibold">Scansione QR code</div>
                <div class="text-[11.5px] text-ink-3">Scansiona il QR sull'autorizzazione stampata per la verifica immediata.</div>
            </div>
            <div class="flex-1"></div>
            <a href="{{ route('law-enforcement.radar.index') }}" class="btn btn-sm">
                <x-icon name="qr" size="12" /> Scansiona
            </a>
        </div>
    </div>

    {{-- Cantieri attivi --}}
    @if(($activeRoadworks ?? collect())->isNotEmpty())
    <div class="card overflow-hidden max-w-2xl">
        <div class="px-4 py-3 border-b border-line flex items-center justify-between">
            <div class="text-[13px] font-semibold">Cantieri attivi sul territorio</div>
            <x-chip tone="amber" :dot="true">{{ ($activeRoadworks ?? collect())->count() }}</x-chip>
        </div>
        @foreach($activeRoadworks as $i => $rw)
        <div class="flex items-center gap-3 px-4 py-3 {{ $i < count($activeRoadworks) - 1 ? 'border-b border-line' : '' }}">
            <div class="w-2 h-2 rounded-full bg-accent shrink-0"></div>
            <div class="flex-1 min-w-0">
                <div class="text-[12.5px] font-medium truncate">{{ $rw->title ?? 'Cantiere #'.$rw->id }}</div>
                <div class="text-[10.5px] text-ink-3">
                    {{ $rw->valid_from?->format('d/m/Y') }} – {{ $rw->valid_to?->format('d/m/Y') ?? 'indefinito' }}
                    @if($rw->entity)· {{ $rw->entity->nome }}@endif
                </div>
            </div>
            <x-chip :tone="match($rw->severity?->value ?? '') {
                'closed' => 'danger',
                'restricted' => 'amber',
                default => 'default',
            }">{{ $rw->severity?->label() ?? 'Attivo' }}</x-chip>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

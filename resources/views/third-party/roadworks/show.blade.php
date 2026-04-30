@extends('layouts.third-party')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('third-party.roadworks.index') }}" class="hover:text-ink transition-colors">Cantieri</a>
        <span class="mx-1">/</span>
        <span>{{ $roadwork->title }}</span>
    </nav>
    <div class="flex items-start justify-between">
        <h1 class="text-xl font-bold tracking-tight">{{ $roadwork->title }}</h1>
        <div class="flex items-center gap-2">
            @can('update', $roadwork)
            <a href="{{ route('third-party.roadworks.edit', $roadwork) }}" class="btn">
                Modifica
            </a>
            @endcan
        </div>
    </div>
</div>

<div class="card p-6 max-w-2xl">
    <dl class="grid grid-cols-2 gap-4">
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Ente</dt>
            <dd class="text-[13px] font-medium text-ink">{{ $roadwork->entity->nome }}</dd>
        </div>
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Gravità</dt>
            <dd class="mt-0.5">
                @php
                    $sevTone = match($roadwork->severity) {
                        \App\Enums\RoadworkSeverity::Closed => 'danger',
                        \App\Enums\RoadworkSeverity::Restricted => 'amber',
                        \App\Enums\RoadworkSeverity::Information => 'info',
                        default => 'default'
                    };
                @endphp
                <x-chip :tone="$sevTone">{{ $roadwork->severity->label() }}</x-chip>
            </dd>
        </div>
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Data inizio</dt>
            <dd class="text-[13px] font-mono font-medium text-ink">{{ $roadwork->valid_from->format('d/m/Y') }}</dd>
        </div>
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Data fine</dt>
            <dd class="text-[13px] font-mono text-ink-2">{{ $roadwork->valid_to?->format('d/m/Y') ?? '—' }}</dd>
        </div>
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Stato</dt>
            <dd class="mt-0.5">
                @php
                    $stTone = match($roadwork->status) {
                        \App\Enums\RoadworkStatus::Active => 'success',
                        \App\Enums\RoadworkStatus::Closed => 'default',
                        \App\Enums\RoadworkStatus::Planned => 'amber',
                        default => 'default'
                    };
                @endphp
                <x-chip :tone="$stTone" dot="true">{{ $roadwork->status->label() }}</x-chip>
            </dd>
        </div>
    </dl>
    
    @if($roadwork->note)
    <div class="pt-4 mt-4 border-t border-line">
        <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Note</dt>
        <dd class="text-[13px] text-ink-2">{{ $roadwork->note }}</dd>
    </div>
    @endif
</div>
@endsection

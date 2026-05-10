@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6">
    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Diagnostica</div>
        <h1 class="text-[22px] font-semibold mt-1">Cache & queue</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Stato live di Redis, queue jobs e failed jobs. Esegui i singoli test per verificare connessione e tempi.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-system.health-card service="redis" label="Redis" icon="clock" />
        <x-system.health-card service="queue" label="Queue" icon="clock" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="card p-4">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Cache driver</div>
            <div class="text-[20px] font-semibold mt-1 mono">{{ $cacheDriver }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Queue driver</div>
            <div class="text-[20px] font-semibold mt-1 mono">{{ $queueDriver }}</div>
            <div class="text-[11.5px] text-ink-3 mt-1">{{ $queueSize }} job in coda</div>
        </div>
        <div class="card p-4 {{ $failed > 0 ? 'border-danger' : '' }}">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Failed jobs</div>
            <div class="text-[20px] font-semibold mt-1 num {{ $failed > 0 ? 'text-danger' : '' }}">{{ $failed }}</div>
        </div>
    </div>

    @if($failedRows->isNotEmpty())
        <div>
            <div class="flex items-center gap-3 mb-3">
                <div class="text-[11px] text-ink-3 tracking-widest uppercase font-semibold">Ultimi 10 failed</div>
                <div class="flex-1 h-px bg-line"></div>
            </div>
            <div class="card overflow-hidden">
                <div class="grid grid-cols-[160px_120px_140px_1fr] px-4 py-2 bg-surface-2 border-b border-line text-[10.5px] text-ink-3 uppercase font-semibold tracking-wider">
                    <div>Failed at</div><div>Connection</div><div>Queue</div><div>UUID</div>
                </div>
                @foreach($failedRows as $row)
                    <div class="grid grid-cols-[160px_120px_140px_1fr] px-4 py-2.5 items-center text-[12px] {{ ! $loop->last ? 'border-b border-line' : '' }}">
                        <span class="mono text-ink-3 text-[11px]">{{ $row->failed_at }}</span>
                        <span class="mono">{{ $row->connection }}</span>
                        <span class="mono">{{ $row->queue }}</span>
                        <span class="mono text-ink-3 truncate">{{ $row->uuid ?? '—' }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection

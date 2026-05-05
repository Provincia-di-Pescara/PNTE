@extends('layouts.system-sidebar')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Audit infra</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Audit infrastruttura</h1>
            <p class="text-xs text-ink-3 mt-0.5">Eventi di livello sistema: toggle tenant, test SMTP, azioni operative.</p>
        </div>
    </div>

    <div class="card overflow-hidden">
        @forelse($events as $event)
            <div class="grid items-center text-[12.5px] border-b border-line last:border-0"
                 style="grid-template-columns: 170px 180px 180px 1fr; padding: 10px 16px;">
                <div class="mono text-ink-3">{{ optional($event->created_at)->format('d M Y · H:i') }}</div>
                <div class="font-medium">{{ $event->actor_name }}</div>
                <div class="mono">{{ $event->action }}</div>
                <div class="text-ink-2">{{ $event->detail }}</div>
            </div>
        @empty
            <div class="px-4 py-8 text-sm text-ink-2 text-center">Nessun evento di audit disponibile.</div>
        @endforelse
    </div>
</div>
@endsection

@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6">
    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Connettori & API</div>
        <h1 class="text-[22px] font-semibold mt-1">Integrazioni esterne</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Configurazione e test delle integrazioni di sistema verso identity provider, PDND, PagoPA, AINOP, mail e PEC.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($schema as $key => $cfg)
            <a href="{{ route('system.integrations.show', ['service' => $key]) }}"
               class="card p-4 no-underline text-ink hover:bg-surface-2 transition-colors flex items-start gap-3">
                <div class="w-10 h-10 rounded-lg bg-accent-bg text-accent-ink border border-line flex items-center justify-center shrink-0">
                    <x-icon name="{{ $cfg['icon'] }}" size="16" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-[13.5px] font-semibold">{{ $cfg['label'] }}</h3>
                    <p class="text-[11.5px] text-ink-3 mt-1 leading-relaxed line-clamp-2">{{ $cfg['doc'] }}</p>
                    <div class="text-[10.5px] text-ink-3 mono mt-2">test: <span class="text-ink-2">{{ $cfg['diagnostic_key'] }}</span></div>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection

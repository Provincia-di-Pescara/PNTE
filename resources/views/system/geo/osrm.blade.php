@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6">
    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Dataset geo</div>
        <h1 class="text-[22px] font-semibold mt-1">OSRM routing engine</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Verifica il motore di routing self-hosted OSRM. Endpoint configurato:
            <code class="mono text-[11.5px] text-ink-2">{{ $baseUrl ?: 'non configurato' }}</code>
        </p>
    </div>

    <div class="card p-5 max-w-3xl space-y-4">
        <h2 class="text-[14px] font-semibold">Diagnostic OSRM</h2>
        <p class="text-[12px] text-ink-3">Esegue una richiesta route Pescara → L'Aquila. Verifica versione, tempi e validità della risposta.</p>
        <x-system.test-runner method="GET" :endpoint="$healthEndpoint" label="Test connessione OSRM" />
    </div>

    <div class="card p-5 max-w-3xl space-y-4">
        <h2 class="text-[14px] font-semibold">Test pipeline routing</h2>
        <p class="text-[12px] text-ink-3">
            Esegue snap+breakdown completo: OSRM → WKT LINESTRING → ST_Intersects → entity_id km.
            Personalizza i waypoint qui sotto.
        </p>
        <x-system.test-runner
            method="POST"
            :endpoint="$routingEndpoint"
            :payload="['from' => ['lat' => 42.4647, 'lng' => 14.2156], 'to' => ['lat' => 42.3498, 'lng' => 13.3995]]"
            label="Esegui pipeline" />
    </div>
</div>
@endsection

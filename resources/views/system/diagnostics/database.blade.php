@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6">
    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Diagnostica</div>
        <h1 class="text-[22px] font-semibold mt-1">Database & PostGIS</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Verifica connessione PostgreSQL, versione, presenza estensione PostGIS e una query
            spaziale di sample (<code class="mono text-[11px]">ST_Buffer</code> su SRID 4326).
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <x-system.health-card service="db" label="PostgreSQL" icon="doc" />
        <x-system.health-card service="postgis" label="PostGIS" icon="map" />
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div class="card p-4">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Connection</div>
            <div class="text-[20px] font-semibold mt-1 mono">{{ $connection }}</div>
        </div>
        <div class="card p-4">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Driver</div>
            <div class="text-[20px] font-semibold mt-1 mono">{{ $driver }}</div>
        </div>
    </div>
</div>
@endsection

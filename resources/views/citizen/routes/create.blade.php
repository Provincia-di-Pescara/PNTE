@extends('layouts.citizen')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="#" class="hover:text-ink transition-colors">Percorsi</a>
        <span class="mx-1">/</span>
        <span>Nuovo</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">Traccia percorso</h1>
    <p class="text-sm text-ink-2 mt-1">Clicca sulla mappa per aggiungere punti del percorso.</p>
</div>

<form method="POST" action="{{ route('my.routes.store') }}" id="route-form">
    @csrf
    <input type="hidden" name="waypoints" id="input-waypoints">
    <input type="hidden" name="geometry" id="input-geometry">
    <input type="hidden" name="distance_km" id="input-distance-km">

    <div class="card overflow-hidden">
        <div id="map" class="w-full bg-surface-2" style="height: 500px;"></div>
    </div>

    <div class="mt-4 flex items-center gap-3">
        <button type="submit" id="btn-submit" class="btn btn-primary">
            Salva percorso
        </button>
        <button type="button" id="btn-clear" class="btn">
            Azzera
        </button>
    </div>
    @error('geometry')<p class="mt-2 text-[11px] text-danger">{{ $message }}</p>@enderror
    @error('waypoints')<p class="mt-2 text-[11px] text-danger">{{ $message }}</p>@enderror
</form>
@endsection

@push('scripts')
@vite('resources/js/route-builder.js')
@endpush

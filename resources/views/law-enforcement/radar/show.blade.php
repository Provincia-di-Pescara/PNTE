@extends('layouts.law-enforcement')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Autorizzazione {{ sprintf('GTE-%06d', $application->id) }}</h1>
        <p class="text-sm text-ink-2 mt-1">Verifica trasporto eccezionale</p>
    </div>
    <span class="badge badge-green text-sm px-3 py-1">APPROVATA</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card p-6">
        <h2 class="text-base font-semibold mb-4">Trasportatore</h2>
        <dl class="space-y-3 text-sm">
            <dt class="text-ink-2">Azienda</dt>
            <dd class="font-medium text-base">{{ $application->company?->ragione_sociale ?? '—' }}</dd>

            <dt class="text-ink-2">P.IVA</dt>
            <dd class="font-mono">{{ $application->company?->partita_iva ?? '—' }}</dd>
        </dl>
    </div>

    <div class="card p-6">
        <h2 class="text-base font-semibold mb-4">Veicolo</h2>
        <dl class="space-y-3 text-sm">
            <dt class="text-ink-2">Targa</dt>
            <dd class="font-mono font-bold text-xl">{{ $application->vehicle?->targa ?? '—' }}</dd>

            <dt class="text-ink-2">Tipo</dt>
            <dd>{{ $application->vehicle?->tipo?->label() ?? '—' }}</dd>

            <dt class="text-ink-2">Massa complessiva</dt>
            <dd class="font-mono">{{ $application->vehicle ? number_format($application->vehicle->massa_complessiva / 1000, 1) . ' t' : '—' }}</dd>
        </dl>
    </div>

    <div class="card p-6">
        <h2 class="text-base font-semibold mb-4">Autorizzazione</h2>
        <dl class="space-y-3 text-sm">
            <dt class="text-ink-2">Protocollo</dt>
            <dd class="font-mono font-bold">{{ sprintf('GTE-%06d', $application->id) }}</dd>

            <dt class="text-ink-2">Tipo istanza</dt>
            <dd>{{ $application->tipo_istanza->label() }}</dd>

            <dt class="text-ink-2">Validità</dt>
            <dd class="font-mono font-semibold">{{ $application->valida_da->format('d/m/Y') }} → {{ $application->valida_fino->format('d/m/Y') }}</dd>

            @if($application->numero_viaggi)
            <dt class="text-ink-2">Viaggi autorizzati / effettuati</dt>
            <dd>{{ $application->numero_viaggi }} / {{ $application->viaggi_effettuati }}</dd>
            @endif
        </dl>
    </div>

    @if($application->trips->isNotEmpty())
    <div class="card p-6">
        <h2 class="text-base font-semibold mb-4">Viaggi in corso</h2>
        @foreach($application->trips->where('stato', \App\Enums\TripStatus::Active) as $trip)
        <div class="flex items-center justify-between text-sm py-2 border-b border-line last:border-0">
            <span class="text-ink-2">Avviato alle</span>
            <span class="font-mono font-semibold">{{ $trip->started_at?->format('H:i d/m/Y') ?? '—' }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>

<div class="mt-6">
    <a href="{{ route('law-enforcement.radar.index') }}" class="btn btn-ghost">← Torna al radar</a>
</div>
@endsection

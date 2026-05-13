@extends('layouts.citizen')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Istanza #{{ $application->id }}</h1>
        <p class="text-sm text-ink-2 mt-1">{{ $application->tipo_istanza->label() }}</p>
    </div>
    <div class="flex items-center gap-3">
        <x-status-pill :state="$application->stato->value" />
        @if($application->stato === \App\Enums\ApplicationStatus::Draft)
        <a href="{{ route('my.applications.edit', $application) }}" class="btn btn-ghost">Modifica</a>
        @endif
        @if($application->stato === \App\Enums\ApplicationStatus::Approved && !$application->isSospesa())
        @php $viaggiRimanenti = $application->viaggiRimanenti(); @endphp
        @if($viaggiRimanenti !== 0)
        <form method="POST" action="{{ route('my.applications.trips.store', $application) }}">
            @csrf
            <button type="submit" class="btn btn-primary">Avvia viaggio</button>
        </form>
        @endif
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        {{-- Dati trasporto --}}
        <div class="card p-6">
            <h2 class="text-base font-semibold mb-4">Dati trasporto</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-ink-2">Azienda</dt>
                <dd class="font-medium">{{ $application->company?->ragione_sociale ?? '—' }}</dd>

                <dt class="text-ink-2">Veicolo</dt>
                <dd class="font-mono font-semibold">{{ $application->vehicle?->targa ?? '—' }}</dd>

                <dt class="text-ink-2">Tipo</dt>
                <dd>{{ $application->tipo_istanza->label() }}</dd>

                <dt class="text-ink-2">Validità</dt>
                <dd>{{ $application->valida_da->format('d/m/Y') }} → {{ $application->valida_fino->format('d/m/Y') }}</dd>

                @if($application->numero_viaggi)
                <dt class="text-ink-2">Viaggi autorizzati</dt>
                <dd>{{ $application->numero_viaggi }}</dd>

                <dt class="text-ink-2">Viaggi effettuati</dt>
                <dd>{{ $application->viaggi_effettuati }}</dd>
                @endif

                @if($application->note)
                <dt class="text-ink-2">Note</dt>
                <dd class="col-span-1">{{ $application->note }}</dd>
                @endif
            </dl>
        </div>

        {{-- Nulla osta --}}
        @if($application->clearances->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-line">
                <h2 class="text-base font-semibold">Nulla osta enti</h2>
            </div>
            <table class="w-full text-[13px]">
                <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
                    <tr>
                        <th class="px-5 py-3 font-medium text-left">Ente</th>
                        <th class="px-5 py-3 font-medium text-center">Stato</th>
                        <th class="px-5 py-3 font-medium text-left">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($application->clearances as $clearance)
                    <tr>
                        <td class="px-5 py-3">{{ $clearance->entity->nome }}</td>
                        <td class="px-5 py-3 text-center">
                            <x-chip :tone="match($clearance->stato->value) {
                                'approved','pre_cleared' => 'success',
                                'rejected' => 'danger',
                                default => 'amber',
                            }" :dot="true">{{ $clearance->stato->label() }}</x-chip>
                        </td>
                        <td class="px-5 py-3 text-ink-2">{{ $clearance->note ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Viaggi --}}
        @if($application->trips->isNotEmpty())
        <div class="card overflow-hidden">
            <div class="px-6 py-4 border-b border-line">
                <h2 class="text-base font-semibold">Storico viaggi</h2>
            </div>
            <table class="w-full text-[13px]">
                <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
                    <tr>
                        <th class="px-5 py-3 font-medium text-left">#</th>
                        <th class="px-5 py-3 font-medium text-left">Inizio</th>
                        <th class="px-5 py-3 font-medium text-left">Fine</th>
                        <th class="px-5 py-3 font-medium text-center">Stato</th>
                        <th class="px-5 py-3 font-medium text-right">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($application->trips as $trip)
                    <tr>
                        <td class="px-5 py-3 font-mono text-xs text-ink-2">#{{ $trip->id }}</td>
                        <td class="px-5 py-3 font-mono">{{ $trip->started_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3 font-mono text-ink-2">{{ $trip->ended_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="px-5 py-3 text-center">
                            <x-chip :tone="$trip->stato === \App\Enums\TripStatus::Active ? 'success' : 'default'" :dot="true">
                                {{ $trip->stato->label() }}
                            </x-chip>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($trip->isActive())
                            <form method="POST" action="{{ route('my.trips.end', $trip) }}" class="inline">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-ghost text-danger">Termina</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <div class="space-y-4">
        <div class="card p-5">
            <h3 class="text-[11px] text-ink-3 tracking-[0.08em] uppercase mb-3">Macchina a stati</h3>
            @php
            $stateOrder = ['draft','submitted','waiting_clearances','waiting_payment','approved','rejected'];
            $currentIdx = array_search($application->stato->value, $stateOrder);
            @endphp
            <div class="relative pl-5 space-y-3">
                <div class="absolute left-[9px] top-1 bottom-1 w-px bg-line"></div>
                @foreach(\App\Enums\ApplicationStatus::cases() as $i => $s)
                @php $sIdx = array_search($s->value, $stateOrder); @endphp
                <div class="relative">
                    <div class="absolute -left-5 top-1 w-3 h-3 rounded-full
                        {{ $application->stato === $s ? 'bg-accent ring-4 ring-accent-bg' : ($sIdx < $currentIdx ? 'bg-ink' : 'bg-surface-2 border border-line-2') }}"></div>
                    <div class="text-[13px] {{ $application->stato === $s ? 'font-semibold text-ink' : ($sIdx < $currentIdx ? 'text-ink-2 font-medium' : 'text-ink-3 font-medium') }}">
                        {{ $s->label() }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h3 class="text-sm font-semibold mb-3">Riferimento</h3>
            <dl class="text-sm space-y-2">
                <dt class="text-ink-2">Protocollo</dt>
                <dd class="font-mono text-xs">{{ sprintf('PNTE-%06d', $application->id) }}</dd>
                <dt class="text-ink-2 mt-2">Presentata il</dt>
                <dd>{{ $application->created_at->format('d/m/Y') }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection

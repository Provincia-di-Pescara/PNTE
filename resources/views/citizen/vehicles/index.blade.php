@extends('layouts.citizen')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Miei Veicoli</h1>
        <p class="text-sm text-ink-2 mt-1">Veicoli registrati per le tue aziende delegate.</p>
    </div>
    @can('create', \App\Models\Vehicle::class)
    <a href="{{ route('my.vehicles.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Aggiungi veicolo
    </a>
    @endcan
</div>

<div class="card overflow-hidden">
    @if($vehicles->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="truck" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun veicolo registrato</p>
        <p class="text-xs text-ink-2 mt-1">Aggiungi il tuo primo veicolo al garage.</p>
        @can('create', \App\Models\Vehicle::class)
        <div class="mt-6">
            <a href="{{ route('my.vehicles.create') }}" class="btn btn-primary">
                Aggiungi veicolo
            </a>
        </div>
        @endcan
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Targa</th>
                <th class="px-5 py-3 font-medium">Tipo</th>
                <th class="px-5 py-3 font-medium">Azienda</th>
                <th class="px-5 py-3 font-medium text-center">N° Assi</th>
                <th class="px-5 py-3 font-medium text-right">PTT (kg)</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($vehicles as $vehicle)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-mono font-semibold text-ink">{{ $vehicle->targa }}</td>
                <td class="px-5 py-3">
                    @php
                        $tone = match($vehicle->tipo) {
                            \App\Enums\VehicleType::Trattore     => 'info',
                            \App\Enums\VehicleType::Rimorchio    => 'success',
                            \App\Enums\VehicleType::Semirimorchio => 'amber',
                            \App\Enums\VehicleType::MezzoDopera  => 'danger',
                            default => 'default',
                        };
                    @endphp
                    <x-chip :tone="$tone">{{ $vehicle->tipo->label() }}</x-chip>
                </td>
                <td class="px-5 py-3 text-ink-2">{{ $vehicle->company->ragione_sociale ?? '—' }}</td>
                <td class="px-5 py-3 text-center text-ink-2 font-mono">{{ $vehicle->numero_assi }}</td>
                <td class="px-5 py-3 text-right text-ink-2 font-mono">
                    {{ $vehicle->massa_complessiva ? number_format($vehicle->massa_complessiva) : '—' }}
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <a href="{{ route('my.vehicles.show', $vehicle) }}" class="btn btn-ghost btn-sm">Dettaglio</a>
                        @can('update', $vehicle)
                        <a href="{{ route('my.vehicles.edit', $vehicle) }}" class="btn btn-ghost btn-sm">Modifica</a>
                        @endcan
                        @can('delete', $vehicle)
                        <form method="POST" action="{{ route('my.vehicles.destroy', $vehicle) }}"
                              onsubmit="return confirm('Eliminare il veicolo {{ $vehicle->targa }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-danger hover:bg-danger-bg hover:text-danger">Elimina</button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection

@extends('layouts.citizen')

@section('content')
<div class="mb-6 flex items-start justify-between">
    <div>
        <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
            <a href="{{ route('my.vehicles.index') }}" class="hover:text-ink transition-colors">Veicoli</a>
            <span class="mx-1">/</span>
            <span>{{ $vehicle->targa }}</span>
        </nav>
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold tracking-tight font-mono uppercase">{{ $vehicle->targa }}</h1>
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
        </div>
        <p class="text-[13px] text-ink-2 mt-1">{{ $vehicle->marca }} {{ $vehicle->modello }}</p>
    </div>
    <div class="flex items-center gap-2">
        @can('update', $vehicle)
        <a href="{{ route('my.vehicles.edit', $vehicle) }}" class="btn">
            Modifica
        </a>
        @endcan
        @can('delete', $vehicle)
        <form method="POST" action="{{ route('my.vehicles.destroy', $vehicle) }}"
              onsubmit="return confirm('Eliminare il veicolo {{ $vehicle->targa }}? L\'operazione è irreversibile.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn text-danger hover:bg-danger-bg border-line-2 hover:border-danger/30">
                Elimina
            </button>
        </form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    {{-- Dati anagrafici --}}
    <div class="lg:col-span-2 card overflow-hidden">
        <div class="px-5 py-3 border-b border-line bg-surface-2">
            <h2 class="text-sm font-semibold">Dati veicolo</h2>
        </div>
        <div class="p-5">
            <dl class="grid grid-cols-2 gap-x-6 gap-y-4">
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Azienda</dt>
                    <dd class="text-[13px] font-medium text-ink">{{ $vehicle->company->ragione_sociale }}</dd>
                </div>
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Targa</dt>
                    <dd class="text-[13px] font-mono font-semibold text-ink">{{ $vehicle->targa }}</dd>
                </div>
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Numero telaio (VIN)</dt>
                    <dd class="text-[13px] font-mono text-ink-2">{{ $vehicle->numero_telaio ?? '—' }}</dd>
                </div>
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Marca</dt>
                    <dd class="text-[13px] text-ink-2">{{ $vehicle->marca ?? '—' }}</dd>
                </div>
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Modello</dt>
                    <dd class="text-[13px] text-ink-2">{{ $vehicle->modello ?? '—' }}</dd>
                </div>
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Anno immatricolazione</dt>
                    <dd class="text-[13px] text-ink-2">{{ $vehicle->anno_immatricolazione ?? '—' }}</dd>
                </div>
                <div class="flex flex-col">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">N° assi</dt>
                    <dd class="text-[13px] text-ink-2">{{ $vehicle->numero_assi }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Masse e dimensioni --}}
    <div class="lg:col-span-1 card overflow-hidden">
        <div class="px-5 py-3 border-b border-line bg-surface-2">
            <h2 class="text-sm font-semibold">Masse e dimensioni</h2>
        </div>
        <div class="p-5">
            <dl class="space-y-4">
                <div class="flex justify-between items-center">
                    <dt class="text-[12px] text-ink-2">Massa a vuoto</dt>
                    <dd class="text-[13px] font-mono font-medium text-ink">{{ $vehicle->massa_vuoto ? number_format($vehicle->massa_vuoto).' kg' : '—' }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-[12px] text-ink-2">PTT (massa complessiva)</dt>
                    <dd class="text-[13px] font-mono font-medium text-ink">{{ $vehicle->massa_complessiva ? number_format($vehicle->massa_complessiva).' kg' : '—' }}</dd>
                </div>
                <div class="pt-4 border-t border-line border-dashed"></div>
                <div class="flex justify-between items-center">
                    <dt class="text-[12px] text-ink-2">Lunghezza</dt>
                    <dd class="text-[13px] font-mono text-ink-2">{{ $vehicle->lunghezza ? number_format($vehicle->lunghezza).' mm' : '—' }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-[12px] text-ink-2">Larghezza</dt>
                    <dd class="text-[13px] font-mono text-ink-2">{{ $vehicle->larghezza ? number_format($vehicle->larghezza).' mm' : '—' }}</dd>
                </div>
                <div class="flex justify-between items-center">
                    <dt class="text-[12px] text-ink-2">Altezza</dt>
                    <dd class="text-[13px] font-mono text-ink-2">{{ $vehicle->altezza ? number_format($vehicle->altezza).' mm' : '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>

{{-- Configurazione assi --}}
<div class="card overflow-hidden">
    <div class="px-5 py-3 border-b border-line bg-surface-2">
        <h2 class="text-sm font-semibold">Configurazione assi</h2>
    </div>
    @if($vehicle->axles->isEmpty())
    <div class="py-12 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="plus" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun asse configurato</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium text-center w-16">N°</th>
                <th class="px-5 py-3 font-medium">Tipo</th>
                <th class="px-5 py-3 font-medium text-right">Interasse (mm)</th>
                <th class="px-5 py-3 font-medium text-right">Carico tecnico (kg)</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($vehicle->axles as $axle)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 text-center font-semibold text-ink">{{ $axle->posizione }}</td>
                <td class="px-5 py-3 text-ink-2">{{ $axle->tipo->label() }}</td>
                <td class="px-5 py-3 text-right font-mono text-ink-2">
                    {{ $axle->interasse ? number_format($axle->interasse) : '—' }}
                </td>
                <td class="px-5 py-3 text-right font-mono font-medium text-ink">
                    {{ number_format($axle->carico_tecnico) }}
                </td>
            </tr>
            @endforeach
        </tbody>
        @php
            $totalCarico = $vehicle->axles->sum('carico_tecnico');
        @endphp
        <tfoot class="bg-surface border-t border-line">
            <tr>
                <td colspan="3" class="px-5 py-3 text-right text-[11px] font-semibold text-ink-3 uppercase tracking-wider">Totale carico tecnico</td>
                <td class="px-5 py-3 text-right font-mono font-bold text-ink">{{ number_format($totalCarico) }} kg</td>
            </tr>
        </tfoot>
    </table>
    @endif
</div>
@endsection

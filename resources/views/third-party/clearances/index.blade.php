@extends('layouts.third-party')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Nulla osta in gestione</h1>
    <p class="text-sm text-ink-2 mt-1">Autorizzazioni richieste dagli operatori per il tuo ente.</p>
</div>

<div class="card overflow-hidden">
    @if($clearances->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="doc" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun nulla osta in attesa</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">#</th>
                <th class="px-5 py-3 font-medium">Istanza</th>
                <th class="px-5 py-3 font-medium">Azienda</th>
                <th class="px-5 py-3 font-medium">Veicolo</th>
                <th class="px-5 py-3 font-medium text-center">Stato</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($clearances as $clearance)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-mono text-xs text-ink-2">#{{ $clearance->id }}</td>
                <td class="px-5 py-3 text-ink">{{ sprintf('GTE-%06d', $clearance->application_id) }}</td>
                <td class="px-5 py-3 text-ink-2">{{ $clearance->application->company?->ragione_sociale ?? '—' }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $clearance->application->vehicle?->targa ?? '—' }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="badge badge-{{ $clearance->stato->color() }}">{{ $clearance->stato->label() }}</span>
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('third-party.clearances.show', $clearance) }}" class="btn btn-sm btn-ghost">Esamina</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-5 py-3 border-t border-line">
        {{ $clearances->links() }}
    </div>
    @endif
</div>
@endsection

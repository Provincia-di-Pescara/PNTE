@extends('layouts.law-enforcement')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Radar trasporti attivi</h1>
    <p class="text-sm text-ink-2 mt-1">Autorizzazioni approvate valide in data odierna — {{ now()->format('d/m/Y') }}</p>
</div>

<div class="card overflow-hidden">
    @if($applications->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="truck" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun trasporto autorizzato attivo</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Protocollo</th>
                <th class="px-5 py-3 font-medium">Azienda</th>
                <th class="px-5 py-3 font-medium">Veicolo</th>
                <th class="px-5 py-3 font-medium">Valida fino</th>
                <th class="px-5 py-3 font-medium text-center">Viaggi attivi</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($applications as $app)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-mono text-xs">{{ sprintf('PNTE-%06d', $app->id) }}</td>
                <td class="px-5 py-3 text-ink">{{ $app->company?->ragione_sociale ?? '—' }}</td>
                <td class="px-5 py-3 font-mono font-semibold">{{ $app->vehicle?->targa ?? '—' }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $app->valida_fino->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-center">
                    @if($app->trips->isNotEmpty())
                    <span class="badge badge-green">{{ $app->trips->count() }} in corso</span>
                    @else
                    <span class="text-ink-3">—</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('law-enforcement.radar.show', $app) }}" class="btn btn-sm btn-ghost">Dettaglio</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-5 py-3 border-t border-line">
        {{ $applications->links() }}
    </div>
    @endif
</div>
@endsection

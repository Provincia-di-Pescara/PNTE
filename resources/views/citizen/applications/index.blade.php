@extends('layouts.citizen')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Le mie istanze</h1>
        <p class="text-sm text-ink-2 mt-1">Richieste di autorizzazione al trasporto eccezionale.</p>
    </div>
    @can('create', \App\Models\Application::class)
    <a href="{{ route('my.applications.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Nuova istanza
    </a>
    @endcan
</div>

<div class="card overflow-hidden">
    @if($applications->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="doc" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessuna istanza presentata</p>
        <p class="text-xs text-ink-2 mt-1">Crea la tua prima richiesta di autorizzazione.</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">#</th>
                <th class="px-5 py-3 font-medium">Tipo</th>
                <th class="px-5 py-3 font-medium">Azienda</th>
                <th class="px-5 py-3 font-medium">Veicolo</th>
                <th class="px-5 py-3 font-medium">Valida dal</th>
                <th class="px-5 py-3 font-medium">Valida fino</th>
                <th class="px-5 py-3 font-medium text-center">Stato</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($applications as $app)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-mono text-ink-2 text-xs">#{{ $app->id }}</td>
                <td class="px-5 py-3 text-ink">{{ $app->tipo_istanza->label() }}</td>
                <td class="px-5 py-3 text-ink-2">{{ $app->company?->ragione_sociale ?? '—' }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $app->vehicle?->targa ?? '—' }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $app->valida_da->format('d/m/Y') }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $app->valida_fino->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-center">
                    <span class="badge badge-{{ $app->stato->color() }}">{{ $app->stato->label() }}</span>
                </td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('my.applications.show', $app) }}" class="btn btn-sm btn-ghost">Dettaglio</a>
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

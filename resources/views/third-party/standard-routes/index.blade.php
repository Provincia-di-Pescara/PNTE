@extends('layouts.third-party')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Strade Standard (ARS)</h1>
        <p class="text-sm text-ink-2 mt-1">Archivio Regionale delle Strade — percorsi pre-approvati per trasporti eccezionali.</p>
    </div>
    @can('create', \App\Models\StandardRoute::class)
    <a href="{{ route('third-party.standard-routes.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Nuova strada
    </a>
    @endcan
</div>

<div class="card overflow-hidden">
    @if($standardRoutes->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="doc" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessuna strada standard registrata</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Nome</th>
                <th class="px-5 py-3 font-medium">Ente</th>
                <th class="px-5 py-3 font-medium">Tipi veicolo</th>
                <th class="px-5 py-3 font-medium text-center">Attiva</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($standardRoutes as $sr)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-medium text-ink">{{ $sr->nome }}</td>
                <td class="px-5 py-3 text-ink-2">{{ $sr->entity->nome }}</td>
                <td class="px-5 py-3 text-ink-2">{{ count($sr->vehicle_types) }} tipo/i</td>
                <td class="px-5 py-3 text-center">
                    <x-chip :tone="$sr->active ? 'success' : 'default'" dot="true">{{ $sr->active ? 'Attiva' : 'Inattiva' }}</x-chip>
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        @can('update', $sr)
                        <a href="{{ route('third-party.standard-routes.edit', $sr) }}" class="btn btn-ghost btn-sm">Modifica</a>
                        @endcan
                        @can('delete', $sr)
                        <form method="POST" action="{{ route('third-party.standard-routes.destroy', $sr) }}" class="inline" onsubmit="return confirm('Eliminare questa strada standard?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-danger hover:bg-danger-bg hover:text-danger">Elimina</button>
                        </form>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($standardRoutes->hasPages())
    <div class="px-5 py-3 border-t border-line">{{ $standardRoutes->links() }}</div>
    @endif
    @endif
</div>
@endsection

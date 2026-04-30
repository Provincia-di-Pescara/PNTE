@extends('layouts.third-party')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Cantieri stradali</h1>
        <p class="text-sm text-ink-2 mt-1">Gestione cantieri e interruzioni stradali per il webGIS.</p>
    </div>
    @can('create', \App\Models\Roadwork::class)
    <a href="{{ route('third-party.roadworks.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Nuovo cantiere
    </a>
    @endcan
</div>

<div class="card overflow-hidden">
    @if($roadworks->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="doc" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun cantiere registrato</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Titolo</th>
                <th class="px-5 py-3 font-medium">Ente</th>
                <th class="px-5 py-3 font-medium">Dal</th>
                <th class="px-5 py-3 font-medium">Al</th>
                <th class="px-5 py-3 font-medium">Gravità</th>
                <th class="px-5 py-3 font-medium text-center">Stato</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($roadworks as $rw)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-medium text-ink">{{ $rw->title }}</td>
                <td class="px-5 py-3 text-ink-2">{{ $rw->entity->nome }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $rw->valid_from->format('d/m/Y') }}</td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $rw->valid_to?->format('d/m/Y') ?? '—' }}</td>
                <td class="px-5 py-3">
                    @php
                        $sevTone = match($rw->severity) {
                            \App\Enums\RoadworkSeverity::Closed => 'danger',
                            \App\Enums\RoadworkSeverity::Restricted => 'amber',
                            \App\Enums\RoadworkSeverity::Information => 'info',
                            default => 'default'
                        };
                    @endphp
                    <x-chip :tone="$sevTone">{{ $rw->severity->label() }}</x-chip>
                </td>
                <td class="px-5 py-3 text-center">
                    @php
                        $stTone = match($rw->status) {
                            \App\Enums\RoadworkStatus::Active => 'success',
                            \App\Enums\RoadworkStatus::Closed => 'default',
                            \App\Enums\RoadworkStatus::Planned => 'amber',
                            default => 'default'
                        };
                    @endphp
                    <x-chip :tone="$stTone" dot="true">{{ $rw->status->label() }}</x-chip>
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        @can('update', $rw)
                        <a href="{{ route('third-party.roadworks.edit', $rw) }}" class="btn btn-ghost btn-sm">Modifica</a>
                        @endcan
                        @can('delete', $rw)
                        <form method="POST" action="{{ route('third-party.roadworks.destroy', $rw) }}" class="inline" onsubmit="return confirm('Eliminare questo cantiere?')">
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
    @if($roadworks->hasPages())
    <div class="px-5 py-3 border-t border-line">{{ $roadworks->links() }}</div>
    @endif
    @endif
</div>
@endsection

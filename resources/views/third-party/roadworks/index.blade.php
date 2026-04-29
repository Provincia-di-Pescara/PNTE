@extends('layouts.third-party')
@section('title', 'Cantieri')
@section('content')
<div class="mb-6 flex items-center justify-between">
    <h1 class="text-xl font-bold text-slate-900">Cantieri stradali</h1>
    @can('create', \App\Models\Roadwork::class)
    <a href="{{ route('third-party.roadworks.create') }}"
       class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        Nuovo cantiere
    </a>
    @endcan
</div>
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Titolo</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ente</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Dal</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Al</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Gravità</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Stato</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($roadworks as $rw)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-900">{{ $rw->title }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $rw->entity->nome }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $rw->valid_from->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $rw->valid_to?->format('d/m/Y') ?? '—' }}</td>
                <td class="px-4 py-3">
                    @php $sev = $rw->severity; @endphp
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $sev === \App\Enums\RoadworkSeverity::Closed ? 'bg-red-100 text-red-700' :
                           ($sev === \App\Enums\RoadworkSeverity::Restricted ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                        {{ $sev->label() }}
                    </span>
                </td>
                <td class="px-4 py-3">
                    @php $st = $rw->status; @endphp
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                        {{ $st === \App\Enums\RoadworkStatus::Active ? 'bg-green-100 text-green-700' :
                           ($st === \App\Enums\RoadworkStatus::Closed ? 'bg-slate-100 text-slate-600' : 'bg-yellow-100 text-yellow-700') }}">
                        {{ $st->label() }}
                    </span>
                </td>
                <td class="px-4 py-3 text-right space-x-2">
                    @can('update', $rw)
                    <a href="{{ route('third-party.roadworks.edit', $rw) }}" class="text-xs text-blue-600 hover:underline">Modifica</a>
                    @endcan
                    @can('delete', $rw)
                    <form method="POST" action="{{ route('third-party.roadworks.destroy', $rw) }}" class="inline"
                          onsubmit="return confirm('Eliminare questo cantiere?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:underline">Elimina</button>
                    </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">Nessun cantiere.</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($roadworks->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">{{ $roadworks->links() }}</div>
    @endif
</div>
@endsection

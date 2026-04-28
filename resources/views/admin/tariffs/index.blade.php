@extends('layouts.admin')
@section('title', 'Tariffario')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Tariffario D.P.R. 495/1992</h1>
        <p class="text-sm text-slate-500 mt-0.5">Coefficienti di usura stradale per tipo di asse. Versioni ordinate per tipo e data.</p>
    </div>
    <a href="{{ route('admin.tariffs.create') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors">
        + Aggiungi coefficiente
    </a>
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    @if($tariffs->isEmpty())
    <div class="px-6 py-12 text-center">
        <p class="text-sm text-slate-500">Nessun coefficiente presente. Aggiungi il primo.</p>
    </div>
    @else
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Tipo asse</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase tracking-wide">Coefficiente</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Valido dal</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Valido al</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase tracking-wide">Stato</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @foreach($tariffs as $tariff)
            @php
                $isActive = $tariff->valid_from <= today() && ($tariff->valid_to === null || $tariff->valid_to >= today());
            @endphp
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-medium text-slate-900">{{ $tariff->tipo_asse->label() }}</td>
                <td class="px-4 py-3 text-right font-mono text-slate-700">{{ number_format((float) $tariff->coefficiente, 6) }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $tariff->valid_from->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $tariff->valid_to ? $tariff->valid_to->format('d/m/Y') : '—' }}</td>
                <td class="px-4 py-3">
                    @if($isActive)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Attiva</span>
                    @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">Storico</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-3">
                        <a href="{{ route('admin.tariffs.edit', $tariff) }}"
                           class="text-sm text-blue-600 hover:text-blue-700">Modifica</a>
                        <form method="POST" action="{{ route('admin.tariffs.destroy', $tariff) }}" class="inline"
                              onsubmit="return confirm('Eliminare questa voce tariffaria?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-sm text-red-500 hover:text-red-700">Elimina</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($tariffs->hasPages())
    <div class="px-4 py-3 border-t border-slate-100">
        {{ $tariffs->links() }}
    </div>
    @endif
    @endif
</div>
@endsection

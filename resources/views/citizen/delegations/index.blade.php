@extends('layouts.citizen')

@section('title', 'Mie Deleghe')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-slate-900">Mie Deleghe Aziendali</h1>
        <p class="text-sm text-slate-500 mt-0.5">Le aziende per cui hai richiesto o ottenuto delega operativa.</p>
    </div>
    <a href="{{ route('my.delegations.create') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
        Nuova delega
    </a>
</div>

@if($companies->isEmpty())
    <div class="text-center py-16 bg-white rounded-xl border border-slate-200">
        <svg class="mx-auto w-10 h-10 text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
        </svg>
        <p class="text-sm text-slate-500 mb-4">Nessuna delega presente.</p>
        <a href="{{ route('my.delegations.create') }}"
           class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 font-medium">
            Richiedi la tua prima delega
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>
@else
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="min-w-full divide-y divide-slate-200">
            <thead>
                <tr class="bg-slate-50">
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Azienda</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">P.IVA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Valida dal</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Valida al</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Stato</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($companies as $company)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-slate-900">{{ $company->ragione_sociale }}</span>
                        @if($company->comune)
                            <span class="block text-xs text-slate-500">{{ $company->comune }}{{ $company->provincia ? ' ('.$company->provincia.')' : '' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600 font-mono">{{ $company->partita_iva }}</td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $company->pivot->valid_from ? \Carbon\Carbon::parse($company->pivot->valid_from)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-600">
                        {{ $company->pivot->valid_to ? \Carbon\Carbon::parse($company->pivot->valid_to)->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($company->pivot->approved_at)
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Approvata</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">In attesa</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection

@extends('layouts.citizen')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Mie Deleghe Aziendali</h1>
        <p class="text-sm text-ink-2 mt-1">Le aziende per cui hai richiesto o ottenuto delega operativa.</p>
    </div>
    <a href="{{ route('my.delegations.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Nuova delega
    </a>
</div>

<div class="card overflow-hidden">
    @if($companies->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="user" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessuna delega presente</p>
        <div class="mt-6">
            <a href="{{ route('my.delegations.create') }}" class="btn btn-primary">
                Richiedi la tua prima delega
            </a>
        </div>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Azienda</th>
                <th class="px-5 py-3 font-medium">P.IVA</th>
                <th class="px-5 py-3 font-medium">Valida dal</th>
                <th class="px-5 py-3 font-medium">Valida al</th>
                <th class="px-5 py-3 font-medium text-center">Stato</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($companies as $company)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3">
                    <span class="font-medium text-ink">{{ $company->ragione_sociale }}</span>
                    @if($company->comune)
                        <span class="block text-[11px] text-ink-3 mt-0.5">{{ $company->comune }}{{ $company->provincia ? ' ('.$company->provincia.')' : '' }}</span>
                    @endif
                </td>
                <td class="px-5 py-3 font-mono text-ink-2">{{ $company->partita_iva }}</td>
                <td class="px-5 py-3 text-ink-2 font-mono text-[12px]">
                    {{ $company->pivot->valid_from ? \Carbon\Carbon::parse($company->pivot->valid_from)->format('d/m/Y') : '—' }}
                </td>
                <td class="px-5 py-3 text-ink-2 font-mono text-[12px]">
                    {{ $company->pivot->valid_to ? \Carbon\Carbon::parse($company->pivot->valid_to)->format('d/m/Y') : '—' }}
                </td>
                <td class="px-5 py-3 text-center">
                    @if($company->pivot->approved_at)
                        <x-chip tone="success" dot="true">Approvata</x-chip>
                    @else
                        <x-chip tone="amber" dot="true">In attesa</x-chip>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Aziende</h1>
        <p class="text-sm text-ink-2 mt-1">Gestione aziende di trasporto e deleghe operative.</p>
    </div>
    @can('create', \App\Models\Company::class)
    <a href="{{ route('admin.companies.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Nuova azienda
    </a>
    @endcan
</div>

<div class="card overflow-hidden">
    @if($companies->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="cone" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessuna azienda registrata</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-4 py-3 font-medium">Ragione sociale</th>
                <th class="px-4 py-3 font-medium">P.IVA</th>
                <th class="px-4 py-3 font-medium">Comune</th>
                <th class="px-4 py-3 font-medium text-center">Delegati</th>
                <th class="px-4 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($companies as $company)
            <tr class="row-hover transition-colors">
                <td class="px-4 py-3 font-medium text-ink">{{ $company->ragione_sociale }}</td>
                <td class="px-4 py-3 font-mono text-ink-2">{{ $company->partita_iva }}</td>
                <td class="px-4 py-3 text-ink-2">{{ $company->comune ? $company->comune.' ('.$company->provincia.')' : '—' }}</td>
                <td class="px-4 py-3 text-center">
                    <x-chip>{{ $company->users_count }}</x-chip>
                </td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.companies.show', $company) }}" class="btn btn-ghost btn-sm">Dettaglio</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($companies->hasPages())
    <div class="px-4 py-3 border-t border-line bg-surface">{{ $companies->links() }}</div>
    @endif
    @endif
</div>
@endsection

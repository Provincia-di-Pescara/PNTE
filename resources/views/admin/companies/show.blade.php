@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-start justify-between">
    <div>
        <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
            <a href="{{ route('admin.companies.index') }}" class="hover:text-ink transition-colors">Aziende</a>
            <span class="mx-1">/</span>
            <span>{{ $company->ragione_sociale }}</span>
        </nav>
        <h1 class="text-xl font-bold tracking-tight">{{ $company->ragione_sociale }}</h1>
        <p class="text-[13px] font-mono text-ink-2 mt-0.5">P.IVA {{ $company->partita_iva }}</p>
    </div>
    <div class="flex gap-2">
        @can('update', $company)
        <a href="{{ route('admin.companies.edit', $company) }}" class="btn">
            Modifica
        </a>
        @endcan
        @can('delete', $company)
        <form method="POST" action="{{ route('admin.companies.destroy', $company) }}"
              onsubmit="return confirm('Eliminare questa azienda?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn text-danger hover:bg-danger-bg border-line-2 hover:border-danger/30">
                Elimina
            </button>
        </form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Dati anagrafici --}}
    <div class="lg:col-span-1">
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-line bg-surface-2">
                <h2 class="text-sm font-semibold">Dati anagrafici</h2>
            </div>
            <dl class="divide-y divide-line">
                @foreach([
                    ['Ragione sociale', $company->ragione_sociale],
                    ['P.IVA', $company->partita_iva],
                    ['C.F. Azienda', $company->codice_fiscale ?? '—'],
                    ['Indirizzo', $company->indirizzo ?? '—'],
                    ['Comune', $company->comune ? $company->comune.' ('.$company->provincia.')' : '—'],
                    ['CAP', $company->cap ?? '—'],
                    ['E-mail', $company->email ?? '—'],
                    ['PEC', $company->pec ?? '—'],
                    ['Telefono', $company->telefono ?? '—'],
                ] as [$label, $value])
                <div class="flex flex-col px-5 py-2.5 text-[13px] hover:bg-surface-2 transition-colors">
                    <dt class="font-medium text-ink-2 text-[11px] uppercase tracking-wider mb-0.5">{{ $label }}</dt>
                    <dd class="text-ink">{{ $value }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>

    {{-- Delegati --}}
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="px-5 py-3 border-b border-line bg-surface-2 flex items-center justify-between">
                <h2 class="text-sm font-semibold">Utenti delegati</h2>
                <x-chip>{{ $company->users->count() }} utenti</x-chip>
            </div>
            @if($company->users->isEmpty())
            <div class="py-12 text-center flex flex-col items-center justify-center">
                <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
                    <x-icon name="user" size="24" stroke="1.5" />
                </div>
                <p class="text-sm font-semibold">Nessun utente associato</p>
                <p class="text-[13px] text-ink-2 mt-1">Le richieste di delega appariranno qui.</p>
            </div>
            @else
            <table class="w-full text-left text-[13px]">
                <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
                    <tr>
                        <th class="px-5 py-3 font-medium">Utente</th>
                        <th class="px-5 py-3 font-medium">Ruolo</th>
                        <th class="px-5 py-3 font-medium">Valida dal</th>
                        <th class="px-5 py-3 font-medium">Stato</th>
                        @can('approveDelegation', $company)
                        <th class="px-5 py-3"></th>
                        @endcan
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @foreach($company->users as $user)
                    <tr class="row-hover transition-colors">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <x-avatar :name="$user->name" tone="default" />
                                <div>
                                    <div class="font-medium text-ink">{{ $user->name }}</div>
                                    <div class="text-[11px] text-ink-3">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 text-ink-2 capitalize">{{ $user->pivot->role }}</td>
                        <td class="px-5 py-3 text-ink-2 font-mono text-[12px]">{{ \Carbon\Carbon::parse($user->pivot->valid_from)->format('d/m/Y') }}</td>
                        <td class="px-5 py-3">
                            @if($user->pivot->approved_at)
                            <x-chip tone="success" dot="true">Approvata</x-chip>
                            @else
                            <x-chip tone="amber" dot="true">In attesa</x-chip>
                            @endif
                        </td>
                        @can('approveDelegation', $company)
                        <td class="px-5 py-3 text-right">
                            @if(!$user->pivot->approved_at)
                            <form method="POST" action="{{ route('admin.companies.delegation.action', [$company, $user]) }}" class="inline-flex gap-2">
                                @csrf
                                <button name="action" value="approve" class="btn btn-sm btn-ghost text-success hover:bg-success-bg hover:text-success border-success/30">Approva</button>
                                <button name="action" value="reject" class="btn btn-sm btn-ghost text-danger hover:bg-danger-bg hover:text-danger border-danger/30"
                                        onclick="return confirm('Rifiutare questa delega?')">Rifiuta</button>
                            </form>
                            @endif
                        </td>
                        @endcan
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>
@endsection

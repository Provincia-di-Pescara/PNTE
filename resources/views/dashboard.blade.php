@extends('layouts.admin')

@section('content')
<div class="space-y-5"
     x-data="{
        selected: null,
        applications: {{ Js::from($recentApplications ?? collect()) }},
        get sel() { return this.selected !== null ? this.applications.find(a => a.id === this.selected) : null; },
        states: [
            { key: 'draft',              label: 'Bozza' },
            { key: 'submitted',          label: 'Inviata' },
            { key: 'waiting_clearances', label: 'Attesa nulla osta' },
            { key: 'waiting_payment',    label: 'Attesa pagamento' },
            { key: 'approved',           label: 'Autorizzata' },
        ],
        stateOrder: ['draft','submitted','waiting_clearances','waiting_payment','approved'],
        stateIndex(s) { return this.stateOrder.indexOf(s); },
     }">

    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Scrivania · Provincia di Pescara</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Istruttoria pratiche</h1>
        </div>
        <div class="flex-1"></div>
        <a href="{{ route('admin.entities.index') }}" class="btn">
            <x-icon name="download" size="12" /> Esporta CSV
        </a>
        <a href="#" class="btn btn-primary">
            <x-icon name="plus" size="12" /> Nuova pratica
        </a>
    </div>

    @if(($entitiesWithoutGeom ?? 0) > 0)
    <div class="rounded-lg border border-line bg-accent-bg px-4 py-3 flex gap-3 text-sm text-accent-ink">
        <x-icon name="alert" size="16" class="shrink-0 mt-0.5" />
        <div>
            <span class="font-semibold">{{ $entitiesWithoutGeom }} {{ $entitiesWithoutGeom === 1 ? 'ente' : 'enti' }} senza geometria.</span>
            Le coperture territoriali non saranno calcolate correttamente.
            <a href="{{ route('admin.entities.index') }}" class="underline font-medium ml-1">Gestisci →</a>
        </div>
    </div>
    @endif

    {{-- KPI Grid --}}
    <div class="grid grid-cols-4 gap-3">
        @php
        $kpis = [
            ['label' => 'Pratiche aperte',       'value' => $openCount ?? 0,                 'sub' => 'tutte le fasi attive',    'tone' => null],
            ['label' => 'In attesa nulla osta',   'value' => $waitingClearancesCount ?? 0,    'sub' => 'enti coinvolti',           'tone' => 'amber'],
            ['label' => 'In attesa pagamento',    'value' => $waitingPaymentCount ?? 0,       'sub' => 'pagamenti da ricevere',    'tone' => 'amber'],
            ['label' => 'Approvate (mese)',        'value' => $approvedThisMonthCount ?? 0,   'sub' => now()->translatedFormat('F Y'), 'tone' => 'success'],
        ];
        @endphp
        @foreach($kpis as $k)
        <div class="card p-4">
            <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase font-medium">{{ $k['label'] }}</div>
            <div class="num text-[26px] font-semibold mt-1 {{ $k['tone'] === 'amber' ? 'text-accent-ink' : ($k['tone'] === 'success' ? 'text-success' : 'text-ink') }}">
                {{ $k['value'] }}
            </div>
            <div class="text-[11.5px] text-ink-3 mt-0.5">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- Table + Detail Panel --}}
    <div class="grid grid-cols-[1.4fr_1fr] gap-4 min-h-[520px]">

        {{-- Applications table --}}
        <div class="card overflow-hidden flex flex-col">
            <div class="px-4 py-2.5 border-b border-line flex items-center gap-2">
                <div class="text-[13px] font-semibold">Pratiche · Ultime 30</div>
                <x-chip>{{ ($recentApplications ?? collect())->count() }}</x-chip>
                <div class="flex-1"></div>
                <button class="btn btn-sm"><x-icon name="filter" size="12" /> Filtri</button>
                <button class="btn btn-sm"><x-icon name="refresh" size="12" /></button>
            </div>

            @if(($recentApplications ?? collect())->isEmpty())
            <div class="flex-1 flex flex-col items-center justify-center py-16 text-center">
                <div class="w-12 h-12 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-3">
                    <x-icon name="truck" size="22" stroke="1.5" />
                </div>
                <p class="text-sm font-semibold">Nessuna pratica registrata</p>
                <p class="text-xs text-ink-2 mt-1">Le pratiche appariranno qui una volta inserite.</p>
            </div>
            @else
            <div class="overflow-auto flex-1">
                <table class="w-full border-collapse text-[12.5px]">
                    <thead>
                        <tr class="bg-surface-2 text-ink-3 text-[10.5px] uppercase tracking-[0.08em]">
                            @foreach(['Pratica','Richiedente','Tratta','Stato','Data','Importo'] as $h)
                            <th class="text-left px-3 py-2 font-medium border-b border-line">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentApplications as $app)
                        <tr class="row-hover cursor-pointer transition-colors"
                            :class="selected === {{ $app->id }} ? 'bg-accent-bg border-l-[3px] border-accent' : 'border-l-[3px] border-transparent'"
                            @click="selected = {{ $app->id }}">
                            <td class="px-3 py-2.5 border-b border-line">
                                <div class="mono text-[11.5px] font-semibold">{{ sprintf('GTE-%04d', $app->id) }}</div>
                                <div class="text-[11px] text-ink-3">{{ $app->vehicle?->targa ?? '—' }}</div>
                            </td>
                            <td class="px-3 py-2.5 border-b border-line font-medium">
                                {{ $app->company?->ragione_sociale ?? '—' }}
                            </td>
                            <td class="px-3 py-2.5 border-b border-line">
                                @if($app->route)
                                <div class="text-[12px]">
                                    {{ number_format($app->route->distance_km ?? 0, 1) }} km
                                </div>
                                @else
                                <span class="text-ink-3">—</span>
                                @endif
                            </td>
                            <td class="px-3 py-2.5 border-b border-line">
                                <x-status-pill :state="$app->stato->value" />
                            </td>
                            <td class="px-3 py-2.5 border-b border-line text-ink-2">
                                {{ $app->created_at->format('d M Y') }}
                            </td>
                            <td class="px-3 py-2.5 border-b border-line text-right num">
                                @if($app->wear_calculation && isset($app->wear_calculation['total']))
                                    € {{ number_format($app->wear_calculation['total'], 2) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>

        {{-- Detail panel --}}
        <div class="card flex flex-col overflow-hidden">
            <template x-if="sel === null">
                <div class="flex-1 flex flex-col items-center justify-center text-center p-8 text-ink-3">
                    <x-icon name="doc" size="28" stroke="1.3" />
                    <p class="text-sm mt-3">Seleziona una pratica</p>
                    <p class="text-xs text-ink-3 mt-1">Clicca su una riga per vedere i dettagli e la timeline di stato.</p>
                </div>
            </template>

            <template x-if="sel !== null">
                <div class="flex flex-col h-full">
                    {{-- Header --}}
                    <div class="px-4 py-3 border-b border-line flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="mono text-[11px] text-ink-3" x-text="'GTE-' + String(sel.id).padStart(4, '0')"></div>
                            <div class="font-semibold text-[14px] truncate" x-text="sel.company?.ragione_sociale ?? '—'"></div>
                        </div>
                        <span class="chip"
                              :class="{
                                  'chip-success': sel.stato === 'approved',
                                  'chip-danger':  sel.stato === 'rejected',
                                  'chip-amber':   sel.stato === 'waiting_clearances' || sel.stato === 'waiting_payment',
                                  'chip-info':    sel.stato === 'submitted',
                              }"
                              x-text="{draft:'Bozza',submitted:'Inviata',waiting_clearances:'Attesa nulla osta',waiting_payment:'Attesa pagamento',approved:'Autorizzata',rejected:'Respinta'}[sel.stato] ?? sel.stato">
                        </span>
                    </div>

                    {{-- Metadata --}}
                    <div class="px-4 py-3 grid grid-cols-3 gap-3 text-[12px] border-b border-line">
                        <div>
                            <div class="text-[10px] text-ink-3 uppercase tracking-[0.08em]">Tratta</div>
                            <div x-text="sel.route ? (sel.route.distance_km ? Number(sel.route.distance_km).toFixed(1) + ' km' : '—') : '—'"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-ink-3 uppercase tracking-[0.08em]">Veicolo</div>
                            <div class="mono" x-text="sel.vehicle?.targa ?? '—'"></div>
                        </div>
                        <div>
                            <div class="text-[10px] text-ink-3 uppercase tracking-[0.08em]">Data</div>
                            <div x-text="sel.created_at ? new Date(sel.created_at).toLocaleDateString('it-IT') : '—'"></div>
                        </div>
                    </div>

                    {{-- Nulla osta progress --}}
                    <div class="px-4 py-3 border-b border-line" x-show="sel.stato === 'waiting_clearances'">
                        <div class="flex items-center gap-2 text-[12px]">
                            <x-icon name="clock" size="13" />
                            <span class="text-ink-3">Nulla osta:</span>
                            <span class="font-semibold num" x-text="sel.clearances?.filter(c => c.stato !== 'pending').length + ' di ' + sel.clearances?.length + ' ricevuti'"></span>
                        </div>
                    </div>

                    {{-- State machine timeline --}}
                    <div class="px-4 py-3 flex-1 overflow-auto">
                        <div class="text-[11px] text-ink-3 tracking-[0.08em] uppercase mb-3">Macchina a stati</div>
                        <div class="relative pl-5">
                            <div class="absolute left-[9px] top-1 bottom-1 w-px bg-line"></div>
                            <template x-for="(s, idx) in states" :key="s.key">
                                <div class="relative mb-3.5 last:mb-0">
                                    <div class="absolute -left-5 top-0.5 w-3 h-3 rounded-full transition-all"
                                         :class="{
                                            'bg-ink': stateIndex(sel.stato) > idx,
                                            'bg-accent ring-4 ring-accent-bg': stateIndex(sel.stato) === idx,
                                            'bg-surface-2 border border-line-2': stateIndex(sel.stato) < idx,
                                         }"></div>
                                    <div class="flex items-baseline gap-2">
                                        <div class="text-[13px]"
                                             :class="{
                                                'font-semibold text-ink': stateIndex(sel.stato) === idx,
                                                'text-ink-2 font-medium': stateIndex(sel.stato) > idx,
                                                'text-ink-3 font-medium': stateIndex(sel.stato) < idx,
                                             }"
                                             x-text="s.label"></div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="p-3 border-t border-line flex gap-2">
                        <a :href="'#'" class="btn flex-1 text-center justify-center">Apri PDF</a>
                        <a :href="'#'" class="btn btn-primary flex-[1.4] text-center justify-center">Sollecita enti</a>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- System stats (collapsed below fold) --}}
    <details class="card overflow-hidden">
        <summary class="px-5 py-3 cursor-pointer text-[13px] font-semibold flex items-center gap-2 select-none list-none">
            <x-icon name="layers" size="14" />
            Riepilogo sistema
            <x-chip class="ml-2">{{ $userCount ?? 0 }} utenti</x-chip>
        </summary>
        <div class="border-t border-line">
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-0">
                @foreach([
                    ['Utenti', $userCount ?? 0],
                    ['Enti', $entityCount ?? 0],
                    ['Aziende', $companyCount ?? 0],
                    ['Percorsi', $routeCount ?? 0],
                    ['Cantieri', $roadworkCount ?? 0],
                    ['Tariffe', $tariffCount ?? 0],
                ] as $i => [$label, $value])
                <div class="p-4 {{ $i < 5 ? 'border-r border-line' : '' }}">
                    <div class="text-xs text-ink-2 font-medium">{{ $label }}</div>
                    <div class="text-2xl font-bold mt-1 num">{{ $value }}</div>
                </div>
                @endforeach
            </div>

            {{-- Impersonation log --}}
            @if(($recentImpersonations ?? collect())->isNotEmpty())
            <div class="border-t border-line p-5">
                <h3 class="text-sm font-semibold mb-3">Ultime impersonazioni</h3>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-ink-3 border-b border-line">
                            <th class="pb-2 font-medium">Operatore</th>
                            <th class="pb-2 font-medium">Utente impersonato</th>
                            <th class="pb-2 font-medium">Inizio</th>
                            <th class="pb-2 font-medium">Fine</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($recentImpersonations as $log)
                        <tr>
                            <td class="py-2 font-medium">{{ $log->impersonator?->name ?? '–' }}</td>
                            <td class="py-2 text-ink-2">{{ $log->impersonated?->name ?? '–' }}</td>
                            <td class="py-2 text-ink-3">{{ $log->started_at?->format('d/m H:i') }}</td>
                            <td class="py-2 text-ink-3">{{ $log->ended_at?->format('d/m H:i') ?? 'In corso' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            {{-- Impersonation quick panel --}}
            @if(($allUsers ?? collect())->isNotEmpty())
            <div class="border-t border-line p-5">
                <h3 class="text-sm font-semibold mb-3">Ambienti di test</h3>
                <div class="grid grid-cols-2 gap-2">
                    @foreach(($allUsers ?? collect())->take(8) as $targetUser)
                    @if($targetUser->id !== auth()->id() && $targetUser->canBeImpersonated())
                    <form method="POST" action="{{ route('admin.users.impersonate', $targetUser) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 p-2 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 text-left group">
                            <x-avatar :name="$targetUser->name" tone="amber" />
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium truncate">{{ $targetUser->name }}</div>
                                <div class="text-[10px] text-ink-3 truncate">{{ $targetUser->roles->first()?->name ?? 'nessun ruolo' }}</div>
                            </div>
                        </button>
                    </form>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </details>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Dashboard Operativa</h1>
        <p class="text-sm text-ink-2 mt-1">Riepilogo sistema e stato elaborazioni</p>
    </div>

    @if(($entitiesWithoutGeom ?? 0) > 0)
    <div class="rounded-lg border border-amber-300 bg-amber-50 dark:bg-amber-900/20 p-4 flex gap-3 text-sm">
        <x-icon name="warning" size="18" class="text-amber-500 shrink-0 mt-0.5" />
        <div>
            <span class="font-semibold text-amber-700 dark:text-amber-400">{{ $entitiesWithoutGeom }} {{ $entitiesWithoutGeom === 1 ? 'ente' : 'enti' }} senza geometria.</span>
            <span class="text-amber-600 dark:text-amber-500"> Le coperture territoriali non saranno calcolate correttamente.</span>
            <a href="{{ route('admin.entities.index') }}" class="underline font-medium ml-1">Gestisci enti →</a>
        </div>
    </div>
    @endif

    <!-- KPI Grid -->
    <div class="grid grid-cols-3 gap-4 sm:grid-cols-6">
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Utenti</div>
            <div class="text-2xl font-bold mt-1 num">{{ $userCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Enti registrati</div>
            <div class="text-2xl font-bold mt-1 num">{{ $entityCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Aziende</div>
            <div class="text-2xl font-bold mt-1 num">{{ $companyCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Percorsi</div>
            <div class="text-2xl font-bold mt-1 num">{{ $routeCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Cantieri</div>
            <div class="text-2xl font-bold mt-1 num">{{ $roadworkCount ?? 0 }}</div>
        </div>
        <div class="card p-4">
            <div class="text-xs text-ink-2 font-medium">Tariffe attive</div>
            <div class="text-2xl font-bold mt-1 num">{{ $tariffCount ?? 0 }}</div>
        </div>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <!-- Left column -->
        <div class="col-span-2 space-y-6">
            <!-- Chart.js role distribution -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Distribuzione utenti per ruolo</h3>
                <div class="flex items-center gap-6">
                    <div class="w-40 h-40 shrink-0">
                        <canvas id="roleChart"></canvas>
                    </div>
                    <div class="space-y-2 flex-1">
                        @php
                            $roleLabels = [
                                'super-admin' => 'Super Admin',
                                'operator' => 'Operatore',
                                'citizen' => 'Cittadino/Azienda',
                                'third-party' => 'Ente Terzo',
                                'law-enforcement' => 'Forze dell\'Ordine',
                            ];
                            $roleColors = ['#6366f1','#22c55e','#f59e0b','#3b82f6','#ef4444'];
                            $idx = 0;
                        @endphp
                        @foreach($usersByRole ?? [] as $role => $count)
                        <div class="flex items-center gap-2 text-xs">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $roleColors[$idx % 5] }}"></span>
                            <span class="text-ink-2 flex-1">{{ $roleLabels[$role] ?? $role }}</span>
                            <span class="font-semibold num">{{ $count }}</span>
                        </div>
                        @php $idx++ @endphp
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Impersonation log -->
            @if(($recentImpersonations ?? collect())->isNotEmpty())
            <div class="card p-5">
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
                        <tr class="py-1">
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

            <!-- Applications placeholder -->
            <div class="card p-6 border border-dashed border-line-2 bg-surface flex flex-col items-center justify-center text-center py-12">
                <div class="w-12 h-12 bg-surface-2 rounded-full flex items-center justify-center text-ink-3 mb-4">
                    <x-icon name="truck" size="24" stroke="1.5" />
                </div>
                <h3 class="text-sm font-semibold">Pratiche · In arrivo con v0.5.x</h3>
                <p class="text-xs text-ink-2 mt-1 max-w-sm">La gestione del flusso documentale e la state machine delle pratiche saranno disponibili a breve.</p>
            </div>
        </div>

        <!-- Right column -->
        <div class="col-span-1 space-y-6">
            <!-- Quick links -->
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-4">Accesso rapido</h3>
                <div class="space-y-2">
                    <a href="{{ route('admin.entities.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="bridge" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Enti territoriali</div>
                            <div class="text-[10px] text-ink-2">Gestione Comuni e Province</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.tariffs.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="euro" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Tariffario</div>
                            <div class="text-[10px] text-ink-2">Coefficienti di usura</div>
                        </div>
                    </a>
                    <a href="{{ route('admin.settings.users.index') }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 group">
                        <div class="w-8 h-8 rounded bg-surface flex items-center justify-center text-ink-2 group-hover:text-accent">
                            <x-icon name="user" />
                        </div>
                        <div>
                            <div class="text-xs font-semibold">Gestione utenti</div>
                            <div class="text-[10px] text-ink-2">Ruoli e impersonazione</div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Impersonation quick panel -->
            @if(($allUsers ?? collect())->isNotEmpty())
            <div class="card p-5">
                <h3 class="text-sm font-semibold mb-3">Ambienti di test</h3>
                <p class="text-[10px] text-ink-3 mb-3">Accedi come un altro utente per testare il sistema.</p>
                <div class="space-y-1.5">
                    @foreach(($allUsers ?? collect())->take(8) as $targetUser)
                    @if($targetUser->id !== auth()->id() && $targetUser->canBeImpersonated())
                    <form method="POST" action="{{ route('admin.users.impersonate', $targetUser) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2.5 p-2 rounded-lg border border-line hover:border-accent transition-colors bg-surface-2 text-left group">
                            <div class="w-6 h-6 rounded-full bg-accent/10 flex items-center justify-center text-accent text-[10px] font-bold shrink-0">
                                {{ strtoupper(substr($targetUser->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-xs font-medium truncate">{{ $targetUser->name }}</div>
                                <div class="text-[10px] text-ink-3 truncate">{{ $targetUser->roles->first()?->name ?? 'nessun ruolo' }}</div>
                            </div>
                            <x-icon name="arrow-right" size="12" class="text-ink-3 group-hover:text-accent shrink-0" />
                        </button>
                    </form>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('roleChart');
    if (ctx && window.Chart) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_map(fn($r) => match($r) {
                    'super-admin' => 'Super Admin',
                    'operator' => 'Operatore',
                    'citizen' => 'Cittadino',
                    'third-party' => 'Ente Terzo',
                    'law-enforcement' => 'Forze Ordine',
                    default => $r,
                }, array_keys($usersByRole ?? []))) !!},
                datasets: [{
                    data: {!! json_encode(array_values($usersByRole ?? [])) !!},
                    backgroundColor: ['#6366f1','#22c55e','#f59e0b','#3b82f6','#ef4444'],
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                cutout: '65%',
            }
        });
    }
});
</script>
@endpush
@endsection


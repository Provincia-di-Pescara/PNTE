@extends('layouts.citizen')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('my.delegations.index') }}" class="hover:text-ink transition-colors">Deleghe</a>
        <span class="mx-1">/</span>
        <span>Nuova</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">Richiedi delega aziendale</h1>
    <p class="text-sm text-ink-2 mt-1">Cerca l'azienda per P.IVA. Se già registrata, i dati saranno precompilati.</p>
</div>

<div class="card p-6" x-data="{ found: null, loading: false, company: {}, piva: '{{ old('partita_iva', '') }}' }">

    {{-- Sezione lookup P.IVA --}}
    <div class="mb-6 border-b border-line pb-6">
        <label class="block text-xs font-semibold text-ink-2 mb-1.5">
            Partita IVA azienda <span class="text-danger">*</span>
        </label>
        <div class="flex gap-3">
            <input type="text" x-model="piva" maxlength="11" placeholder="11 cifre"
                   class="w-48 h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            <button type="button"
                    @click="
                        loading = true;
                        found = null;
                        const token = document.querySelector('meta[name=\'csrf-token\']').content;
                        fetch('{{ route('my.delegations.lookup') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ partita_iva: piva })
                        })
                        .then(r => r.json())
                        .then(data => { found = data.found; company = data.company ?? {}; loading = false; })
                        .catch(() => { loading = false; })
                    "
                    :disabled="loading || piva.length !== 11"
                    class="btn btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                <span x-show="!loading"><x-icon name="search" size="14" /> Cerca</span>
                <span x-show="loading" class="animate-pulse">Ricerca in corso...</span>
            </button>
        </div>
        @error('partita_iva')<p class="mt-1.5 text-[11px] text-danger">{{ $message }}</p>@enderror
    </div>

    {{-- Form di richiesta delega --}}
    <form method="POST" action="{{ route('my.delegations.store') }}">
        @csrf

        <input type="hidden" name="partita_iva" :value="piva">

        {{-- Azienda trovata --}}
        <div x-show="found === true" x-cloak class="mb-6 p-4 bg-success-bg border border-success/30 rounded-lg">
            <div class="flex items-start gap-3">
                <x-icon name="check" size="20" class="text-success mt-0.5 shrink-0" />
                <div>
                    <p class="text-sm font-semibold text-success" x-text="company.ragione_sociale"></p>
                    <p class="text-[13px] text-success/80 mt-0.5" x-text="[company.comune, company.provincia].filter(Boolean).join(' — ')"></p>
                    <p x-show="company.pec" class="text-[11px] text-success/80 mt-1 font-mono" x-text="'PEC: ' + company.pec"></p>
                </div>
            </div>
            <input type="hidden" name="ragione_sociale" x-bind:value="company.ragione_sociale">
        </div>

        {{-- Azienda non trovata — inserimento manuale --}}
        <div x-show="found === false" x-cloak class="mb-6 space-y-4">
            <div class="p-3 bg-amber-bg border border-amber/30 rounded-lg mb-4">
                <p class="text-[13px] text-amber">Azienda non trovata nel sistema. Compila i dati per registrarla.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">Ragione sociale <span class="text-danger">*</span></label>
                    <input type="text" name="ragione_sociale" value="{{ old('ragione_sociale') }}"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    @error('ragione_sociale')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">Codice fiscale</label>
                    <input type="text" name="codice_fiscale" value="{{ old('codice_fiscale') }}" maxlength="16"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
                    @error('codice_fiscale')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">Telefono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    @error('telefono')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">Indirizzo</label>
                    <input type="text" name="indirizzo" value="{{ old('indirizzo') }}"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    @error('indirizzo')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">Comune</label>
                    <input type="text" name="comune" value="{{ old('comune') }}"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    @error('comune')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-ink-2 mb-1.5">CAP</label>
                        <input type="text" name="cap" value="{{ old('cap') }}" maxlength="5"
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
                        @error('cap')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-ink-2 mb-1.5">Prov.</label>
                        <input type="text" name="provincia" value="{{ old('provincia') }}" maxlength="2"
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono uppercase focus:border-accent focus:outline-none transition-colors">
                        @error('provincia')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    @error('email')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-ink-2 mb-1.5">PEC</label>
                    <input type="email" name="pec" value="{{ old('pec') }}"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    @error('pec')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        {{-- Periodo validità + submit --}}
        <div x-show="found !== null" x-cloak>
            <div class="border-t border-line pt-6 mt-4">
                <h2 class="text-sm font-semibold mb-4">Periodo di validità delega</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-semibold text-ink-2 mb-1.5">Valida dal <span class="text-danger">*</span></label>
                        <input type="date" name="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
                        @error('valid_from')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-ink-2 mb-1.5">Valida al (opzionale)</label>
                        <input type="date" name="valid_to" value="{{ old('valid_to') }}"
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
                        @error('valid_to')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="btn btn-primary">Richiedi delega</button>
                    <a href="{{ route('my.delegations.index') }}" class="btn">Annulla</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

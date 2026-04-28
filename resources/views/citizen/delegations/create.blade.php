@extends('layouts.citizen')

@section('title', 'Richiedi delega aziendale')

@section('content')
<div class="mb-6">
    <a href="{{ route('my.delegations.index') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-slate-700 mb-3">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
        </svg>
        Torna alle deleghe
    </a>
    <h1 class="text-xl font-bold text-slate-900">Richiedi delega aziendale</h1>
    <p class="text-sm text-slate-500 mt-0.5">Cerca l'azienda per P.IVA. Se già registrata, i dati saranno precompilati.</p>
</div>

<div class="bg-white rounded-xl border border-slate-200 p-6"
     x-data="{ found: null, loading: false, company: {}, piva: '{{ old('partita_iva', '') }}' }">

    {{-- Sezione lookup P.IVA --}}
    <div class="mb-6">
        <label class="block text-sm font-medium text-slate-700 mb-1.5">
            Partita IVA azienda
            <span class="text-red-500">*</span>
        </label>
        <div class="flex gap-2">
            <input
                type="text"
                x-model="piva"
                maxlength="11"
                placeholder="11 cifre"
                class="flex-1 rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none"
            >
            <button
                type="button"
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
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
                <svg x-show="!loading" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 15.803a7.5 7.5 0 0 0 10.607 0Z" />
                </svg>
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Cerca
            </button>
        </div>
        @error('partita_iva')
            <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Form di richiesta delega --}}
    <form method="POST" action="{{ route('my.delegations.store') }}">
        @csrf

        {{-- Campo hidden per P.IVA (passato al backend) --}}
        <input type="hidden" name="partita_iva" :value="piva">

        {{-- Azienda trovata --}}
        <div x-show="found === true" x-cloak class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-green-600 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <div>
                    <p class="text-sm font-semibold text-green-800" x-text="company.ragione_sociale"></p>
                    <p class="text-xs text-green-700 mt-0.5" x-text="[company.comune, company.provincia].filter(Boolean).join(' — ')"></p>
                    <p x-show="company.pec" class="text-xs text-green-600 mt-0.5" x-text="'PEC: ' + company.pec"></p>
                </div>
            </div>
            {{-- Hidden per ragione_sociale quando azienda trovata --}}
            <input type="hidden" name="ragione_sociale" x-bind:value="company.ragione_sociale">
        </div>

        {{-- Azienda non trovata — inserimento manuale --}}
        <div x-show="found === false" x-cloak class="mb-6">
            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg mb-4">
                <p class="text-xs text-amber-800">Azienda non trovata nel sistema. Compila i dati per registrarla.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">
                        Ragione sociale <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="ragione_sociale" value="{{ old('ragione_sociale') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('ragione_sociale')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Codice fiscale</label>
                    <input type="text" name="codice_fiscale" value="{{ old('codice_fiscale') }}" maxlength="16"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('codice_fiscale')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Telefono</label>
                    <input type="text" name="telefono" value="{{ old('telefono') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('telefono')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Indirizzo</label>
                    <input type="text" name="indirizzo" value="{{ old('indirizzo') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('indirizzo')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Comune</label>
                    <input type="text" name="comune" value="{{ old('comune') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('comune')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">CAP</label>
                        <input type="text" name="cap" value="{{ old('cap') }}" maxlength="5"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        @error('cap')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Prov.</label>
                        <input type="text" name="provincia" value="{{ old('provincia') }}" maxlength="2"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono uppercase focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        @error('provincia')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">PEC</label>
                    <input type="email" name="pec" value="{{ old('pec') }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                    @error('pec')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Periodo validità + submit — visibili dopo lookup --}}
        <div x-show="found !== null" x-cloak>
            <div class="border-t border-slate-100 pt-5 mt-2">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">Periodo di validità delega</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Valida dal <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="valid_from" value="{{ old('valid_from', date('Y-m-d')) }}"
                               min="{{ date('Y-m-d') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        @error('valid_from')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Valida al (opzionale)</label>
                        <input type="date" name="valid_to" value="{{ old('valid_to') }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none">
                        @error('valid_to')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3">
                    <a href="{{ route('my.delegations.index') }}"
                       class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition-colors">
                        Annulla
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                        Richiedi delega
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

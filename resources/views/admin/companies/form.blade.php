@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('admin.companies.index') }}" class="hover:text-ink transition-colors">Aziende</a>
        <span class="mx-1">/</span>
        <span>{{ $company->exists ? $company->ragione_sociale : 'Nuova' }}</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">{{ $company->exists ? 'Modifica azienda' : 'Nuova azienda' }}</h1>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
    <ul class="list-disc list-inside text-sm text-danger space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}"
      class="max-w-2xl space-y-6"
      x-data="companyForm({{ json_encode([
          'pdndEnabled' => \App\Models\Setting::get('pdnd_enabled', '0') === '1',
          'lookupUrl'   => route('api.admin.companies.lookup'),
          'alreadyVerified' => $company->exists && $company->infocamere_verified_at !== null,
          'verifiedAt'  => $company->exists && $company->infocamere_verified_at ? $company->infocamere_verified_at->toIso8601String() : null,
      ]) }})">
    @csrf
    @if($company->exists) @method('PUT') @endif

    {{-- Hidden field written by Alpine when user verifies via PDND --}}
    <input type="hidden" name="infocamere_verified" :value="verified ? '1' : '0'">

    <div class="card p-6 space-y-5">
        <div class="flex items-center justify-between border-b border-line pb-2 mb-4">
            <h2 class="text-sm font-semibold">Dati azienda</h2>
            @if(\App\Models\Setting::get('pdnd_enabled', '0') === '1')
            <div class="flex items-center gap-2">
                <template x-if="verified">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-1 text-[11px] font-semibold text-success">
                        <svg class="h-3 w-3" viewBox="0 0 16 16" fill="currentColor"><path d="M8 0a8 8 0 1 1 0 16A8 8 0 0 1 8 0zm3.78 5.22-4.53 4.53-1.53-1.53a.75.75 0 1 0-1.06 1.06l2.06 2.06c.29.29.77.29 1.06 0l5.06-5.06a.75.75 0 1 0-1.06-1.06z"/></svg>
                        Verificato PDND
                    </span>
                </template>
                <button type="button"
                        @click="lookupPiva()"
                        :disabled="loading || pivaValue.length !== 11"
                        class="btn btn-sm text-[12px] px-3 h-7 flex items-center gap-1.5">
                    <template x-if="!loading">
                        <svg class="h-3.5 w-3.5" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="7" cy="7" r="4.5"/><path d="M10.5 10.5 13 13" stroke-linecap="round"/></svg>
                    </template>
                    <template x-if="loading">
                        <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="8" cy="8" r="6" stroke-dasharray="20 20" stroke-linecap="round"/></svg>
                    </template>
                    <span x-text="loading ? 'Ricerca…' : 'Verifica P.IVA (Registro Imprese)'"></span>
                </button>
            </div>
            @endif
        </div>

        <template x-if="lookupError">
            <div class="rounded-md bg-danger/10 border border-danger/20 px-3 py-2 text-[12px] text-danger" x-text="lookupError"></div>
        </template>

        <div>
            <label for="ragione_sociale" class="block text-xs font-semibold text-ink-2 mb-1.5">Ragione sociale <span class="text-danger">*</span></label>
            <input type="text" id="ragione_sociale" name="ragione_sociale"
                   :value="fields.ragione_sociale ?? '{{ old('ragione_sociale', $company->ragione_sociale) }}'"
                   @input="fields.ragione_sociale = $event.target.value; verified = false"
                   required
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('ragione_sociale') border-danger @enderror">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="partita_iva" class="block text-xs font-semibold text-ink-2 mb-1.5">Partita IVA <span class="text-danger">*</span></label>
                <input type="text" id="partita_iva" name="partita_iva"
                       x-model="pivaValue"
                       @input="verified = false"
                       value="{{ old('partita_iva', $company->partita_iva) }}"
                       required maxlength="11"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('partita_iva') border-danger @enderror">
            </div>
            <div>
                <label for="codice_fiscale" class="block text-xs font-semibold text-ink-2 mb-1.5">Codice fiscale azienda</label>
                <input type="text" id="codice_fiscale" name="codice_fiscale"
                       :value="fields.codice_fiscale ?? '{{ old('codice_fiscale', $company->codice_fiscale) }}'"
                       @input="fields.codice_fiscale = $event.target.value; verified = false"
                       maxlength="16"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div>
            <label for="indirizzo" class="block text-xs font-semibold text-ink-2 mb-1.5">Indirizzo</label>
            <input type="text" id="indirizzo" name="indirizzo"
                   :value="fields.indirizzo ?? '{{ old('indirizzo', $company->indirizzo) }}'"
                   @input="fields.indirizzo = $event.target.value; verified = false"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label for="comune" class="block text-xs font-semibold text-ink-2 mb-1.5">Comune</label>
                <input type="text" id="comune" name="comune"
                       :value="fields.comune ?? '{{ old('comune', $company->comune) }}'"
                       @input="fields.comune = $event.target.value; verified = false"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="cap" class="block text-xs font-semibold text-ink-2 mb-1.5">CAP</label>
                <input type="text" id="cap" name="cap"
                       :value="fields.cap ?? '{{ old('cap', $company->cap) }}'"
                       @input="fields.cap = $event.target.value; verified = false"
                       maxlength="5"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div>
            <label for="provincia" class="block text-xs font-semibold text-ink-2 mb-1.5">Provincia (sigla)</label>
            <input type="text" id="provincia" name="provincia"
                   :value="fields.provincia ?? '{{ old('provincia', $company->provincia) }}'"
                   @input="fields.provincia = $event.target.value; verified = false"
                   maxlength="2"
                   class="w-20 h-9 px-3 rounded-md border border-line bg-surface text-[13px] uppercase font-mono focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>

    <div class="card p-6 space-y-5">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Contatti</h2>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-ink-2 mb-1.5">E-mail</label>
                <input type="email" id="email" name="email"
                       :value="fields.email ?? '{{ old('email', $company->email) }}'"
                       @input="fields.email = $event.target.value; verified = false"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="pec" class="block text-xs font-semibold text-ink-2 mb-1.5">PEC</label>
                <input type="email" id="pec" name="pec"
                       :value="fields.pec ?? '{{ old('pec', $company->pec) }}'"
                       @input="fields.pec = $event.target.value; verified = false"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div>
            <label for="telefono" class="block text-xs font-semibold text-ink-2 mb-1.5">Telefono</label>
            <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $company->telefono) }}"
                   class="w-48 h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">
            {{ $company->exists ? 'Salva modifiche' : 'Crea azienda' }}
        </button>
        <a href="{{ $company->exists ? route('admin.companies.show', $company) : route('admin.companies.index') }}" class="btn">
            Annulla
        </a>
    </div>
</form>

@push('scripts')
<script>
function companyForm(config) {
    return {
        pdndEnabled: config.pdndEnabled ?? false,
        lookupUrl: config.lookupUrl ?? '',
        verified: config.alreadyVerified ?? false,
        loading: false,
        lookupError: null,
        pivaValue: '{{ old('partita_iva', $company->partita_iva) }}',
        fields: {},

        async lookupPiva() {
            if (this.pivaValue.length !== 11) return;
            this.loading = true;
            this.lookupError = null;

            try {
                const res = await fetch(this.lookupUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ piva: this.pivaValue }),
                });

                const json = await res.json();

                if (!res.ok) {
                    const codes = {
                        'pdnd_disabled': 'Integrazione PDND non attiva. Configura il pannello PDND.',
                        'not_found': 'Partita IVA non trovata nel Registro Imprese.',
                        'api_error': json.error ?? 'Errore durante la consultazione.',
                    };
                    this.lookupError = codes[json.code] ?? json.error ?? 'Errore sconosciuto.';
                    return;
                }

                const d = json.data;
                this.fields = {
                    ragione_sociale: d.ragione_sociale ?? '',
                    codice_fiscale: d.codice_fiscale ?? '',
                    indirizzo: d.indirizzo ?? '',
                    comune: d.comune ?? '',
                    cap: d.cap ?? '',
                    provincia: d.provincia ? d.provincia.toUpperCase() : '',
                    email: d.email ?? '',
                    pec: d.pec ?? '',
                };

                // Sync text fields that don't use x-model
                this.$nextTick(() => {
                    for (const [key, val] of Object.entries(this.fields)) {
                        const el = document.getElementById(key);
                        if (el && val) el.value = val;
                    }
                });

                this.verified = true;
            } catch (err) {
                this.lookupError = 'Errore di rete. Riprova più tardi.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
@endsection

@extends('layouts.settings')

@section('settings-content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Integrazioni</h1>
    <p class="text-sm text-ink-2 mt-0.5">Sistemi nazionali e regionali collegati al gestionale. Le credenziali sono custodite in HSM.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-danger/10 text-danger text-[13px] font-medium">{{ session('error') }}</div>
@endif

{{-- Integration overview cards --}}
<div class="grid grid-cols-3 gap-3 mb-8"
     x-data="{
         integrations: [
             { key: 'pagopa',  name: 'PagoPA',       org: 'AgID · pagoPA S.p.A.',                         status: 'off',  sync: 'Non configurato',  env: '—' },
             { key: 'ainop',   name: 'AINOP',         org: 'MIT · Archivio nazionale opere pubbliche',     status: 'off',  sync: 'Non configurato',  env: '—' },
             { key: 'pdnd',    name: 'PDND',          org: 'Dipartimento Trasformazione Digitale',          status: '{{ $settings['pdnd_enabled'] === '1' ? 'ok' : 'off' }}',  sync: '{{ $settings['ipa_last_sync_at'] ? \Carbon\Carbon::parse($settings['ipa_last_sync_at'])->diffForHumans() : 'Mai' }}',  env: 'Produzione' },
             { key: 'ipa',     name: 'IPA',           org: 'AgID · Indice Pubblica Amministrazione',        status: '{{ $settings['pdnd_ipa_url'] ? 'ok' : 'off' }}',  sync: '{{ $settings['ipa_last_sync_at'] ? \Carbon\Carbon::parse($settings['ipa_last_sync_at'])->diffForHumans() : 'Mai' }}',  env: 'Produzione' },
             { key: 'siope',   name: 'SIOPE+',        org: 'MEF · Banca d\'Italia',                        status: 'off',  sync: 'Non collegato',    env: '—' },
             { key: 'osrm',    name: 'OSRM (routing)','org': 'Self-hosted',                                 status: 'ok',   sync: 'Real-time',         env: 'Produzione' },
         ]
     }">
    <template x-for="it in integrations" :key="it.key">
        <div class="card p-4">
            <div class="flex items-start justify-between gap-2 mb-2">
                <div class="text-[13px] font-semibold" x-text="it.name"></div>
                <span class="w-2 h-2 rounded-full mt-1 shrink-0"
                      :class="{
                          'bg-success': it.status === 'ok',
                          'bg-amber-400': it.status === 'warn',
                          'bg-ink-3/40': it.status === 'off',
                      }"></span>
            </div>
            <div class="text-[10.5px] text-ink-3 truncate mb-2" x-text="it.org"></div>
            <div class="flex items-center gap-1.5">
                <span class="text-[10.5px] mono text-ink-3" x-text="it.env"></span>
                <span class="text-ink-3 text-[10px]">·</span>
                <span class="text-[10.5px] text-ink-3" x-text="it.sync"></span>
            </div>
        </div>
    </template>
</div>

{{-- ── Autenticazione PDND ────────────────────────────────────────── --}}
<div class="space-y-6" x-data="{ openPdnd: true, openIpa: false, openInfocamere: false }">

    <div class="card overflow-hidden">
        <button type="button" @click="openPdnd = !openPdnd"
                class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-surface-2 transition-colors">
            <div class="flex-1">
                <div class="text-[13px] font-semibold">Autenticazione PDND</div>
                <div class="text-[11.5px] text-ink-3 mt-0.5">Client ID, token endpoint, chiave RS256 e chiave DPoP.</div>
            </div>
            @if($settings['pdnd_enabled'] === '1')
            <x-chip tone="success" :dot="true">Abilitato</x-chip>
            @else
            <x-chip tone="default">Disabilitato</x-chip>
            @endif
            <x-icon name="chevron" size="14" class="text-ink-3 shrink-0 transition-transform" ::class="openPdnd ? 'rotate-90' : ''" />
        </button>

        <div x-show="openPdnd" x-transition:enter="transition-all duration-150 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-all duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-5 pb-5 border-t border-line pt-4">
                <form method="POST" action="{{ route('admin.settings.pdnd.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="pdnd_enabled" name="pdnd_enabled" value="1"
                               @checked(old('pdnd_enabled', $settings['pdnd_enabled']) === '1')
                               class="h-4 w-4 rounded border-line accent-accent">
                        <label for="pdnd_enabled" class="text-[13px] font-medium">Abilita integrazione PDND</label>
                    </div>

                    <div>
                        <label for="pdnd_client_id" class="block text-xs font-semibold text-ink-2 mb-1.5">Client ID (e-service subscriber)</label>
                        <input type="text" id="pdnd_client_id" name="pdnd_client_id"
                               value="{{ old('pdnd_client_id', $settings['pdnd_client_id']) }}"
                               placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pdnd_client_id') border-danger @enderror">
                        <p class="mt-1 text-[11px] text-ink-3">Visibile nel portale <a href="https://selfcare.pagopa.it" target="_blank" rel="noopener" class="underline">selfcare.pagopa.it</a> → Client delle API.</p>
                        @error('pdnd_client_id')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="pdnd_token_endpoint" class="block text-xs font-semibold text-ink-2 mb-1.5">Token endpoint PDND</label>
                        <input type="url" id="pdnd_token_endpoint" name="pdnd_token_endpoint"
                               value="{{ old('pdnd_token_endpoint', $settings['pdnd_token_endpoint']) }}"
                               placeholder="https://auth.interop.pagopa.it/as/token.oauth2"
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pdnd_token_endpoint') border-danger @enderror">
                        @error('pdnd_token_endpoint')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="pdnd_private_key" class="block text-xs font-semibold text-ink-2 mb-1.5">Chiave privata RS256 (PEM) — client assertion</label>
                        <textarea id="pdnd_private_key" name="pdnd_private_key" rows="6"
                                  placeholder="-----BEGIN RSA PRIVATE KEY-----&#10;...&#10;-----END RSA PRIVATE KEY-----"
                                  class="w-full px-3 py-2 rounded-md border border-line bg-surface text-[12px] font-mono focus:border-accent focus:outline-none transition-colors resize-y @error('pdnd_private_key') border-danger @enderror">{{ old('pdnd_private_key', $settings['pdnd_private_key']) }}</textarea>
                        <p class="mt-1 text-[11px] text-ink-3">Lascia vuoto per non modificare. Carica la chiave privata RSA associata al certificato registrato su PDND.</p>
                        @error('pdnd_private_key')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div x-data="dpopKeyGenerator('{{ route('admin.settings.pdnd.generate-dpop-key') }}')" class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label for="pdnd_dpop_private_key" class="block text-xs font-semibold text-ink-2">Chiave privata ES256 P-256 (PEM) — DPoP proof</label>
                            <button type="button" @click="generate()"
                                    :disabled="loading"
                                    class="btn btn-sm text-[12px] px-3 h-7">
                                <span x-text="loading ? 'Generazione…' : 'Genera nuova chiave DPoP'"></span>
                            </button>
                        </div>
                        <textarea id="pdnd_dpop_private_key" name="pdnd_dpop_private_key" rows="6"
                                  x-model="pem"
                                  placeholder="-----BEGIN EC PRIVATE KEY-----&#10;...&#10;-----END EC PRIVATE KEY-----"
                                  class="w-full px-3 py-2 rounded-md border border-line bg-surface text-[12px] font-mono focus:border-accent focus:outline-none transition-colors resize-y @error('pdnd_dpop_private_key') border-danger @enderror">{{ old('pdnd_dpop_private_key', $settings['pdnd_dpop_private_key']) }}</textarea>
                        <template x-if="error">
                            <p class="text-[11px] text-danger" x-text="error"></p>
                        </template>
                        <p class="text-[11px] text-ink-3">Lascia vuoto per non modificare. La chiave DPoP è generata lato server (EC P-256) e non viene mai esposta al browser in testo in chiaro.</p>
                        @error('pdnd_dpop_private_key')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="btn btn-primary">Salva impostazioni PDND</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ── IPA (AgID) ─────────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <button type="button" @click="openIpa = !openIpa"
                class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-surface-2 transition-colors">
            <div class="flex-1">
                <div class="text-[13px] font-semibold">IPA — Indice Pubbliche Amministrazioni</div>
                <div class="text-[11.5px] text-ink-3 mt-0.5">
                    Sincronizza le PEC degli enti dal catalogo AgID IPA.
                    @if($settings['ipa_last_sync_at'])
                    · ultima sync {{ \Carbon\Carbon::parse($settings['ipa_last_sync_at'])->format('d/m/Y H:i') }}
                    @endif
                </div>
            </div>
            @if($settings['pdnd_ipa_url'])
            <x-chip tone="success" :dot="true">Configurato</x-chip>
            @else
            <x-chip tone="amber" :dot="true">Da configurare</x-chip>
            @endif
            <x-icon name="chevron" size="14" class="text-ink-3 shrink-0 transition-transform" ::class="openIpa ? 'rotate-90' : ''" />
        </button>

        <div x-show="openIpa" x-transition:enter="transition-all duration-150 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-all duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-5 pb-5 border-t border-line pt-4 space-y-4">
                <form method="POST" action="{{ route('admin.settings.pdnd.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="pdnd_enabled" value="{{ $settings['pdnd_enabled'] }}">
                    <input type="hidden" name="pdnd_client_id" value="{{ $settings['pdnd_client_id'] }}">
                    <input type="hidden" name="pdnd_token_endpoint" value="{{ $settings['pdnd_token_endpoint'] }}">
                    <input type="hidden" name="pdnd_infocamere_url" value="{{ $settings['pdnd_infocamere_url'] }}">

                    <div>
                        <label for="pdnd_ipa_url" class="block text-xs font-semibold text-ink-2 mb-1.5">URL base e-service IPA (PDND)</label>
                        <input type="url" id="pdnd_ipa_url" name="pdnd_ipa_url"
                               value="{{ old('pdnd_ipa_url', $settings['pdnd_ipa_url']) }}"
                               placeholder="https://api.interop.pagopa.it/ipa/..."
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pdnd_ipa_url') border-danger @enderror">
                        <p class="mt-1 text-[11px] text-ink-3">Richiedi il voucher dell'e-service su <a href="https://selfcare.pagopa.it" target="_blank" rel="noopener" class="underline">interop.pagopa.it</a>, poi incolla qui l'URL base dell'API.</p>
                        @error('pdnd_ipa_url')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-sm">Salva URL IPA</button>
                </form>

                <div class="pt-4 border-t border-line">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-[13px] font-medium">Sincronizzazione manuale</p>
                            @if($settings['ipa_last_sync_at'])
                            <p class="text-[11px] text-ink-3 mt-0.5">
                                Ultima sync: {{ \Carbon\Carbon::parse($settings['ipa_last_sync_at'])->format('d/m/Y H:i') }}
                                @if($settings['ipa_last_sync_result'])
                                @php $syncResult = json_decode($settings['ipa_last_sync_result'], true); @endphp
                                — {{ $syncResult['updated'] ?? 0 }} aggiornati, {{ $syncResult['errors'] ?? 0 }} errori
                                @endif
                            </p>
                            @else
                            <p class="text-[11px] text-ink-3 mt-0.5">Mai eseguita. La sync automatica è schedulata ogni giorno alle 02:00.</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('admin.settings.pdnd.sync-ipa') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm">Sincronizza ora</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── InfoCamere / Registro Imprese ──────────────────────────────── --}}
    <div class="card overflow-hidden">
        <button type="button" @click="openInfocamere = !openInfocamere"
                class="w-full flex items-center gap-3 px-5 py-4 text-left hover:bg-surface-2 transition-colors">
            <div class="flex-1">
                <div class="text-[13px] font-semibold">InfoCamere — Registro Imprese</div>
                <div class="text-[11.5px] text-ink-3 mt-0.5">Verifica camerale imprese per deleghe aziendali. Richiede accettazione manuale Unioncamere.</div>
            </div>
            @if($settings['pdnd_infocamere_url'])
            <x-chip tone="success" :dot="true">Configurato</x-chip>
            @else
            <x-chip tone="default">Non configurato</x-chip>
            @endif
            <x-icon name="chevron" size="14" class="text-ink-3 shrink-0 transition-transform" ::class="openInfocamere ? 'rotate-90' : ''" />
        </button>

        <div x-show="openInfocamere" x-transition:enter="transition-all duration-150 ease-out" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-all duration-100 ease-in" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="px-5 pb-5 border-t border-line pt-4">
                <div class="mb-4 flex gap-3 rounded-md bg-warning/10 border border-warning/30 px-4 py-3">
                    <svg class="h-4 w-4 text-warning mt-0.5 shrink-0" viewBox="0 0 16 16" fill="currentColor"><path d="M8 1a7 7 0 1 1 0 14A7 7 0 0 1 8 1zm0 1.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11zM8 10a.75.75 0 1 1 0 1.5A.75.75 0 0 1 8 10zm.75-5v4h-1.5V5h1.5z"/></svg>
                    <div class="text-[12px] text-ink">
                        <p class="font-semibold mb-1">Accettazione manuale richiesta</p>
                        <p>L'e-service <em>Servizi consultazione Registro Imprese</em> (Unioncamere) richiede approvazione manuale su PDND. Richiedi l'accordo di fruizione su <a href="https://selfcare.pagopa.it" target="_blank" rel="noopener" class="underline font-medium">interop.pagopa.it</a> e attendi l'accettazione prima di configurare l'URL.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.settings.pdnd.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="pdnd_enabled" value="{{ $settings['pdnd_enabled'] }}">
                    <input type="hidden" name="pdnd_client_id" value="{{ $settings['pdnd_client_id'] }}">
                    <input type="hidden" name="pdnd_token_endpoint" value="{{ $settings['pdnd_token_endpoint'] }}">
                    <input type="hidden" name="pdnd_ipa_url" value="{{ $settings['pdnd_ipa_url'] }}">

                    <div>
                        <label for="pdnd_infocamere_url" class="block text-xs font-semibold text-ink-2 mb-1.5">URL base e-service Registro Imprese (PDND)</label>
                        <input type="url" id="pdnd_infocamere_url" name="pdnd_infocamere_url"
                               value="{{ old('pdnd_infocamere_url', $settings['pdnd_infocamere_url']) }}"
                               placeholder="https://api.interop.pagopa.it/infocamere/..."
                               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pdnd_infocamere_url') border-danger @enderror">
                        @error('pdnd_infocamere_url')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="btn btn-sm">Salva URL Registro Imprese</button>
                </form>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
function dpopKeyGenerator(url) {
    return {
        url,
        loading: false,
        error: null,
        pem: document.getElementById('pdnd_dpop_private_key')?.value ?? '',

        async generate() {
            this.loading = true;
            this.error = null;

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                        'Accept': 'application/json',
                    },
                });

                const json = await res.json();

                if (!res.ok) {
                    this.error = json.error ?? 'Errore durante la generazione.';
                    return;
                }

                this.pem = json.private_key ?? '';
            } catch (err) {
                this.error = 'Errore di rete. Riprova più tardi.';
            } finally {
                this.loading = false;
            }
        },
    };
}
</script>
@endpush
@endsection

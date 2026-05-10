@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6"
     x-data="{
        endpoint: @js($endpoints[0]['path']),
        method: @js($endpoints[0]['method']),
        bodyText: '',
        loading: false,
        response: null,
        samples: @js($samples),
        endpoints: @js($endpoints),

        onEndpointChange() {
            const ep = this.endpoints.find(e => e.path === this.endpoint);
            if (ep) {
                this.method = ep.method;
                if (this.samples[ep.path] !== undefined) {
                    this.bodyText = JSON.stringify(this.samples[ep.path], null, 2);
                } else if (ep.method === 'GET') {
                    this.bodyText = '';
                }
            }
        },
        async send() {
            this.loading = true;
            this.response = null;
            const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
            const opts = {
                method: this.method,
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf ?? '' },
                credentials: 'same-origin',
            };
            if (this.method !== 'GET' && this.bodyText.trim() !== '') {
                opts.headers['Content-Type'] = 'application/json';
                opts.body = this.bodyText;
            }
            const t0 = performance.now();
            try {
                const res = await fetch(this.endpoint, opts);
                const text = await res.text();
                let body;
                try { body = JSON.parse(text); } catch (e) { body = { raw: text }; }
                this.response = {
                    status: res.status,
                    ok: res.ok,
                    elapsed_ms: Math.round(performance.now() - t0),
                    body,
                };
            } catch (e) {
                this.response = { error: e.message, elapsed_ms: Math.round(performance.now() - t0) };
            } finally {
                this.loading = false;
            }
        },
        copyCurl() {
            const lines = [];
            lines.push('curl -X ' + this.method + ' ' + window.location.origin + this.endpoint);
            lines.push('  -H Accept:application/json');
            lines.push('  -b cookies.txt');
            if (this.method !== 'GET' && this.bodyText.trim() !== '') {
                lines.push('  -H Content-Type:application/json');
                lines.push('  --data-binary @body.json');
            }
            navigator.clipboard.writeText(lines.join(' \\\n'));
        }
     }"
     x-init="onEndpointChange()">

    <div class="flex items-end gap-4">
        <div class="flex-1">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Diagnostica</div>
            <h1 class="text-[22px] font-semibold mt-1">API tester</h1>
            <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
                Esegue chiamate autenticate verso gli endpoint della piattaforma usando la sessione corrente.
                Visualizza richiesta, risposta JSON e tempi. Equivalente curl copiabile.
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[420px_1fr] gap-4">
        {{-- Request panel --}}
        <div class="card p-4 space-y-4">
            <div>
                <label class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold block mb-1.5">Endpoint</label>
                <select x-model="endpoint" @change="onEndpointChange"
                        class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px] mono">
                    @foreach($endpoints as $ep)
                        <option value="{{ $ep['path'] }}">{{ $ep['method'] }} {{ $ep['path'] }} — {{ $ep['name'] }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Body (JSON)</label>
                    <span class="chip mono text-[10.5px]" x-text="method"></span>
                </div>
                <textarea x-model="bodyText"
                          :disabled="method === 'GET'"
                          rows="10"
                          class="w-full p-2 border border-line rounded-md bg-surface-2 text-[11.5px] mono leading-relaxed disabled:opacity-50"
                          placeholder="GET non accetta body"></textarea>
            </div>

            <div class="flex items-center gap-2">
                <button @click="send" :disabled="loading"
                        class="btn btn-primary"
                        :class="loading ? 'opacity-60 cursor-wait' : ''">
                    <x-icon name="bolt" size="12" />
                    <span x-show="!loading">Esegui chiamata</span>
                    <span x-show="loading">In corso…</span>
                </button>
                <button @click="copyCurl" class="btn">
                    Copia curl
                </button>
            </div>
        </div>

        {{-- Response panel --}}
        <div class="card overflow-hidden">
            <div class="px-4 py-2.5 border-b border-line bg-surface-2 flex items-center gap-3">
                <span class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Risposta</span>
                <template x-if="response && !response.error">
                    <span :class="response.ok ? 'chip chip-success' : 'chip chip-danger'">
                        HTTP <span x-text="response.status"></span>
                    </span>
                </template>
                <template x-if="response?.error">
                    <span class="chip chip-danger">Network error</span>
                </template>
                <span x-show="response" class="mono text-[11px] text-ink-3">
                    <span x-text="response?.elapsed_ms"></span> ms
                </span>
            </div>
            <pre class="p-4 text-[11.5px] mono overflow-auto max-h-[560px] leading-relaxed"
                 x-text="response ? JSON.stringify(response.body ?? { error: response.error }, null, 2) : 'Nessuna chiamata eseguita.'"></pre>
        </div>
    </div>
</div>
@endsection

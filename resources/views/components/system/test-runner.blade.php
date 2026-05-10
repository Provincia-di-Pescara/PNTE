@props([
    'endpoint' => '',
    'method' => 'POST',
    'label' => 'Esegui test',
    'payload' => null,
    'service' => null,
])

@php
    $payloadJson = $payload !== null ? json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : 'null';
    $id = 'tr_'.\Illuminate\Support\Str::random(8);
@endphp

<div x-data="{
        loading: false,
        result: null,
        error: null,
        startedAt: null,
        async run() {
            this.loading = true;
            this.error = null;
            this.result = null;
            this.startedAt = performance.now();
            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                const opts = {
                    method: @js($method),
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf ?? '',
                    },
                    credentials: 'same-origin',
                };
                const payload = {{ $payloadJson }};
                if (payload !== null && @js($method) !== 'GET') {
                    opts.headers['Content-Type'] = 'application/json';
                    opts.body = JSON.stringify(payload);
                }
                const res = await fetch(@js($endpoint), opts);
                const text = await res.text();
                let body;
                try { body = JSON.parse(text); } catch (e) { body = { raw: text }; }
                this.result = { status: res.status, ok: res.ok, body };
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        }
     }"
     class="space-y-3"
     id="{{ $id }}">
    <div class="flex items-center gap-3">
        <button @click="run" :disabled="loading"
                class="btn btn-primary"
                :class="loading ? 'opacity-60 cursor-wait' : ''">
            <span x-show="!loading" class="flex items-center gap-1.5">
                <x-icon name="bolt" size="12" />
                {{ $label }}
            </span>
            <span x-show="loading" class="flex items-center gap-1.5">
                <svg class="animate-spin h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                    <circle cx="12" cy="12" r="9" stroke-opacity="0.3"/>
                    <path d="M21 12a9 9 0 0 0-9-9"/>
                </svg>
                In corso…
            </span>
        </button>
        <span x-show="result" class="text-[11.5px] text-ink-3 mono">
            <template x-if="result?.ok">
                <span class="chip chip-success">HTTP <span x-text="result.status"></span></span>
            </template>
            <template x-if="result && !result.ok">
                <span class="chip chip-danger">HTTP <span x-text="result.status"></span></span>
            </template>
        </span>
        <span x-show="error" class="chip chip-danger" x-text="error"></span>
    </div>

    <div x-show="result" x-cloak class="card overflow-hidden">
        <div class="px-4 py-2 border-b border-line bg-surface-2 flex items-center justify-between">
            <span class="text-[10.5px] text-ink-3 uppercase tracking-wider font-semibold">Risposta</span>
            <button @click="navigator.clipboard.writeText(JSON.stringify(result.body, null, 2))"
                    class="btn btn-sm btn-ghost text-[10.5px]">
                Copia
            </button>
        </div>
        <pre class="p-4 text-[11.5px] mono overflow-auto max-h-[420px] leading-relaxed"
             x-text="JSON.stringify(result?.body, null, 2)"></pre>
    </div>
</div>

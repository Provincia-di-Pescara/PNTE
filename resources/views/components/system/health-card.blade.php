@props([
    'service' => '',
    'label' => '',
    'icon' => 'layers',
])

<div class="card p-4"
     x-data="{
        loading: false,
        result: null,
        error: null,
        async refresh() {
            this.loading = true;
            this.error = null;
            try {
                const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                const res = await fetch('/api/v1/system/health/{{ $service }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf ?? '' },
                    credentials: 'same-origin',
                });
                this.result = await res.json();
            } catch (e) {
                this.error = e.message;
            } finally {
                this.loading = false;
            }
        }
     }"
     x-init="refresh()">
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-lg bg-surface-2 border border-line flex items-center justify-center text-ink-3 shrink-0">
            <x-icon name="{{ $icon }}" size="16" />
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 mb-1">
                <h3 class="text-[13.5px] font-semibold truncate">{{ $label }}</h3>
                <template x-if="loading">
                    <span class="chip text-[10px]">…</span>
                </template>
                <template x-if="!loading && result?.ok">
                    <span class="chip chip-success">operativo</span>
                </template>
                <template x-if="!loading && result && !result.ok">
                    <span class="chip chip-danger">errore</span>
                </template>
            </div>
            <div class="text-[11.5px] text-ink-3 mono truncate" x-text="result?.version ?? '—'"></div>
            <div class="text-[10.5px] text-ink-3 mt-1 flex items-center gap-2">
                <span class="num" x-text="result ? result.latency_ms + ' ms' : '—'"></span>
                <span x-show="result?.error" x-text="result?.error" class="text-danger truncate"></span>
            </div>
        </div>
        <button @click.prevent="refresh()" :disabled="loading"
                class="btn btn-sm btn-ghost shrink-0" title="Riesegui test">
            <x-icon name="refresh" size="11" />
        </button>
    </div>
</div>

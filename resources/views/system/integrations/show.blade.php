@extends('layouts.system')

@php
    $tabs = [
        ['key' => 'configure', 'label' => 'Configurazione', 'icon' => 'sliders', 'href' => route('system.integrations.show', ['service' => $service, 'tab' => 'configure'])],
        ['key' => 'test',      'label' => 'Test connessione', 'icon' => 'bolt',  'href' => route('system.integrations.show', ['service' => $service, 'tab' => 'test'])],
        ['key' => 'audit',     'label' => 'Audit',          'icon' => 'doc',   'href' => route('system.integrations.show', ['service' => $service, 'tab' => 'audit']), 'count' => $auditLogs->count()],
    ];
@endphp

@section('tabs')
    <x-system.tab-strip :tabs="$tabs" :active="$tab" />
@endsection

@section('content')
<div class="px-6 py-6 space-y-6">
    <div class="flex items-end gap-4">
        <div class="flex-1">
            <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Connettori & API</div>
            <h1 class="text-[22px] font-semibold mt-1">{{ $config['label'] }}</h1>
            <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">{{ $config['doc'] }}</p>
        </div>
    </div>

    @if($tab === 'configure')
        <form method="POST" action="{{ route('system.integrations.update', ['service' => $service]) }}" class="card p-5 max-w-3xl space-y-4">
            @csrf
            @method('PUT')

            @foreach($config['fields'] as $field)
                @php
                    $name = $field['name'];
                    $value = $values[$name] ?? '';
                    $type = $field['type'];
                @endphp

                <div class="space-y-1">
                    <label for="{{ $name }}" class="text-[11.5px] font-semibold text-ink-2 block">
                        {{ $field['label'] }}
                        @if(! empty($field['required']) && empty($field['secret']))
                            <span class="text-danger">*</span>
                        @endif
                    </label>

                    @if($type === 'boolean')
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" id="{{ $name }}" name="{{ $name }}" value="1"
                                   class="rounded border-line"
                                   @checked($value === '1' || $value === 1 || $value === true)>
                            <span class="text-[12.5px] text-ink-2">Attivo</span>
                        </label>
                    @elseif($type === 'textarea')
                        <textarea id="{{ $name }}" name="{{ $name }}" rows="6"
                                  class="w-full p-2 border border-line rounded-md bg-surface text-[12px] mono leading-relaxed"
                                  placeholder="{{ $field['hint'] ?? '' }}">{{ ! empty($field['secret']) ? '' : $value }}</textarea>
                        @if(! empty($field['secret']) && ! empty($values[$name.'_present']))
                            <div class="text-[11px] text-ink-3 mt-1">Valore corrente memorizzato. Lascia vuoto per non modificare.</div>
                        @endif
                    @elseif($type === 'select')
                        <select id="{{ $name }}" name="{{ $name }}"
                                class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px]">
                            @foreach($field['options'] as $optValue => $optLabel)
                                <option value="{{ $optValue }}" @selected((string) $value === (string) $optValue)>
                                    {{ $optLabel }}
                                </option>
                            @endforeach
                        </select>
                    @else
                        <input type="{{ $type === 'password' ? 'password' : ($type === 'number' ? 'number' : ($type === 'email' ? 'email' : ($type === 'url' ? 'url' : 'text'))) }}"
                               id="{{ $name }}" name="{{ $name }}"
                               value="{{ ! empty($field['secret']) ? '' : $value }}"
                               class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px] mono"
                               placeholder="{{ $field['hint'] ?? '' }}"
                               @if(! empty($field['secret']) && ! empty($values[$name.'_present'])) data-has-value="true" @endif>
                        @if(! empty($field['secret']) && ! empty($values[$name.'_present']))
                            <div class="text-[11px] text-ink-3 mt-1">Valore corrente memorizzato. Lascia vuoto per non modificare.</div>
                        @endif
                    @endif

                    @if(! empty($field['hint']) && empty($field['secret']))
                        <div class="text-[11px] text-ink-3">{{ $field['hint'] }}</div>
                    @endif
                </div>
            @endforeach

            <div class="flex items-center gap-2 pt-2 border-t border-line">
                <button type="submit" class="btn btn-primary">Salva</button>
                <a href="{{ route('system.integrations.show', ['service' => $service, 'tab' => 'test']) }}" class="btn">
                    <x-icon name="bolt" size="11" /> Vai a Test connessione
                </a>
            </div>
        </form>
    @elseif($tab === 'test')
        <div class="card p-5 max-w-3xl space-y-4">
            <h2 class="text-[14px] font-semibold">Test connessione · {{ $config['label'] }}</h2>
            <p class="text-[12.5px] text-ink-3">
                Esegue il diagnostic <code class="mono text-[11px]">{{ $config['diagnostic_key'] }}</code>
                contro la configurazione corrente. Risultato e latenza vengono tracciati in audit.
            </p>

            <x-system.test-runner
                method="GET"
                :endpoint="route('system.api.health.single', ['service' => $config['diagnostic_key']])"
                label="Esegui test {{ $config['label'] }}" />

            @if($service === 'smtp')
                <div class="border-t border-line pt-4 space-y-3">
                    <h3 class="text-[12.5px] font-semibold">Invio test mail</h3>
                    <p class="text-[11.5px] text-ink-3">Invia una mail di prova all'indirizzo specificato usando la configurazione SMTP corrente.</p>
                    <div x-data="{ to: '{{ auth()->user()?->email ?? '' }}' }">
                        <input type="email" x-model="to"
                               class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px] mono mb-3"
                               placeholder="destinatario@example.test">

                        <div x-data="{
                                loading: false, result: null,
                                async send() {
                                    this.loading = true; this.result = null;
                                    const csrf = document.querySelector('meta[name=csrf-token]')?.getAttribute('content');
                                    try {
                                        const res = await fetch('{{ route('system.api.test.mail') }}', {
                                            method: 'POST',
                                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf ?? '' },
                                            credentials: 'same-origin',
                                            body: JSON.stringify({ to: $root.querySelector('input').value })
                                        });
                                        this.result = { status: res.status, ok: res.ok, body: await res.json() };
                                    } catch (e) { this.result = { error: e.message }; } finally { this.loading = false; }
                                }
                             }">
                            <button @click.prevent="send" :disabled="loading" class="btn btn-primary">
                                <x-icon name="bell" size="11" />
                                <span x-show="!loading">Invia mail di test</span>
                                <span x-show="loading">In corso…</span>
                            </button>
                            <pre x-show="result" x-cloak class="card p-3 mt-3 text-[11.5px] mono overflow-auto" x-text="JSON.stringify(result?.body ?? result, null, 2)"></pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @else {{-- audit --}}
        <div class="card overflow-hidden">
            <div class="grid grid-cols-[160px_220px_140px_1fr] px-4 py-2 bg-surface-2 border-b border-line text-[10.5px] text-ink-3 uppercase font-semibold tracking-wider">
                <div>Quando</div><div>Action</div><div>Attore</div><div>Dettaglio</div>
            </div>
            @forelse($auditLogs as $log)
                <div class="grid grid-cols-[160px_220px_140px_1fr] px-4 py-2.5 items-center text-[12.5px] {{ ! $loop->last ? 'border-b border-line' : '' }}">
                    <span class="mono text-ink-3 text-[11px]">{{ $log->created_at->format('d M · H:i:s') }}</span>
                    <span class="mono text-[11px]">{{ $log->action }}</span>
                    <span>{{ $log->actor_name }}</span>
                    <span class="text-ink-3 truncate">{{ $log->detail }}</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-[12.5px] text-ink-3">
                    Nessun audit registrato per questo connettore.
                </div>
            @endforelse
        </div>
    @endif
</div>
@endsection

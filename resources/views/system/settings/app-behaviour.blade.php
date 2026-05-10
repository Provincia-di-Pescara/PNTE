@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6">
    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Sistema</div>
        <h1 class="text-[22px] font-semibold mt-1">App behaviour</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Comportamento applicativo globale. Le modifiche sono persistite in <code class="mono text-[11px]">settings</code>
            e applicate al boot dell'applicazione.
        </p>
    </div>

    <form method="POST" action="{{ route('system.settings.app-behaviour.update') }}" class="card p-5 max-w-2xl space-y-4">
        @csrf
        @method('PUT')

        <div class="space-y-1">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="app_debug" value="1" class="rounded border-line"
                       @checked((string) $values['app_debug'] === '1')>
                <span class="text-[12.5px] font-semibold text-ink-2">Debug mode</span>
            </label>
            <div class="text-[11px] text-ink-3 ml-6">Espone stack trace su errori. Mai abilitare in produzione.</div>
        </div>

        <div class="space-y-1">
            <label for="app_timezone" class="text-[11.5px] font-semibold text-ink-2 block">Timezone</label>
            <select id="app_timezone" name="app_timezone" class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px] mono">
                @foreach($timezones as $tz)
                    <option value="{{ $tz }}" @selected($values['app_timezone'] === $tz)>{{ $tz }}</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1">
            <label for="app_locale" class="text-[11.5px] font-semibold text-ink-2 block">Locale</label>
            <select id="app_locale" name="app_locale" class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px]">
                @foreach($locales as $code => $label)
                    <option value="{{ $code }}" @selected($values['app_locale'] === $code)>{{ $label }} ({{ $code }})</option>
                @endforeach
            </select>
        </div>

        <div class="space-y-1 border-t border-line pt-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="app_maintenance_mode" value="1" class="rounded border-line"
                       @checked((string) $values['app_maintenance_mode'] === '1')>
                <span class="text-[12.5px] font-semibold text-ink-2">Maintenance mode</span>
            </label>
            <div class="text-[11px] text-ink-3 ml-6">
                Equivale a <code class="mono">php artisan down</code>. Tutti gli utenti tranne system-admin
                vedranno la pagina di manutenzione.
            </div>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-line">
            <button type="submit" class="btn btn-primary">Salva</button>
        </div>
    </form>
</div>
@endsection

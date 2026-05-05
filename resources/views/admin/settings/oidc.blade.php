@extends('layouts.settings')

@section('settings-content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">SPID / CIE (OIDC)</h1>
    <p class="text-sm text-ink-2 mt-0.5">Configurazione del provider di identità per accesso con SPID e Carta d'Identità Elettronica.</p>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-danger/10 text-danger text-[13px] font-medium">{{ session('error') }}</div>
@endif

<div class="card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.oidc.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-3">
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" name="oidc_enabled" value="1" @checked(old('oidc_enabled', $settings['oidc_enabled']) === '1')
                       class="sr-only peer">
                <div class="w-9 h-5 bg-surface-2 rounded-full border border-line peer-checked:bg-accent peer-checked:border-accent transition-all
                            after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all
                            peer-checked:after:translate-x-4"></div>
            </label>
            <span class="text-sm font-medium">Abilita accesso SPID/CIE</span>
        </div>

        <div>
            <label for="oidc_discovery_url" class="block text-xs font-semibold text-ink-2 mb-1.5">Discovery URL (OpenID Connect)</label>
            <input type="url" id="oidc_discovery_url" name="oidc_discovery_url"
                   value="{{ old('oidc_discovery_url', $settings['oidc_discovery_url']) }}"
                   placeholder="https://proxy.spid.example.it/.well-known/openid-configuration"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('oidc_discovery_url') border-danger @enderror">
            <p class="mt-1 text-[11px] text-ink-3">Endpoint discovery del proxy SPID/CIE (es. Spid Proxy, OneIdentity, Agid).</p>
            @error('oidc_discovery_url')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="oidc_client_id" class="block text-xs font-semibold text-ink-2 mb-1.5">Client ID</label>
            <input type="text" id="oidc_client_id" name="oidc_client_id"
                   value="{{ old('oidc_client_id', $settings['oidc_client_id']) }}"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('oidc_client_id') border-danger @enderror">
            @error('oidc_client_id')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="oidc_client_secret" class="block text-xs font-semibold text-ink-2 mb-1.5">Client Secret</label>
            <input type="password" id="oidc_client_secret" name="oidc_client_secret" autocomplete="new-password"
                   placeholder="Lascia vuoto per non modificare"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            <p class="mt-1 text-[11px] text-ink-3">Salvato cifrato nel database. Lascia vuoto per mantenere il valore corrente.</p>
        </div>

        <div>
            <label for="oidc_scopes" class="block text-xs font-semibold text-ink-2 mb-1.5">Scopes</label>
            <input type="text" id="oidc_scopes" name="oidc_scopes"
                   value="{{ old('oidc_scopes', $settings['oidc_scopes']) }}"
                   placeholder="openid profile email"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('oidc_scopes') border-danger @enderror">
            <p class="mt-1 text-[11px] text-ink-3">Spazi come separatori. Per SPID include anche <code>fiscal_code</code>.</p>
            @error('oidc_scopes')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="pt-4 mt-2 border-t border-line">
            <button type="submit" class="btn btn-primary">Salva impostazioni OIDC</button>
        </div>
    </form>
</div>

<div class="mt-4 max-w-2xl p-4 rounded-md bg-surface-2 border border-line text-xs text-ink-3">
    <strong class="text-ink-2">Nota:</strong> Le credenziali OIDC vengono lette all'avvio della sessione di autenticazione.
    Dopo la modifica, le sessioni attive non sono influenzate.
    Consulta la documentazione AgID per i requisiti del Service Provider SPID Level 2.
</div>
@endsection

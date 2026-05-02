@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.settings.index') }}" class="text-ink-3 hover:text-ink transition-colors">
        <x-icon name="chevron" size="14" class="rotate-180" />
    </a>
    <div>
        <h1 class="text-xl font-bold tracking-tight">Server PEC</h1>
        <p class="text-sm text-ink-2 mt-0.5">Configurazione casella PEC per ricezione e invio comunicazioni ufficiali.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-danger/10 text-danger text-[13px] font-medium">{{ session('error') }}</div>
@endif

<div class="space-y-6 max-w-2xl">
    <!-- IMAP -->
    <div class="card p-6">
        <h2 class="text-sm font-semibold mb-4">Ricezione (IMAP)</h2>
        <form method="POST" action="{{ route('admin.settings.pec.update') }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label for="pec_host" class="block text-xs font-semibold text-ink-2 mb-1.5">Host IMAP</label>
                    <input type="text" id="pec_host" name="pec_host"
                           value="{{ old('pec_host', $settings['pec_host']) }}"
                           placeholder="imaps.pec.example.it"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pec_host') border-danger @enderror">
                    @error('pec_host')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="pec_port" class="block text-xs font-semibold text-ink-2 mb-1.5">Porta</label>
                    <input type="number" id="pec_port" name="pec_port"
                           value="{{ old('pec_port', $settings['pec_port']) }}"
                           min="1" max="65535"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pec_port') border-danger @enderror">
                    @error('pec_port')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="pec_encryption" class="block text-xs font-semibold text-ink-2 mb-1.5">Cifratura</label>
                <select id="pec_encryption" name="pec_encryption"
                        class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    <option value="ssl" @selected(old('pec_encryption', $settings['pec_encryption']) === 'ssl')>SSL</option>
                    <option value="tls" @selected(old('pec_encryption', $settings['pec_encryption']) === 'tls')>TLS/STARTTLS</option>
                    <option value="none" @selected(old('pec_encryption', $settings['pec_encryption']) === 'none')>Nessuna</option>
                </select>
            </div>

            <div>
                <label for="pec_username" class="block text-xs font-semibold text-ink-2 mb-1.5">Username (indirizzo PEC)</label>
                <input type="email" id="pec_username" name="pec_username"
                       value="{{ old('pec_username', $settings['pec_username']) }}"
                       placeholder="protocollo@pec.provincia.example.it"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pec_username') border-danger @enderror">
                @error('pec_username')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="pec_password" class="block text-xs font-semibold text-ink-2 mb-1.5">Password</label>
                <input type="password" id="pec_password" name="pec_password" autocomplete="new-password"
                       placeholder="Lascia vuoto per non modificare"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
                <p class="mt-1 text-[11px] text-ink-3">Salvata cifrata nel database.</p>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-2">
                    <label for="pec_smtp_host" class="block text-xs font-semibold text-ink-2 mb-1.5">Host SMTP (invio PEC)</label>
                    <input type="text" id="pec_smtp_host" name="pec_smtp_host"
                           value="{{ old('pec_smtp_host', $settings['pec_smtp_host']) }}"
                           placeholder="smtp.pec.example.it"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pec_smtp_host') border-danger @enderror">
                    @error('pec_smtp_host')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="pec_smtp_port" class="block text-xs font-semibold text-ink-2 mb-1.5">Porta SMTP</label>
                    <input type="number" id="pec_smtp_port" name="pec_smtp_port"
                           value="{{ old('pec_smtp_port', $settings['pec_smtp_port']) }}"
                           min="1" max="65535"
                           class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('pec_smtp_port') border-danger @enderror">
                    @error('pec_smtp_port')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="pt-4 border-t border-line flex gap-3">
                <button type="submit" class="btn btn-primary">Salva</button>
            </div>
        </form>
    </div>

    <!-- Test IMAP -->
    <div class="card p-5">
        <h2 class="text-sm font-semibold mb-2">Test connessione IMAP</h2>
        <p class="text-xs text-ink-2 mb-3">Verifica che le credenziali IMAP siano corrette aprendo una connessione di test.</p>
        <form method="POST" action="{{ route('admin.settings.pec.test') }}">
            @csrf
            <button type="submit" class="btn btn-secondary">
                <x-icon name="check" size="14" />
                Testa connessione IMAP
            </button>
        </form>
    </div>
</div>
@endsection

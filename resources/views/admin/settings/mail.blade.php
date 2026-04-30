@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Impostazioni email</h1>
    <p class="text-sm text-ink-2 mt-1">Configurazione server SMTP per notifiche e PEC.</p>
</div>

<div class="card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.mail.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="mail_host" class="block text-xs font-semibold text-ink-2 mb-1.5">Host SMTP</label>
                <input type="text" id="mail_host" name="mail_host" value="{{ old('mail_host', $settings['mail_host']) }}" placeholder="smtp.example.com"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('mail_host') border-danger @enderror">
                @error('mail_host')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="mail_port" class="block text-xs font-semibold text-ink-2 mb-1.5">Porta</label>
                <input type="number" id="mail_port" name="mail_port" value="{{ old('mail_port', $settings['mail_port']) }}" min="1" max="65535"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('mail_port') border-danger @enderror">
                @error('mail_port')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="mail_encryption" class="block text-xs font-semibold text-ink-2 mb-1.5">Cifratura</label>
            <select id="mail_encryption" name="mail_encryption"
                    class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                @foreach(['tls' => 'TLS (raccomandato)', 'ssl' => 'SSL', 'none' => 'Nessuna'] as $val => $label)
                <option value="{{ $val }}" @selected(old('mail_encryption', $settings['mail_encryption']) === $val)>{{ $label }}</option>
                @endforeach
            </select>
            @error('mail_encryption')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="mail_username" class="block text-xs font-semibold text-ink-2 mb-1.5">Username SMTP</label>
            <input type="text" id="mail_username" name="mail_username" value="{{ old('mail_username', $settings['mail_username']) }}" autocomplete="off"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('mail_username') border-danger @enderror">
            @error('mail_username')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="mail_password" class="block text-xs font-semibold text-ink-2 mb-1.5">
                Password SMTP <span class="font-normal text-ink-3">(lascia vuoto per non modificare)</span>
            </label>
            <input type="password" id="mail_password" name="mail_password" autocomplete="new-password"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            @error('mail_password')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="mail_from_address" class="block text-xs font-semibold text-ink-2 mb-1.5">Indirizzo mittente</label>
                <input type="email" id="mail_from_address" name="mail_from_address" value="{{ old('mail_from_address', $settings['mail_from_address']) }}" placeholder="noreply@provincia.pe.it"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('mail_from_address') border-danger @enderror">
                @error('mail_from_address')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="mail_from_name" class="block text-xs font-semibold text-ink-2 mb-1.5">Nome mittente</label>
                <input type="text" id="mail_from_name" name="mail_from_name" value="{{ old('mail_from_name', $settings['mail_from_name']) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('mail_from_name') border-danger @enderror">
                @error('mail_from_name')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="pt-4 mt-6 border-t border-line">
            <button type="submit" class="btn btn-primary">Salva impostazioni</button>
        </div>
    </form>

    {{-- Sezione test email --}}
    <div class="mt-8 pt-6 border-t border-line">
        <h2 class="text-sm font-semibold mb-1">Verifica configurazione</h2>
        <p class="text-[13px] text-ink-2 mb-4">Invia un'email di test al tuo indirizzo ({{ auth()->user()->email }}) usando le impostazioni salvate.</p>
        <form method="POST" action="{{ route('admin.settings.mail.test') }}">
            @csrf
            <button type="submit" class="btn">
                <x-icon name="user" size="14" /> Invia email di test
            </button>
        </form>
    </div>
</div>
@endsection

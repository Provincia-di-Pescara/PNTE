@extends('setup.layout')
@section('content')

<div class="mb-6">
    <h2 class="text-lg font-semibold text-slate-900">Configurazione posta elettronica</h2>
    <p class="text-sm text-slate-500 mt-1">Impostazioni SMTP per l'invio di notifiche. Puoi saltare questo passaggio e configurarlo in seguito dal pannello admin.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 p-4">
    <p class="text-sm text-green-700">{{ session('success') }}</p>
</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
    <p class="text-sm text-red-700">{{ session('error') }}</p>
</div>
@endif
@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('setup.step3.store') }}" class="space-y-5" x-data="{ configured: {{ !empty($data['mail_host']) ? 'true' : 'false' }} }">
    @csrf

    <div class="flex items-center gap-3 p-3 rounded-lg bg-slate-50 border border-slate-200">
        <input type="checkbox" id="configure_mail" x-model="configured"
               class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500">
        <label for="configure_mail" class="text-sm font-medium text-slate-700 cursor-pointer">
            Configura server SMTP ora
        </label>
    </div>

    <div x-show="configured" x-cloak class="space-y-5">
        <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2 sm:col-span-1">
                <label for="mail_host" class="block text-sm font-medium text-slate-700">Host SMTP</label>
                <input type="text" id="mail_host" name="mail_host"
                       value="{{ old('mail_host', $data['mail_host'] ?? '') }}"
                       placeholder="smtp.example.com"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="col-span-2 sm:col-span-1">
                <label for="mail_port" class="block text-sm font-medium text-slate-700">Porta</label>
                <input type="number" id="mail_port" name="mail_port"
                       value="{{ old('mail_port', $data['mail_port'] ?? '587') }}"
                       min="1" max="65535"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        <div>
            <label for="mail_encryption" class="block text-sm font-medium text-slate-700">Cifratura</label>
            <select id="mail_encryption" name="mail_encryption"
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                <option value="tls" @selected(old('mail_encryption', $data['mail_encryption'] ?? 'tls') === 'tls')>TLS (raccomandato)</option>
                <option value="ssl" @selected(old('mail_encryption', $data['mail_encryption'] ?? '') === 'ssl')>SSL</option>
                <option value="none" @selected(old('mail_encryption', $data['mail_encryption'] ?? '') === 'none')>Nessuna</option>
            </select>
        </div>

        <div>
            <label for="mail_username" class="block text-sm font-medium text-slate-700">Username SMTP</label>
            <input type="text" id="mail_username" name="mail_username"
                   value="{{ old('mail_username', $data['mail_username'] ?? '') }}"
                   autocomplete="off"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div>
            <label for="mail_password" class="block text-sm font-medium text-slate-700">Password SMTP</label>
            <input type="password" id="mail_password" name="mail_password"
                   autocomplete="new-password"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="mail_from_address" class="block text-sm font-medium text-slate-700">Indirizzo mittente</label>
                <input type="email" id="mail_from_address" name="mail_from_address"
                       value="{{ old('mail_from_address', $data['mail_from_address'] ?? '') }}"
                       placeholder="noreply@provincia.pe.it"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div>
                <label for="mail_from_name" class="block text-sm font-medium text-slate-700">Nome mittente</label>
                <input type="text" id="mail_from_name" name="mail_from_name"
                       value="{{ old('mail_from_name', $data['mail_from_name'] ?? 'GTE Abruzzo') }}"
                       class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
        </div>

        <div class="pt-1">
            <button type="submit" formaction="{{ route('setup.test-email') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-2 border border-slate-300 text-slate-600 text-sm font-medium rounded-lg hover:bg-slate-50 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                Invia email di test
            </button>
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <a href="{{ route('setup.step2') }}"
           class="flex-1 flex justify-center py-2.5 px-4 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
            Indietro
        </a>
        <button type="submit"
                class="flex-1 flex justify-center py-2.5 px-4 rounded-lg bg-blue-600 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            Continua
        </button>
    </div>
</form>

@endsection

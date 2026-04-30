@extends('setup.layout')
@section('content')

<div class="mb-6">
    <h2 class="text-lg font-semibold text-slate-900">Riepilogo configurazione</h2>
    <p class="text-sm text-slate-500 mt-1">Verifica i dati prima di completare il setup. Potrai modificarli in seguito dal pannello admin.</p>
</div>

<div class="space-y-4 mb-8">
    {{-- Admin account --}}
    <div class="rounded-lg border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between bg-slate-50 px-4 py-2 border-b border-slate-200">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Account amministratore</span>
            <a href="{{ route('setup.step1') }}" class="text-xs text-blue-600 hover:underline">Modifica</a>
        </div>
        <dl class="divide-y divide-slate-100">
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">Nome</dt>
                <dd class="text-slate-900 font-medium">{{ $admin['name'] }}</dd>
            </div>
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">E-mail</dt>
                <dd class="text-slate-900 font-medium">{{ $admin['email'] }}</dd>
            </div>
        </dl>
    </div>

    {{-- App settings --}}
    <div class="rounded-lg border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between bg-slate-50 px-4 py-2 border-b border-slate-200">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Applicazione</span>
            <a href="{{ route('setup.step2') }}" class="text-xs text-blue-600 hover:underline">Modifica</a>
        </div>
        <dl class="divide-y divide-slate-100">
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">Nome</dt>
                <dd class="text-slate-900 font-medium">{{ $app['app_name'] }}</dd>
            </div>
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">Fuso orario</dt>
                <dd class="text-slate-900 font-medium">{{ $app['app_timezone'] }}</dd>
            </div>
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">Lingua</dt>
                <dd class="text-slate-900 font-medium">{{ $app['app_locale'] === 'it' ? 'Italiano' : 'English' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Mail --}}
    <div class="rounded-lg border border-slate-200 overflow-hidden">
        <div class="flex items-center justify-between bg-slate-50 px-4 py-2 border-b border-slate-200">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Posta elettronica</span>
            <a href="{{ route('setup.step3') }}" class="text-xs text-blue-600 hover:underline">Modifica</a>
        </div>
        @if(!empty($mail['mail_host']))
        <dl class="divide-y divide-slate-100">
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">Host</dt>
                <dd class="text-slate-900 font-medium">{{ $mail['mail_host'] }}:{{ $mail['mail_port'] }}</dd>
            </div>
            <div class="flex px-4 py-2.5 text-sm">
                <dt class="w-24 shrink-0 text-slate-500">Mittente</dt>
                <dd class="text-slate-900 font-medium">{{ $mail['mail_from_name'] }} &lt;{{ $mail['mail_from_address'] }}&gt;</dd>
            </div>
        </dl>
        @else
        <p class="px-4 py-3 text-sm text-slate-400 italic">Non configurata — potrai impostarla in seguito.</p>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('setup.complete') }}">
    @csrf
    <button type="submit"
            class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-lg bg-green-600 text-sm font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        Completa setup e accedi
    </button>
</form>

@endsection

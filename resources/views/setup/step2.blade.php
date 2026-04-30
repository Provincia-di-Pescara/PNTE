@extends('setup.layout')
@section('content')

<div class="mb-6">
    <h2 class="text-lg font-semibold text-slate-900">Impostazioni applicazione</h2>
    <p class="text-sm text-slate-500 mt-1">Personalizza il nome e le impostazioni regionali del sistema.</p>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('setup.step2.store') }}" class="space-y-5">
    @csrf

    <div>
        <label for="app_name" class="block text-sm font-medium text-slate-700">Nome applicazione</label>
        <input type="text" id="app_name" name="app_name"
               value="{{ old('app_name', $data['app_name'] ?? 'GTE Abruzzo') }}"
               required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('app_name') border-red-400 @enderror">
    </div>

    <div>
        <label for="app_timezone" class="block text-sm font-medium text-slate-700">Fuso orario</label>
        <select id="app_timezone" name="app_timezone"
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            @foreach(\DateTimeZone::listIdentifiers(\DateTimeZone::EUROPE) as $tz)
            <option value="{{ $tz }}" @selected(old('app_timezone', $data['app_timezone'] ?? 'Europe/Rome') === $tz)>{{ $tz }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-slate-700 mb-2">Lingua</label>
        <div class="flex gap-4">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="app_locale" value="it"
                       @checked(old('app_locale', $data['app_locale'] ?? 'it') === 'it')
                       class="text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-slate-700">Italiano</span>
            </label>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio" name="app_locale" value="en"
                       @checked(old('app_locale', $data['app_locale'] ?? 'it') === 'en')
                       class="text-blue-600 focus:ring-blue-500">
                <span class="text-sm text-slate-700">English</span>
            </label>
        </div>
    </div>

    <div class="flex gap-3 pt-2">
        <a href="{{ route('setup.step1') }}"
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

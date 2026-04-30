@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.settings.index') }}" class="text-ink-3 hover:text-ink transition-colors">
        <x-icon name="chevron" size="14" class="rotate-180" />
    </a>
    <div>
        <h1 class="text-xl font-bold tracking-tight">Impostazioni generali</h1>
        <p class="text-sm text-ink-2 mt-0.5">Nome applicazione, fuso orario e lingua.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif

<div class="card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.general.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="app_name" class="block text-xs font-semibold text-ink-2 mb-1.5">Nome applicazione</label>
            <input type="text" id="app_name" name="app_name" value="{{ old('app_name', $settings['app_name']) }}"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('app_name') border-danger @enderror">
            <p class="mt-1 text-[11px] text-ink-3">Mostrato nel titolo del browser e nelle email.</p>
            @error('app_name')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="app_timezone" class="block text-xs font-semibold text-ink-2 mb-1.5">Fuso orario</label>
            <select id="app_timezone" name="app_timezone"
                    class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                @foreach($timezones as $tz)
                <option value="{{ $tz }}" @selected(old('app_timezone', $settings['app_timezone']) === $tz)>{{ $tz }}</option>
                @endforeach
            </select>
            @error('app_timezone')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="app_locale" class="block text-xs font-semibold text-ink-2 mb-1.5">Lingua</label>
            <select id="app_locale" name="app_locale"
                    class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                <option value="it" @selected(old('app_locale', $settings['app_locale']) === 'it')>Italiano</option>
                <option value="en" @selected(old('app_locale', $settings['app_locale']) === 'en')>English</option>
            </select>
            @error('app_locale')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="pt-4 mt-6 border-t border-line">
            <button type="submit" class="btn btn-primary">Salva impostazioni</button>
        </div>
    </form>
</div>
@endsection

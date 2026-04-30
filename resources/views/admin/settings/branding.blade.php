@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-center gap-3">
    <a href="{{ route('admin.settings.index') }}" class="text-ink-3 hover:text-ink transition-colors">
        <x-icon name="chevron" size="14" class="rotate-180" />
    </a>
    <div>
        <h1 class="text-xl font-bold tracking-tight">Branding</h1>
        <p class="text-sm text-ink-2 mt-0.5">Logo, titolo e colori dell'istanza.</p>
    </div>
</div>

@if(session('success'))
<div class="mb-4 px-4 py-2.5 rounded-md bg-success/10 text-success text-[13px] font-medium">{{ session('success') }}</div>
@endif

<div class="card p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.settings.branding.update') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label for="brand_header_title" class="block text-xs font-semibold text-ink-2 mb-1.5">Titolo applicazione</label>
            <input type="text" id="brand_header_title" name="brand_header_title"
                   value="{{ old('brand_header_title', $settings['brand_header_title']) }}"
                   placeholder="GTE Abruzzo"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('brand_header_title') border-danger @enderror">
            <p class="mt-1 text-[11px] text-ink-3">Mostrato nella sidebar e nell'intestazione.</p>
            @error('brand_header_title')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="brand_primary_color" class="block text-xs font-semibold text-ink-2 mb-1.5">Colore primario (HEX)</label>
            <div class="flex items-center gap-3">
                <input type="color" id="brand_primary_color_picker" name="brand_primary_color"
                       value="{{ old('brand_primary_color', $settings['brand_primary_color']) }}"
                       class="w-10 h-9 rounded-md border border-line bg-surface cursor-pointer p-0.5">
                <input type="text" id="brand_primary_color_text"
                       value="{{ old('brand_primary_color', $settings['brand_primary_color']) }}"
                       placeholder="#0055CC"
                       class="flex-1 h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('brand_primary_color') border-danger @enderror"
                       x-data
                       x-on:input="document.getElementById('brand_primary_color_picker').value = $event.target.value">
            </div>
            @error('brand_primary_color')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="brand_logo" class="block text-xs font-semibold text-ink-2 mb-1.5">Logo</label>
            @if($settings['brand_logo_url'])
            <div class="mb-3 flex items-center gap-3">
                <img src="{{ $settings['brand_logo_url'] }}" alt="Logo attuale" class="h-10 object-contain border border-line rounded p-1">
                <span class="text-[12px] text-ink-3">Logo attuale</span>
            </div>
            @endif
            <input type="file" id="brand_logo" name="brand_logo" accept="image/png,image/svg+xml,image/jpeg,image/webp"
                   class="w-full text-[13px] text-ink-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border file:border-line file:bg-surface-2 file:text-ink file:text-[12px] file:cursor-pointer hover:file:bg-surface">
            <p class="mt-1 text-[11px] text-ink-3">PNG, SVG, JPG o WEBP — max 512 KB.</p>
            @error('brand_logo')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="pt-4 mt-6 border-t border-line">
            <button type="submit" class="btn btn-primary">Salva branding</button>
        </div>
    </form>
</div>
@endsection

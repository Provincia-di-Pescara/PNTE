@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Impostazioni</h1>
    <p class="text-sm text-ink-2 mt-1">Configura l'istanza, le integrazioni e gli accessi.</p>
</div>

<div x-data="{ search: '' }">
    <div class="mb-5">
        <div class="relative max-w-sm">
            <x-icon name="search" size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-ink-3 pointer-events-none" />
            <input type="text" x-model="search" placeholder="Cerca impostazione..." autocomplete="off"
                   class="w-full h-9 pl-8 pr-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($categories as $cat)
        @php
            $keywords = implode(' ', $cat['keywords']);
            $label = $cat['label'];
            $isActive = $cat['status'] === 'active';
        @endphp
        <div x-show="search === '' || '{{ strtolower($label . ' ' . $keywords) }}'.includes(search.toLowerCase())"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            @if($isActive && $cat['route'])
            <a href="{{ route($cat['route']) }}"
               class="card p-4 flex items-start gap-3 hover:border-accent transition-colors group">
                <div class="w-8 h-8 rounded-md bg-surface-2 flex items-center justify-center shrink-0 group-hover:bg-accent/10 transition-colors">
                    <x-icon name="{{ $cat['icon'] }}" size="14" class="text-ink-2 group-hover:text-accent transition-colors" />
                </div>
                <div class="min-w-0">
                    <div class="text-[13px] font-semibold text-ink leading-tight">{{ $label }}</div>
                    <div class="text-[11px] text-ink-3 mt-0.5 truncate">{{ implode(', ', array_slice($cat['keywords'], 0, 3)) }}</div>
                </div>
                <x-icon name="chevron" size="14" class="text-ink-3 shrink-0 mt-1 ml-auto" />
            </a>
            @else
            <div class="card p-4 flex items-start gap-3 opacity-50 cursor-not-allowed">
                <div class="w-8 h-8 rounded-md bg-surface-2 flex items-center justify-center shrink-0">
                    <x-icon name="{{ $cat['icon'] }}" size="14" class="text-ink-3" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <div class="text-[13px] font-semibold text-ink leading-tight">{{ $label }}</div>
                        <span class="text-[10px] bg-surface-2 text-ink-3 px-1.5 py-0.5 rounded font-medium uppercase tracking-wider">Soon</span>
                    </div>
                    <div class="text-[11px] text-ink-3 mt-0.5 truncate">{{ implode(', ', array_slice($cat['keywords'], 0, 3)) }}</div>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    <div x-show="search !== '' && document.querySelectorAll('[x-show]').length === 0"
         class="text-sm text-ink-3 mt-4">
        Nessuna impostazione trovata per "<span x-text="search"></span>".
    </div>
</div>
@endsection

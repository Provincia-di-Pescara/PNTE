@props([
    'tabs' => [],
    'active' => null,
])

@php
    /** @var array<int, array{key: string, label: string, href: string, icon?: string, count?: int}> $tabs */
@endphp

@if(! empty($tabs))
<div class="border-b border-line bg-surface px-6">
    <div class="flex items-center gap-1 -mb-px overflow-x-auto">
        @foreach($tabs as $tab)
            @php
                $isActive = $tab['key'] === $active;
            @endphp
            <a href="{{ $tab['href'] }}"
               class="flex items-center gap-1.5 px-3 py-2.5 text-[13px] font-medium border-b-2 no-underline whitespace-nowrap transition-colors {{ $isActive ? 'border-accent text-ink' : 'border-transparent text-ink-3 hover:text-ink hover:bg-surface-2' }}">
                @if(! empty($tab['icon']))
                    <x-icon name="{{ $tab['icon'] }}" size="12" />
                @endif
                {{ $tab['label'] }}
                @if(isset($tab['count']))
                    <span class="ml-1 text-[10.5px] tabular-nums {{ $isActive ? 'text-accent-ink' : 'text-ink-3' }}">{{ $tab['count'] }}</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endif

@props(['name', 'tone' => 'default'])
@php
$initials = collect(explode(' ', $name))->map(fn($part) => substr($part, 0, 1))->take(2)->implode('');
$c = match($tone) {
    'amber' => 'bg-accent-bg text-accent-ink',
    'info' => 'bg-info-bg text-info',
    default => 'bg-surface-2 text-ink-2 border border-line',
};
@endphp
<div {{ $attributes->merge(['class' => "flex items-center justify-center w-8 h-8 rounded-full text-xs font-semibold uppercase $c"]) }}>
    {{ $initials }}
</div>

@props(['tone' => 'default', 'dot' => false])
@php $c = match($tone) {
    'amber'   => 'bg-accent-bg text-accent-ink border-accent/30',
    'success' => 'bg-success-bg text-success border-success/30',
    'danger'  => 'bg-danger-bg text-danger border-danger/30',
    'info'    => 'bg-info-bg text-info border-info/30',
    default   => 'bg-surface-2 text-ink-2 border-line',
}; @endphp
<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[11.5px] font-medium border $c"]) }}>
    @if($dot)<span class="w-1.5 h-1.5 rounded-full bg-current"></span>@endif
    {{ $slot }}
</span>

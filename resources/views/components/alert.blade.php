@props(['tone' => 'info'])
@php $c = match($tone) {
    'success' => 'bg-success-bg text-success border-success/40',
    'danger'  => 'bg-danger-bg text-danger border-danger/40',
    'warning' => 'bg-accent-bg text-accent-ink border-accent/40',
    default   => 'bg-info-bg text-info border-info/40',
}; @endphp
<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm $c"]) }}>
    {{ $slot }}
</div>

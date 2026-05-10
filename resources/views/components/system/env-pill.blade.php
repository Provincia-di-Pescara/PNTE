@props([
    'env' => null,
])

@php
    $env = $env ?? app()->environment();
    $tone = match ($env) {
        'production', 'prod' => 'success',
        'staging' => 'amber',
        'local', 'development', 'dev', 'testing' => 'info',
        default => 'default',
    };
    $label = match ($env) {
        'production', 'prod' => 'Produzione',
        'staging' => 'Staging',
        'local' => 'Locale',
        'development', 'dev' => 'Development',
        'testing' => 'Testing',
        default => ucfirst($env),
    };
@endphp

<span class="chip chip-{{ $tone === 'amber' ? 'amber' : ($tone === 'success' ? 'success' : ($tone === 'info' ? 'info' : '')) }}"
      title="APP_ENV: {{ $env }}">
    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    Ambiente: {{ $label }}
</span>

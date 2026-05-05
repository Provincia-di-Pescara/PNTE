@extends('layouts.app')

@section('nav-items')
    @php
        $nav = [
            ['icon' => 'sliders',  'label' => 'Pannello',       'route' => 'system.dashboard',    'pattern' => 'system.dashboard'],
            ['icon' => 'flag',     'label' => 'Tenant',         'route' => 'system.tenants',      'pattern' => 'system.tenants*'],
            ['icon' => 'layers',   'label' => 'Connettori',      'route' => 'system.connectors',   'pattern' => 'system.connectors'],
            ['icon' => 'bell',     'label' => 'SMTP/IMAP',      'route' => 'system.smtp',         'pattern' => 'system.smtp*'],
            ['icon' => 'clock',    'label' => 'Scheduler',       'route' => 'system.scheduler',    'pattern' => 'system.scheduler'],
            ['icon' => 'doc',      'label' => 'Telemetria',      'route' => 'system.metrics',      'pattern' => 'system.metrics'],
            ['icon' => 'map',      'label' => 'Geo dataset',     'route' => 'system.geo',          'pattern' => 'system.geo'],
            ['icon' => 'doc',      'label' => 'Audit infra',     'route' => 'system.audit',        'pattern' => 'system.audit'],
            ['icon' => 'refresh',  'label' => 'Release',         'route' => 'system.release',      'pattern' => 'system.release'],
            ['icon' => 'user',     'label' => 'Utenti sistema',  'route' => 'system.users.index',  'pattern' => 'system.users.*'],
        ];
    @endphp

    @foreach($nav as $item)
        @php
            $isActive = request()->routeIs($item['pattern']);
            $classes  = $isActive
                ? 'bg-surface-2 text-ink font-semibold'
                : 'text-ink-2 hover:bg-surface-2 hover:text-ink font-medium';
        @endphp
        <a href="{{ route($item['route']) }}"
           class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-[13px] transition-colors {{ $classes }}">
            <x-icon name="{{ $item['icon'] }}" size="14" class="{{ $isActive ? 'text-accent' : '' }}" />
            <span class="flex-1">{{ $item['label'] }}</span>
        </a>
    @endforeach
@endsection

@extends('layouts.app')

@section('nav-items')
    @php
        $nav = [
            ['icon' => 'layers', 'label' => 'Overview',          'route' => 'agency.dashboard',  'pattern' => 'agency.dashboard'],
            ['icon' => 'users',  'label' => 'Gestione Partner',  'route' => 'agency.partners',   'pattern' => 'agency.partners'],
            ['icon' => 'doc',    'label' => 'Pratiche cliente',  'route' => 'agency.applications','pattern' => 'agency.applications.*'],
            ['icon' => 'shield', 'label' => 'Audit',             'route' => 'agency.audit',      'pattern' => 'agency.audit'],
        ];
    @endphp

    @foreach($nav as $item)
        @php
            $isActive = Route::has($item['route']) && request()->routeIs($item['pattern']);
            $classes  = $isActive
                ? 'bg-surface-2 text-ink font-semibold'
                : 'text-ink-2 hover:bg-surface-2 hover:text-ink font-medium';
        @endphp
        <a href="{{ Route::has($item['route']) ? route($item['route']) : '#' }}"
           class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-[13px] transition-colors {{ $classes }}">
            <x-icon name="{{ $item['icon'] }}" size="14" class="{{ $isActive ? 'text-accent' : '' }}" />
            <span class="flex-1">{{ $item['label'] }}</span>
        </a>
    @endforeach
@endsection

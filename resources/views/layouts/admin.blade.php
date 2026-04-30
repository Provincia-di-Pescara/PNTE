@extends('layouts.app')

@section('nav-items')
    @php
        $nav = [
            ['icon' => 'truck',    'label' => 'Pratiche', 'route' => 'dashboard', 'pattern' => 'dashboard', 'badge' => null],
            ['icon' => 'plus',     'label' => 'Apri pratica', 'route' => null, 'pattern' => 'pratiche.create', 'badge' => null],
            ['icon' => 'bridge',   'label' => 'Enti territoriali', 'route' => 'admin.entities.index', 'pattern' => 'admin.entities.*', 'badge' => null],
            ['icon' => 'euro',     'label' => 'Tariffario', 'route' => 'admin.tariffs.index', 'pattern' => 'admin.tariffs.*', 'badge' => null],
            ['icon' => 'layers',   'label' => 'AINOP / PDND', 'route' => null, 'pattern' => 'ainop.*', 'badge' => null],
            ['icon' => 'doc',      'label' => 'Ragioneria', 'route' => null, 'pattern' => 'ragioneria.*', 'badge' => null],
            ['icon' => 'clock',    'label' => 'Audit log', 'route' => null, 'pattern' => 'audit.*', 'badge' => null],
            ['icon' => 'cone',     'label' => 'Aziende', 'route' => 'admin.companies.index', 'pattern' => 'admin.companies.*', 'badge' => null],
            ['icon' => 'sliders',  'label' => 'Impostazioni', 'route' => 'admin.settings.index', 'pattern' => 'admin.settings.*', 'badge' => null],
        ];
    @endphp

    @foreach($nav as $item)
        @php
            $isActive = $item['pattern'] ? request()->routeIs($item['pattern']) : false;
            $classes = $isActive 
                ? 'bg-surface-2 text-ink font-semibold' 
                : 'text-ink-2 hover:bg-surface-2 hover:text-ink font-medium';
        @endphp
        <a href="{{ $item['route'] ? route($item['route']) : '#' }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-[13px] transition-colors {{ $classes }}">
            <x-icon name="{{ $item['icon'] }}" size="14" class="{{ $isActive ? 'text-accent' : '' }}" />
            <span class="flex-1">{{ $item['label'] }}</span>
            @if($item['badge'])
                <span class="bg-accent text-bg text-[9px] px-1.5 py-0.5 rounded-full font-bold">{{ $item['badge'] }}</span>
            @endif
        </a>
    @endforeach
@endsection

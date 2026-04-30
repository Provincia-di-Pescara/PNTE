@extends('layouts.app')

@section('nav-items')
    @php
        $nav = [
            ['icon' => 'search',   'label' => 'Verifica', 'route' => 'dashboard', 'pattern' => 'dashboard', 'badge' => null],
            ['icon' => 'truck',    'label' => 'Transiti oggi', 'route' => null, 'pattern' => 'transiti.*', 'badge' => null],
            ['icon' => 'cone',     'label' => 'Cantieri attivi', 'route' => null, 'pattern' => 'cantieri.*', 'badge' => null],
            ['icon' => 'map',      'label' => 'Mappa real-time', 'route' => null, 'pattern' => 'mappa.*', 'badge' => null],
        ];
    @endphp

    @foreach($nav as $item)
        @php
            $isActive = $item['pattern'] ? request()->routeIs($item['pattern']) : false;
            $classes = $isActive 
                ? 'bg-surface-2 text-ink font-semibold' 
                : 'text-ink-2 hover:bg-surface-2 hover:text-ink font-medium';
        @endphp
        <a href="{{ $item['route'] && Route::has($item['route']) ? route($item['route']) : '#' }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-md text-[13px] transition-colors {{ $classes }}">
            <x-icon name="{{ $item['icon'] }}" size="14" class="{{ $isActive ? 'text-accent' : '' }}" />
            <span class="flex-1">{{ $item['label'] }}</span>
            @if($item['badge'])
                <span class="bg-accent text-bg text-[9px] px-1.5 py-0.5 rounded-full font-bold">{{ $item['badge'] }}</span>
            @endif
        </a>
    @endforeach
@endsection

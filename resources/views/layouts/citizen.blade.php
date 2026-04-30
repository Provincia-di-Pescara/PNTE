@extends('layouts.app')

@section('nav-items')
    @php
        $nav = [
            ['icon' => 'truck',    'label' => 'Le mie pratiche', 'route' => 'dashboard', 'pattern' => 'dashboard', 'badge' => null],
            ['icon' => 'plus',     'label' => 'Nuova domanda', 'route' => null, 'pattern' => 'pratiche.create', 'badge' => null],
            ['icon' => 'axles',    'label' => 'Garage Virtuale', 'route' => 'citizen.vehicles.index', 'pattern' => 'citizen.vehicles.*', 'badge' => null],
            ['icon' => 'user',     'label' => 'Deleghe', 'route' => 'citizen.delegations.index', 'pattern' => 'citizen.delegations.*', 'badge' => null],
            ['icon' => 'doc',      'label' => 'Fatturazione', 'route' => null, 'pattern' => 'fatturazione.*', 'badge' => null],
            ['icon' => 'search',   'label' => 'Guida & FAQ', 'route' => null, 'pattern' => 'faq.*', 'badge' => null],
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

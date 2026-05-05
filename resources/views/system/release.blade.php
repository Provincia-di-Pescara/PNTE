@extends('layouts.system-sidebar')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Release & migrazioni</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Release & migrazioni</h1>
            <p class="text-xs text-ink-3 mt-0.5">Catalogo Developers Italia · EUPL-1.2.</p>
        </div>
        <div class="flex-1"></div>
        <a href="#" class="btn">publiccode.yml</a>
    </div>

    <div class="grid grid-cols-4 gap-3">
        @foreach($releases as $release)
            <div class="card p-4">
                <div class="mono text-[11px] text-ink-3">{{ $release['version'] }}</div>
                <div class="text-[12.5px] font-semibold mt-1">{{ $release['label'] }}</div>
                <div class="text-[11px] text-ink-3 mt-2">{{ $release['status'] }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection

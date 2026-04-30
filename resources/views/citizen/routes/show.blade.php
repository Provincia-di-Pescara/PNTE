@extends('layouts.citizen')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
            <a href="#" class="hover:text-ink transition-colors">Percorsi</a>
            <span class="mx-1">/</span>
            <span>#{{ $route->id }}</span>
        </nav>
        <h1 class="text-xl font-bold tracking-tight font-mono uppercase">Percorso #{{ $route->id }}</h1>
    </div>
    <a href="{{ route('my.routes.create') }}" class="btn btn-primary">
        Nuovo percorso
    </a>
</div>

<div class="card p-6 mb-6">
    <dl class="grid grid-cols-2 gap-4">
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Distanza totale</dt>
            <dd class="text-[13px] font-mono font-medium text-ink">{{ number_format((float)$route->distance_km, 3, ',', '.') }} km</dd>
        </div>
        <div class="flex flex-col">
            <dt class="text-[11px] font-semibold uppercase tracking-wider text-ink-3 mb-0.5">Salvato il</dt>
            <dd class="text-[13px] font-medium text-ink">{{ $route->created_at->format('d/m/Y H:i') }}</dd>
        </div>
    </dl>
</div>

@if($route->entity_breakdown)
<div class="card overflow-hidden mb-6">
    <div class="px-5 py-3 border-b border-line bg-surface-2">
        <h2 class="text-sm font-semibold">Ripartizione km per ente</h2>
    </div>
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Ente ID</th>
                <th class="px-5 py-3 font-medium text-right">km</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($route->entity_breakdown as $entityId => $km)
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 text-ink">{{ $entityId }}</td>
                <td class="px-5 py-3 text-right font-mono font-medium text-ink">{{ number_format((float)$km, 3, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif
@endsection

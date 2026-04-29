@extends('layouts.third-party')
@section('title', $roadwork->title)
@section('content')
<div class="mb-6">
    <nav class="text-sm text-slate-500 mb-2">
        <a href="{{ route('third-party.roadworks.index') }}" class="hover:text-slate-700">Cantieri</a>
        <span class="mx-1">/</span><span>{{ $roadwork->title }}</span>
    </nav>
    <div class="flex items-start justify-between">
        <h1 class="text-xl font-bold text-slate-900">{{ $roadwork->title }}</h1>
        <div class="flex gap-2">
            @can('update', $roadwork)
            <a href="{{ route('third-party.roadworks.edit', $roadwork) }}"
               class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Modifica
            </a>
            @endcan
        </div>
    </div>
</div>
<div class="bg-white rounded-xl border border-slate-200 p-6 max-w-2xl space-y-4">
    <dl class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <dt class="font-medium text-slate-500">Ente</dt>
            <dd class="mt-1 text-slate-900">{{ $roadwork->entity->nome }}</dd>
        </div>
        <div>
            <dt class="font-medium text-slate-500">Gravità</dt>
            <dd class="mt-1">
                @php $sev = $roadwork->severity; @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                    {{ $sev === \App\Enums\RoadworkSeverity::Closed ? 'bg-red-100 text-red-700' :
                       ($sev === \App\Enums\RoadworkSeverity::Restricted ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700') }}">
                    {{ $sev->label() }}
                </span>
            </dd>
        </div>
        <div>
            <dt class="font-medium text-slate-500">Data inizio</dt>
            <dd class="mt-1 text-slate-900">{{ $roadwork->valid_from->format('d/m/Y') }}</dd>
        </div>
        <div>
            <dt class="font-medium text-slate-500">Data fine</dt>
            <dd class="mt-1 text-slate-900">{{ $roadwork->valid_to?->format('d/m/Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="font-medium text-slate-500">Stato</dt>
            <dd class="mt-1">
                @php $st = $roadwork->status; @endphp
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                    {{ $st === \App\Enums\RoadworkStatus::Active ? 'bg-green-100 text-green-700' :
                       ($st === \App\Enums\RoadworkStatus::Closed ? 'bg-slate-100 text-slate-600' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ $st->label() }}
                </span>
            </dd>
        </div>
    </dl>
    @if($roadwork->note)
    <div class="pt-4 border-t border-slate-100">
        <dt class="text-sm font-medium text-slate-500">Note</dt>
        <dd class="mt-1 text-sm text-slate-900">{{ $roadwork->note }}</dd>
    </div>
    @endif
</div>
@endsection

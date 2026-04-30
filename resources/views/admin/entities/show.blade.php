@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-start justify-between">
    <div>
        <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
            <a href="{{ route('admin.entities.index') }}" class="hover:text-ink transition-colors">Enti</a>
            <span class="mx-1">/</span>
            <span>{{ $entity->nome }}</span>
        </nav>
        <div class="flex items-center gap-3">
            <h1 class="text-xl font-bold tracking-tight">{{ $entity->nome }}</h1>
            @php
                $tone = match($entity->tipo) {
                    \App\Enums\EntityType::Comune => 'info',
                    \App\Enums\EntityType::Provincia => 'amber',
                    \App\Enums\EntityType::Anas => 'success',
                    default => 'default',
                };
            @endphp
            <x-chip :tone="$tone">{{ $entity->tipo->label() }}</x-chip>
        </div>
    </div>
    <div class="flex gap-2">
        @can('update', $entity)
        <a href="{{ route('admin.entities.edit', $entity) }}" class="btn">
            Modifica
        </a>
        @endcan
        @can('delete', $entity)
        <form method="POST" action="{{ route('admin.entities.destroy', $entity) }}"
              onsubmit="return confirm('Eliminare questo ente?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn text-danger hover:bg-danger-bg border-line-2 hover:border-danger/30">
                Elimina
            </button>
        </form>
        @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-line bg-surface-2">
            <h2 class="text-sm font-semibold">Dati anagrafici</h2>
        </div>
        <dl class="divide-y divide-line">
            @foreach([
                ['Codice ISTAT', $entity->codice_istat ?? '—'],
                ['Indirizzo', $entity->indirizzo ?? '—'],
                ['E-mail', $entity->email ?? '—'],
                ['PEC', $entity->pec ?? '—'],
                ['Telefono', $entity->telefono ?? '—'],
                ['C.F. / P.IVA', $entity->codice_fisc_piva ?? '—'],
                ['Codice SDI', $entity->codice_sdi ?? '—'],
            ] as [$label, $value])
            <div class="flex px-5 py-3 text-[13px] hover:bg-surface-2 transition-colors">
                <dt class="w-32 shrink-0 font-medium text-ink-2">{{ $label }}</dt>
                <dd class="text-ink">{{ $value }}</dd>
            </div>
            @endforeach
        </dl>
    </div>

    <div class="card overflow-hidden">
        <div class="px-5 py-3 border-b border-line bg-surface-2">
            <h2 class="text-sm font-semibold">Geometria GIS</h2>
        </div>
        <div class="p-5">
            @if($entity->geom)
            <x-chip tone="success" dot="true">MULTIPOLYGON presente</x-chip>
            <p class="mt-4 text-[11px] text-ink-3 font-mono truncate">{{ substr($entity->geom, 0, 120) }}…</p>
            @else
            <div class="text-center py-8">
                <p class="text-[13px] font-medium text-ink-2">Geometria non ancora caricata.</p>
                <p class="text-[11px] text-ink-3 mt-1">L'import shapefile dei confini comunali avverrà in v0.4.x.</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Enti territoriali</h1>
        <p class="text-sm text-ink-2 mt-1">Comuni, Province, ANAS e Autostrade coinvolti nelle autorizzazioni.</p>
    </div>
    @can('create', \App\Models\Entity::class)
    <a href="{{ route('admin.entities.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Nuovo ente
    </a>
    @endcan
</div>

<div class="card overflow-hidden">
    @if($entities->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="bridge" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun ente registrato</p>
        <p class="text-xs text-ink-2 mt-1">Aggiungi enti o importa shapefile dei confini (in arrivo con v0.4.x).</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-4 py-3 font-medium">Nome</th>
                <th class="px-4 py-3 font-medium">Tipo</th>
                <th class="px-4 py-3 font-medium">ISTAT / AINOP</th>
                <th class="px-4 py-3 font-medium">PEC</th>
                <th class="px-4 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($entities as $entity)
            <tr class="row-hover transition-colors">
                <td class="px-4 py-3 font-medium text-ink">{{ $entity->nome }}</td>
                <td class="px-4 py-3">
                    @php
                        $tone = match($entity->tipo) {
                            \App\Enums\EntityType::Comune => 'info',
                            \App\Enums\EntityType::Provincia => 'amber',
                            \App\Enums\EntityType::Anas => 'success',
                            default => 'default',
                        };
                    @endphp
                    <x-chip :tone="$tone">{{ $entity->tipo->label() }}</x-chip>
                </td>
                <td class="px-4 py-3 font-mono text-ink-2">{{ $entity->codice_istat ?? '—' }}</td>
                <td class="px-4 py-3 text-ink-2 truncate max-w-[180px]" title="{{ $entity->pec }}">{{ $entity->pec ?? '—' }}</td>
                <td class="px-4 py-3 text-right">
                    <a href="{{ route('admin.entities.show', $entity) }}" class="btn btn-ghost btn-sm">Dettaglio</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($entities->hasPages())
    <div class="px-4 py-3 border-t border-line bg-surface">{{ $entities->links() }}</div>
    @endif
    @endif
</div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Tariffario D.P.R. 495/1992</h1>
        <p class="text-sm text-ink-2 mt-1">Coefficienti di usura stradale per tipo di asse. Versioni ordinate per tipo e data.</p>
    </div>
    <a href="{{ route('admin.tariffs.create') }}" class="btn btn-primary">
        <x-icon name="plus" size="14" /> Aggiungi coefficiente
    </a>
</div>

<div class="card overflow-hidden">
    @if($tariffs->isEmpty())
    <div class="py-16 text-center flex flex-col items-center justify-center">
        <div class="w-12 h-12 rounded-full bg-surface-2 flex items-center justify-center text-ink-3 mb-4">
            <x-icon name="euro" size="24" stroke="1.5" />
        </div>
        <p class="text-sm font-semibold">Nessun coefficiente presente</p>
        <p class="text-xs text-ink-2 mt-1">Aggiungi il primo coefficiente di usura per iniziare.</p>
    </div>
    @else
    <table class="w-full text-left text-[13px]">
        <thead class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider border-b border-line bg-surface-2">
            <tr>
                <th class="px-5 py-3 font-medium">Tipo asse</th>
                <th class="px-5 py-3 font-medium text-right">Coefficiente</th>
                <th class="px-5 py-3 font-medium">Valido dal</th>
                <th class="px-5 py-3 font-medium">Valido al</th>
                <th class="px-5 py-3 font-medium text-center">Stato</th>
                <th class="px-5 py-3 font-medium text-right">Azioni</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line">
            @foreach($tariffs as $tariff)
            @php
                $isActive = $tariff->valid_from <= today() && ($tariff->valid_to === null || $tariff->valid_to >= today());
            @endphp
            <tr class="row-hover transition-colors">
                <td class="px-5 py-3 font-medium text-ink">{{ $tariff->tipo_asse->label() }}</td>
                <td class="px-5 py-3 text-right font-mono text-ink-2">{{ number_format((float) $tariff->coefficiente, 6) }}</td>
                <td class="px-5 py-3 text-ink-2 font-mono text-[12px]">{{ $tariff->valid_from->format('d/m/Y') }}</td>
                <td class="px-5 py-3 text-ink-2 font-mono text-[12px]">{{ $tariff->valid_to ? $tariff->valid_to->format('d/m/Y') : '—' }}</td>
                <td class="px-5 py-3 text-center">
                    @if($isActive)
                    <x-chip tone="success" dot="true">Attiva</x-chip>
                    @else
                    <x-chip tone="default">Storico</x-chip>
                    @endif
                </td>
                <td class="px-5 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('admin.tariffs.edit', $tariff) }}" class="btn btn-ghost btn-sm">Modifica</a>
                        <form method="POST" action="{{ route('admin.tariffs.destroy', $tariff) }}" class="inline"
                              onsubmit="return confirm('Eliminare questa voce tariffaria?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-ghost btn-sm text-danger hover:bg-danger-bg hover:text-danger">Elimina</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @if($tariffs->hasPages())
    <div class="px-5 py-3 border-t border-line bg-surface">
        {{ $tariffs->links() }}
    </div>
    @endif
    @endif
</div>
@endsection

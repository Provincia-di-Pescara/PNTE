@extends('layouts.admin')
@section('title', 'Modifica coefficiente tariffario')

@section('content')
<div class="mb-6">
    <nav class="text-sm text-slate-500 mb-2">
        <a href="{{ route('admin.tariffs.index') }}" class="hover:text-slate-700">Tariffario</a>
        <span class="mx-1">/</span>
        <span>Modifica</span>
    </nav>
    <h1 class="text-xl font-bold text-slate-900">Modifica coefficiente tariffario</h1>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 p-6 max-w-2xl">
    <form method="POST" action="{{ route('admin.tariffs.update', $tariff) }}">
        @csrf
        @method('PUT')

        <div class="space-y-5">
            <div>
                <label for="tipo_asse" class="block text-sm font-medium text-slate-700">Tipo asse <span class="text-red-500">*</span></label>
                <select id="tipo_asse" name="tipo_asse" required
                        class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('tipo_asse') border-red-400 @enderror">
                    <option value="">— Seleziona —</option>
                    @foreach(\App\Enums\AxleType::cases() as $type)
                    <option value="{{ $type->value }}" @selected(old('tipo_asse', $tariff->tipo_asse->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
                @error('tipo_asse')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="coefficiente" class="block text-sm font-medium text-slate-700">Coefficiente <span class="text-red-500">*</span></label>
                <input type="number" id="coefficiente" name="coefficiente"
                       value="{{ old('coefficiente', $tariff->coefficiente) }}"
                       step="0.000001" min="0" required
                       class="mt-1 block w-48 rounded-lg border border-slate-300 px-3 py-2 text-sm font-mono shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('coefficiente') border-red-400 @enderror">
                @error('coefficiente')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="valid_from" class="block text-sm font-medium text-slate-700">Valido dal <span class="text-red-500">*</span></label>
                    <input type="date" id="valid_from" name="valid_from"
                           value="{{ old('valid_from', $tariff->valid_from->format('Y-m-d')) }}" required
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('valid_from') border-red-400 @enderror">
                    @error('valid_from')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="valid_to" class="block text-sm font-medium text-slate-700">Valido al</label>
                    <input type="date" id="valid_to" name="valid_to"
                           value="{{ old('valid_to', $tariff->valid_to?->format('Y-m-d')) }}"
                           class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('valid_to') border-red-400 @enderror">
                    @error('valid_to')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-400">Lascia vuoto per una tariffa senza scadenza.</p>
                </div>
            </div>

            <div>
                <label for="note" class="block text-sm font-medium text-slate-700">Note</label>
                <textarea id="note" name="note" rows="3"
                          class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('note') border-red-400 @enderror">{{ old('note', $tariff->note) }}</textarea>
                @error('note')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-3 mt-8 pt-6 border-t border-slate-100">
            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">
                Salva modifiche
            </button>
            <a href="{{ route('admin.tariffs.index') }}"
               class="px-5 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                Annulla
            </a>
        </div>
    </form>
</div>
@endsection

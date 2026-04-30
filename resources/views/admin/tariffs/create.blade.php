@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('admin.tariffs.index') }}" class="hover:text-ink transition-colors">Tariffario</a>
        <span class="mx-1">/</span>
        <span>Nuovo</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">Aggiungi coefficiente tariffario</h1>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
    <ul class="list-disc list-inside text-sm text-danger space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('admin.tariffs.store') }}" class="max-w-2xl space-y-6">
    @csrf

    <div class="card p-6 space-y-5">
        <div>
            <label for="tipo_asse" class="block text-xs font-semibold text-ink-2 mb-1.5">Tipo asse <span class="text-danger">*</span></label>
            <select id="tipo_asse" name="tipo_asse" required
                    class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('tipo_asse') border-danger @enderror">
                <option value="">— Seleziona —</option>
                @foreach(\App\Enums\AxleType::cases() as $type)
                <option value="{{ $type->value }}" @selected(old('tipo_asse') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            @error('tipo_asse')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="coefficiente" class="block text-xs font-semibold text-ink-2 mb-1.5">Coefficiente <span class="text-danger">*</span></label>
            <input type="number" id="coefficiente" name="coefficiente" value="{{ old('coefficiente') }}" step="0.000001" min="0" required
                   class="w-48 h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('coefficiente') border-danger @enderror">
            @error('coefficiente')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="valid_from" class="block text-xs font-semibold text-ink-2 mb-1.5">Valido dal <span class="text-danger">*</span></label>
                <input type="date" id="valid_from" name="valid_from" value="{{ old('valid_from') }}" required
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('valid_from') border-danger @enderror">
                @error('valid_from')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="valid_to" class="block text-xs font-semibold text-ink-2 mb-1.5">Valido al</label>
                <input type="date" id="valid_to" name="valid_to" value="{{ old('valid_to') }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('valid_to') border-danger @enderror">
                @error('valid_to')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
                <p class="mt-1 text-[11px] text-ink-3">Lascia vuoto per una tariffa senza scadenza.</p>
            </div>
        </div>

        <div>
            <label for="note" class="block text-xs font-semibold text-ink-2 mb-1.5">Note</label>
            <textarea id="note" name="note" rows="3"
                      class="w-full p-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('note') border-danger @enderror">{{ old('note') }}</textarea>
            @error('note')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Salva</button>
        <a href="{{ route('admin.tariffs.index') }}" class="btn">Annulla</a>
    </div>
</form>
@endsection

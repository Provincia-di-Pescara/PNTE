@extends('layouts.citizen')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Nuova istanza di trasporto</h1>
    <p class="text-sm text-ink-2 mt-1">Compila il modulo per richiedere un'autorizzazione al trasporto eccezionale.</p>
</div>

<form method="POST" action="{{ route('my.applications.store') }}" class="space-y-6">
    @csrf

    <div class="card p-6 space-y-4">
        <h2 class="text-base font-semibold">Tipo istanza</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach(\App\Enums\TipoIstanza::cases() as $tipo)
            <label class="relative flex cursor-pointer rounded-lg border border-line p-4 hover:bg-surface-2 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="radio" name="tipo_istanza" value="{{ $tipo->value }}" class="sr-only" {{ old('tipo_istanza') === $tipo->value ? 'checked' : '' }}>
                <div>
                    <p class="text-sm font-semibold">{{ $tipo->label() }}</p>
                </div>
            </label>
            @endforeach
        </div>
        @error('tipo_istanza')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
    </div>

    <div class="card p-6 space-y-4">
        <h2 class="text-base font-semibold">Dati del trasporto</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Azienda richiedente</label>
                <select name="company_id" class="input @error('company_id') input-error @enderror">
                    <option value="">— Seleziona azienda —</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->ragione_sociale }}
                    </option>
                    @endforeach
                </select>
                @error('company_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Veicolo trattore</label>
                <select name="vehicle_id" class="input @error('vehicle_id') input-error @enderror">
                    <option value="">— Seleziona veicolo —</option>
                    @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id') == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->targa }} ({{ $vehicle->tipo->label() }})
                    </option>
                    @endforeach
                </select>
                @error('vehicle_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Valida dal</label>
                <input type="date" name="valida_da" value="{{ old('valida_da') }}" class="input @error('valida_da') input-error @enderror">
                @error('valida_da')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Valida fino al</label>
                <input type="date" name="valida_fino" value="{{ old('valida_fino') }}" class="input @error('valida_fino') input-error @enderror">
                @error('valida_fino')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Numero viaggi (forfettaria)</label>
                <input type="number" name="numero_viaggi" min="1" value="{{ old('numero_viaggi') }}" class="input @error('numero_viaggi') input-error @enderror" placeholder="Lascia vuoto per istanza analitica">
                @error('numero_viaggi')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="label">Note aggiuntive</label>
            <textarea name="note" rows="3" class="input @error('note') input-error @enderror" placeholder="Eventuali note per l'ufficio...">{{ old('note') }}</textarea>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('my.applications.index') }}" class="btn btn-ghost">Annulla</a>
        <button type="submit" class="btn btn-primary">Invia istanza</button>
    </div>
</form>
@endsection

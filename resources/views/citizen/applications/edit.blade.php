@extends('layouts.citizen')

@section('content')
<div class="mb-6">
    <h1 class="text-xl font-bold tracking-tight">Modifica istanza #{{ $application->id }}</h1>
    <p class="text-sm text-ink-2 mt-1">Puoi modificare solo le istanze in bozza.</p>
</div>

<form method="POST" action="{{ route('my.applications.update', $application) }}" class="space-y-6">
    @csrf @method('PUT')

    <div class="card p-6 space-y-4">
        <h2 class="text-base font-semibold">Dati del trasporto</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="label">Azienda richiedente</label>
                <select name="company_id" class="input @error('company_id') input-error @enderror">
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id', $application->company_id) == $company->id ? 'selected' : '' }}>
                        {{ $company->ragione_sociale }}
                    </option>
                    @endforeach
                </select>
                @error('company_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Veicolo trattore</label>
                <select name="vehicle_id" class="input @error('vehicle_id') input-error @enderror">
                    @foreach($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $application->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->targa }} ({{ $vehicle->tipo->label() }})
                    </option>
                    @endforeach
                </select>
                @error('vehicle_id')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Valida dal</label>
                <input type="date" name="valida_da" value="{{ old('valida_da', $application->valida_da->format('Y-m-d')) }}" class="input @error('valida_da') input-error @enderror">
                @error('valida_da')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Valida fino al</label>
                <input type="date" name="valida_fino" value="{{ old('valida_fino', $application->valida_fino->format('Y-m-d')) }}" class="input @error('valida_fino') input-error @enderror">
                @error('valida_fino')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="label">Numero viaggi (forfettaria)</label>
                <input type="number" name="numero_viaggi" min="1" value="{{ old('numero_viaggi', $application->numero_viaggi) }}" class="input @error('numero_viaggi') input-error @enderror">
                @error('numero_viaggi')<p class="text-xs text-danger mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="label">Note aggiuntive</label>
            <textarea name="note" rows="3" class="input @error('note') input-error @enderror">{{ old('note', $application->note) }}</textarea>
        </div>
    </div>

    <div class="flex justify-end gap-3">
        <a href="{{ route('my.applications.show', $application) }}" class="btn btn-ghost">Annulla</a>
        <button type="submit" class="btn btn-primary">Salva modifiche</button>
    </div>
</form>
@endsection

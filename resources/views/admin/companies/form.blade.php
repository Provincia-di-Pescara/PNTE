@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('admin.companies.index') }}" class="hover:text-ink transition-colors">Aziende</a>
        <span class="mx-1">/</span>
        <span>{{ $company->exists ? $company->ragione_sociale : 'Nuova' }}</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">{{ $company->exists ? 'Modifica azienda' : 'Nuova azienda' }}</h1>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
    <ul class="list-disc list-inside text-sm text-danger space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $company->exists ? route('admin.companies.update', $company) : route('admin.companies.store') }}" class="max-w-2xl space-y-6">
    @csrf
    @if($company->exists) @method('PUT') @endif

    <div class="card p-6 space-y-5">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Dati azienda</h2>
        
        <div>
            <label for="ragione_sociale" class="block text-xs font-semibold text-ink-2 mb-1.5">Ragione sociale <span class="text-danger">*</span></label>
            <input type="text" id="ragione_sociale" name="ragione_sociale" value="{{ old('ragione_sociale', $company->ragione_sociale) }}" required
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('ragione_sociale') border-danger @enderror">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="partita_iva" class="block text-xs font-semibold text-ink-2 mb-1.5">Partita IVA <span class="text-danger">*</span></label>
                <input type="text" id="partita_iva" name="partita_iva" value="{{ old('partita_iva', $company->partita_iva) }}" required maxlength="11"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors @error('partita_iva') border-danger @enderror">
            </div>
            <div>
                <label for="codice_fiscale" class="block text-xs font-semibold text-ink-2 mb-1.5">Codice fiscale azienda</label>
                <input type="text" id="codice_fiscale" name="codice_fiscale" value="{{ old('codice_fiscale', $company->codice_fiscale) }}" maxlength="16"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div>
            <label for="indirizzo" class="block text-xs font-semibold text-ink-2 mb-1.5">Indirizzo</label>
            <input type="text" id="indirizzo" name="indirizzo" value="{{ old('indirizzo', $company->indirizzo) }}"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label for="comune" class="block text-xs font-semibold text-ink-2 mb-1.5">Comune</label>
                <input type="text" id="comune" name="comune" value="{{ old('comune', $company->comune) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="cap" class="block text-xs font-semibold text-ink-2 mb-1.5">CAP</label>
                <input type="text" id="cap" name="cap" value="{{ old('cap', $company->cap) }}" maxlength="5"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div>
            <label for="provincia" class="block text-xs font-semibold text-ink-2 mb-1.5">Provincia (sigla)</label>
            <input type="text" id="provincia" name="provincia" value="{{ old('provincia', $company->provincia) }}" maxlength="2"
                   class="w-20 h-9 px-3 rounded-md border border-line bg-surface text-[13px] uppercase font-mono focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>

    <div class="card p-6 space-y-5">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Contatti</h2>
        
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-ink-2 mb-1.5">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $company->email) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="pec" class="block text-xs font-semibold text-ink-2 mb-1.5">PEC</label>
                <input type="email" id="pec" name="pec" value="{{ old('pec', $company->pec) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div>
            <label for="telefono" class="block text-xs font-semibold text-ink-2 mb-1.5">Telefono</label>
            <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $company->telefono) }}"
                   class="w-48 h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">
            {{ $company->exists ? 'Salva modifiche' : 'Crea azienda' }}
        </button>
        <a href="{{ $company->exists ? route('admin.companies.show', $company) : route('admin.companies.index') }}" class="btn">
            Annulla
        </a>
    </div>
</form>
@endsection

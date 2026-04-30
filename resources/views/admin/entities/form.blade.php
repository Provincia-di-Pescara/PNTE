@extends('layouts.admin')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('admin.entities.index') }}" class="hover:text-ink transition-colors">Enti</a>
        <span class="mx-1">/</span>
        <span>{{ $entity->exists ? $entity->nome : 'Nuovo' }}</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">{{ $entity->exists ? 'Modifica ente' : 'Nuovo ente territoriale' }}</h1>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
    <ul class="list-disc list-inside text-sm text-danger space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $entity->exists ? route('admin.entities.update', $entity) : route('admin.entities.store') }}" class="max-w-2xl space-y-6">
    @csrf
    @if($entity->exists) @method('PUT') @endif

    <div class="card p-6 space-y-5">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Anagrafica</h2>
        
        <div>
            <label for="nome" class="block text-xs font-semibold text-ink-2 mb-1.5">Nome <span class="text-danger">*</span></label>
            <input type="text" id="nome" name="nome" value="{{ old('nome', $entity->nome) }}" required
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('nome') border-danger @enderror">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="tipo" class="block text-xs font-semibold text-ink-2 mb-1.5">Tipo <span class="text-danger">*</span></label>
                <select id="tipo" name="tipo" required
                        class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                    <option value="">— Seleziona —</option>
                    @foreach($types as $type)
                    <option value="{{ $type->value }}" @selected(old('tipo', $entity->tipo?->value) === $type->value)>{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="codice_istat" class="block text-xs font-semibold text-ink-2 mb-1.5">Codice ISTAT</label>
                <input type="text" id="codice_istat" name="codice_istat" value="{{ old('codice_istat', $entity->codice_istat) }}" maxlength="10"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="email" class="block text-xs font-semibold text-ink-2 mb-1.5">E-mail</label>
                <input type="email" id="email" name="email" value="{{ old('email', $entity->email) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="pec" class="block text-xs font-semibold text-ink-2 mb-1.5">PEC</label>
                <input type="email" id="pec" name="pec" value="{{ old('pec', $entity->pec) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="telefono" class="block text-xs font-semibold text-ink-2 mb-1.5">Telefono</label>
                <input type="text" id="telefono" name="telefono" value="{{ old('telefono', $entity->telefono) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="codice_sdi" class="block text-xs font-semibold text-ink-2 mb-1.5">Codice SDI</label>
                <input type="text" id="codice_sdi" name="codice_sdi" value="{{ old('codice_sdi', $entity->codice_sdi) }}" maxlength="7"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="indirizzo" class="block text-xs font-semibold text-ink-2 mb-1.5">Indirizzo sede</label>
                <input type="text" id="indirizzo" name="indirizzo" value="{{ old('indirizzo', $entity->indirizzo) }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label for="codice_fisc_piva" class="block text-xs font-semibold text-ink-2 mb-1.5">Codice fiscale / P.IVA</label>
                <input type="text" id="codice_fisc_piva" name="codice_fisc_piva" value="{{ old('codice_fisc_piva', $entity->codice_fisc_piva) }}" maxlength="16"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>
    </div>

    <div class="card p-6 space-y-5">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Integrazioni (AINOP / GIS)</h2>
        
        <div>
            <label for="codice_univoco_ainop" class="block text-xs font-semibold text-ink-2 mb-1.5">Codice AINOP</label>
            <input type="text" id="codice_univoco_ainop" name="codice_univoco_ainop" value="{{ old('codice_univoco_ainop', $entity->codice_univoco_ainop) }}" maxlength="50"
                   class="w-full sm:w-1/2 h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            <p class="mt-1 text-[11px] text-ink-3">Stub per integrazione PDND (v1.0.0).</p>
        </div>

        <div>
            <label for="geom" class="block text-xs font-semibold text-ink-2 mb-1.5">Geometria (WKT — opzionale)</label>
            <textarea id="geom" name="geom" rows="4" placeholder="MULTIPOLYGON (((...)))"
                      class="w-full p-3 rounded-md border border-line bg-surface text-xs font-mono focus:border-accent focus:outline-none transition-colors">{{ old('geom', $entity->geom) }}</textarea>
            <p class="mt-1 text-[11px] text-ink-3">Verrà popolato automaticamente con l'import shapefile (v0.4.x).</p>
        </div>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">
            {{ $entity->exists ? 'Salva modifiche' : 'Crea ente' }}
        </button>
        <a href="{{ $entity->exists ? route('admin.entities.show', $entity) : route('admin.entities.index') }}" class="btn">
            Annulla
        </a>
    </div>
</form>
@endsection

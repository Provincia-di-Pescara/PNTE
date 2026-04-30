@extends('layouts.citizen')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('my.vehicles.index') }}" class="hover:text-ink transition-colors">Veicoli</a>
        <span class="mx-1">/</span>
        <span>Nuovo</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">Aggiungi veicolo</h1>
    <p class="text-sm text-ink-2 mt-1">Inserisci i dati del veicolo e configura gli assi.</p>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
    <ul class="list-disc list-inside text-sm text-danger space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('my.vehicles.store') }}" class="space-y-6">
    @csrf

    <div class="card p-6">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Dati generali</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- Azienda --}}
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Azienda <span class="text-danger">*</span></label>
                <select name="company_id"
                        class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('company_id') border-danger @enderror">
                    <option value="">— seleziona —</option>
                    @foreach($companies as $company)
                    <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->ragione_sociale }}
                    </option>
                    @endforeach
                </select>
                @error('company_id')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Tipo --}}
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Tipo veicolo <span class="text-danger">*</span></label>
                <select name="tipo"
                        class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors @error('tipo') border-danger @enderror">
                    <option value="">— seleziona —</option>
                    @foreach(\App\Enums\VehicleType::cases() as $type)
                    <option value="{{ $type->value }}" {{ old('tipo') === $type->value ? 'selected' : '' }}>
                        {{ $type->label() }}
                    </option>
                    @endforeach
                </select>
                @error('tipo')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Targa --}}
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Targa <span class="text-danger">*</span></label>
                <input type="text" name="targa" value="{{ old('targa') }}" maxlength="15" placeholder="es. AB123CD"
                       style="text-transform:uppercase"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono uppercase focus:border-accent focus:outline-none transition-colors @error('targa') border-danger @enderror">
                @error('targa')<p class="mt-1 text-[11px] text-danger">{{ $message }}</p>@enderror
            </div>

            {{-- Numero telaio --}}
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Numero telaio (VIN)</label>
                <input type="text" name="numero_telaio" value="{{ old('numero_telaio') }}" maxlength="17"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
            </div>

            {{-- Marca --}}
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Marca</label>
                <input type="text" name="marca" value="{{ old('marca') }}" maxlength="100"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>

            {{-- Modello --}}
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Modello</label>
                <input type="text" name="modello" value="{{ old('modello') }}" maxlength="100"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>

            {{-- Anno immatricolazione --}}
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Anno immatricolazione</label>
                <input type="number" name="anno_immatricolazione" value="{{ old('anno_immatricolazione') }}" min="1900" max="{{ date('Y') }}" placeholder="{{ date('Y') }}"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>
    </div>

    {{-- Dati massa --}}
    <div class="card p-6">
        <h2 class="text-sm font-semibold mb-4 border-b border-line pb-2">Massa e dimensioni</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Massa a vuoto (kg)</label>
                <input type="number" name="massa_vuoto" value="{{ old('massa_vuoto') }}" min="0" placeholder="es. 10000"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Massa complessiva / PTT (kg)</label>
                <input type="number" name="massa_complessiva" value="{{ old('massa_complessiva') }}" min="0" placeholder="es. 44000"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Lunghezza (mm)</label>
                <input type="number" name="lunghezza" value="{{ old('lunghezza') }}" min="0" placeholder="es. 18750"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Larghezza (mm)</label>
                <input type="number" name="larghezza" value="{{ old('larghezza') }}" min="0" placeholder="es. 2550"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
            <div>
                <label class="block text-xs font-semibold text-ink-2 mb-1.5">Altezza (mm)</label>
                <input type="number" name="altezza" value="{{ old('altezza') }}" min="0" placeholder="es. 4000"
                       class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            </div>
        </div>
    </div>

    {{-- Configuratore assi --}}
    <div class="card p-6"
         x-data="{
            axles: [{ posizione: 1, tipo: 'singolo', interasse: '', carico_tecnico: '' }],
            addAxle() {
                this.axles.push({
                    posizione: this.axles.length + 1,
                    tipo: 'singolo',
                    interasse: '',
                    carico_tecnico: ''
                });
            },
            removeAxle(i) {
                this.axles.splice(i, 1);
                this.axles.forEach((a, idx) => a.posizione = idx + 1);
            }
         }">

        <div class="mb-4 flex items-center justify-between border-b border-line pb-2">
            <h2 class="text-sm font-semibold">Configurazione assi <span class="text-danger">*</span></h2>
            <button type="button" @click="addAxle()" x-show="axles.length < 9" class="btn btn-sm btn-ghost">
                <x-icon name="plus" size="14" /> Aggiungi asse
            </button>
        </div>

        @error('axles')
            <p class="mb-2 text-[11px] text-danger">{{ $message }}</p>
        @enderror

        <template x-for="(axle, i) in axles" :key="i">
            <div class="grid grid-cols-12 gap-3 mb-3 items-end">
                <div class="col-span-1">
                    <label class="block text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-1.5">N°</label>
                    <input type="text" :value="axle.posizione" readonly
                           class="w-full h-9 rounded-md border border-line bg-surface-2 px-2 text-[13px] text-center text-ink-2">
                    <input type="hidden" :name="`axles[${i}][posizione]`" :value="axle.posizione">
                </div>
                <div class="col-span-3">
                    <label class="block text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-1.5">Tipo</label>
                    <select :name="`axles[${i}][tipo]`" x-model="axle.tipo"
                            class="w-full h-9 rounded-md border border-line bg-surface px-2 text-[13px] focus:border-accent focus:outline-none transition-colors">
                        <option value="singolo">Singolo</option>
                        <option value="tandem">Tandem</option>
                        <option value="tridem">Tridem</option>
                    </select>
                </div>
                <div class="col-span-3">
                    <label class="block text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-1.5">Interasse (mm)</label>
                    <input type="number" :name="`axles[${i}][interasse]`" x-model="axle.interasse" min="0" placeholder="es. 1300"
                           class="w-full h-9 rounded-md border border-line bg-surface px-2 text-[13px] focus:border-accent focus:outline-none transition-colors">
                </div>
                <div class="col-span-4">
                    <label class="block text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-1.5">Carico tecnico (kg) <span class="text-danger">*</span></label>
                    <input type="number" :name="`axles[${i}][carico_tecnico]`" x-model="axle.carico_tecnico" min="1" required placeholder="es. 8000"
                           class="w-full h-9 rounded-md border border-line bg-surface px-2 text-[13px] focus:border-accent focus:outline-none transition-colors">
                </div>
                <div class="col-span-1">
                    <button type="button" @click="removeAxle(i)" x-show="axles.length > 1"
                            class="w-full h-9 flex items-center justify-center text-danger hover:bg-danger-bg hover:text-danger rounded-md transition-colors">
                        &#x2715;
                    </button>
                </div>
            </div>
        </template>
    </div>

    <div class="flex gap-3">
        <button type="submit" class="btn btn-primary">Salva veicolo</button>
        <a href="{{ route('my.vehicles.index') }}" class="btn">Annulla</a>
    </div>

</form>
@endsection

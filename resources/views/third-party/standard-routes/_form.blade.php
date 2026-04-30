<div class="space-y-5">
    <div>
        <label for="entity_id" class="block text-xs font-semibold text-ink-2 mb-1.5">Ente <span class="text-danger">*</span></label>
        <select id="entity_id" name="entity_id" required
                class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            <option value="">— Seleziona —</option>
            @foreach($entities as $entity)
            <option value="{{ $entity->id }}" @selected(old('entity_id', $standardRoute->entity_id ?? null) == $entity->id)>{{ $entity->nome }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="nome" class="block text-xs font-semibold text-ink-2 mb-1.5">Nome <span class="text-danger">*</span></label>
        <input type="text" id="nome" name="nome" required maxlength="255"
               value="{{ old('nome', $standardRoute->nome ?? '') }}"
               placeholder="es. SP17 Pescara–Chieti km 0-12"
               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
    </div>
    <div>
        <label for="geometry" class="block text-xs font-semibold text-ink-2 mb-1.5">Geometria WKT <span class="text-danger">*</span></label>
        <textarea id="geometry" name="geometry" rows="3" required
                  placeholder="LINESTRING(13.5 42.3, 13.6 42.4)"
                  class="w-full p-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">{{ old('geometry', isset($standardRoute) ? ($standardRoute->getRawWkt() ?? '') : '') }}</textarea>
        <p class="mt-1 text-[11px] text-ink-3">Formato: LINESTRING(lng lat, lng lat, ...) — SRID 4326</p>
    </div>
    <div>
        <label class="block text-xs font-semibold text-ink-2 mb-1.5">Tipi di veicolo <span class="text-danger">*</span></label>
        <div class="grid grid-cols-2 gap-2">
            @foreach(\App\Enums\VehicleType::cases() as $vt)
            <label class="flex items-center gap-2 text-[13px]">
                <input type="checkbox" name="vehicle_types[]" value="{{ $vt->value }}"
                       @checked(in_array($vt->value, old('vehicle_types', $standardRoute->vehicle_types ?? [])))
                       class="rounded border-line">
                {{ $vt->label() }}
            </label>
            @endforeach
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="max_massa_kg" class="block text-xs font-semibold text-ink-2 mb-1.5">Massa max (kg)</label>
            <input type="number" id="max_massa_kg" name="max_massa_kg" min="1"
                   value="{{ old('max_massa_kg', $standardRoute->max_massa_kg ?? '') }}"
                   placeholder="Nessun limite"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
        <div>
            <label for="max_lunghezza_mm" class="block text-xs font-semibold text-ink-2 mb-1.5">Lunghezza max (mm)</label>
            <input type="number" id="max_lunghezza_mm" name="max_lunghezza_mm" min="1"
                   value="{{ old('max_lunghezza_mm', $standardRoute->max_lunghezza_mm ?? '') }}"
                   placeholder="Nessun limite"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
        <div>
            <label for="max_larghezza_mm" class="block text-xs font-semibold text-ink-2 mb-1.5">Larghezza max (mm)</label>
            <input type="number" id="max_larghezza_mm" name="max_larghezza_mm" min="1"
                   value="{{ old('max_larghezza_mm', $standardRoute->max_larghezza_mm ?? '') }}"
                   placeholder="Nessun limite"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
        <div>
            <label for="max_altezza_mm" class="block text-xs font-semibold text-ink-2 mb-1.5">Altezza max (mm)</label>
            <input type="number" id="max_altezza_mm" name="max_altezza_mm" min="1"
                   value="{{ old('max_altezza_mm', $standardRoute->max_altezza_mm ?? '') }}"
                   placeholder="Nessun limite"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>
    <div class="flex items-center gap-2">
        <input type="hidden" name="active" value="0">
        <input type="checkbox" id="active" name="active" value="1"
               @checked(old('active', $standardRoute->active ?? true))
               class="rounded border-line">
        <label for="active" class="text-[13px] font-medium">Strada attiva</label>
    </div>
    <div>
        <label for="note" class="block text-xs font-semibold text-ink-2 mb-1.5">Note</label>
        <textarea id="note" name="note" rows="3"
                  class="w-full p-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">{{ old('note', $standardRoute->note ?? '') }}</textarea>
    </div>
</div>

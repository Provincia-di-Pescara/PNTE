<div class="space-y-5">
    <div>
        <label for="entity_id" class="block text-xs font-semibold text-ink-2 mb-1.5">Ente <span class="text-danger">*</span></label>
        <select id="entity_id" name="entity_id" required
                class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
            <option value="">— Seleziona —</option>
            @foreach($entities as $entity)
            <option value="{{ $entity->id }}" @selected(old('entity_id', $roadwork->entity_id ?? null) == $entity->id)>{{ $entity->nome }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="title" class="block text-xs font-semibold text-ink-2 mb-1.5">Titolo <span class="text-danger">*</span></label>
        <input type="text" id="title" name="title" required maxlength="255"
               value="{{ old('title', $roadwork->title ?? '') }}"
               class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
    </div>
    <div>
        <label for="geometry" class="block text-xs font-semibold text-ink-2 mb-1.5">Geometria WKT <span class="text-danger">*</span></label>
        <textarea id="geometry" name="geometry" rows="3" required
                  placeholder="LINESTRING(13.5 42.3, 13.6 42.4)"
                  class="w-full p-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">{{ old('geometry', isset($roadwork) ? ($roadwork->getRawWkt() ?? '') : '') }}</textarea>
        <p class="mt-1 text-[11px] text-ink-3">Formato: LINESTRING(lng lat, lng lat, ...)</p>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="valid_from" class="block text-xs font-semibold text-ink-2 mb-1.5">Data inizio <span class="text-danger">*</span></label>
            <input type="date" id="valid_from" name="valid_from" required
                   value="{{ old('valid_from', isset($roadwork) ? $roadwork->valid_from?->format('Y-m-d') : '') }}"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
        </div>
        <div>
            <label for="valid_to" class="block text-xs font-semibold text-ink-2 mb-1.5">Data fine</label>
            <input type="date" id="valid_to" name="valid_to"
                   value="{{ old('valid_to', isset($roadwork) ? $roadwork->valid_to?->format('Y-m-d') : '') }}"
                   class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] font-mono focus:border-accent focus:outline-none transition-colors">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="severity" class="block text-xs font-semibold text-ink-2 mb-1.5">Gravità <span class="text-danger">*</span></label>
            <select id="severity" name="severity" required
                    class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                @foreach(\App\Enums\RoadworkSeverity::cases() as $sev)
                <option value="{{ $sev->value }}" @selected(old('severity', $roadwork->severity->value ?? null) === $sev->value)>{{ $sev->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-xs font-semibold text-ink-2 mb-1.5">Stato <span class="text-danger">*</span></label>
            <select id="status" name="status" required
                    class="w-full h-9 px-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">
                @foreach(\App\Enums\RoadworkStatus::cases() as $st)
                <option value="{{ $st->value }}" @selected(old('status', $roadwork->status->value ?? null) === $st->value)>{{ $st->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label for="note" class="block text-xs font-semibold text-ink-2 mb-1.5">Note</label>
        <textarea id="note" name="note" rows="3"
                  class="w-full p-3 rounded-md border border-line bg-surface text-[13px] focus:border-accent focus:outline-none transition-colors">{{ old('note', $roadwork->note ?? '') }}</textarea>
    </div>
</div>

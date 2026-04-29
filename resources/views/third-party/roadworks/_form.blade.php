<div class="space-y-5">
    <div>
        <label for="entity_id" class="block text-sm font-medium text-slate-700">Ente <span class="text-red-500">*</span></label>
        <select id="entity_id" name="entity_id" required
                class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
            <option value="">— Seleziona —</option>
            @foreach($entities as $entity)
            <option value="{{ $entity->id }}" @selected(old('entity_id', $roadwork->entity_id ?? null) == $entity->id)>{{ $entity->nome }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label for="title" class="block text-sm font-medium text-slate-700">Titolo <span class="text-red-500">*</span></label>
        <input type="text" id="title" name="title" required maxlength="255"
               value="{{ old('title', $roadwork->title ?? '') }}"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
    </div>
    <div>
        <label for="geometry" class="block text-sm font-medium text-slate-700">Geometria WKT <span class="text-red-500">*</span></label>
        <textarea id="geometry" name="geometry" rows="3" required
                  placeholder="LINESTRING(13.5 42.3, 13.6 42.4)"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-xs font-mono shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('geometry', isset($roadwork) ? ($roadwork->getRawWkt() ?? '') : '') }}</textarea>
        <p class="mt-1 text-xs text-slate-400">Formato: LINESTRING(lng lat, lng lat, ...)</p>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="valid_from" class="block text-sm font-medium text-slate-700">Data inizio <span class="text-red-500">*</span></label>
            <input type="date" id="valid_from" name="valid_from" required
                   value="{{ old('valid_from', isset($roadwork) ? $roadwork->valid_from?->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
        <div>
            <label for="valid_to" class="block text-sm font-medium text-slate-700">Data fine</label>
            <input type="date" id="valid_to" name="valid_to"
                   value="{{ old('valid_to', isset($roadwork) ? $roadwork->valid_to?->format('Y-m-d') : '') }}"
                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        </div>
    </div>
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="severity" class="block text-sm font-medium text-slate-700">Gravità <span class="text-red-500">*</span></label>
            <select id="severity" name="severity" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @foreach(\App\Enums\RoadworkSeverity::cases() as $sev)
                <option value="{{ $sev->value }}" @selected(old('severity', $roadwork->severity->value ?? null) === $sev->value)>{{ $sev->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="status" class="block text-sm font-medium text-slate-700">Stato <span class="text-red-500">*</span></label>
            <select id="status" name="status" required
                    class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
                @foreach(\App\Enums\RoadworkStatus::cases() as $st)
                <option value="{{ $st->value }}" @selected(old('status', $roadwork->status->value ?? null) === $st->value)>{{ $st->label() }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div>
        <label for="note" class="block text-sm font-medium text-slate-700">Note</label>
        <textarea id="note" name="note" rows="3"
                  class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">{{ old('note', $roadwork->note ?? '') }}</textarea>
    </div>
</div>

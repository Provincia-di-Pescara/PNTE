@extends('layouts.system')

@section('content')
<div class="px-6 py-6 space-y-6">
    <div>
        <div class="text-[10.5px] tracking-widest text-ink-3 uppercase font-semibold">Sistema</div>
        <h1 class="text-[22px] font-semibold mt-1">Branding piattaforma</h1>
        <p class="text-[12.5px] text-ink-3 mt-1 max-w-3xl leading-relaxed">
            Branding minimale globale: nome e logo della piattaforma mostrati a tutti i ruoli.
            Il branding non è personalizzabile per-tenant: le province sono tutte uguali post-certificazione.
        </p>
    </div>

    <form method="POST" action="{{ route('system.settings.branding.update') }}" enctype="multipart/form-data" class="card p-5 max-w-2xl space-y-4">
        @csrf
        @method('PUT')

        <div class="space-y-1">
            <label for="platform_name" class="text-[11.5px] font-semibold text-ink-2 block">Nome piattaforma</label>
            <input type="text" id="platform_name" name="platform_name"
                   value="{{ $values['platform_name'] }}"
                   class="w-full h-9 px-2.5 border border-line rounded-md bg-surface text-[12.5px]"
                   required maxlength="120">
            <div class="text-[11px] text-ink-3">Mostrato in topbar, titolo finestra e mail di sistema.</div>
        </div>

        <div class="space-y-1">
            <label for="platform_logo" class="text-[11.5px] font-semibold text-ink-2 block">Logo (opzionale)</label>
            @if($values['platform_logo'])
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ $values['platform_logo'] }}" alt="" class="w-12 h-12 rounded-md object-contain bg-surface-2 border border-line">
                    <span class="text-[11.5px] text-ink-3 mono">{{ $values['platform_logo'] }}</span>
                </div>
            @endif
            <input type="file" id="platform_logo" name="platform_logo" accept="image/*"
                   class="w-full text-[12.5px]">
            <div class="text-[11px] text-ink-3">Max 1 MB. PNG/SVG consigliati. Se non caricato resta il monogramma "GTE".</div>
        </div>

        <div class="flex items-center gap-2 pt-2 border-t border-line">
            <button type="submit" class="btn btn-primary">Salva</button>
        </div>
    </form>
</div>
@endsection

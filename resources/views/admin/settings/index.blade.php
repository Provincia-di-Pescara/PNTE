@extends('layouts.settings')

@section('settings-content')
<div class="flex flex-col items-center justify-center py-20 text-center text-ink-3">
    <div class="w-12 h-12 bg-surface-2 rounded-xl flex items-center justify-center mb-4">
        <x-icon name="bolt" size="22" stroke="1.5" />
    </div>
    <p class="text-[13px] font-medium text-ink">Seleziona una sezione</p>
    <p class="text-[12px] mt-1">Usa il menu a sinistra per navigare tra le impostazioni.</p>
</div>
@endsection

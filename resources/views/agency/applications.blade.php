@extends('layouts.agency')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">Agenzia · Pratiche</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">Pratiche cliente</h1>
        </div>
        <div class="flex-1"></div>
        <button class="btn btn-primary"><x-icon name="plus" size="12" /> Nuova pratica</button>
    </div>

    <div class="card p-8 text-center text-ink-3">
        <x-icon name="doc" size="28" class="mx-auto mb-3" stroke="1.4" />
        <p class="text-sm font-semibold">Pratiche per cliente — disponibile a partire da v0.6.x</p>
        <p class="text-xs mt-1">Le pratiche create per conto dei partner appariranno qui.</p>
    </div>
</div>
@endsection

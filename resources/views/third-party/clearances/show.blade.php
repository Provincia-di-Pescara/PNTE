@extends('layouts.third-party')

@section('content')
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-xl font-bold tracking-tight">Nulla osta #{{ $clearance->id }}</h1>
        <p class="text-sm text-ink-2 mt-1">Istanza {{ sprintf('GTE-%06d', $clearance->application_id) }}</p>
    </div>
    <span class="badge badge-{{ $clearance->stato->color() }} text-sm px-3 py-1">{{ $clearance->stato->label() }}</span>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="card p-6">
            <h2 class="text-base font-semibold mb-4">Dettagli istanza</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-ink-2">Azienda</dt>
                <dd class="font-medium">{{ $clearance->application->company?->ragione_sociale ?? '—' }}</dd>

                <dt class="text-ink-2">Veicolo</dt>
                <dd class="font-mono font-semibold">{{ $clearance->application->vehicle?->targa ?? '—' }}</dd>

                <dt class="text-ink-2">Tipo istanza</dt>
                <dd>{{ $clearance->application->tipo_istanza->label() }}</dd>

                <dt class="text-ink-2">Validità</dt>
                <dd>{{ $clearance->application->valida_da->format('d/m/Y') }} → {{ $clearance->application->valida_fino->format('d/m/Y') }}</dd>

                @if($clearance->application->note)
                <dt class="text-ink-2">Note richiedente</dt>
                <dd>{{ $clearance->application->note }}</dd>
                @endif
            </dl>
        </div>

        @if($clearance->stato === \App\Enums\ClearanceStatus::Pending)
        <div class="card p-6 space-y-4">
            <h2 class="text-base font-semibold">Emetti nulla osta</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('third-party.clearances.approve', $clearance) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label">Note (approvazione)</label>
                        <textarea name="note" rows="3" class="input" placeholder="Note opzionali..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-full">Approva nulla osta</button>
                </form>

                <form method="POST" action="{{ route('third-party.clearances.reject', $clearance) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="label">Motivazione rifiuto <span class="text-danger">*</span></label>
                        <textarea name="note" rows="3" class="input" placeholder="Indica la motivazione del diniego..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger w-full" onclick="return confirm('Confermi il diniego del nulla osta?')">Nega nulla osta</button>
                </form>
            </div>
        </div>
        @endif

        @if($clearance->decided_at)
        <div class="card p-6">
            <h2 class="text-base font-semibold mb-3">Decisione</h2>
            <dl class="text-sm space-y-2">
                <dt class="text-ink-2">Data decisione</dt>
                <dd>{{ $clearance->decided_at->format('d/m/Y H:i') }}</dd>
                @if($clearance->note)
                <dt class="text-ink-2 mt-2">Note</dt>
                <dd>{{ $clearance->note }}</dd>
                @endif
            </dl>
        </div>
        @endif
    </div>

    <div>
        <div class="card p-5">
            <h3 class="text-sm font-semibold mb-3">Ente</h3>
            <p class="text-sm font-medium">{{ $clearance->entity->nome }}</p>
            <p class="text-xs text-ink-2 mt-1">{{ $clearance->entity->tipo->label() }}</p>
        </div>
    </div>
</div>
@endsection

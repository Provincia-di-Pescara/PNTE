@extends('layouts.third-party')

@section('content')
<div class="mb-6">
    <nav class="text-[11px] font-semibold text-ink-3 uppercase tracking-wider mb-2">
        <a href="{{ route('third-party.standard-routes.index') }}" class="hover:text-ink transition-colors">Strade Standard</a>
        <span class="mx-1">/</span>
        <span>Nuova</span>
    </nav>
    <h1 class="text-xl font-bold tracking-tight">Nuova strada standard</h1>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-danger-bg border border-danger/30 p-4">
    <ul class="list-disc list-inside text-sm text-danger space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('third-party.standard-routes.store') }}" class="max-w-2xl">
    @csrf

    <div class="card p-6">
        @include('third-party.standard-routes._form')

        <div class="flex gap-3 mt-8 pt-6 border-t border-line">
            <button type="submit" class="btn btn-primary">Crea strada</button>
            <a href="{{ route('third-party.standard-routes.index') }}" class="btn">Annulla</a>
        </div>
    </div>
</form>
@endsection

@extends('layouts.third-party')
@section('title', 'Modifica cantiere')
@section('content')
<div class="mb-6">
    <nav class="text-sm text-slate-500 mb-2">
        <a href="{{ route('third-party.roadworks.index') }}" class="hover:text-slate-700">Cantieri</a>
        <span class="mx-1">/</span><span>Modifica</span>
    </nav>
    <h1 class="text-xl font-bold text-slate-900">Modifica cantiere</h1>
</div>
@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
    </ul>
</div>
@endif
<div class="bg-white rounded-xl border border-slate-200 p-6 max-w-2xl">
    <form method="POST" action="{{ route('third-party.roadworks.update', $roadwork) }}">
        @csrf @method('PUT')
        @include('third-party.roadworks._form')
        <div class="flex gap-3 mt-8 pt-6 border-t border-slate-100">
            <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-sm font-semibold text-white hover:bg-blue-700 transition-colors">Salva modifiche</button>
            <a href="{{ route('third-party.roadworks.index') }}" class="px-5 py-2 rounded-lg border border-slate-300 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">Annulla</a>
        </div>
    </form>
</div>
@endsection

@extends('setup.layout')
@section('content')

<div class="mb-6">
    <h2 class="text-lg font-semibold text-slate-900">Account amministratore</h2>
    <p class="text-sm text-slate-500 mt-1">Crea il primo account con accesso completo al sistema.</p>
</div>

@if($errors->any())
<div class="mb-4 rounded-lg bg-red-50 border border-red-200 p-4">
    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ route('setup.step1.store') }}" class="space-y-5">
    @csrf

    <div>
        <label for="name" class="block text-sm font-medium text-slate-700">Nome completo</label>
        <input type="text" id="name" name="name" value="{{ old('name', $data['name'] ?? '') }}"
               autocomplete="name" required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('name') border-red-400 @enderror">
    </div>

    <div>
        <label for="email" class="block text-sm font-medium text-slate-700">Indirizzo e-mail</label>
        <input type="email" id="email" name="email" value="{{ old('email', $data['email'] ?? '') }}"
               autocomplete="email" required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('email') border-red-400 @enderror">
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
        <input type="password" id="password" name="password"
               autocomplete="new-password" required minlength="12"
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 @error('password') border-red-400 @enderror">
        <p class="mt-1 text-xs text-slate-400">Minimo 12 caratteri.</p>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700">Conferma password</label>
        <input type="password" id="password_confirmation" name="password_confirmation"
               autocomplete="new-password" required
               class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
    </div>

    <div class="pt-2">
        <button type="submit"
                class="w-full flex justify-center py-2.5 px-4 rounded-lg bg-blue-600 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
            Continua
        </button>
    </div>
</form>

@endsection

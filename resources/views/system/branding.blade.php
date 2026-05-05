@extends('layouts.system')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold">Branding</h1>
        <p class="text-sm text-ink-2 mt-0.5">Nome applicazione, logo e colore istituzionale.</p>
    </div>

    @if(session('success'))
        <x-alert tone="success">{{ session('success') }}</x-alert>
    @endif

    <form method="POST" action="{{ route('system.branding.update') }}"
          enctype="multipart/form-data" class="space-y-5">
        @csrf @method('PUT')

        <div class="bg-surface border border-line rounded-lg divide-y divide-line">
            <div class="px-4 py-3 text-xs font-semibold text-ink-3 uppercase tracking-wider">Identità visiva</div>

            <div class="px-4 py-4 space-y-4">
                <x-form.field label="Nome applicazione" name="app_name"
                              :value="$settings['app_name'] ?? config('app.name')" />

                <div class="flex items-center gap-4">
                    <x-form.field label="Colore principale (hex)" name="branding_color" type="color"
                                  :value="$settings['branding_color'] ?? '#005CA9'" class="w-20 h-10" />
                    <span class="text-xs text-ink-2 pt-5">Usato nella barra superiore e nei badge istituzionali.</span>
                </div>

                <div>
                    <label class="block text-sm font-medium text-ink mb-1">Logo (PNG/SVG, max 512 KB)</label>
                    <input type="file" name="branding_logo" accept="image/png,image/svg+xml"
                           class="text-sm text-ink-2 file:mr-3 file:py-1 file:px-3 file:rounded file:border file:border-line file:text-xs file:bg-surface-2 file:text-ink hover:file:bg-line-2" />
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">Salva branding</button>
        </div>
    </form>
</div>
@endsection

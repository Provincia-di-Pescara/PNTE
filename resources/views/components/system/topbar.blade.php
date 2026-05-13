@php
    $platformName = \App\Models\Setting::get('branding.platform_name', config('app.name', 'PNTE'));
    $platformLogo = \App\Models\Setting::get('branding.platform_logo');
@endphp

<div class="flex items-center gap-4 px-5 h-14 border-b border-line bg-surface shrink-0">
    {{-- Brand --}}
    <a href="{{ route('system.dashboard') }}" class="flex items-center gap-2.5 no-underline text-ink">
        @if($platformLogo)
            <img src="{{ asset($platformLogo) }}" alt="" class="w-7 h-7 rounded-md object-contain bg-surface-2" />
        @else
            <div class="w-7 h-7 rounded-md bg-ink text-bg flex items-center justify-center font-bold text-[12px] tracking-wider">
                PNTE
            </div>
        @endif
        <div class="flex flex-col leading-tight">
            <span class="font-semibold text-[13.5px]">{{ $platformName }}</span>
            <span class="text-[11px] text-ink-3">Pannello /system · infrastruttura</span>
        </div>
    </a>

    <div class="h-6 w-px bg-line ml-1"></div>

    {{-- Environment --}}
    <x-system.env-pill />

    {{-- Quick search (system-admin scope) --}}
    <div class="flex-1 max-w-[420px] flex items-center gap-2 h-8 px-3 bg-surface-2 border border-line rounded-lg text-ink-3">
        <x-icon name="layers" size="13" />
        <span class="text-[12.5px] truncate">Cerca tenant, utente, codice IPA…</span>
        <span class="ml-auto flex gap-1">
            <span class="kbd">⌘</span><span class="kbd">K</span>
        </span>
    </div>

    {{-- Health beacon --}}
    <a href="{{ route('system.diagnostics.index') }}"
       class="btn btn-sm flex items-center gap-1.5"
       title="Diagnostica globale">
        <x-icon name="bolt" size="12" />
        Diagnostica
    </a>

    {{-- User --}}
    @auth
        <div class="flex items-center gap-2 px-2.5 py-1 border border-line rounded-full bg-surface-2">
            <x-avatar :name="auth()->user()->name" tone="info" />
            <div class="flex flex-col leading-tight pr-1">
                <span class="text-[12.5px] font-semibold">{{ auth()->user()->name }}</span>
                <span class="text-[10.5px] text-ink-3">system-admin</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="leading-none">
                @csrf
                <button type="submit" class="btn btn-sm btn-ghost ml-1" title="Esci">
                    <x-icon name="x" size="12" />
                </button>
            </form>
        </div>
    @endauth
</div>

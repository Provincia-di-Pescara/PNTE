@extends('layouts.admin')

@section('content')
<div class="-m-6 flex overflow-hidden" style="height: calc(100% + 3rem)">

    {{-- 264px left nav rail --}}
    <aside class="w-[264px] shrink-0 border-r border-line bg-surface flex flex-col overflow-hidden">

        {{-- Header --}}
        <div class="px-5 py-[18px] pb-3.5 border-b border-line">
            <div class="text-[10.5px] tracking-[0.14em] text-ink-3 uppercase font-medium">Configurazione</div>
            <h1 class="text-[18px] font-semibold tracking-tight mt-1">Impostazioni</h1>
            <div class="text-[11.5px] text-ink-3 mt-0.5">Modifiche tracciate nel registro audit.</div>
        </div>

        {{-- Nav groups --}}
        <div class="flex-1 overflow-y-auto py-2.5 px-2">
            @php
            $navGroups = [
                ['title' => 'Ente', 'items' => [
                    ['icon' => 'flag',    'label' => 'Profilo ente',        'sub' => 'Provincia di Pescara · IPA', 'route' => 'admin.settings.general'],
                    ['icon' => 'euro',    'label' => 'Tariffario',          'sub' => 'Gestione fasce tariffarie',  'route' => 'admin.tariffs.index'],
                    ['icon' => 'map',     'label' => 'Territorio & strade', 'sub' => 'GIS · OSRM · confini enti',  'route' => 'admin.settings.gis'],
                ]],
                ['title' => 'Persone', 'items' => [
                    ['icon' => 'user',    'label' => 'Utenti & ruoli',      'sub' => 'Accessi e permessi',          'route' => 'admin.settings.users.index'],
                    ['icon' => 'share',   'label' => 'Deleghe & SPID',      'sub' => 'Cittadini autorizzati',       'route' => 'admin.settings.oidc'],
                    ['icon' => 'layers',  'label' => 'Enti terzi federati', 'sub' => 'Comuni · ANAS · gestori',     'route' => null],
                ]],
                ['title' => 'Workflow', 'items' => [
                    ['icon' => 'refresh', 'label' => 'Stati pratica',    'sub' => 'Macchina a stati',              'route' => null],
                    ['icon' => 'clock',   'label' => 'SLA & promemoria', 'sub' => '30 gg standard · 7 gg urgenti', 'route' => null],
                    ['icon' => 'doc',     'label' => 'Modelli atti',     'sub' => 'Template autorizzazioni',        'route' => null],
                ]],
                ['title' => 'Sistema', 'items' => [
                    ['icon' => 'bolt',  'label' => 'Integrazioni',     'sub' => 'PagoPA · AINOP · PDND · IPA', 'route' => 'admin.settings.pdnd',  'badge' => true],
                    ['icon' => 'bell',  'label' => 'Notifiche & PEC',  'sub' => 'PEC istituzionale · IO app',  'route' => 'admin.settings.pec'],
                    ['icon' => 'mail',  'label' => 'Mail SMTP',        'sub' => 'Server posta in uscita',      'route' => 'admin.settings.mail'],
                    ['icon' => 'qr',    'label' => 'Sicurezza & audit','sub' => 'MFA · log accessi',           'route' => null],
                    ['icon' => 'brush', 'label' => 'Identità visiva',  'sub' => 'Logo · palette · header',     'route' => 'admin.settings.branding'],
                ]],
            ];
            @endphp

            @foreach($navGroups as $group)
            <div class="mb-3">
                <div class="text-[10px] text-ink-3 tracking-[0.12em] uppercase font-semibold px-3 py-2">{{ $group['title'] }}</div>
                @foreach($group['items'] as $item)
                @php
                    $isActive = !empty($item['route']) && request()->routeIs($item['route']);
                    $isAvailable = !is_null($item['route']);
                @endphp
                @if($isAvailable)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-2.5 px-3 py-2 rounded-[7px] w-full {{ $isActive ? 'bg-accent-bg text-accent-ink' : 'text-ink hover:bg-surface-2' }} transition-colors">
                @else
                <div class="flex items-center gap-2.5 px-3 py-2 rounded-[7px] w-full opacity-40 cursor-not-allowed">
                @endif
                    <span class="w-7 h-7 rounded-[7px] shrink-0 border border-line flex items-center justify-center {{ $isActive ? 'bg-surface text-accent-ink' : 'bg-surface-2 text-ink-2' }}">
                        <x-icon name="{{ $item['icon'] }}" size="14" />
                    </span>
                    <span class="flex-1 min-w-0">
                        <span class="block text-[12.5px] {{ $isActive ? 'font-semibold' : 'font-medium' }} leading-tight">{{ $item['label'] }}</span>
                        <span class="block text-[10.5px] text-ink-3 truncate mt-px">{{ $item['sub'] }}</span>
                    </span>
                    @if(!empty($item['badge']))
                    <span class="shrink-0 w-[18px] h-[18px] rounded-full bg-accent flex items-center justify-center">
                        <span class="w-1.5 h-1.5 rounded-full bg-bg"></span>
                    </span>
                    @endif
                @if($isAvailable)</a>@else</div>@endif
                @endforeach
            </div>
            @endforeach
        </div>

        {{-- Footer: logged-in user --}}
        @auth
        <div class="border-t border-line px-3.5 py-2.5 flex items-center gap-2.5">
            <x-avatar :name="auth()->user()->name" tone="amber" />
            <div class="flex-1 min-w-0">
                <div class="text-[12px] font-semibold truncate">{{ auth()->user()->name }}</div>
                <div class="text-[10.5px] text-ink-3">{{ auth()->user()->getRoleNames()->first() ?? 'Admin' }} · Provincia</div>
            </div>
        </div>
        @endauth
    </aside>

    {{-- Right content area --}}
    <div class="flex-1 overflow-y-auto min-w-0">
        <div class="p-6">
            @yield('settings-content')
        </div>
    </div>

</div>
@endsection

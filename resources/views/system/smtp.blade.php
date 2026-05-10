@extends('layouts.system')

@section('content')
<div class="space-y-5">
    <div class="flex items-end gap-4">
        <div>
            <div class="text-[10.5px] tracking-[0.1em] text-ink-3 uppercase">SMTP/IMAP madre</div>
            <h1 class="text-[22px] font-semibold tracking-tight mt-1">SMTP/IMAP madre</h1>
            <p class="text-xs text-ink-3 mt-0.5">Casella infrastrutturale per invio email e listener PEC.</p>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div class="card p-4">
            <h3 class="text-[13.5px] font-semibold">SMTP in uscita</h3>
            @foreach([
                ['Host', $settings['mail_host'] ?? '—'],
                ['Porta', (string) ($settings['mail_port'] ?? '—')],
                ['Username', $settings['mail_username'] ?? '—'],
                ['Mittente', $settings['mail_from_address'] ?? '—'],
                ['Nome mittente', $settings['mail_from_name'] ?? '—'],
            ] as [$label, $value])
                <div class="flex items-center justify-between py-2 border-b border-line last:border-0 text-[12.5px]">
                    <span class="text-ink-2">{{ $label }}</span>
                    <span class="mono">{{ $value }}</span>
                </div>
            @endforeach
        </div>

        <div class="card p-4">
            <h3 class="text-[13.5px] font-semibold">PEC / IMAP listener</h3>
            @foreach([
                ['Host', $settings['pec_host'] ?? '—'],
                ['Porta', (string) ($settings['pec_port'] ?? '—')],
                ['Username', $settings['pec_username'] ?? '—'],
                ['Encryption', $settings['pec_encryption'] ?? '—'],
                ['Poll interval', $settings['pec_poll_minutes'] ?? '5'],
            ] as [$label, $value])
                <div class="flex items-center justify-between py-2 border-b border-line last:border-0 text-[12.5px]">
                    <span class="text-ink-2">{{ $label }}</span>
                    <span class="mono">{{ $value }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <div class="card p-4">
        <form method="POST" action="{{ route('system.smtp.test') }}" class="flex items-end gap-3">
            @csrf
            <x-form.field name="email" label="Email test" type="email" required />
            <button type="submit" class="btn btn-primary">Invia mail di test</button>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="px-4 py-2.5 border-b border-line text-[13px] font-semibold">Ultimi eventi SMTP</div>
        @forelse($recentMessages as $event)
            <div class="grid items-center text-[12.5px] border-b border-line last:border-0"
                 style="grid-template-columns: 160px 160px 1fr; padding: 10px 16px;">
                <div class="mono text-ink-3">{{ optional($event->created_at)->format('d M · H:i') }}</div>
                <div class="mono">{{ $event->action }}</div>
                <div class="text-ink-2">{{ $event->detail }}</div>
            </div>
        @empty
            <div class="px-4 py-6 text-sm text-ink-2">Nessun evento SMTP registrato.</div>
        @endforelse
    </div>
</div>
@endsection

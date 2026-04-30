<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBrandingSettingsRequest;
use App\Http\Requests\Admin\UpdateGeneralSettingsRequest;
use App\Http\Requests\Admin\UpdateMailSettingsRequest;
use App\Mail\TestMail;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

final class SettingController extends Controller
{
    /** @var array<int, array{key: string, label: string, icon: string, route: string|null, status: string, keywords: array<int, string>}> */
    private static array $categories = [
        ['key' => 'general',    'label' => 'Generale',        'icon' => 'sliders', 'route' => 'admin.settings.general', 'status' => 'active',       'keywords' => ['app', 'nome', 'timezone', 'lingua', 'locale']],
        ['key' => 'mail',       'label' => 'Email',           'icon' => 'mail',    'route' => 'admin.settings.mail',    'status' => 'active',       'keywords' => ['smtp', 'posta', 'email', 'mittente']],
        ['key' => 'branding',   'label' => 'Branding',        'icon' => 'brush',   'route' => 'admin.settings.branding', 'status' => 'active',       'keywords' => ['logo', 'colori', 'intestazione', 'titolo']],
        ['key' => 'users',      'label' => 'Gestione utenti', 'icon' => 'users',   'route' => 'admin.settings.users.index', 'status' => 'active',   'keywords' => ['utenti', 'ruoli', 'enti', 'accessi', 'impersona']],
        ['key' => 'oidc',       'label' => 'SPID / CIE',      'icon' => 'shield',  'route' => null,                     'status' => 'coming-soon', 'keywords' => ['spid', 'cie', 'oidc', 'auth', 'identità']],
        ['key' => 'pec',        'label' => 'Server PEC',      'icon' => 'inbox',   'route' => null,                     'status' => 'coming-soon', 'keywords' => ['pec', 'posta certificata', 'notifiche']],
        ['key' => 'ainop',      'label' => 'AINOP / PDND',    'icon' => 'bridge',  'route' => null,                     'status' => 'coming-soon', 'keywords' => ['ainop', 'pdnd', 'api', 'ponti', 'infrastrutture']],
        ['key' => 'protocol',   'label' => 'Protocollo',      'icon' => 'doc',     'route' => null,                     'status' => 'coming-soon', 'keywords' => ['protocollo', 'docway', 'documentale']],
        ['key' => 'signatures', 'label' => 'Firme remote',    'icon' => 'pen',     'route' => null,                     'status' => 'coming-soon', 'keywords' => ['firma', 'pades', 'cades', 'digitale']],
        ['key' => 'pagopa',     'label' => 'PagoPA',          'icon' => 'euro',    'route' => null,                     'status' => 'coming-soon', 'keywords' => ['pagopa', 'iuv', 'pagamento', 'bollo']],
    ];

    public function index(Request $request): View
    {
        $this->denyUnlessSuper();

        return view('admin.settings.index', ['categories' => self::$categories]);
    }

    public function showGeneral(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'app_name' => Setting::get('app_name', config('app.name', 'GTE Abruzzo')),
            'app_timezone' => Setting::get('app_timezone', 'Europe/Rome'),
            'app_locale' => Setting::get('app_locale', 'it'),
        ];

        $timezones = \DateTimeZone::listIdentifiers();

        return view('admin.settings.general', compact('settings', 'timezones'));
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request): RedirectResponse
    {
        foreach (['app_name', 'app_timezone', 'app_locale'] as $key) {
            Setting::set($key, (string) $request->input($key), 'general');
        }

        return redirect()->route('admin.settings.general')
            ->with('success', 'Impostazioni generali salvate.');
    }

    public function showBranding(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'brand_header_title' => Setting::get('brand_header_title', 'GTE Abruzzo'),
            'brand_primary_color' => Setting::get('brand_primary_color', '#0055CC'),
            'brand_logo_url' => Setting::get('brand_logo_url'),
        ];

        return view('admin.settings.branding', compact('settings'));
    }

    public function updateBranding(UpdateBrandingSettingsRequest $request): RedirectResponse
    {
        Setting::set('brand_header_title', (string) $request->input('brand_header_title'), 'branding');
        Setting::set('brand_primary_color', (string) ($request->input('brand_primary_color') ?? '#0055CC'), 'branding');

        if ($request->hasFile('brand_logo')) {
            $path = $request->file('brand_logo')->store('branding', 'public');
            Setting::set('brand_logo_url', Storage::url($path), 'branding');
        }

        return redirect()->route('admin.settings.branding')
            ->with('success', 'Branding salvato.');
    }

    public function showMail(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'mail_host' => Setting::get('mail_host', ''),
            'mail_port' => Setting::get('mail_port', '587'),
            'mail_encryption' => Setting::get('mail_encryption', 'tls'),
            'mail_username' => Setting::get('mail_username', ''),
            'mail_from_address' => Setting::get('mail_from_address', ''),
            'mail_from_name' => Setting::get('mail_from_name', 'GTE Abruzzo'),
        ];

        return view('admin.settings.mail', compact('settings'));
    }

    public function updateMail(UpdateMailSettingsRequest $request): RedirectResponse
    {
        $fields = [
            'mail_host', 'mail_port', 'mail_encryption',
            'mail_username', 'mail_from_address', 'mail_from_name',
        ];

        foreach ($fields as $key) {
            Setting::set($key, (string) $request->input($key), 'mail');
        }

        if ($request->filled('mail_password')) {
            Setting::set('mail_password', $request->input('mail_password'), 'mail');
        }

        return redirect()->route('admin.settings.mail')
            ->with('success', 'Impostazioni email salvate.');
    }

    public function testMail(): RedirectResponse
    {
        $this->denyUnlessSuper();

        $host = Setting::get('mail_host');

        if (! $host) {
            return redirect()->route('admin.settings.mail')
                ->with('error', 'Configura prima le impostazioni SMTP.');
        }

        $this->applyMailConfig();

        try {
            Mail::to(auth()->user()->email)->send(new TestMail);

            return redirect()->route('admin.settings.mail')
                ->with('success', 'Email di test inviata a '.auth()->user()->email.'.');
        } catch (Throwable $e) {
            return redirect()->route('admin.settings.mail')
                ->with('error', 'Invio fallito: '.$e->getMessage());
        }
    }

    private function denyUnlessSuper(): void
    {
        abort_unless(
            auth()->user()->hasRole(UserRole::SuperAdmin->value),
            403
        );
    }

    private function applyMailConfig(): void
    {
        $map = [
            'mail_host' => 'mail.mailers.smtp.host',
            'mail_port' => 'mail.mailers.smtp.port',
            'mail_encryption' => 'mail.mailers.smtp.encryption',
            'mail_username' => 'mail.mailers.smtp.username',
            'mail_password' => 'mail.mailers.smtp.password',
            'mail_from_address' => 'mail.from.address',
            'mail_from_name' => 'mail.from.name',
        ];

        foreach ($map as $settingKey => $configKey) {
            $value = Setting::get($settingKey);
            if ($value !== null) {
                Config::set($configKey, $value);
            }
        }

        Config::set('mail.default', 'smtp');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBrandingSettingsRequest;
use App\Http\Requests\Admin\UpdateGeneralSettingsRequest;
use App\Http\Requests\Admin\UpdateGisSettingsRequest;
use App\Http\Requests\Admin\UpdateMailSettingsRequest;
use App\Http\Requests\Admin\UpdateOidcSettingsRequest;
use App\Http\Requests\Admin\UpdatePdndSettingsRequest;
use App\Http\Requests\Admin\UpdatePecSettingsRequest;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Services\IpaSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
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
        ['key' => 'gis',        'label' => 'GIS / Mappe',     'icon' => 'map',     'route' => 'admin.settings.gis',     'status' => 'active',       'keywords' => ['gis', 'mappe', 'istat', 'confini', 'geometrie', 'geojson']],
        ['key' => 'oidc',       'label' => 'SPID / CIE',      'icon' => 'shield',  'route' => 'admin.settings.oidc',    'status' => 'active',       'keywords' => ['spid', 'cie', 'oidc', 'auth', 'identità']],
        ['key' => 'pec',        'label' => 'Server PEC',      'icon' => 'inbox',   'route' => 'admin.settings.pec',     'status' => 'active',       'keywords' => ['pec', 'posta certificata', 'notifiche']],
        ['key' => 'pdnd',       'label' => 'PDND / Interoperabilità', 'icon' => 'bridge', 'route' => 'admin.settings.pdnd', 'status' => 'active',       'keywords' => ['pdnd', 'interoperabilità', 'ainop', 'ipa', 'infocamere', 'registro imprese']],
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

    public function showGis(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'osrm_base_url' => Setting::get('osrm_base_url', config('services.osrm.base_url', '')),
        ];

        return view('admin.settings.gis', compact('settings'));
    }

    public function updateGis(UpdateGisSettingsRequest $request): RedirectResponse
    {
        Setting::set('osrm_base_url', (string) $request->input('osrm_base_url'), 'gis');

        return redirect()->route('admin.settings.gis')
            ->with('success', 'Impostazioni GIS salvate.');
    }

    public function fetchBoundaries(Request $request): RedirectResponse
    {
        $this->denyUnlessSuper();

        $tipo = $request->input('tipo', 'comuni');

        if (! in_array($tipo, ['comuni', 'province'], true)) {
            return redirect()->route('admin.settings.gis')
                ->with('error', 'Tipo non valido. Scegli comuni o province.');
        }

        try {
            Artisan::call('gte:fetch-istat-boundaries', ['tipo' => $tipo]);

            $output = Artisan::output();

            return redirect()->route('admin.settings.gis')
                ->with('success', 'Importazione '.$tipo.' completata. '.$output);
        } catch (Throwable $e) {
            return redirect()->route('admin.settings.gis')
                ->with('error', 'Importazione fallita: '.$e->getMessage());
        }
    }

    public function showOidc(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'oidc_enabled' => Setting::get('oidc_enabled', '0'),
            'oidc_discovery_url' => Setting::get('oidc_discovery_url', ''),
            'oidc_client_id' => Setting::get('oidc_client_id', ''),
            'oidc_scopes' => Setting::get('oidc_scopes', 'openid profile email'),
        ];

        return view('admin.settings.oidc', compact('settings'));
    }

    public function updateOidc(UpdateOidcSettingsRequest $request): RedirectResponse
    {
        Setting::set('oidc_enabled', $request->boolean('oidc_enabled') ? '1' : '0', 'oidc');
        Setting::set('oidc_discovery_url', (string) $request->input('oidc_discovery_url'), 'oidc');
        Setting::set('oidc_client_id', (string) $request->input('oidc_client_id'), 'oidc');
        Setting::set('oidc_scopes', (string) $request->input('oidc_scopes'), 'oidc');

        if ($request->filled('oidc_client_secret')) {
            Setting::set('oidc_client_secret', encrypt((string) $request->input('oidc_client_secret')), 'oidc');
        }

        return redirect()->route('admin.settings.oidc')
            ->with('success', 'Impostazioni OIDC/SPID salvate.');
    }

    public function showPec(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'pec_host' => Setting::get('pec_host', ''),
            'pec_port' => Setting::get('pec_port', '993'),
            'pec_username' => Setting::get('pec_username', ''),
            'pec_encryption' => Setting::get('pec_encryption', 'ssl'),
            'pec_smtp_host' => Setting::get('pec_smtp_host', ''),
            'pec_smtp_port' => Setting::get('pec_smtp_port', '465'),
        ];

        return view('admin.settings.pec', compact('settings'));
    }

    public function updatePec(UpdatePecSettingsRequest $request): RedirectResponse
    {
        $fields = ['pec_host', 'pec_port', 'pec_username', 'pec_encryption', 'pec_smtp_host', 'pec_smtp_port'];

        foreach ($fields as $key) {
            Setting::set($key, (string) $request->input($key), 'pec');
        }

        if ($request->filled('pec_password')) {
            Setting::set('pec_password', encrypt((string) $request->input('pec_password')), 'pec');
        }

        return redirect()->route('admin.settings.pec')
            ->with('success', 'Impostazioni PEC salvate.');
    }

    public function testPec(): RedirectResponse
    {
        $this->denyUnlessSuper();

        $host = Setting::get('pec_host');

        if (! $host) {
            return redirect()->route('admin.settings.pec')
                ->with('error', 'Configura prima le impostazioni IMAP PEC.');
        }

        if (! function_exists('imap_open')) {
            return redirect()->route('admin.settings.pec')
                ->with('error', 'Estensione PHP IMAP non disponibile sul server.');
        }

        $port = (int) Setting::get('pec_port', '993');
        $user = Setting::get('pec_username', '');
        $enc = Setting::get('pec_encryption', 'ssl');
        $passwordEncrypted = Setting::get('pec_password');

        if (! $passwordEncrypted) {
            return redirect()->route('admin.settings.pec')
                ->with('error', 'Password PEC non configurata.');
        }

        try {
            $password = decrypt($passwordEncrypted);
            $mailbox = sprintf('{%s:%d/imap/%s}INBOX', $host, $port, $enc);
            $conn = @imap_open($mailbox, $user, $password, 0, 1);

            if ($conn === false) {
                throw new \RuntimeException(imap_last_error() ?: 'Connessione fallita');
            }

            imap_close($conn);

            return redirect()->route('admin.settings.pec')
                ->with('success', 'Connessione IMAP PEC riuscita.');
        } catch (Throwable $e) {
            return redirect()->route('admin.settings.pec')
                ->with('error', 'Test IMAP fallito: '.$e->getMessage());
        }
    }

    public function showPdnd(): View
    {
        $this->denyUnlessSuper();

        $settings = [
            'pdnd_enabled' => Setting::get('pdnd_enabled', '0'),
            'pdnd_client_id' => Setting::get('pdnd_client_id', ''),
            'pdnd_token_endpoint' => Setting::get('pdnd_token_endpoint', ''),
            'pdnd_private_key' => Setting::get('pdnd_private_key', ''),
            'pdnd_dpop_private_key' => Setting::get('pdnd_dpop_private_key', ''),
            'pdnd_ipa_url' => Setting::get('pdnd_ipa_url', ''),
            'pdnd_infocamere_url' => Setting::get('pdnd_infocamere_url', ''),
            'ipa_last_sync_at' => Setting::get('ipa.last_sync_at'),
            'ipa_last_sync_result' => Setting::get('ipa.last_sync_result'),
        ];

        return view('admin.settings.pdnd', compact('settings'));
    }

    public function updatePdnd(UpdatePdndSettingsRequest $request): RedirectResponse
    {
        $this->denyUnlessSuper();

        Setting::set('pdnd_enabled', $request->boolean('pdnd_enabled') ? '1' : '0', 'pdnd');
        Setting::set('pdnd_client_id', (string) $request->input('pdnd_client_id', ''), 'pdnd');
        Setting::set('pdnd_token_endpoint', (string) $request->input('pdnd_token_endpoint', ''), 'pdnd');
        Setting::set('pdnd_ipa_url', (string) $request->input('pdnd_ipa_url', ''), 'pdnd');
        Setting::set('pdnd_infocamere_url', (string) $request->input('pdnd_infocamere_url', ''), 'pdnd');

        if ($request->filled('pdnd_private_key')) {
            Setting::set('pdnd_private_key', (string) $request->input('pdnd_private_key'), 'pdnd');
        }

        if ($request->filled('pdnd_dpop_private_key')) {
            Setting::set('pdnd_dpop_private_key', (string) $request->input('pdnd_dpop_private_key'), 'pdnd');
        }

        return redirect()->route('admin.settings.pdnd')
            ->with('success', 'Impostazioni PDND salvate.');
    }

    public function generateDpopKey(): JsonResponse
    {
        $this->denyUnlessSuper();

        $key = openssl_pkey_new([
            'curve_name' => 'prime256v1',
            'private_key_type' => OPENSSL_KEYTYPE_EC,
        ]);

        if ($key === false) {
            return response()->json(['error' => 'Impossibile generare la chiave EC P-256.'], 500);
        }

        if (! openssl_pkey_export($key, $privatePem)) {
            return response()->json(['error' => 'Impossibile esportare la chiave privata.'], 500);
        }

        $details = openssl_pkey_get_details($key);
        $publicPem = $details['key'] ?? '';

        Setting::set('pdnd_dpop_private_key', $privatePem, 'pdnd');

        return response()->json([
            'private_key' => $privatePem,
            'public_key' => $publicPem,
        ]);
    }

    public function syncIpa(IpaSyncService $ipa): RedirectResponse
    {
        $this->denyUnlessSuper();

        try {
            $result = $ipa->syncAll();

            Setting::set('ipa.last_sync_at', now()->toIso8601String(), 'ipa');
            Setting::set('ipa.last_sync_result', json_encode([
                'updated' => $result['updated'],
                'skipped' => $result['skipped'],
                'errors' => $result['errors'],
            ]), 'ipa');

            $msg = "Sincronizzazione IPA completata: {$result['updated']} aggiornati, {$result['skipped']} invariati, {$result['errors']} errori.";

            return redirect()->route('admin.settings.pdnd')
                ->with('success', $msg);
        } catch (Throwable $e) {
            return redirect()->route('admin.settings.pdnd')
                ->with('error', 'Sincronizzazione IPA fallita: '.$e->getMessage());
        }
    }

    private function denyUnlessSuper(): void
    {
        abort_unless(
            auth()->user()->isEnteManager(),
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

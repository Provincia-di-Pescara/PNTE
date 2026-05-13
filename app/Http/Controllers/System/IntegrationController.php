<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SystemAuditLog;
use App\Services\Diagnostics\HealthCheckService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class IntegrationController extends Controller
{
    /**
     * Schema declarativo per ogni integrazione.
     *
     * Per ogni servizio:
     *   - label, icon, group, diagnostic_key (chiave HealthCheckService corrispondente)
     *   - fields: array di {name, label, type, hint?, required?, secret?, options?}
     */
    public const SCHEMA = [
        'oidc' => [
            'label' => 'SPID/CIE OIDC',
            'icon' => 'qr',
            'group' => 'oidc',
            'diagnostic_key' => 'oidc',
            'doc' => 'Identity Provider OIDC (SPID/CIE) tramite proxy Italia o IDP convenzionato.',
            'fields' => [
                ['name' => 'oidc_enabled', 'label' => 'Abilita OIDC', 'type' => 'boolean'],
                ['name' => 'oidc_discovery_url', 'label' => 'Discovery URL (.well-known)', 'type' => 'url', 'hint' => 'es. https://idp.example.it/.well-known/openid-configuration'],
                ['name' => 'oidc_client_id', 'label' => 'Client ID', 'type' => 'text', 'required' => true],
                ['name' => 'oidc_client_secret', 'label' => 'Client Secret', 'type' => 'password', 'secret' => true, 'hint' => 'lasciare vuoto per non modificare'],
                ['name' => 'oidc_scopes', 'label' => 'Scopes', 'type' => 'text', 'default' => 'openid profile email'],
            ],
        ],
        'pdnd' => [
            'label' => 'PDND interoperabilità',
            'icon' => 'qr',
            'group' => 'pdnd',
            'diagnostic_key' => 'pdnd',
            'doc' => 'Voucher DPoP (RFC 9449) per accesso ad API PDND, IPA, Infocamere, AINOP.',
            'fields' => [
                ['name' => 'pdnd_enabled', 'label' => 'Abilita PDND', 'type' => 'boolean'],
                ['name' => 'pdnd_client_id', 'label' => 'Client ID', 'type' => 'text'],
                ['name' => 'pdnd_token_endpoint', 'label' => 'Token endpoint', 'type' => 'url'],
                ['name' => 'pdnd_private_key', 'label' => 'RSA private key (PEM)', 'type' => 'textarea', 'secret' => true],
                ['name' => 'pdnd_dpop_private_key', 'label' => 'DPoP EC P-256 private key (PEM)', 'type' => 'textarea', 'secret' => true],
                ['name' => 'pdnd_ipa_url', 'label' => 'IPA URL', 'type' => 'url'],
                ['name' => 'pdnd_infocamere_url', 'label' => 'Infocamere URL', 'type' => 'url'],
            ],
        ],
        'pagopa' => [
            'label' => 'PagoPA',
            'icon' => 'qr',
            'group' => 'pagopa',
            'diagnostic_key' => 'pagopa',
            'doc' => 'Pagamenti PagoPA · IUV / Codice avviso. Integrazione tramite station ID e API key.',
            'fields' => [
                ['name' => 'pagopa_enabled', 'label' => 'Abilita PagoPA', 'type' => 'boolean'],
                ['name' => 'pagopa_base_url', 'label' => 'Base URL', 'type' => 'url'],
                ['name' => 'pagopa_station_id', 'label' => 'Station ID', 'type' => 'text'],
                ['name' => 'pagopa_api_key', 'label' => 'API key', 'type' => 'password', 'secret' => true],
                ['name' => 'pagopa_creditor_iuv_prefix', 'label' => 'Prefisso IUV creditore', 'type' => 'text'],
            ],
        ],
        'smtp' => [
            'label' => 'SMTP outbound',
            'icon' => 'bell',
            'group' => 'mail',
            'diagnostic_key' => 'smtp',
            'doc' => 'Mail server di sistema per invio notifiche istituzionali.',
            'fields' => [
                ['name' => 'mail_host', 'label' => 'Host', 'type' => 'text', 'required' => true, 'hint' => 'es. mail.example.com'],
                ['name' => 'mail_port', 'label' => 'Port', 'type' => 'number', 'default' => '587'],
                ['name' => 'mail_encryption', 'label' => 'Encryption', 'type' => 'select', 'options' => ['tls' => 'STARTTLS', 'ssl' => 'SSL/TLS', 'none' => 'Nessuna']],
                ['name' => 'mail_username', 'label' => 'Username', 'type' => 'text'],
                ['name' => 'mail_password', 'label' => 'Password', 'type' => 'password', 'secret' => true],
                ['name' => 'mail_from_address', 'label' => 'From address', 'type' => 'email'],
                ['name' => 'mail_from_name', 'label' => 'From name', 'type' => 'text', 'default' => 'PNTE'],
            ],
        ],
        'pec' => [
            'label' => 'PEC / IMAP listener',
            'icon' => 'bell',
            'group' => 'pec',
            'diagnostic_key' => 'imap',
            'doc' => 'Casella PEC istituzionale di sistema. Listener IMAP riconosce ID pratica nell\'oggetto.',
            'fields' => [
                ['name' => 'pec_host', 'label' => 'IMAP host', 'type' => 'text', 'required' => true, 'hint' => 'es. imaps.pec.aruba.it'],
                ['name' => 'pec_port', 'label' => 'IMAP port', 'type' => 'number', 'default' => '993'],
                ['name' => 'pec_encryption', 'label' => 'Encryption', 'type' => 'select', 'options' => ['ssl' => 'SSL/TLS', 'tls' => 'STARTTLS']],
                ['name' => 'pec_username', 'label' => 'Username', 'type' => 'text'],
                ['name' => 'pec_password', 'label' => 'Password', 'type' => 'password', 'secret' => true, 'encrypt' => true],
                ['name' => 'pec_smtp_host', 'label' => 'SMTP host (in uscita)', 'type' => 'text'],
                ['name' => 'pec_smtp_port', 'label' => 'SMTP port', 'type' => 'number', 'default' => '465'],
            ],
        ],
        'ainop' => [
            'label' => 'AINOP X.509',
            'icon' => 'qr',
            'group' => 'ainop',
            'diagnostic_key' => 'ainop',
            'doc' => 'Archivio Informatico Nazionale Opere Pubbliche (MIT). Accesso tramite certificato X.509.',
            'fields' => [
                ['name' => 'ainop_enabled', 'label' => 'Abilita AINOP', 'type' => 'boolean'],
                ['name' => 'ainop_base_url', 'label' => 'Base URL', 'type' => 'url'],
                ['name' => 'ainop_client_id', 'label' => 'Client ID', 'type' => 'text'],
                ['name' => 'ainop_certificate', 'label' => 'Certificato X.509 (PEM)', 'type' => 'textarea'],
                ['name' => 'ainop_cert_fingerprint', 'label' => 'Fingerprint (SHA-256)', 'type' => 'text'],
            ],
        ],
    ];

    public function __construct(private readonly HealthCheckService $health) {}

    public function index(): View
    {
        return view('system.integrations.index', [
            'schema' => self::SCHEMA,
        ]);
    }

    public function show(string $service, string $tab = 'configure'): View
    {
        $config = $this->requireService($service);
        $tab = in_array($tab, ['configure', 'test', 'audit'], true) ? $tab : 'configure';

        $values = $this->loadValues($service);
        $auditLogs = SystemAuditLog::query()
            ->where(function ($q) use ($config, $service) {
                $q->where('action', 'like', 'diagnostic.run.'.$config['diagnostic_key'])
                    ->orWhere('action', 'like', $service.'.%')
                    ->orWhere('action', 'like', 'integration.'.$service.'.%');
            })
            ->latest('created_at')
            ->limit(40)
            ->get();

        return view('system.integrations.show', [
            'service' => $service,
            'config' => $config,
            'values' => $values,
            'tab' => $tab,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function update(Request $request, string $service): RedirectResponse
    {
        $config = $this->requireService($service);

        $rules = [];
        foreach ($config['fields'] as $field) {
            $rule = ['nullable'];
            if (! empty($field['required']) && empty($field['secret'])) {
                $rule = ['required'];
            }
            $rule[] = match ($field['type']) {
                'email' => 'email:rfc',
                'url' => 'url',
                'number' => 'integer',
                'boolean' => 'boolean',
                default => 'string',
            };
            if ($field['type'] === 'textarea') {
                $rule[] = 'max:8192';
            } elseif ($field['type'] === 'number') {
                $rule[] = 'between:0,65535';
            } elseif ($field['type'] !== 'boolean') {
                $rule[] = 'max:512';
            }
            $rules[$field['name']] = $rule;
        }

        $validated = $request->validate($rules);

        foreach ($config['fields'] as $field) {
            $name = $field['name'];

            if ($field['type'] === 'boolean') {
                Setting::set($name, $request->boolean($name) ? '1' : '0', $config['group']);

                continue;
            }

            $value = $validated[$name] ?? null;

            // Secret fields: skip if empty (don't wipe stored value)
            if (! empty($field['secret']) && ($value === null || $value === '')) {
                continue;
            }

            $valueToSave = (string) ($value ?? '');
            if (! empty($field['encrypt']) && $valueToSave !== '') {
                $valueToSave = encrypt($valueToSave);
            }

            Setting::set($name, $valueToSave, $config['group']);
        }

        $actor = $request->user();
        SystemAuditLog::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'sistema',
            'action' => 'integration.'.$service.'.updated',
            'detail' => 'Configurazione salvata',
            'created_at' => now(),
        ]);

        return redirect()->route('system.integrations.show', ['service' => $service])
            ->with('success', $config['label'].' salvato.');
    }

    public function test(string $service): RedirectResponse
    {
        $config = $this->requireService($service);

        $result = $this->health->runOne($config['diagnostic_key']);

        if ($result->ok) {
            return redirect()->route('system.integrations.show', ['service' => $service, 'tab' => 'test'])
                ->with('success', sprintf('Test OK · %d ms · %s', $result->latencyMs, $result->version ?? ''));
        }

        return redirect()->route('system.integrations.show', ['service' => $service, 'tab' => 'test'])
            ->with('error', 'Test fallito: '.$result->error);
    }

    /**
     * @return array{label: string, icon: string, group: string, diagnostic_key: string, doc: string, fields: array<int, array<string, mixed>>}
     */
    private function requireService(string $service): array
    {
        if (! isset(self::SCHEMA[$service])) {
            abort(404, 'Integrazione sconosciuta: '.$service);
        }

        return self::SCHEMA[$service];
    }

    /**
     * @return array<string, mixed>
     */
    private function loadValues(string $service): array
    {
        $config = self::SCHEMA[$service];
        $values = [];

        foreach ($config['fields'] as $field) {
            $raw = Setting::get($field['name'], $field['default'] ?? null);

            // Secret fields not echoed back
            if (! empty($field['secret'])) {
                $values[$field['name']] = ! empty($raw) ? '••••••' : '';
                $values[$field['name'].'_present'] = ! empty($raw);

                continue;
            }

            $values[$field['name']] = $raw;
        }

        return $values;
    }
}

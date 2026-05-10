<?php

declare(strict_types=1);

namespace App\Http\Controllers\System;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\SystemAuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

final class SettingsController extends Controller
{
    public function branding(): View
    {
        $values = [
            'platform_name' => Setting::get('branding.platform_name', config('app.name', 'GTE Abruzzo')),
            'platform_logo' => Setting::get('branding.platform_logo'),
        ];

        return view('system.settings.branding', compact('values'));
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'platform_name' => ['required', 'string', 'max:120'],
            'platform_logo' => ['nullable', 'image', 'max:1024'],
        ]);

        Setting::set('branding.platform_name', $validated['platform_name'], 'branding');

        if ($request->hasFile('platform_logo')) {
            $path = $request->file('platform_logo')->store('branding', 'public');
            Setting::set('branding.platform_logo', Storage::url($path), 'branding');
        }

        $this->audit($request, 'system.settings.branding.updated', 'Branding piattaforma aggiornato');

        return redirect()->route('system.settings.branding')->with('success', 'Branding salvato.');
    }

    public function appBehaviour(): View
    {
        $values = [
            'app_debug' => Setting::get('app.debug', config('app.debug') ? '1' : '0'),
            'app_timezone' => Setting::get('app.timezone', config('app.timezone', 'Europe/Rome')),
            'app_locale' => Setting::get('app.locale', config('app.locale', 'it')),
            'app_maintenance_mode' => Setting::get('app.maintenance_mode', '0'),
        ];

        $timezones = \DateTimeZone::listIdentifiers();
        $locales = ['it' => 'Italiano', 'en' => 'English'];

        return view('system.settings.app-behaviour', compact('values', 'timezones', 'locales'));
    }

    public function updateAppBehaviour(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_debug' => ['boolean'],
            'app_timezone' => ['required', 'string', 'max:64'],
            'app_locale' => ['required', 'string', 'max:5'],
            'app_maintenance_mode' => ['boolean'],
        ]);

        Setting::set('app.debug', $request->boolean('app_debug') ? '1' : '0', 'app');
        Setting::set('app.timezone', $validated['app_timezone'], 'app');
        Setting::set('app.locale', $validated['app_locale'], 'app');

        $newMaintenance = $request->boolean('app_maintenance_mode');
        $oldMaintenance = (string) (Setting::get('app.maintenance_mode', '0')) === '1';

        Setting::set('app.maintenance_mode', $newMaintenance ? '1' : '0', 'app');

        if ($newMaintenance !== $oldMaintenance) {
            try {
                Artisan::call($newMaintenance ? 'down' : 'up');
                $this->audit($request, 'system.settings.maintenance.'.($newMaintenance ? 'enabled' : 'disabled'), 'Maintenance mode toggled');
            } catch (\Throwable $e) {
                return redirect()->route('system.settings.app-behaviour')
                    ->with('error', 'Impostazioni salvate ma toggle maintenance fallito: '.$e->getMessage());
            }
        }

        $this->audit($request, 'system.settings.app-behaviour.updated', 'App behaviour aggiornato');

        return redirect()->route('system.settings.app-behaviour')->with('success', 'Impostazioni salvate.');
    }

    private function audit(Request $request, string $action, string $detail): void
    {
        $actor = $request->user();
        SystemAuditLog::query()->create([
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name ?? 'sistema',
            'action' => $action,
            'detail' => $detail,
            'created_at' => now(),
        ]);
    }
}

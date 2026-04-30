<?php

declare(strict_types=1);

namespace App\Http\Controllers\Setup;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\AdminAccountRequest;
use App\Http\Requests\Setup\AppSettingsRequest;
use App\Http\Requests\Setup\MailSettingsRequest;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

final class SetupController extends Controller
{
    public function index(): RedirectResponse
    {
        if (Setting::isSetupComplete()) {
            return redirect()->route('dashboard');
        }

        return redirect()->route('setup.step1');
    }

    public function showStep1(Request $request): View
    {
        return view('setup.step1', [
            'currentStep' => 1,
            'data' => $request->session()->get('setup.admin', []),
        ]);
    }

    public function storeStep1(AdminAccountRequest $request): RedirectResponse
    {
        $request->session()->put('setup.admin', $request->only('name', 'email', 'password'));

        return redirect()->route('setup.step2');
    }

    public function showStep2(Request $request): View
    {
        return view('setup.step2', [
            'currentStep' => 2,
            'data' => $request->session()->get('setup.app', [
                'app_name' => 'GTE Abruzzo',
                'app_timezone' => 'Europe/Rome',
                'app_locale' => 'it',
            ]),
        ]);
    }

    public function storeStep2(AppSettingsRequest $request): RedirectResponse
    {
        $request->session()->put('setup.app', $request->only('app_name', 'app_timezone', 'app_locale'));

        return redirect()->route('setup.step3');
    }

    public function showStep3(Request $request): View
    {
        return view('setup.step3', [
            'currentStep' => 3,
            'data' => $request->session()->get('setup.mail', []),
        ]);
    }

    public function storeStep3(MailSettingsRequest $request): RedirectResponse
    {
        $request->session()->put('setup.mail', $request->only(
            'mail_host', 'mail_port', 'mail_encryption',
            'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name',
        ));

        return redirect()->route('setup.riepilogo');
    }

    public function showRiepilogo(Request $request): View|RedirectResponse
    {
        $admin = $request->session()->get('setup.admin');
        $app = $request->session()->get('setup.app');

        if (! $admin || ! $app) {
            return redirect()->route('setup.step1');
        }

        return view('setup.riepilogo', [
            'currentStep' => 4,
            'admin' => $admin,
            'app' => $app,
            'mail' => $request->session()->get('setup.mail', []),
        ]);
    }

    public function complete(Request $request): RedirectResponse
    {
        $admin = $request->session()->get('setup.admin');
        $app = $request->session()->get('setup.app');

        if (! $admin || ! $app) {
            return redirect()->route('setup.step1');
        }

        DB::transaction(function () use ($admin, $app, $request): void {
            // Persist app settings
            Setting::set('app_name', $app['app_name'], 'app');
            Setting::set('app_timezone', $app['app_timezone'], 'app');
            Setting::set('app_locale', $app['app_locale'], 'app');

            // Persist mail settings if provided
            $mail = $request->session()->get('setup.mail', []);
            if (! empty($mail['mail_host'])) {
                foreach ($mail as $key => $value) {
                    Setting::set($key, $value, 'mail');
                }
            }

            // Seed roles
            (new RoleSeeder)->run();

            // Create super-admin user
            $user = User::create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $admin['password'],
            ]);
            $user->assignRole(UserRole::SuperAdmin->value);

            // Mark setup as done
            Setting::set('setup_completed', '1', 'app');
        });

        $request->session()->forget(['setup.admin', 'setup.app', 'setup.mail']);

        return redirect()->route('login')->with('setup_complete', true);
    }

    public function testEmail(Request $request): RedirectResponse
    {
        $host = $request->input('mail_host');

        if (! $host) {
            return redirect()->route('setup.step3')
                ->with('error', 'Inserisci prima i dati SMTP prima di inviare un\'email di test.');
        }

        $adminEmail = $request->session()->get('setup.admin')['email'] ?? null;

        if (! $adminEmail) {
            return redirect()->route('setup.step3')
                ->with('error', 'Completa prima il passo 1 (account amministratore) per definire il destinatario.');
        }

        Config::set('mail.mailers.smtp.host', $host);
        Config::set('mail.mailers.smtp.port', $request->input('mail_port', 587));
        Config::set('mail.mailers.smtp.encryption', $request->input('mail_encryption', 'tls'));
        Config::set('mail.mailers.smtp.username', $request->input('mail_username'));
        Config::set('mail.mailers.smtp.password', $request->input('mail_password'));
        Config::set('mail.from.address', $request->input('mail_from_address'));
        Config::set('mail.from.name', $request->input('mail_from_name', 'GTE Abruzzo'));
        Config::set('mail.default', 'smtp');

        try {
            Mail::to($adminEmail)->send(new TestMail);

            return redirect()->route('setup.step3')
                ->with('success', 'Email di test inviata a '.$adminEmail.'.');
        } catch (Throwable $e) {
            return redirect()->route('setup.step3')
                ->with('error', 'Invio fallito: '.$e->getMessage());
        }
    }
}

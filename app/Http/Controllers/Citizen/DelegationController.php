<?php

declare(strict_types=1);

namespace App\Http\Controllers\Citizen;

use App\Http\Controllers\Controller;
use App\Http\Requests\Citizen\StoreDelegationRequest;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DelegationController extends Controller
{
    public function index(): View
    {
        $companies = auth()->user()
            ->companies()
            ->withPivot('role', 'valid_from', 'valid_to', 'approved_at')
            ->orderBy('ragione_sociale')
            ->get();

        return view('citizen.delegations.index', compact('companies'));
    }

    public function create(): View
    {
        return view('citizen.delegations.create');
    }

    public function lookup(Request $request): JsonResponse
    {
        $request->validate([
            'partita_iva' => ['required', 'string', 'regex:/^\d{11}$/'],
        ]);

        $company = Company::where('partita_iva', $request->partita_iva)->first();

        if ($company) {
            return response()->json([
                'found' => true,
                'company' => $company->only(['id', 'ragione_sociale', 'comune', 'provincia', 'pec']),
            ]);
        }

        return response()->json(['found' => false]);
    }

    public function store(StoreDelegationRequest $request): RedirectResponse
    {
        $piva = $request->partita_iva;

        $company = Company::firstOrCreate(
            ['partita_iva' => $piva],
            $request->only([
                'ragione_sociale',
                'codice_fiscale',
                'indirizzo',
                'comune',
                'cap',
                'provincia',
                'email',
                'pec',
                'telefono',
            ])
        );

        if (auth()->user()->companies()->where('company_id', $company->id)->exists()) {
            return back()->withErrors(['partita_iva' => 'Hai già una delega per questa azienda.']);
        }

        auth()->user()->companies()->attach($company->id, [
            'role' => 'delegato',
            'valid_from' => $request->valid_from,
            'valid_to' => $request->valid_to,
        ]);

        return redirect()->route('my.delegations.index')
            ->with('success', 'Richiesta di delega inviata. In attesa di approvazione.');
    }
}

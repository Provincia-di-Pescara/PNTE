<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveDelegationRequest;
use App\Http\Requests\Admin\StoreCompanyRequest;
use App\Http\Requests\Admin\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class CompanyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Company::class);

        $companies = Company::query()
            ->withCount('users')
            ->orderBy('ragione_sociale')
            ->paginate(25);

        return view('admin.companies.index', compact('companies'));
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.form', ['company' => new Company]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        Company::create($request->validated());

        return redirect()->route('admin.companies.index')
            ->with('success', 'Azienda creata con successo.');
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load(['users' => fn ($q) => $q->withPivot('role', 'valid_from', 'valid_to', 'approved_at', 'approved_by')]);

        return view('admin.companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('admin.companies.form', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $company->update($request->validated());

        return redirect()->route('admin.companies.show', $company)
            ->with('success', 'Azienda aggiornata.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()->route('admin.companies.index')
            ->with('success', 'Azienda eliminata.');
    }

    public function approveDelegation(ApproveDelegationRequest $request, Company $company, User $user): RedirectResponse
    {
        $pivot = $company->users()->where('user_id', $user->id)->firstOrFail();

        if ($request->input('action') === 'approve') {
            $company->users()->updateExistingPivot($user->id, [
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
            $message = 'Delega approvata.';
        } else {
            $company->users()->detach($user->id);
            $message = 'Delega rifiutata.';
        }

        return redirect()->route('admin.companies.show', $company)->with('success', $message);
    }
}

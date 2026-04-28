<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTariffRequest;
use App\Http\Requests\Admin\UpdateTariffRequest;
use App\Models\Tariff;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class TariffController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Tariff::class);

        $tariffs = Tariff::query()
            ->orderBy('tipo_asse')
            ->orderByDesc('valid_from')
            ->paginate(50);

        return view('admin.tariffs.index', compact('tariffs'));
    }

    public function create(): View
    {
        $this->authorize('create', Tariff::class);

        return view('admin.tariffs.create');
    }

    public function store(StoreTariffRequest $request): RedirectResponse
    {
        Tariff::create($request->validated());

        return redirect()->route('admin.tariffs.index')
            ->with('success', 'Coefficiente aggiunto al tariffario.');
    }

    public function edit(Tariff $tariff): View
    {
        $this->authorize('update', $tariff);

        return view('admin.tariffs.edit', compact('tariff'));
    }

    public function update(UpdateTariffRequest $request, Tariff $tariff): RedirectResponse
    {
        $tariff->update($request->validated());

        return redirect()->route('admin.tariffs.index')
            ->with('success', 'Coefficiente aggiornato.');
    }

    public function destroy(Tariff $tariff): RedirectResponse
    {
        $this->authorize('delete', $tariff);
        $tariff->delete();

        return redirect()->route('admin.tariffs.index')
            ->with('success', 'Voce tariffaria eliminata.');
    }
}

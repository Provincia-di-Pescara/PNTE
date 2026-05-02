<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Contracts\InfoCamereServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LookupCompanyRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * POST /api/admin/companies/lookup
 *
 * Queries InfoCamere / Registro Imprese via PDND to pre-fill the company form.
 */
final class CompanyLookupController extends Controller
{
    public function __construct(
        private readonly InfoCamereServiceInterface $infoCamere,
    ) {}

    public function __invoke(LookupCompanyRequest $request): JsonResponse
    {
        if (Setting::get('pdnd_enabled', '0') !== '1') {
            return response()->json([
                'error' => 'Integrazione PDND non attiva. Configura il pannello PDND / Interoperabilità.',
                'code' => 'pdnd_disabled',
            ], 503);
        }

        $piva = (string) $request->input('piva');

        try {
            $data = $this->infoCamere->getByPiva($piva);

            return response()->json(['data' => $data]);
        } catch (RuntimeException $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'non trovata')) {
                return response()->json([
                    'error' => $message,
                    'code' => 'not_found',
                ], 404);
            }

            return response()->json([
                'error' => 'Errore durante la consultazione del Registro Imprese: '.$message,
                'code' => 'api_error',
            ], 502);
        }
    }
}

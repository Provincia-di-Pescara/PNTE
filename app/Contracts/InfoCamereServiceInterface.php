<?php

declare(strict_types=1);

namespace App\Contracts;

interface InfoCamereServiceInterface
{
    /**
     * @return array{ragione_sociale: string, codice_fiscale: string|null, indirizzo: string|null, comune: string|null, cap: string|null, provincia: string|null, email: string|null, pec: string|null}
     */
    public function getByPiva(string $piva): array;
}

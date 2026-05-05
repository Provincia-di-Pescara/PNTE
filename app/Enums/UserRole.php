<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    /** Infrastructure operators — no entity/company binding, only /system panel */
    case SystemAdmin = 'system-admin';

    /** Entity-bound manager with governance powers (is_capofila=true entity) */
    case AdminCapofila = 'admin-capofila';

    /** Entity-bound manager for standard tenant/entity workflows */
    case AdminEnte = 'admin-ente';

    /** Entity-bound OR company-bound staff for day-to-day operations */
    case Operator = 'operator';

    /** Company-bound manager; is_agency flag on company unlocks agency features */
    case AdminAzienda = 'admin-azienda';

    /** Municipality/ANAS operator for clearances and roadworks (entity-bound) */
    case ThirdParty = 'third-party';

    /** Agency operators managing mandates for multiple transport-company clients */
    case Agency = 'agency';

    /** Transport company submitting authorization requests */
    case Citizen = 'citizen';

    /** Forze dell'Ordine — read-only approved transports and QR verification */
    case LawEnforcement = 'law-enforcement';
}

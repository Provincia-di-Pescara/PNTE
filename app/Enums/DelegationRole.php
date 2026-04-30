<?php

declare(strict_types=1);

namespace App\Enums;

enum DelegationRole: string
{
    case Titolare = 'titolare';
    case Delegato = 'delegato';
}

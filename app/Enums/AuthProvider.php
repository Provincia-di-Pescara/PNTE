<?php

declare(strict_types=1);

namespace App\Enums;

enum AuthProvider: string
{
    case Local = 'local';
    case Spid = 'spid';
    case Cie = 'cie';
}

<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super-admin';
    case Operator = 'operator';
    case ThirdParty = 'third-party';
    case Citizen = 'citizen';
    case LawEnforcement = 'law-enforcement';
}

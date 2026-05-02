<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Application;

interface ClearanceDispatchServiceInterface
{
    public function dispatch(Application $application): void;
}

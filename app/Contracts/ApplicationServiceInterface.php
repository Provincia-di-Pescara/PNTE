<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Application;

interface ApplicationServiceInterface
{
    public function submit(Application $application): void;

    public function markPaymentReady(Application $application): void;

    public function approve(Application $application): void;

    public function reject(Application $application, string $reason): void;
}

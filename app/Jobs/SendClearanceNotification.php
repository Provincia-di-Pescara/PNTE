<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Clearance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendClearanceNotification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Clearance $clearance,
    ) {}

    public function handle(): void
    {
        $entity = $this->clearance->entity;

        if (empty($entity->pec)) {
            return;
        }

        $applicationId = $this->clearance->application_id;
        $clearanceId = $this->clearance->id;
        $link = route('third-party.clearances.show', $clearanceId);

        Mail::raw(
            "Gentile Ente,\n\nÈ richiesto il Nulla Osta per la pratica #{$applicationId}.\n\nAccedere al portale: {$link}",
            function ($message) use ($entity): void {
                $message->to($entity->pec)
                    ->subject("Richiesta Nulla Osta - Pratica #{$this->clearance->application_id}");
            }
        );
    }
}

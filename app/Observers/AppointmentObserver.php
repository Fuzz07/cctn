<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\AppointmentRevenueRecorder;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        if ($appointment->status === 'approved') {
            $this->activateClient($appointment);
        }
    }

    public function updated(Appointment $appointment): void
    {
        if ($appointment->status === 'approved'
            && $appointment->wasChanged('status')
            && $appointment->getOriginal('status') !== 'approved') {
            app(AppointmentRevenueRecorder::class)->record($appointment);
        }

        if ($appointment->status === 'approved'
            && ($appointment->wasChanged('status')
                || $appointment->wasChanged('service_id')
                || $appointment->wasChanged('subscription_ends_at'))) {
            $this->activateClient($appointment);
            return;
        }

        if ($appointment->status === 'cancelled') {
            $client = $appointment->client;
            if ($client && (int) $client->current_appointment_id === $appointment->id) {
                $client->deactivateSubscription('cancelled');
            }
        }
    }

    private function activateClient(Appointment $appointment): void
    {
        $appointment->loadMissing(['client', 'service']);

        if (! $appointment->service || strcasecmp($appointment->service->status, 'Active') !== 0) {
            return;
        }

        $appointment->client?->activateSubscription(
            $appointment->service,
            $appointment,
            $appointment->subscription_ends_at,
        );
    }
}

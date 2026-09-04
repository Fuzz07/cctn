<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'service'        => $this->whenLoaded('service', fn() => [
                'id'           => $this->service->id,
                'name'         => $this->service->service_name,
                'price'        => (float) $this->service->price,
                'duration_min' => $this->service->duration_minutes,
            ]),
            'preferred_date' => $this->preferred_date?->format('Y-m-d'),
            'preferred_time' => $this->preferred_time?->format('H:i'),
            'message'          => $this->message,
            'status'           => $this->status,
            'payment_method'   => $this->payment_method ?: 'GCash',
            'payment_status'   => $this->payment_status ?: 'unpaid',
            'reference_number' => $this->reference_number,
            'payment_proof'    => $this->payment_proof ? asset('storage/' . $this->payment_proof) : null,
            'admin_notes'      => $this->admin_notes,
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}

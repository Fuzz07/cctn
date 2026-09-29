<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MaintenanceResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'             => $this->id,
            'subject'        => $this->subject,
            'description'    => $this->description,
            'priority'       => $this->priority,
            'status'         => $this->status,
            'follow_up_note' => $this->follow_up_note,
            'messages'       => MaintenanceMessageResource::collection($this->whenLoaded('messages')),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PaymentMethodResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'payment_type'          => $this->payment_type,
            'provider_name'         => $this->provider_name,
            'formatted_type'        => $this->formatted_type,
            'account_name'          => $this->account_name,
            'account_number'        => $this->account_number,
            'masked_account_number' => $this->masked_account_number,
            'is_default'            => (bool) $this->is_default,
            'notes'                 => $this->notes,
            'theme_color'           => $this->theme_color,
            'created_at'            => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                   => $this->id,
            'account_number'       => $this->account_number,
            'firstname'            => $this->firstname,
            'middlename'           => $this->middlename,
            'lastname'             => $this->lastname,
            'full_name'            => $this->full_name,
            'email'                => $this->email,
            'username'             => $this->username,
            'birthdate'            => $this->birthdate?->format('Y-m-d'),
            'age'                  => $this->age,
            'gender'               => $this->gender,
            'civil_status'         => $this->civil_status,
            'place_of_birth'       => $this->place_of_birth,
            'address_barangay'     => $this->address_barangay,
            'address_municipality' => $this->address_municipality,
            'address_province'     => $this->address_province,
            'contact_no'           => $this->contact_no,
            'account_status'       => $this->account_status ?? ($this->isArchived() ? 'Inactive' : 'Active'),
            'subscription_status'  => $this->subscription_status,
            'subscription_status_label' => $this->subscription_status_label,
            'current_plan'         => $this->currentService?->service_name,
            'subscription_ends_at' => $this->subscription_ends_at?->toIso8601String(),
            'disconnection_request_status' => $this->disconnection_request_status,
            'disconnection_status_label' => $this->disconnection_status_label,
            'disconnection_requested_at' => $this->disconnection_requested_at?->toIso8601String(),
            'can_request_disconnection' => $this->canRequestDisconnection(),
            'profile_photo'        => $this->profile_photo
                ? asset($this->profile_photo)
                : null,
            'created_at'           => $this->created_at?->toIso8601String(),
        ];
    }
}

<?php

namespace App\Http\Resources\MedicalCertificate;

use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalCertificateResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'id_user'           => $this->id_user,
            'id_customer'       => $this->id_customer,
            'id_appointment'    => $this->id_appointment,
            'content'           => $this->content,
            'days_off'          => $this->days_off,
            'issue_date'        => $this->issue_date?->format('Y-m-d'),
            'valid_until'       => $this->valid_until?->format('Y-m-d'),
            'digital_signature' => $this->digital_signature,
            'validation_hash'   => $this->validation_hash,
            'created_at'        => $this->created_at?->toIso8601String(),
            'updated_at'        => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'customer'          => CustomerResource::make($this->whenLoaded('customer')),
            'user'              => UserResource::make($this->whenLoaded('user')),
        ];
    }
}

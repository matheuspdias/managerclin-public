<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\MedicalRecord\MedicalRecordResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'birthdate'  => $this->birthdate ? (is_string($this->birthdate) ? $this->birthdate : $this->birthdate->format('Y-m-d')) : null,
            'cpf'        => $this->cpf,
            'notes'      => $this->notes,
            'image'      => $this->image,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'appointments'    => AppointmentResource::collection($this->whenLoaded('appointments')),
            'medical_records' => MedicalRecordResource::collection($this->whenLoaded('medicalRecords')),
        ];
    }
}

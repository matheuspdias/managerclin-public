<?php

namespace App\Http\Resources\MedicalRecord;

use App\Http\Resources\Appointment\AppointmentResource;
use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MedicalRecordResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'id_user'         => $this->id_user,
            'id_customer'     => $this->id_customer,
            'id_appointment'  => $this->id_appointment,
            'chief_complaint' => $this->chief_complaint,
            'physical_exam'   => $this->physical_exam,
            'diagnosis'       => $this->diagnosis,
            'treatment_plan'  => $this->treatment_plan,
            'prescriptions'   => $this->prescriptions,
            'observations'    => $this->observations,
            'follow_up_date'  => $this->follow_up_date?->format('Y-m-d'),
            'medical_history' => $this->medical_history,
            'allergies'       => $this->allergies,
            'medications'     => $this->medications,
            'created_at'      => $this->created_at?->toIso8601String(),
            'updated_at'      => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'customer'        => CustomerResource::make($this->whenLoaded('customer')),
            'user'            => UserResource::make($this->whenLoaded('user')),
            'appointment'     => AppointmentResource::make($this->whenLoaded('appointment')),
        ];
    }
}

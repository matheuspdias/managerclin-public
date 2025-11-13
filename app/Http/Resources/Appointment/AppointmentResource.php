<?php

namespace App\Http\Resources\Appointment;

use App\Http\Resources\Customer\CustomerResource;
use App\Http\Resources\Room\RoomResource;
use App\Http\Resources\Service\ServiceResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transforma o recurso em um array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'id_user'    => $this->id_user,
            'id_customer' => $this->id_customer,
            'id_room'    => $this->id_room,
            'id_service' => $this->id_service,
            'date'       => $this->date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time'   => $this->end_time,
            'price'      => $this->price ? number_format((float) $this->price, 2, '.', '') : null,
            'status'     => $this->status,
            'notes'      => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),

            // Relacionamentos
            'customer'   => CustomerResource::make($this->whenLoaded('customer')),
            'user'       => UserResource::make($this->whenLoaded('user')),
            'room'       => RoomResource::make($this->whenLoaded('room')),
            'service'    => ServiceResource::make($this->whenLoaded('service')),
        ];
    }
}

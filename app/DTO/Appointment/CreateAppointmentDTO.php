<?php

namespace App\DTO\Appointment;

use App\Http\Requests\Appointment\CreateAppointmentRequest;

class CreateAppointmentDTO
{
    public function __construct(
        public int $id_user,
        public int $id_customer,
        public int $id_room,
        public int $id_service,
        public string $date,
        public string $start_time,
        public string $end_time,
        public ?float $price = null,
        public ?string $status = null,
        public ?string $notes = null
    ) {}

    public static function makeFromRequest(CreateAppointmentRequest $request): self
    {
        return new self(
            $request->id_user,
            $request->id_customer,
            $request->id_room,
            $request->id_service,
            $request->date,
            $request->start_time,
            $request->end_time,
            $request->price,
            $request->status,
            $request->notes
        );
    }
}

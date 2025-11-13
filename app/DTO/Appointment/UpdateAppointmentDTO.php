<?php

namespace App\DTO\Appointment;

use App\Http\Requests\Appointment\UpdateAppointmentRequest;

class UpdateAppointmentDTO
{
    public function __construct(
        public ?int $id_user = null,
        public ?int $id_customer = null,
        public ?int $id_room = null,
        public ?int $id_service = null,
        public ?string $date = null,
        public ?string $start_time = null,
        public ?string $end_time = null,
        public ?float $price = null,
        public ?string $status = null,
        public ?string $notes = null
    ) {}

    public static function makeFromRequest(UpdateAppointmentRequest $request): self
    {
        return new self(
            $request->get('id_user'),
            $request->get('id_customer'),
            $request->get('id_room'),
            $request->get('id_service'),
            $request->get('date'),
            $request->get('start_time'),
            $request->get('end_time'),
            $request->get('price'),
            $request->get('status'),
            $request->get('notes')
        );
    }
}

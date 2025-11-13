<?php

namespace App\DTO\Telemedicine;

use App\Http\Requests\CreateTelemedicineSessionRequest;

class CreateTelemedicineSessionDTO
{
    public function __construct(
        public int $appointmentId,
        public ?string $serverUrl = null,
    ) {}

    public static function makeFromRequest(CreateTelemedicineSessionRequest $request): self
    {
        return new self(
            appointmentId: $request->validated()['appointment_id'],
            serverUrl: $request->validated()['server_url'] ?? null,
        );
    }
}

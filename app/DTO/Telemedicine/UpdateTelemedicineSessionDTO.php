<?php

namespace App\DTO\Telemedicine;

use App\Http\Requests\UpdateTelemedicineSessionRequest;

class UpdateTelemedicineSessionDTO
{
    public function __construct(
        public string $status,
        public ?string $notes = null,
        public ?string $endReason = null,
    ) {}

    public static function makeFromRequest(UpdateTelemedicineSessionRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            status: $validated['status'],
            notes: $validated['notes'] ?? null,
            endReason: $validated['end_reason'] ?? null,
        );
    }
}

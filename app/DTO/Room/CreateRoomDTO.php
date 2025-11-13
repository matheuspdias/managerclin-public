<?php

namespace App\DTO\Room;

use App\Http\Requests\Room\CreateRoomRequest;

class CreateRoomDTO
{
    public function __construct(
        public string $name,
        public ?string $location
    ) {}

    public static function makeFromRequest(CreateRoomRequest $request): self
    {
        return new self(
            $request->name,
            $request->location
        );
    }
}

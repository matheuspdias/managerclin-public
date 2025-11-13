<?php

namespace App\DTO\Room;

use App\Http\Requests\Room\UpdateRoomRequest;

class UpdateRoomDTO
{
    public function __construct(
        public ?string $name,
        public ?string $location

    ) {}

    public static function makeFromRequest(UpdateRoomRequest $request): self
    {
        return new self(
            $request->get('name'),
            $request->get('location')
        );
    }
}

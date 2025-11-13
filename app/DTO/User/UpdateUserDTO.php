<?php

namespace App\DTO\User;

use App\Http\Requests\User\UpdateUserRequest;

class UpdateUserDTO
{
    public function __construct(
        public ?int $id_role = null,
        public ?string $name,
        public ?string $email,
        public ?string $phone = null,

    ) {}

    public static function makeFromRequest(UpdateUserRequest $request): self
    {
        return new self(
            $request->get('id_role'),
            $request->get('name'),
            $request->get('email'),
            $request->get('phone'),
        );
    }
}

<?php

namespace App\DTO\User;

use App\Http\Requests\User\CreateUserRequest;

class CreateUserDTO
{
    public function __construct(
        public int $id_role,
        public string $name,
        public string $email,
        public ?string $phone,
        public ?string $password = null, // Senha temporária, opcional
        public ?int $id_company = null, // ID da empresa, opcional
    ) {}

    public static function makeFromRequest(CreateUserRequest $request): self
    {
        return new self(
            $request->get('id_role'),
            $request->get('name'),
            $request->get('email'),
            $request->get('phone'),
        );
    }
}

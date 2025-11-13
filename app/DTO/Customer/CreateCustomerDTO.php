<?php

namespace App\DTO\Customer;

use App\Http\Requests\Customer\CreateCustomerRequest;

class CreateCustomerDTO
{
    public function __construct(
        public string $name,
        public ?string $email,
        public ?string $phone = null,
        public ?string $birthdate = null,
        public ?string $cpf = null,
        public ?string $notes = null,
        public ?string $image = null,

    ) {}

    public static function makeFromRequest(CreateCustomerRequest $request): self
    {
        return new self(
            $request->name,
            $request->email,
            $request->phone,
            $request->birthdate,
            $request->cpf,
            $request->notes,
            $request->image
        );
    }
}

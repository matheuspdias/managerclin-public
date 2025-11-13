<?php

namespace App\DTO\Customer;

use App\Http\Requests\Customer\UpdateCustomerRequest;

class UpdateCustomerDTO
{
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?string $phone = null,
        public ?string $birthdate = null,
        public ?string $cpf = null,
        public ?string $notes = null,
        public ?string $image = null

    ) {}

    public static function makeFromRequest(UpdateCustomerRequest $request): self
    {
        return new self(
            $request->get('name'),
            $request->get('email'),
            $request->get('phone'),
            $request->get('birthdate'),
            $request->get('cpf'),
            $request->get('notes'),
            $request->get('image'),
        );
    }
}

<?php

namespace App\DTO\Account;

use App\Http\Requests\Account\CreateAccountRequest;

class CreateAccountDTO
{
    public function __construct(
        public string $name,
        public string $company_name,
        public string $email,
        public string $phone,
        public string $password,

    ) {}

    public static function makeFromRequest(CreateAccountRequest $request): self
    {
        $data = $request->validated();
        return new self(
            name: $data['name'],
            company_name: $data['company_name'],
            email: $data['email'],
            phone: $data['phone'],
            password: $data['password'],
        );
    }
}

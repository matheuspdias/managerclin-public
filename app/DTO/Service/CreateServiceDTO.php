<?php

namespace App\DTO\Service;

use App\Http\Requests\Service\CreateServiceRequest;

class CreateServiceDTO
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $price,
    ) {}

    public static function makeFromRequest(CreateServiceRequest $request): self
    {
        return new self(
            $request->name,
            $request->description,
            $request->price
        );
    }
}

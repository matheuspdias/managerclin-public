<?php

namespace App\DTO\Service;

use App\Http\Requests\Service\UpdateServiceRequest;

class UpdateServiceDTO
{
    public function __construct(
        public ?string $name,
        public ?string $description,
        public ?string $price

    ) {}

    public static function makeFromRequest(UpdateServiceRequest $request): self
    {
        return new self(
            $request->get('name'),
            $request->get('description'),
            $request->get('price')
        );
    }
}

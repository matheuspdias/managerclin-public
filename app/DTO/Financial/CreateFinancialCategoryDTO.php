<?php

namespace App\DTO\Financial;

use Illuminate\Http\Request;

class CreateFinancialCategoryDTO
{
    public function __construct(
        public string $name,
        public string $type,
        public string $color = '#3B82F6',
        public ?string $icon = null,
        public bool $is_active = true,
        public ?string $description = null,
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            type: $request->input('type'),
            color: $request->input('color', '#3B82F6'),
            icon: $request->input('icon'),
            is_active: (bool) $request->input('is_active', true),
            description: $request->input('description'),
        );
    }
}
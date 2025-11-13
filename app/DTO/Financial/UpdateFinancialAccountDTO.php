<?php

namespace App\DTO\Financial;

use Illuminate\Http\Request;

class UpdateFinancialAccountDTO
{
    public function __construct(
        public string $name,
        public string $type,
        public ?string $bank_name = null,
        public ?string $account_number = null,
        public float $initial_balance = 0,
        public bool $is_active = true,
        public ?string $description = null,
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            name: $request->input('name'),
            type: $request->input('type'),
            bank_name: $request->input('bank_name'),
            account_number: $request->input('account_number'),
            initial_balance: (float) $request->input('initial_balance', 0),
            is_active: (bool) $request->input('is_active', true),
            description: $request->input('description'),
        );
    }
}
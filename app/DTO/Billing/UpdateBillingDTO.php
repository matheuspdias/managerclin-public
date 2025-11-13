<?php

namespace App\DTO\Billing;

use Illuminate\Http\Request;

class UpdateBillingDTO
{
    public function __construct(
        public readonly ?string $planId,
        public readonly ?int $additionalUsers,
        public readonly ?string $paymentMethodId,
    ) {}

    public static function makeFromRequest(Request $request): self
    {
        return new self(
            planId: $request->input('plan_id'),
            additionalUsers: $request->input('additional_users'),
            paymentMethodId: $request->input('payment_method_id'),
        );
    }
}

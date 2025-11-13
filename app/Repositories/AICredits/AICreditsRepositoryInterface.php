<?php

namespace App\Repositories\AICredits;

use App\Models\Company;

interface AICreditsRepositoryInterface
{
    public function getCompanyCredits(Company $company): array;

    public function addCredits(Company $company, int $credits): void;

    public function consumeCredits(Company $company, int $credits): bool;

    public function getAvailablePackages(): array;

    public function createPaymentIntent(Company $company, string $priceId): array;

    public function purchaseWithSavedCard(Company $company, string $priceId): array;
}





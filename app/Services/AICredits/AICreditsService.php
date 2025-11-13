<?php

namespace App\Services\AICredits;

use App\Models\Company;
use App\Repositories\AICredits\AICreditsRepositoryInterface;
use App\Traits\ThrowsExceptions;

class AICreditsService
{
    use ThrowsExceptions;

    public function __construct(
        protected AICreditsRepositoryInterface $repository
    ) {}

    public function getCreditsData(Company $company): array
    {
        $credits = $this->repository->getCompanyCredits($company);
        $packages = $this->repository->getAvailablePackages();

        return [
            'credits' => $credits,
            'packages' => $packages,
        ];
    }

    public function createPaymentIntent(Company $company, string $priceId): array
    {
        return $this->repository->createPaymentIntent($company, $priceId);
    }

    public function consumeCredits(Company $company, int $credits): bool
    {
        return $this->repository->consumeCredits($company, $credits);
    }

    public function addCredits(Company $company, int $credits): void
    {
        $this->repository->addCredits($company, $credits);
    }

    public function getAvailablePackages(): array
    {
        return $this->repository->getAvailablePackages();
    }

    public function purchaseWithSavedCard(Company $company, string $priceId): array
    {
        return $this->repository->purchaseWithSavedCard($company, $priceId);
    }
}





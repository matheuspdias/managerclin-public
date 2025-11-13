<?php

namespace App\Repositories\Billing;

use App\Repositories\BaseRepositoryInterface;
use App\Models\Company;

interface BillingRepositoryInterface extends BaseRepositoryInterface
{
    public function getCompanySubscription(Company $company): ?object;
    
    public function getCompanyInvoices(Company $company): array;
    
    public function getCompanyPaymentMethod(Company $company): ?array;
    
    public function getCompanyUsersCount(Company $company): int;
    
    public function getAvailablePlans(): array;
    
    public function updateCompanySubscription(Company $company, string $planId, ?string $paymentMethod = null): void;
    
    public function updateAdditionalUsers(Company $company, int $quantity): void;
    
    public function updateCompanyPaymentMethod(Company $company, string $paymentMethodId): void;
}

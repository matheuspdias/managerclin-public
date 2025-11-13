<?php

namespace App\Repositories\Customer;

use App\Models\Customer;
use App\Repositories\BaseRepositoryInterface;

interface CustomerRepositoryInterface extends BaseRepositoryInterface
{
    public function getTotalCustomersCount(): int;
    public function getRegisteredCustomersInPeriodCount(array $period): int;
    public function createDefaultCustomer(int $idCompany): Customer;
}

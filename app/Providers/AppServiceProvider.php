<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Debug\ExceptionHandler;
use App\Exceptions\Handler;
use App\Models\Company;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);

        $this->app->bind(
            \App\Repositories\User\UserRepositoryInterface::class,
            \App\Repositories\User\UserEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Customer\CustomerRepositoryInterface::class,
            \App\Repositories\Customer\CustomerEloquentORM::class
        );


        $this->app->bind(
            \App\Repositories\Appointment\AppointmentRepositoryInterface::class,
            \App\Repositories\Appointment\AppointmentEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Role\RoleRepositoryInterface::class,
            \App\Repositories\Role\RoleEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Service\ServiceRepositoryInterface::class,
            \App\Repositories\Service\ServiceEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Room\RoomRepositoryInterface::class,
            \App\Repositories\Room\RoomEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\MedicalRecord\MedicalRecordRepositoryInterface::class,
            \App\Repositories\MedicalRecord\MedicalRecordEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\UserSchedule\UserScheduleRepositoryInterface::class,
            \App\Repositories\UserSchedule\UserScheduleEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\UserSchedule\UserScheduleExceptionRepositoryInterface::class,
            \App\Repositories\UserSchedule\UserScheduleExceptionEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Company\CompanyRepositoryInterface::class,
            \App\Repositories\Company\CompanyEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Billing\BillingRepositoryInterface::class,
            \App\Repositories\Billing\BillingEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\AICredits\AICreditsRepositoryInterface::class,
            \App\Repositories\AICredits\AICreditsEloquentORM::class
        );

        // Financial repositories
        $this->app->bind(
            \App\Repositories\Financial\FinancialAccountRepositoryInterface::class,
            \App\Repositories\Financial\FinancialAccountEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Financial\FinancialCategoryRepositoryInterface::class,
            \App\Repositories\Financial\FinancialCategoryEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Financial\FinancialTransactionRepositoryInterface::class,
            \App\Repositories\Financial\FinancialTransactionEloquentORM::class
        );

        // Inventory repositories
        $this->app->bind(
            \App\Repositories\Inventory\InventoryCategoryRepositoryInterface::class,
            \App\Repositories\Inventory\InventoryCategoryEloquentORM::class
        );

        $this->app->bind(
            \App\Repositories\Inventory\InventoryProductRepositoryInterface::class,
            \App\Repositories\Inventory\InventoryProductEloquentORM::class
        );

        // Marketing repositories
        $this->app->bind(
            \App\Repositories\Marketing\MarketingCampaignRepositoryInterface::class,
            \App\Repositories\Marketing\MarketingCampaignEloquentORM::class
        );

        // Telemedicine repositories
        $this->app->bind(
            \App\Repositories\Telemedicine\TelemedicineRepositoryInterface::class,
            \App\Repositories\Telemedicine\TelemedicineEloquentORM::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Company::class);
    }
}

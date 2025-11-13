<?php

namespace App\Services\Company;

use App\DTO\Account\CreateAccountDTO;
use App\Enums\RoleTypeEnum;
use App\Models\Company;
use App\Models\User;
use App\Repositories\Company\CompanyRepositoryInterface;
use App\Services\Appointment\AppointmentService;
use App\Services\ClinicService\ClinicService;
use App\Services\Customer\CustomerService;
use App\Services\Role\RoleService;
use App\Services\Room\RoomService;
use App\Services\Schedule\UserScheduleService;
use App\Services\User\UserService;
use App\Services\Whatsapp\WhatsappService;
use App\Traits\ThrowsExceptions;
use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class CompanyService
{
    use ThrowsExceptions;
    public function __construct(
        protected CompanyRepositoryInterface $repository,
        protected UserService $userService,
        protected RoleService $roleService,
        protected CustomerService $customerService,
        protected ClinicService $clinicService,
        protected RoomService $roomService,
        protected AppointmentService $appointmentService,
        protected WhatsappService $whatsappService,
        protected UserScheduleService $userScheduleService
    ) {}

    public function getAllPaginate(string|null $search = null, int $page, int $perPage, string $order): LengthAwarePaginator
    {
        return $this->repository->paginate($search, $page, $perPage, $order);
    }

    public function getAll(): Collection
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): Company
    {
        $company = $this->repository->findById($id);
        if (!$company) {
            $this->throwNotFound('Instituição não encontrada.');
        }
        return $company;
    }

    public function createCompanyWithUserAdmin(CreateAccountDTO $dto): User
    {
        // 1️⃣ Criação de empresa e dados internos dentro da transaction
        $companyData = DB::transaction(function () use ($dto) {
            $roleAdmin = $this->roleService->getRoleByType(RoleTypeEnum::ADMIN);

            // Cria empresa
            $companyData = (object)[
                'name' => $dto->company_name,
                'ai_credits' => 50, // créditos iniciais de AI
                'telemedicine_credits' => 20, // créditos iniciais de telemedicina (plano Essencial - padrão trial)
                'trial_ends_at' => now()->addDays(14), // período de teste de 14 dias
            ];

            $company = $this->repository->store($companyData);
            if (!$company) {
                throw new \Exception('Erro ao criar a instituição.');
            }

            // Cria serviços e salas padrão
            $defaultServices  = $this->clinicService->createDefaultServices($company->id);
            $defaultRooms     = $this->roomService->createDefaultRooms($company->id);

            // Cria usuário administrador
            $defaultAdmin = $this->userService->createDefaultUserAdmin((object)[
                'name'        => $dto->name,
                'email'       => $dto->email,
                'phone'       => $dto->phone,
                'password'    => Hash::make($dto->password),
                'id_company'  => $company->id,
                'id_role'     => $roleAdmin->id,
            ]);

            if (!$defaultAdmin) {
                throw new \Exception('Erro ao criar usuário administrador.');
            }

            // cria os horarios padrão do admin
            $this->userScheduleService->createDefaultUserSchedule($defaultAdmin);

            // Cria cliente padrão e agendamento inicial
            $defaultCustomer = $this->customerService->createDefaultCustomer($company->id);
            $this->appointmentService->createDefaultAppointments(
                $company->id,
                $defaultAdmin->id,
                $defaultCustomer->id,
                $defaultRooms[0]->id,
                $defaultServices[0]->id
            );

            return ['company' => $company, 'defaultAdmin' => $defaultAdmin];
        });

        // 2️⃣ Operações externas (stripe e WhatsappInstance) fora da transaction
        $this->createExternalServices($companyData['company']);

        // 3️⃣ Eventos e login do admin
        event(new Registered($companyData['defaultAdmin']));
        Auth::login($companyData['defaultAdmin']);

        return $companyData['defaultAdmin'];
    }


    public function destroy(int $id): void
    {
        $this->repository->deleteById($id);
    }

    public function createExternalServices(Company $company): void
    {
        try {
            // Cria Stripe Customer
            $company->createAsStripeCustomer();

            // Marcar integração Stripe como sucesso
            $company->integration_status()->updateOrCreate(
                ['service' => 'stripe'],
                ['status' => 'success', 'message' => 'Stripe Customer criado com sucesso']
            );
        } catch (\Throwable $e) {
            Log::error("Erro Stripe para empresa {$company->id}: {$e->getMessage()}");
            $company->integration_status()->updateOrCreate(
                ['service' => 'stripe'],
                ['status' => 'failed', 'message' => $e->getMessage()]
            );
        }

        try {
            // Cria instância WhatsApp
            $this->whatsappService->createInstanceConfig($company);

            // Marcar integração WhatsApp como sucesso
            $company->integration_status()->updateOrCreate(
                ['service' => 'whatsapp'],
                ['status' => 'success', 'message' => 'Instância WhatsApp criada com sucesso']
            );
        } catch (\Throwable $e) {
            Log::error("Erro WhatsApp para empresa {$company->id}: {$e->getMessage()}");
            $company->integration_status()->updateOrCreate(
                ['service' => 'whatsapp'],
                ['status' => 'failed', 'message' => $e->getMessage()]
            );
        }
    }
}

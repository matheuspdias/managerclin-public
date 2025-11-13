<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Company;
use App\Models\Customer;
use App\Models\MedicalRecord;
use App\Models\Role;
use App\Models\Room;
use App\Models\Service;
use App\Models\User;
use App\Models\UserSchedule;
use App\Models\UserScheduleException;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {

        //esse seed so roda em ambiente local ou desenvolvimento
        if (!App::environment('local', 'development')) {
            $this->command->info('Este seeder só roda em local ou desenvolvimento. Ambiente atual: ' . App::environment());
            return;
        }

        // Criação da empresa
        $company = Company::create([
            'name' => 'Clínica Modelo',
            'signature_status' => 'active',
            'signature_start_at' => now(),
            'signature_end_at' => now()->addYear(),
        ]);


        // Funções (roles)
        $roles = [
            Role::create(['name' => 'Administrador', 'type' => 'ADMIN']),
            Role::create(['name' => 'Recepcionista', 'type' => 'RECEPTIONIST']),
            Role::create(['name' => 'Médico', 'type' => 'DOCTOR']),
            Role::create(['name' => 'Financeiro', 'type' => 'FINANCE'])
        ];

        // Usuários
        $users = [];
        for ($i = 0; $i < 5; $i++) {
            $users[] = User::create([
                'name' => 'Usuário ' . ($i + 1),
                'email' => 'usuario' . ($i + 1) . '@clinica.com',
                'phone' => '119' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'password' => Hash::make('password'),
                'id_company' => $company->id,
                'id_role' => $roles[array_rand($roles)]->id,
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
            ]);
        }

        //criar horarios para todos os dias
        foreach ($users as $user) {
            for ($day = 0; $day < 7; $day++) { // 0 = Domingo, 6 = Sábado
                UserSchedule::create([
                    'id_user' => $user->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'is_work' => $day === 0 ? false : true, // Domingo não é dia útil
                    'id_company' => $company->id
                ]);
            }
        }

        // Exceções de horários
        foreach ($users as $user) {
            UserScheduleException::create([
                'id_user' => $user->id,
                'date' => now()->addDays(rand(1, 28)),
                'start_time' => '08:00',
                'end_time' => '12:00',
                'is_available' => rand(0, 1) ? true : false,
                'reason' => 'Exceção de horário para manutenção',
                'id_company' => $company->id,
                'created_by' => $user->id,
                'updated_by' => $user->id,
                'deleted_by' => null,
            ]);
        }

        // Salas
        $rooms = [
            Room::create(['name' => 'Sala 1', 'id_company' => $company->id]),
            Room::create(['name' => 'Sala 2', 'id_company' => $company->id]),
            Room::create(['name' => 'Sala 3', 'id_company' => $company->id]),
        ];

        // Serviços
        $services = [
            Service::create(['name' => 'Consulta', 'description' => 'Consulta médica', 'price' => 150.00, 'id_company' => $company->id]),
            Service::create(['name' => 'Exame', 'description' => 'Exame laboratorial', 'price' => 250.00, 'id_company' => $company->id]),
            Service::create(['name' => 'Cirurgia', 'description' => 'Procedimento cirúrgico', 'price' => 500.00, 'id_company' => $company->id]),
        ];

        // Clientes
        $customers = [];
        for ($i = 0; $i < 10; $i++) {
            $customers[] = Customer::create([
                'name' => 'Cliente ' . ($i + 1),
                'email' => 'cliente' . ($i + 1) . '@email.com',
                'phone' => '(11) 9' . rand(1000, 9999) . '-' . rand(1000, 9999),
                'id_company' => $company->id,
            ]);
        }

        // Consultas e prontuários
        foreach ($customers as $customer) {
            $appointment = Appointment::create([
                'date' => now()->addDays(rand(1, 30)),
                'start_time' => '08:00',
                'end_time' => '08:30',
                'status' => 'scheduled',
                'id_user' => $users[array_rand($users)]->id,
                'id_customer' => $customer->id,
                'id_room' => $rooms[array_rand($rooms)]->id,
                'id_service' => $services[array_rand($services)]->id,
                'id_company' => $company->id,
            ]);

            MedicalRecord::create([
                'id_customer' => $customer->id,
                'id_user' => $users[array_rand($users)]->id,
                'id_appointment' => $appointment->id,
                'medical_history' => 'Histórico médico padrão',
                'allergies' => 'Nenhuma',
                'medications' => 'Nenhuma',
                'id_company' => $company->id,
            ]);
        }
    }
}

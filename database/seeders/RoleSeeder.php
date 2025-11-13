<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando criação de roles');

        $rolesToCreate = [
            ['name' => 'Administrador', 'type' => 'ADMIN'],
            ['name' => 'Recepcionista', 'type' => 'RECEPTIONIST'],
            ['name' => 'Médico', 'type' => 'DOCTOR'],
            ['name' => 'Financeiro', 'type' => 'FINANCE'],
        ];

        $createdCount = 0;
        $existingCount = 0;

        foreach ($rolesToCreate as $roleData) {
            // Verifica se a role já existe pelo type (que deve ser único)
            $existingRole = Role::where('type', $roleData['type'])->first();

            if ($existingRole) {
                $this->command->info("Role '{$roleData['name']}' ({$roleData['type']}) já existe. Pulando...");
                $existingCount++;
                continue;
            }

            try {
                $role = Role::create($roleData);
                $this->command->info("✅ Role '{$role->name}' ({$role->type}) criada com sucesso!");
                $createdCount++;

                // Log para produção
                Log::info("Role criada em produção", [
                    'role_name' => $role->name,
                    'role_type' => $role->type,
                    'role_id' => $role->id
                ]);
            } catch (\Exception $e) {
                $this->command->error("❌ Erro ao criar role '{$roleData['name']}': " . $e->getMessage());

                // Log do erro
                Log::error("Erro ao criar role em produção", [
                    'role_data' => $roleData,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Resumo final
        $this->command->newLine();
        $this->command->info("📊 Resumo da execução:");
        $this->command->info("   Roles criadas: {$createdCount}");
        $this->command->info("   Roles já existentes: {$existingCount}");
        $this->command->info("   Total processadas: " . ($createdCount + $existingCount));

        if ($createdCount > 0) {
            Log::info("RoleSeeder executado com sucesso", [
                'created_count' => $createdCount,
                'existing_count' => $existingCount,
                'environment' => App::environment()
            ]);
        }

        $this->command->info('✅ RoleSeeder finalizado!');
    }
}

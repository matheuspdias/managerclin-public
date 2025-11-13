<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('description')->nullable();
            $table->enum('type', ['ADMIN', 'DOCTOR', 'RECEPTIONIST', 'FINANCE'])->unique();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();
        });

        //create default roles
        DB::table('roles')->insert([
            ['name' => 'Administrador', 'description' => 'Gerencia o sistema', 'type' => 'ADMIN'],
            ['name' => 'Médico', 'description' => 'Realiza atendimentos médicos', 'type' => 'DOCTOR'],
            ['name' => 'Recepcionista', 'description' => 'Atende pacientes e gerencia agendamentos', 'type' => 'RECEPTIONIST'],
            ['name' => 'Financeiro', 'description' => 'Gerencia as finanças da clínica', 'type' => 'FINANCE'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Adiciona o valor 'IN_PROGRESS' ao ENUM de status
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('SCHEDULED', 'IN_PROGRESS', 'COMPLETED', 'CANCELLED') DEFAULT 'SCHEDULED'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Primeiro, converte todos os agendamentos com status 'IN_PROGRESS' para 'SCHEDULED'
        // para evitar erro ao remover o valor do ENUM
        DB::table('appointments')
            ->where('status', 'IN_PROGRESS')
            ->update(['status' => 'SCHEDULED']);

        // Agora é seguro remover o valor 'IN_PROGRESS' do ENUM de status
        DB::statement("ALTER TABLE appointments MODIFY COLUMN status ENUM('SCHEDULED', 'COMPLETED', 'CANCELLED') DEFAULT 'SCHEDULED'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('telemedicine_sessions', function (Blueprint $table) {
            $table->integer('credits_consumed')->default(1)->after('duration_minutes')
                ->comment('Número de créditos consumidos pela sessão');
            $table->timestamp('last_credit_check_at')->nullable()->after('credits_consumed')
                ->comment('Última verificação de créditos para sessão ativa');

            // Índice para otimizar busca de sessões que precisam verificação
            $table->index(['status', 'last_credit_check_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('telemedicine_sessions', function (Blueprint $table) {
            $table->dropIndex(['status', 'last_credit_check_at']);
            $table->dropColumn(['credits_consumed', 'last_credit_check_at']);
        });
    }
};

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
        Schema::create('telemedicine_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')
                ->constrained('appointments')
                ->onDelete('cascade')
                ->comment('ID do agendamento associado');
            $table->string('room_name')->unique()->comment('Nome único da sala Jitsi');
            $table->string('server_url')->default('https://meet.jit.si')->comment('URL do servidor Jitsi');
            $table->enum('status', ['WAITING', 'ACTIVE', 'COMPLETED', 'CANCELLED'])
                ->default('WAITING')
                ->comment('Status da sessão');
            $table->timestamp('started_at')->nullable()->comment('Data/hora de início da sessão');
            $table->timestamp('ended_at')->nullable()->comment('Data/hora de término da sessão');
            $table->integer('duration_minutes')->default(0)->comment('Duração da sessão em minutos');
            $table->text('notes')->nullable()->comment('Observações sobre a sessão');
            $table->timestamps();

            // Índices para melhorar performance
            $table->index('appointment_id');
            $table->index('status');
            $table->index(['status', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telemedicine_sessions');
    }
};

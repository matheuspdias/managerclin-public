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
        Schema::table('appointments', function (Blueprint $table) {
            // Índices para otimizar consultas de notificações WhatsApp
            $table->index(['date', 'id_company'], 'idx_appointments_date_company');
            $table->index(['notified_day_before_at'], 'idx_appointments_notified_day_before');
            $table->index(['notified_same_day_at'], 'idx_appointments_notified_same_day');
            $table->index(['date', 'id_company', 'notified_day_before_at'], 'idx_appointments_notifications_query');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_appointments_date_company');
            $table->dropIndex('idx_appointments_notified_day_before');
            $table->dropIndex('idx_appointments_notified_same_day');
            $table->dropIndex('idx_appointments_notifications_query');
        });
    }
};

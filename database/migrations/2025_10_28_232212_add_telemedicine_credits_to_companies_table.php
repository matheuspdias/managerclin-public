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
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('telemedicine_credits')->default(0)->after('ai_credits_last_purchase')
                ->comment('Créditos de telemedicina (plano + adicionais comprados)');
            $table->integer('telemedicine_additional_credits')->default(0)->after('telemedicine_credits')
                ->comment('Créditos adicionais de telemedicina comprados');
            $table->timestamp('telemedicine_credits_last_purchase')->nullable()->after('telemedicine_additional_credits')
                ->comment('Data da última compra de créditos adicionais de telemedicina');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn([
                'telemedicine_credits',
                'telemedicine_additional_credits',
                'telemedicine_credits_last_purchase'
            ]);
        });
    }
};

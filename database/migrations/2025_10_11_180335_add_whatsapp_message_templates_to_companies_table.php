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
            $table->text('whatsapp_message_day_before')->nullable()->after('ai_credits_last_purchase');
            $table->text('whatsapp_message_3hours_before')->nullable()->after('whatsapp_message_day_before');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['whatsapp_message_day_before', 'whatsapp_message_3hours_before']);
        });
    }
};

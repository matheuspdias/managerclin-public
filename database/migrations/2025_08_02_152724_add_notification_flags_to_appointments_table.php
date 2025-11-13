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
            $table->timestamp('notified_day_before_at')->nullable()->after('start_time');
            $table->timestamp('notified_same_day_at')->nullable()->after('notified_day_before_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['notified_day_before_at', 'notified_same_day_at']);
        });
    }
};

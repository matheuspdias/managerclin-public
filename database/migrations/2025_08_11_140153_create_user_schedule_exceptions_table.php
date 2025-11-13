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
        Schema::create('user_schedule_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('is_available')->default(true);
            $table->string('reason')->nullable();

            $table->foreignId('id_company')->constrained('companies')->cascadeOnDelete();

            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_schedule_exceptions');
    }
};

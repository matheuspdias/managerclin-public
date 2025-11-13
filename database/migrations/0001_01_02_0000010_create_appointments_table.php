<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_customer')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('id_room')->constrained('rooms')->cascadeOnDelete();
            $table->foreignId('id_service')->constrained('services')->cascadeOnDelete();

            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            $table->decimal('price', 10, 2)->nullable();

            // Enum status (crie uma migration para criar o enum no banco ou use string)
            $table->enum('status', ['SCHEDULED', 'CANCELLED', 'COMPLETED'])->default('SCHEDULED');

            $table->text('notes')->nullable();

            $table->foreignId('id_company')->constrained('companies')->cascadeOnDelete();

            // Auditoria
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};

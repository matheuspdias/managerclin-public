<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('cpf')->nullable();
            $table->text('notes')->nullable();
            $table->string('image')->nullable();

            // Foreign key para Company
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
        Schema::dropIfExists('customers');
    }
};

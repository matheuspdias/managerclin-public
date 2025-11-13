<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();

            $table->foreignId('id_user')->constrained('users')->cascadeOnDelete();
            $table->foreignId('id_customer')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('id_appointment')->nullable()->constrained('appointments')->cascadeOnDelete();
            $table->text('medical_history')->nullable();
            $table->text('allergies')->nullable();
            $table->text('medications')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->text('physical_exam')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('prescriptions')->nullable();
            $table->text('observations')->nullable();
            $table->date('follow_up_date')->nullable();

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
        Schema::dropIfExists('medical_records');
    }
};

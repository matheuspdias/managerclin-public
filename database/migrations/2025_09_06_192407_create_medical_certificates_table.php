<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/2023_01_01_create_medical_certificates_table.php
    public function up()
    {
        Schema::create('medical_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_user')->constrained('users')->onDelete('cascade');
            $table->foreignId('id_customer')->constrained('customers')->onDelete('cascade');
            $table->text('content');
            $table->integer('days_off')->default(0);
            $table->date('issue_date');
            $table->date('valid_until');
            $table->string('digital_signature')->nullable();
            $table->string('validation_hash')->unique();

            $table->foreignId('id_company')->constrained('companies')->cascadeOnDelete();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->default(null)->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('medical_certificates');
    }
};

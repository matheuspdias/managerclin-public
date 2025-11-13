<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_company');
            $table->string('service');           // ex: stripe, whatsapp
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('message')->nullable(); // detalhes do erro ou sucesso
            $table->timestamps();

            $table->unique(['id_company', 'service']);
            $table->foreign('id_company')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_status');
    }
};

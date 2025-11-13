<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_suppliers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_company');
            $table->string('name');
            $table->string('cnpj', 18)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_company')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['id_company', 'active']);
            $table->index(['id_company', 'cnpj']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_suppliers');
    }
};
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
        Schema::create('financial_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // INCOME, EXPENSE
            $table->string('color', 7)->default('#3B82F6'); // Hex color for UI
            $table->string('icon')->nullable(); // Lucide icon name
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            // Multi-tenancy
            $table->foreignId('id_company')->constrained('companies')->onDelete('cascade');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_company', 'type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_categories');
    }
};

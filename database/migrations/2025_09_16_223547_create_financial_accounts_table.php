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
        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('CHECKING'); // CHECKING, SAVINGS, CASH, CREDIT_CARD
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
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

            $table->index(['id_company', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_accounts');
    }
};
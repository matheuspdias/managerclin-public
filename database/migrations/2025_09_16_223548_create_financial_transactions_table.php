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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // INCOME, EXPENSE, TRANSFER
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->date('transaction_date');
            $table->date('due_date')->nullable();
            $table->string('status')->default('PENDING'); // PENDING, PAID, OVERDUE, CANCELLED
            $table->string('payment_method')->nullable(); // CASH, CARD, TRANSFER, PIX, CHECK
            $table->string('document_number')->nullable();
            $table->text('notes')->nullable();
            $table->json('attachments')->nullable(); // File paths for receipts/invoices

            // Foreign keys
            $table->foreignId('id_financial_account')->constrained('financial_accounts')->onDelete('cascade');
            $table->foreignId('id_financial_category')->constrained('financial_categories')->onDelete('cascade');
            $table->foreignId('id_customer')->nullable()->constrained('customers')->onDelete('set null');
            $table->foreignId('id_appointment')->nullable()->constrained('appointments')->onDelete('set null');

            // For transfers between accounts
            $table->foreignId('id_transfer_account')->nullable()->constrained('financial_accounts')->onDelete('set null');

            // Multi-tenancy
            $table->foreignId('id_company')->constrained('companies')->onDelete('cascade');

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('deleted_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['id_company', 'type', 'status']);
            $table->index(['id_company', 'transaction_date']);
            $table->index(['id_financial_account', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};

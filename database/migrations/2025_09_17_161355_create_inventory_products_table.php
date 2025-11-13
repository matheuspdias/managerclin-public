<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_company');
            $table->unsignedBigInteger('id_category');
            $table->unsignedBigInteger('id_supplier')->nullable();
            $table->string('name');
            $table->string('code')->nullable(); // Código interno do produto
            $table->string('barcode')->nullable(); // Código de barras
            $table->text('description')->nullable();
            $table->string('unit'); // Unidade de medida (un, cx, ml, etc)
            $table->decimal('current_stock', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->decimal('maximum_stock', 10, 2)->nullable();
            $table->decimal('cost_price', 10, 2)->nullable(); // Preço de custo
            $table->decimal('sale_price', 10, 2)->nullable(); // Preço de venda
            $table->date('expiry_date')->nullable(); // Data de validade
            $table->string('batch_number')->nullable(); // Lote
            $table->text('storage_location')->nullable(); // Local de armazenamento
            $table->boolean('requires_prescription')->default(false); // Requer receita
            $table->boolean('controlled_substance')->default(false); // Substância controlada
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('id_company')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('id_category')->references('id')->on('inventory_categories')->onDelete('restrict');
            $table->foreign('id_supplier')->references('id')->on('inventory_suppliers')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['id_company', 'active']);
            $table->index(['id_company', 'id_category']);
            $table->index(['id_company', 'code']);
            $table->index(['id_company', 'barcode']);
            $table->index(['current_stock', 'minimum_stock']); // Para alertas de estoque baixo
            $table->index('expiry_date'); // Para alertas de vencimento
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_products');
    }
};
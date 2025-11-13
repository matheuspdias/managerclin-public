<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_company');
            $table->unsignedBigInteger('id_product');
            $table->unsignedBigInteger('id_user'); // Usuário que fez a movimentação
            $table->enum('type', ['IN', 'OUT', 'ADJUSTMENT', 'TRANSFER', 'RETURN']); // Tipo de movimentação
            $table->decimal('quantity', 10, 2); // Quantidade movimentada
            $table->decimal('unit_cost', 10, 2)->nullable(); // Custo unitário (para entradas)
            $table->decimal('total_cost', 10, 2)->nullable(); // Custo total
            $table->decimal('stock_before', 10, 2); // Estoque antes da movimentação
            $table->decimal('stock_after', 10, 2); // Estoque após a movimentação
            $table->string('reason'); // Motivo da movimentação
            $table->text('notes')->nullable(); // Observações
            $table->string('document_number')->nullable(); // Número do documento (NF, recibo, etc)
            $table->date('movement_date'); // Data da movimentação
            $table->string('batch_number')->nullable(); // Lote do produto
            $table->date('expiry_date')->nullable(); // Data de validade
            $table->timestamps();

            $table->foreign('id_company')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('id_product')->references('id')->on('inventory_products')->onDelete('restrict');
            $table->foreign('id_user')->references('id')->on('users')->onDelete('restrict');

            $table->index(['id_company', 'movement_date']);
            $table->index(['id_company', 'id_product']);
            $table->index(['id_company', 'type']);
            $table->index('movement_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
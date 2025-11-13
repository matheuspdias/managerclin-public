<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');

            $table->enum('signature_status', ['TRIAL', 'ACTIVE', 'EXPIRED', 'CANCELLED'])->default('TRIAL');
            $table->dateTime('signature_start_at')->nullable();
            $table->dateTime('signature_end_at')->nullable();
            $table->integer('ai_credits')->default(0);
            $table->integer('ai_additional_credits')->default(0);
            $table->timestamp('ai_credits_last_purchase')->nullable();


            $table->timestamps(); // created_at & updated_at
            $table->softDeletes(); // deleted_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};

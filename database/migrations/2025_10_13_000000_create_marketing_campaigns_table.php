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
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_company')->constrained('companies')->onDelete('cascade');

            // Campaign details
            $table->string('name');
            $table->text('message');
            $table->enum('status', ['draft', 'scheduled', 'sending', 'sent', 'failed', 'cancelled'])->default('draft');

            // Targeting
            $table->enum('target_audience', ['all', 'with_appointments', 'without_appointments', 'custom'])->default('all');
            $table->json('target_filters')->nullable(); // For custom filters

            // Scheduling
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('sent_at')->nullable();

            // Statistics
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);

            // Audit fields
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('id_company');
            $table->index('status');
            $table->index('scheduled_at');
        });

        Schema::create('marketing_campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_campaign')->constrained('marketing_campaigns')->onDelete('cascade');
            $table->foreignId('id_customer')->constrained('customers')->onDelete('cascade');

            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('id_campaign');
            $table->index('id_customer');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_campaign_recipients');
        Schema::dropIfExists('marketing_campaigns');
    }
};

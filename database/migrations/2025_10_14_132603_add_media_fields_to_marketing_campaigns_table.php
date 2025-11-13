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
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('media_type')->nullable()->after('message'); // image, video, document, audio
            $table->string('media_url')->nullable()->after('media_type'); // URL ou base64 da mídia
            $table->string('media_filename')->nullable()->after('media_url'); // Nome do arquivo (para documents)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'media_url', 'media_filename']);
        });
    }
};

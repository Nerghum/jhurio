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
        Schema::table('products', function (Blueprint $table) {
            $table->longText('content')->nullable();
            $table->enum('status', ['published', 'draft'])->default('draft');
            $table->integer('stock_quantity')->default(0);
            $table->unsignedBigInteger('thumbnail_media_id')->nullable();
            $table->string('video_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['content', 'status', 'stock_quantity', 'thumbnail_media_id', 'video_url']);
        });
    }
};

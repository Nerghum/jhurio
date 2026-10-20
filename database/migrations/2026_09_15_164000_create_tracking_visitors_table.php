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
        Schema::create('tracking_visitors', function (Blueprint $table) {
            $table->id();
            $table->uuid('visitor_id')->unique();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->string('country')->nullable();
            $table->string('division')->nullable();
            $table->string('city')->nullable();
            $table->string('device')->nullable(); // Desktop, Mobile, Tablet
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->string('screen_size')->nullable();
            $table->string('language')->nullable();
            $table->string('first_source')->nullable();
            $table->timestamps();

            $table->index('last_seen_at');
            $table->index('country');
            $table->index('division');
            $table->index('device');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_visitors');
    }
};

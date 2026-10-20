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
        Schema::create('tracking_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('session_id')->unique();
            $table->uuid('tracking_visitor_id')->index(); // Matches tracking_visitors.visitor_id
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration')->default(0); // in seconds
            $table->string('landing_page')->nullable();
            $table->string('exit_page')->nullable();
            $table->integer('pages_viewed')->default(0);
            $table->string('traffic_source')->nullable(); // Direct, Google, Facebook, etc.
            $table->text('referrer')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_term')->nullable();
            $table->timestamps();

            $table->index('started_at');
            $table->index('traffic_source');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_sessions');
    }
};

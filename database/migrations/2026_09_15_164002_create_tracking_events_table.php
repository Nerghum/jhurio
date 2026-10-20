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
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->id();
            $table->uuid('tracking_visitor_id')->index();
            $table->uuid('tracking_session_id')->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('event_type')->index(); // page_view, add_to_cart, etc.
            $table->text('page_url')->nullable();
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->string('search_keyword')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->json('metadata')->nullable(); // For any extra data
            $table->timestamps();

            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};

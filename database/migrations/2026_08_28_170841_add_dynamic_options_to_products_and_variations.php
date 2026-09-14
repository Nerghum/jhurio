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
            $table->json('options')->nullable()->after('status');
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->json('options')->nullable()->after('product_id');
            // Drop old columns if they exist. We don't drop 'name' yet since it could be used as a fallback label
            if (Schema::hasColumn('product_variations', 'color_code')) {
                $table->dropColumn('color_code');
            }
            if (Schema::hasColumn('product_variations', 'size')) {
                $table->dropColumn('size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_variations', function (Blueprint $table) {
            $table->dropColumn('options');
            $table->string('color_code')->nullable();
            $table->string('size')->nullable();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('options');
        });
    }
};

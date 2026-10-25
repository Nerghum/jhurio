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
        if (Schema::hasTable('products') && ! Schema::hasColumn('products', 'purchase_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('purchase_price', 10, 2)->default(0)->after('price');
            });
        }

        if (Schema::hasTable('product_variations') && ! Schema::hasColumn('product_variations', 'purchase_price')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->decimal('purchase_price', 10, 2)->nullable()->after('price');
            });
        }

        if (Schema::hasTable('order_items') && ! Schema::hasColumn('order_items', 'purchase_price')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('purchase_price', 10, 2)->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products') && Schema::hasColumn('products', 'purchase_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('purchase_price');
            });
        }

        if (Schema::hasTable('product_variations') && Schema::hasColumn('product_variations', 'purchase_price')) {
            Schema::table('product_variations', function (Blueprint $table) {
                $table->dropColumn('purchase_price');
            });
        }

        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'purchase_price')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn('purchase_price');
            });
        }
    }
};

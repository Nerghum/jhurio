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
        Schema::table('coupons', function (Blueprint $table) {
            $table->string('code')->unique()->nullable()->after('id');
            $table->enum('type', ['fixed', 'percent'])->default('fixed')->after('code');
            $table->decimal('value', 10, 2)->default(0)->after('type');
            $table->decimal('min_order_value', 10, 2)->nullable()->after('value');
            $table->timestamp('expires_at')->nullable()->after('min_order_value');
            $table->integer('usage_limit')->nullable()->after('expires_at');
            $table->integer('times_used')->default(0)->after('usage_limit');
            $table->boolean('is_active')->default(true)->after('times_used');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'code', 'type', 'value', 'min_order_value', 'expires_at', 'usage_limit', 'times_used', 'is_active',
            ]);
        });
    }
};

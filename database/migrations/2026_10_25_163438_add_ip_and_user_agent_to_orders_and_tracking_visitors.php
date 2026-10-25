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
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (! Schema::hasColumn('orders', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('tracking_session_id');
                }
                if (! Schema::hasColumn('orders', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('ip_address');
                }
            });
        }

        if (Schema::hasTable('tracking_visitors')) {
            Schema::table('tracking_visitors', function (Blueprint $table) {
                if (! Schema::hasColumn('tracking_visitors', 'ip_address')) {
                    $table->string('ip_address', 45)->nullable()->after('visitor_id');
                }
                if (! Schema::hasColumn('tracking_visitors', 'user_agent')) {
                    $table->text('user_agent')->nullable()->after('device');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (Schema::hasColumn('orders', 'ip_address')) {
                    $table->dropColumn('ip_address');
                }
                if (Schema::hasColumn('orders', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
            });
        }

        if (Schema::hasTable('tracking_visitors')) {
            Schema::table('tracking_visitors', function (Blueprint $table) {
                if (Schema::hasColumn('tracking_visitors', 'ip_address')) {
                    $table->dropColumn('ip_address');
                }
                if (Schema::hasColumn('tracking_visitors', 'user_agent')) {
                    $table->dropColumn('user_agent');
                }
            });
        }
    }
};

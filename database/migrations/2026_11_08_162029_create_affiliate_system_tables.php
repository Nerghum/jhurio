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
        Schema::create('affiliates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('code', 30)->unique();
            $table->string('status')->default('pending'); // pending, approved, rejected, suspended
            $table->decimal('balance', 12, 2)->default(0.00); // withdrawable
            $table->decimal('pending_balance', 12, 2)->default(0.00); // pending order delivery
            $table->decimal('lifetime_earnings', 12, 2)->default(0.00);
            $table->string('payout_method')->nullable(); // bkash, nagad, rocket, bank
            $table->string('payout_account')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->string('api_token', 80)->nullable()->unique();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('affiliate_referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->decimal('order_amount', 12, 2)->default(0.00);
            $table->decimal('commission_amount', 12, 2)->default(0.00);
            $table->string('status')->default('pending'); // pending, approved, cancelled
            $table->json('items_breakdown')->nullable();
            $table->timestamps();
        });

        Schema::create('affiliate_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('affiliate_id')->constrained('affiliates')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('payout_method');
            $table->string('account_details');
            $table->string('status')->default('pending'); // pending, paid, rejected
            $table->string('transaction_id')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('affiliate_id')->nullable()->constrained('affiliates')->nullOnDelete();
            $table->string('affiliate_code', 50)->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['affiliate_id']);
            $table->dropColumn(['affiliate_id', 'affiliate_code']);
        });

        Schema::dropIfExists('affiliate_payouts');
        Schema::dropIfExists('affiliate_referrals');
        Schema::dropIfExists('affiliates');
    }
};

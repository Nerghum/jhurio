<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\AffiliateReferral;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AffiliateService
{
    /**
     * Calculate commission for an order based on:
     * - 150 Tk or 10% of product price (whichever is more)
     * - Capped up to 300 Tk per product
     * - Bundle products are counted as a single product
     *
     * @return array{total_commission: float, items_breakdown: array}
     */
    public static function calculateCommission(Order $order): array
    {
        $items = $order->items;
        if ($items->isEmpty()) {
            return [
                'total_commission' => 0.0,
                'items_breakdown' => [],
            ];
        }

        $totalCommission = 0.0;
        $itemsBreakdown = [];

        // Check if items share a bundle tag or if individual items are bundles
        $bundleGroups = [];
        $regularItems = [];

        foreach ($items as $item) {
            $variationName = (string) ($item->variation_name ?? '');
            $productName = (string) ($item->product_name ?? '');
            $isBundle = false;
            $bundleTag = null;

            if (preg_match('/\[(.*bundle.*)\]/i', $variationName, $m)) {
                $isBundle = true;
                $bundleTag = trim($m[1]);
            } elseif (stripos($productName, 'bundle') !== false || stripos($variationName, 'bundle') !== false) {
                $isBundle = true;
                $bundleTag = 'bundle_'.$item->product_id;
            }

            if ($isBundle && $bundleTag) {
                $bundleGroups[$bundleTag][] = $item;
            } else {
                $regularItems[] = $item;
            }
        }

        // Process bundled items as single products
        foreach ($bundleGroups as $tag => $bundleItemList) {
            $bundleTotalPrice = 0.0;
            $names = [];

            foreach ($bundleItemList as $bItem) {
                $bundleTotalPrice += (floatval($bItem->price) * intval($bItem->quantity));
                $names[] = $bItem->product_name;
            }

            $tenPercent = 0.10 * $bundleTotalPrice;
            $bundleCommission = min(300.0, max(150.0, $tenPercent));
            $totalCommission += $bundleCommission;

            $itemsBreakdown[] = [
                'type' => 'bundle',
                'name' => implode(' + ', array_unique($names))." ({$tag})",
                'price' => round($bundleTotalPrice, 2),
                'quantity' => 1,
                'commission_rate' => $tenPercent > 150.0 ? '10%' : '150 Tk min',
                'commission_earned' => round($bundleCommission, 2),
            ];
        }

        // Process regular items
        foreach ($regularItems as $item) {
            $unitPrice = floatval($item->price);
            $qty = max(1, intval($item->quantity));

            $tenPercent = 0.10 * $unitPrice;
            $unitCommission = min(300.0, max(150.0, $tenPercent));
            $lineCommission = $unitCommission * $qty;
            $totalCommission += $lineCommission;

            $itemsBreakdown[] = [
                'type' => 'single',
                'name' => $item->product_name.($item->variation_name ? ' ('.$item->variation_name.')' : ''),
                'price' => round($unitPrice, 2),
                'quantity' => $qty,
                'unit_commission' => round($unitCommission, 2),
                'commission_rate' => $tenPercent > 150.0 ? '10%' : '150 Tk min',
                'commission_earned' => round($lineCommission, 2),
            ];
        }

        return [
            'total_commission' => round($totalCommission, 2),
            'items_breakdown' => $itemsBreakdown,
        ];
    }

    /**
     * Record an affiliate referral when an order is created.
     */
    public static function recordReferral(Order $order, ?string $affiliateCode): ?AffiliateReferral
    {
        if (empty($affiliateCode)) {
            return null;
        }

        $affiliate = Affiliate::where('code', trim($affiliateCode))
            ->where('status', 'approved')
            ->first();

        if (! $affiliate) {
            return null;
        }

        return DB::transaction(function () use ($order, $affiliate) {
            $order->update([
                'affiliate_id' => $affiliate->id,
                'affiliate_code' => $affiliate->code,
            ]);

            $calc = self::calculateCommission($order);

            $referral = AffiliateReferral::create([
                'affiliate_id' => $affiliate->id,
                'order_id' => $order->id,
                'order_amount' => $order->total_amount,
                'commission_amount' => $calc['total_commission'],
                'status' => 'pending',
                'items_breakdown' => $calc['items_breakdown'],
            ]);

            // Add to pending balance
            if ($calc['total_commission'] > 0) {
                $affiliate->increment('pending_balance', $calc['total_commission']);
            }

            return $referral;
        });
    }

    /**
     * Handle order status transitions (delivered, completed, cancelled, returned).
     */
    public static function handleOrderStatusChanged(Order $order, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) {
            return;
        }

        $referral = AffiliateReferral::where('order_id', $order->id)->first();
        if (! $referral) {
            return;
        }

        $affiliate = $referral->affiliate;
        if (! $affiliate) {
            return;
        }

        $isSuccess = in_array(strtolower($newStatus), ['delivered', 'completed']);
        $isCancelled = in_array(strtolower($newStatus), ['cancelled', 'returned']);
        $commission = floatval($referral->commission_amount);

        DB::transaction(function () use ($referral, $affiliate, $isSuccess, $isCancelled, $commission) {
            if ($isSuccess && $referral->status !== 'approved') {
                $referral->update(['status' => 'approved']);

                // Move from pending to withdrawable balance & record lifetime earnings
                $affiliate->pending_balance = max(0, floatval($affiliate->pending_balance) - $commission);
                $affiliate->balance = floatval($affiliate->balance) + $commission;
                $affiliate->lifetime_earnings = floatval($affiliate->lifetime_earnings) + $commission;
                $affiliate->save();

                Log::info("Affiliate Referral #{$referral->id} APPROVED. Earned {$commission} Tk for Affiliate #{$affiliate->id}");
            } elseif ($isCancelled && $referral->status !== 'cancelled') {
                $prevStatus = $referral->status;
                $referral->update(['status' => 'cancelled']);

                if ($prevStatus === 'pending') {
                    $affiliate->pending_balance = max(0, floatval($affiliate->pending_balance) - $commission);
                    $affiliate->save();
                } elseif ($prevStatus === 'approved') {
                    $affiliate->balance = max(0, floatval($affiliate->balance) - $commission);
                    $affiliate->lifetime_earnings = max(0, floatval($affiliate->lifetime_earnings) - $commission);
                    $affiliate->save();
                }

                Log::info("Affiliate Referral #{$referral->id} CANCELLED. Deducted {$commission} Tk from Affiliate #{$affiliate->id}");
            }
        });
    }
}

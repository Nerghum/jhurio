<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use App\Models\Product;
use App\Models\User;
use App\Services\FirebasePushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AffiliateController extends Controller
{
    /**
     * Affiliate Registration (from mobile app or web)
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25|unique:affiliates,phone',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:6',
            'payout_method' => 'required|string|in:bkash,nagad,rocket,bank',
            'payout_account' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $code = Affiliate::generateUniqueCode($validated['name']);

        $affiliate = Affiliate::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'], // hashed via model cast
            'code' => $code,
            'status' => 'pending',
            'balance' => 0.00,
            'pending_balance' => 0.00,
            'lifetime_earnings' => 0.00,
            'payout_method' => strtolower($validated['payout_method']),
            'payout_account' => $validated['payout_account'],
            'notes' => $validated['notes'] ?? null,
            'api_token' => Str::random(60),
        ]);

        // Push notification to Admin
        try {
            $admins = User::whereNotNull('fcm_token')->get();
            foreach ($admins as $admin) {
                FirebasePushService::send(
                    $admin->fcm_token,
                    'New Affiliate Registration! 🤝',
                    "{$affiliate->name} ({$affiliate->phone}) applied for the affiliate program.",
                    ['url' => route('admin.affiliates.show', $affiliate->id)]
                );
            }
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => true,
            'status' => 'pending',
            'message' => 'Your affiliate application has been submitted successfully! It is currently under review by our admin team. You will be able to log in and generate links once approved.',
            'affiliate' => [
                'id' => $affiliate->id,
                'name' => $affiliate->name,
                'phone' => $affiliate->phone,
                'code' => $affiliate->code,
                'status' => $affiliate->status,
            ],
        ], 201);
    }

    /**
     * Affiliate Login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $affiliate = Affiliate::where('phone', trim($request->phone))->first();

        if (! $affiliate || ! Hash::check($request->password, $affiliate->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid phone number or password.',
            ], 401);
        }

        if ($affiliate->status === 'pending') {
            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => 'Your application is still under review by our admin. Please check back soon or contact support.',
                'affiliate' => [
                    'id' => $affiliate->id,
                    'name' => $affiliate->name,
                    'phone' => $affiliate->phone,
                    'status' => $affiliate->status,
                ],
            ], 403);
        }

        if ($affiliate->status === 'rejected') {
            return response()->json([
                'success' => false,
                'status' => 'rejected',
                'message' => 'Your affiliate application was not approved. '.($affiliate->rejection_reason ? 'Reason: '.$affiliate->rejection_reason : 'Please contact support.'),
                'affiliate' => [
                    'id' => $affiliate->id,
                    'name' => $affiliate->name,
                    'status' => $affiliate->status,
                    'rejection_reason' => $affiliate->rejection_reason,
                ],
            ], 403);
        }

        if ($affiliate->status === 'suspended') {
            return response()->json([
                'success' => false,
                'status' => 'suspended',
                'message' => 'Your affiliate account is currently suspended. Please contact admin.',
            ], 403);
        }

        // Generate / refresh token
        $affiliate->api_token = Str::random(60);
        $affiliate->save();

        return response()->json([
            'success' => true,
            'status' => 'approved',
            'token' => $affiliate->api_token,
            'message' => 'Logged in successfully.',
            'affiliate' => $this->formatAffiliateProfile($affiliate),
        ]);
    }

    /**
     * Get Affiliate Dashboard & Metrics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $affiliate = $this->getAuthenticatedAffiliate($request);
        if (! $affiliate) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $referrals = AffiliateReferral::where('affiliate_id', $affiliate->id)
            ->with(['order.items.product.thumbnail'])
            ->latest()
            ->take(30)
            ->get();

        $payouts = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->latest()
            ->take(20)
            ->get();

        $stats = [
            'balance' => floatval($affiliate->balance),
            'pending_balance' => floatval($affiliate->pending_balance),
            'lifetime_earnings' => floatval($affiliate->lifetime_earnings),
            'total_referrals' => AffiliateReferral::where('affiliate_id', $affiliate->id)->count(),
            'approved_referrals' => AffiliateReferral::where('affiliate_id', $affiliate->id)->where('status', 'approved')->count(),
            'pending_referrals' => AffiliateReferral::where('affiliate_id', $affiliate->id)->where('status', 'pending')->count(),
            'total_paid' => floatval(AffiliatePayout::where('affiliate_id', $affiliate->id)->where('status', 'paid')->sum('amount')),
            'commission_formula' => '150 Tk or 10% of product price (up to 300 Tk per product). Credited upon delivery.',
        ];

        return response()->json([
            'success' => true,
            'affiliate' => $this->formatAffiliateProfile($affiliate),
            'stats' => $stats,
            'referrals' => $referrals->map(function ($ref) {
                return [
                    'id' => $ref->id,
                    'order_id' => $ref->order_id,
                    'order_amount' => floatval($ref->order_amount),
                    'commission_amount' => floatval($ref->commission_amount),
                    'status' => $ref->status, // pending, approved, cancelled
                    'order_status' => $ref->order->status ?? 'pending',
                    'customer_name' => $ref->order->name ?? 'Customer',
                    'items_breakdown' => $ref->items_breakdown,
                    'created_at' => $ref->created_at->toIso8601String(),
                ];
            }),
            'payouts' => $payouts,
        ]);
    }

    /**
     * Request a Payout
     */
    public function requestPayout(Request $request): JsonResponse
    {
        $affiliate = $this->getAuthenticatedAffiliate($request);
        if (! $affiliate) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $minPayout = 100.0; // Minimum payout 100 Tk
        $currentBalance = floatval($affiliate->balance);

        $validated = $request->validate([
            'amount' => "required|numeric|min:{$minPayout}|max:{$currentBalance}",
            'payout_method' => 'nullable|string|in:bkash,nagad,rocket,bank',
            'account_details' => 'nullable|string|max:100',
        ], [
            'amount.max' => "You cannot withdraw more than your current withdrawable balance (৳{$currentBalance}).",
            'amount.min' => "Minimum payout request is ৳{$minPayout}.",
        ]);

        $amount = floatval($validated['amount']);
        $method = $validated['payout_method'] ?? $affiliate->payout_method ?? 'bkash';
        $account = $validated['account_details'] ?? $affiliate->payout_account;

        if (empty($account)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide your payment account number (e.g. bKash / Nagad wallet number).',
            ], 422);
        }

        // Check for existing pending payout request
        $hasPending = AffiliatePayout::where('affiliate_id', $affiliate->id)->where('status', 'pending')->exists();
        if ($hasPending) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pending payout request in progress. Please wait until admin processes it.',
            ], 400);
        }

        $payout = AffiliatePayout::create([
            'affiliate_id' => $affiliate->id,
            'amount' => $amount,
            'payout_method' => $method,
            'account_details' => $account,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        // Push notification to Admin
        try {
            $admins = User::whereNotNull('fcm_token')->get();
            foreach ($admins as $admin) {
                FirebasePushService::send(
                    $admin->fcm_token,
                    'New Affiliate Payout Request! 💸',
                    "{$affiliate->name} requested a payout of ৳{$amount} via {$method}.",
                    ['url' => route('admin.affiliates.payouts')]
                );
            }
        } catch (\Throwable $e) {
        }

        return response()->json([
            'success' => true,
            'message' => "Payout request for ৳{$amount} submitted successfully! Admin will process your payment and provide the Transaction ID.",
            'payout' => $payout,
            'new_balance' => floatval($affiliate->balance),
        ]);
    }

    /**
     * Link Generator & Products List
     */
    public function links(Request $request): JsonResponse
    {
        $affiliate = $this->getAuthenticatedAffiliate($request);
        if (! $affiliate) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $baseUrl = config('app.url', 'https://jhurio.com');
        $code = $affiliate->code;

        $products = Product::where('status', 'published')
            ->with('thumbnail')
            ->select('id', 'name', 'slug', 'price', 'sale_price', 'thumbnail_media_id')
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($p) use ($baseUrl, $code) {
                $price = floatval($p->sale_price ?? $p->price);
                $tenPercent = 0.10 * $price;
                $comm = min(300.0, max(150.0, $tenPercent));

                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $price,
                    'thumbnail_url' => $p->thumbnail?->url ?? $p->image_url,
                    'affiliate_link' => "{$baseUrl}/product/{$p->slug}?ref={$code}",
                    'estimated_commission' => $comm,
                ];
            });

        return response()->json([
            'success' => true,
            'code' => $code,
            'general_link' => "{$baseUrl}?ref={$code}",
            'shop_link' => "{$baseUrl}/shop?ref={$code}",
            'products' => $products,
        ]);
    }

    private function getAuthenticatedAffiliate(Request $request): ?Affiliate
    {
        $token = $request->bearerToken() ?? $request->header('X-Affiliate-Token') ?? $request->input('token');
        if (! $token) {
            return null;
        }

        return Affiliate::where('api_token', $token)->first();
    }

    private function formatAffiliateProfile(Affiliate $affiliate): array
    {
        $baseUrl = config('app.url', 'https://jhurio.com');

        return [
            'id' => $affiliate->id,
            'name' => $affiliate->name,
            'phone' => $affiliate->phone,
            'email' => $affiliate->email,
            'code' => $affiliate->code,
            'status' => $affiliate->status,
            'balance' => floatval($affiliate->balance),
            'pending_balance' => floatval($affiliate->pending_balance),
            'lifetime_earnings' => floatval($affiliate->lifetime_earnings),
            'payout_method' => $affiliate->payout_method,
            'payout_account' => $affiliate->payout_account,
            'referral_link' => "{$baseUrl}?ref={$affiliate->code}",
            'approved_at' => $affiliate->approved_at?->toIso8601String(),
            'notes' => $affiliate->notes,
        ];
    }
}

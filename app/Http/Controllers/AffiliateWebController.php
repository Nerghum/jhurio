<?php

namespace App\Http\Controllers;

use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use App\Models\Product;
use App\Models\User;
use App\Services\FirebasePushService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AffiliateWebController extends Controller
{
    /**
     * Show Affiliate Landing / Login / Registration Page
     */
    public function index()
    {
        if (session()->has('affiliate_id')) {
            return redirect()->route('affiliate.dashboard');
        }

        return view('affiliate.index');
    }

    /**
     * Handle Web Registration
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:25|unique:affiliates,phone',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:6|confirmed',
            'payout_method' => 'required|string|in:bkash,nagad,rocket,bank',
            'payout_account' => 'required|string|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $code = Affiliate::generateUniqueCode($validated['name']);

        $affiliate = Affiliate::create([
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'email' => $validated['email'] ?? null,
            'password' => $validated['password'],
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

        return back()->with('application_submitted', true)->with('affiliate_code', $affiliate->code);
    }

    /**
     * Handle Web Login
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $affiliate = Affiliate::where('phone', trim($request->phone))->first();

        if (! $affiliate || ! Hash::check($request->password, $affiliate->password)) {
            return back()->with('error', 'Invalid phone number or password.')->withInput();
        }

        if ($affiliate->status === 'pending') {
            return back()->with('pending_notice', 'Your application is under review by our admin. You will have full access as soon as it is approved.')->withInput();
        }

        if ($affiliate->status === 'rejected') {
            return back()->with('error', 'Your application was rejected. '.($affiliate->rejection_reason ? 'Reason: '.$affiliate->rejection_reason : ''))->withInput();
        }

        if ($affiliate->status === 'suspended') {
            return back()->with('error', 'Your account has been suspended. Please contact support.')->withInput();
        }

        session(['affiliate_id' => $affiliate->id]);

        return redirect()->route('affiliate.dashboard')->with('success', "Welcome back, {$affiliate->name}!");
    }

    /**
     * Affiliate Portal Dashboard
     */
    public function dashboard()
    {
        if (! session()->has('affiliate_id')) {
            return redirect()->route('affiliate.index');
        }

        $affiliate = Affiliate::findOrFail(session('affiliate_id'));

        $referrals = AffiliateReferral::where('affiliate_id', $affiliate->id)
            ->with(['order.items'])
            ->latest()
            ->paginate(15, ['*'], 'referrals_page');

        $payouts = AffiliatePayout::where('affiliate_id', $affiliate->id)
            ->latest()
            ->get();

        $products = Product::where('status', 'published')
            ->with('thumbnail')
            ->latest()
            ->take(24)
            ->get();

        return view('affiliate.dashboard', compact('affiliate', 'referrals', 'payouts', 'products'));
    }

    /**
     * Request Payout
     */
    public function requestPayout(Request $request)
    {
        if (! session()->has('affiliate_id')) {
            return redirect()->route('affiliate.index');
        }

        $affiliate = Affiliate::findOrFail(session('affiliate_id'));
        $balance = floatval($affiliate->balance);
        $minPayout = 100.0;

        $request->validate([
            'amount' => "required|numeric|min:{$minPayout}|max:{$balance}",
            'payout_method' => 'required|string|in:bkash,nagad,rocket,bank',
            'account_details' => 'required|string|max:100',
        ], [
            'amount.max' => "Withdrawal amount cannot exceed your available balance of ৳{$balance}.",
            'amount.min' => "Minimum withdrawal amount is ৳{$minPayout}.",
        ]);

        $hasPending = AffiliatePayout::where('affiliate_id', $affiliate->id)->where('status', 'pending')->exists();
        if ($hasPending) {
            return back()->with('error', 'You already have a pending payout request under review.');
        }

        AffiliatePayout::create([
            'affiliate_id' => $affiliate->id,
            'amount' => floatval($request->amount),
            'payout_method' => strtolower($request->payout_method),
            'account_details' => $request->account_details,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return back()->with('success', 'Your payout request has been submitted! Admin will send payment and attach the Transaction ID.');
    }

    /**
     * Affiliate Logout
     */
    public function logout()
    {
        session()->forget('affiliate_id');

        return redirect()->route('affiliate.index')->with('success', 'You have been logged out.');
    }
}

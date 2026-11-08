<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Models\AffiliatePayout;
use App\Models\AffiliateReferral;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AffiliateController extends Controller
{
    public function index(Request $request)
    {
        $query = Affiliate::withCount(['referrals', 'payouts'])->latest();

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'rejected', 'suspended'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $affiliates = $query->paginate(20)->withQueryString();

        $stats = [
            'total' => Affiliate::count(),
            'pending' => Affiliate::where('status', 'pending')->count(),
            'approved' => Affiliate::where('status', 'approved')->count(),
            'rejected' => Affiliate::where('status', 'rejected')->count(),
            'total_paid' => AffiliatePayout::where('status', 'paid')->sum('amount'),
            'pending_payouts' => AffiliatePayout::where('status', 'pending')->count(),
        ];

        return view('admin.affiliates.index', compact('affiliates', 'stats'));
    }

    public function show(Affiliate $affiliate)
    {
        $affiliate->load([
            'referrals.order',
            'payouts' => fn ($q) => $q->latest(),
        ]);

        return view('admin.affiliates.show', compact('affiliate'));
    }

    public function approve(Affiliate $affiliate)
    {
        $affiliate->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('success', "Affiliate '{$affiliate->name}' ({$affiliate->code}) has been approved successfully.");
    }

    public function reject(Request $request, Affiliate $affiliate)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $affiliate->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        return back()->with('success', "Affiliate application for '{$affiliate->name}' has been rejected.");
    }

    public function referrals(Request $request)
    {
        $query = AffiliateReferral::with(['affiliate', 'order'])->latest();

        if ($request->filled('status') && in_array($request->status, ['pending', 'approved', 'cancelled'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('affiliate_id')) {
            $query->where('affiliate_id', $request->affiliate_id);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->whereHas('affiliate', function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            })->orWhere('order_id', $s);
        }

        $referrals = $query->paginate(25)->withQueryString();

        $stats = [
            'total_referrals' => AffiliateReferral::count(),
            'pending_referrals' => AffiliateReferral::where('status', 'pending')->count(),
            'approved_referrals' => AffiliateReferral::where('status', 'approved')->count(),
            'cancelled_referrals' => AffiliateReferral::where('status', 'cancelled')->count(),
            'total_commission_paid' => AffiliateReferral::where('status', 'approved')->sum('commission_amount'),
            'pending_commission' => AffiliateReferral::where('status', 'pending')->sum('commission_amount'),
        ];

        return view('admin.affiliates.referrals', compact('referrals', 'stats'));
    }

    public function payouts(Request $request)
    {
        $query = AffiliatePayout::with('affiliate')->latest();

        if ($request->filled('status') && in_array($request->status, ['pending', 'paid', 'rejected'])) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('transaction_id', 'like', "%{$s}%")
                    ->orWhere('account_details', 'like', "%{$s}%")
                    ->orWhereHas('affiliate', function ($aq) use ($s) {
                        $aq->where('name', 'like', "%{$s}%")
                            ->orWhere('phone', 'like', "%{$s}%")
                            ->orWhere('code', 'like', "%{$s}%");
                    });
            });
        }

        $payouts = $query->paginate(20)->withQueryString();

        $stats = [
            'total_payouts' => AffiliatePayout::count(),
            'pending_count' => AffiliatePayout::where('status', 'pending')->count(),
            'paid_count' => AffiliatePayout::where('status', 'paid')->count(),
            'rejected_count' => AffiliatePayout::where('status', 'rejected')->count(),
            'pending_amount' => AffiliatePayout::where('status', 'pending')->sum('amount'),
            'total_paid_amount' => AffiliatePayout::where('status', 'paid')->sum('amount'),
        ];

        return view('admin.affiliates.payouts', compact('payouts', 'stats'));
    }

    public function approvePayout(Request $request, AffiliatePayout $payout)
    {
        $request->validate([
            'transaction_id' => 'required|string|max:100',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        if ($payout->status !== 'pending') {
            return back()->with('error', 'This payout request has already been processed.');
        }

        $affiliate = $payout->affiliate;

        if (floatval($affiliate->balance) < floatval($payout->amount)) {
            return back()->with('error', "Affiliate current balance ({$affiliate->balance} Tk) is less than requested payout amount ({$payout->amount} Tk).");
        }

        DB::transaction(function () use ($payout, $affiliate, $request) {
            $affiliate->balance = max(0, floatval($affiliate->balance) - floatval($payout->amount));
            $affiliate->save();

            $payout->update([
                'status' => 'paid',
                'transaction_id' => trim($request->transaction_id),
                'admin_notes' => $request->admin_notes,
                'processed_at' => now(),
            ]);
        });

        return back()->with('success', "Payout of ৳{$payout->amount} approved with Transaction ID: {$request->transaction_id}");
    }

    public function rejectPayout(Request $request, AffiliatePayout $payout)
    {
        $request->validate([
            'admin_notes' => 'required|string|max:500',
        ]);

        if ($payout->status !== 'pending') {
            return back()->with('error', 'This payout request has already been processed.');
        }

        $payout->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Payout request has been rejected.');
    }
}

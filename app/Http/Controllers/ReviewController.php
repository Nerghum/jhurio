<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'media.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $product = Product::findOrFail($productId);

        // Ensure user is logged in
        if (! auth()->check()) {
            return redirect()->back()->with('error', 'You must be logged in to leave a review.');
        }

        $user = auth()->user();

        // Verify if they purchased the product and find an unreviewed order
        $deliveredOrders = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->whereHas('items', function ($query) use ($product) {
                $query->where('product_id', $product->id);
            })
            ->get();

        if ($deliveredOrders->isEmpty()) {
            return redirect()->back()->with('error', 'Only customers who have received (delivered) this product can leave a review.');
        }

        // Find an order that hasn't been reviewed yet
        $unreviewedOrder = null;
        foreach ($deliveredOrders as $order) {
            $hasReviewed = Review::where('user_id', $user->id)
                ->where('product_id', $product->id)
                ->where('order_id', $order->id)
                ->exists();

            if (! $hasReviewed) {
                $unreviewedOrder = $order;
                break;
            }
        }

        if (! $unreviewedOrder) {
            return redirect()->back()->with('error', 'You have already reviewed this product for all your purchases.');
        }

        // Handle media uploads
        $mediaUrls = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                $path = $file->store('reviews', 'public');
                $mediaUrls[] = '/storage/'.$path;
            }
        }

        // Create the review
        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $unreviewedOrder->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'media' => ! empty($mediaUrls) ? $mediaUrls : null,
            'is_verified_purchase' => true,
            'status' => 'approved', // Auto-approve for now
        ]);

        // Auto-generate discount coupon if verified purchase
        $couponMessage = '';
        if (true) {
            $couponCode = 'REVIEW-'.strtoupper(Str::random(6));
            Coupon::create([
                'code' => $couponCode,
                'type' => 'percent',
                'value' => 10, // 10% discount
                'is_active' => true,
                'usage_limit' => 1,
            ]);

            // Store the coupon in session so we can show it via SweetAlert/Popup
            session()->flash('reward_coupon', $couponCode);
            $couponMessage = ' Thank you for your verified review! You have received a 10% discount coupon: <strong>'.$couponCode.'</strong>';
        }

        return redirect()->back()->with('success', 'Your review has been submitted successfully.'.$couponMessage);
    }
}

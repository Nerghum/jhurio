<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function api()
    {
        $apiEnabled = Setting::get('api_enabled', '0') === '1';
        $apiToken = Setting::get('api_token');

        if (! $apiToken) {
            $apiToken = Str::random(32);
            Setting::set('api_token', $apiToken);
        }

        return view('admin.settings.api', compact('apiEnabled', 'apiToken'));
    }

    public function updateApi(Request $request)
    {
        $enabled = $request->has('api_enabled') ? '1' : '0';
        Setting::set('api_enabled', $enabled);

        if ($request->has('generate_token')) {
            Setting::set('api_token', Str::random(32));

            return back()->with('success', 'API settings updated and new token generated successfully.');
        }

        return back()->with('success', 'API settings updated successfully.');
    }

    public function tracking()
    {
        $tracking = [
            'fb_pixels' => Setting::get('fb_pixels', '[]'),
            'gtm_id' => Setting::get('gtm_id'),
            'google_ads_id' => Setting::get('google_ads_id'),
            'ga4_id' => Setting::get('ga4_id'),
            'tiktok_pixel_id' => Setting::get('tiktok_pixel_id'),
        ];

        return view('admin.settings.tracking', compact('tracking'));
    }

    public function updateTracking(Request $request)
    {
        $keys = [
            'gtm_id',
            'google_ads_id',
            'ga4_id',
            'tiktok_pixel_id',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        if ($request->has('fb_pixels')) {
            $fbPixels = $request->input('fb_pixels', []);
            $fbPixels = array_filter($fbPixels, function ($pixel) {
                return ! empty($pixel['pixel_id']);
            });
            Setting::set('fb_pixels', json_encode(array_values($fbPixels)));
        } else {
            Setting::set('fb_pixels', '[]');
        }

        return back()->with('success', 'Tracking settings updated successfully.');
    }

    public function notifications()
    {
        $firebase = [
            'apiKey' => Setting::get('firebase_api_key'),
            'authDomain' => Setting::get('firebase_auth_domain'),
            'projectId' => Setting::get('firebase_project_id'),
            'storageBucket' => Setting::get('firebase_storage_bucket'),
            'messagingSenderId' => Setting::get('firebase_messaging_sender_id'),
            'appId' => Setting::get('firebase_app_id'),
            'vapidKey' => Setting::get('firebase_vapid_key'),
            'serviceAccount' => Setting::get('firebase_service_account'),
        ];

        return view('admin.settings.notifications', compact('firebase'));
    }

    public function updateNotifications(Request $request)
    {
        $keys = [
            'firebase_api_key',
            'firebase_auth_domain',
            'firebase_project_id',
            'firebase_storage_bucket',
            'firebase_messaging_sender_id',
            'firebase_app_id',
            'firebase_vapid_key',
            'firebase_service_account',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        return back()->with('success', 'Notification settings updated successfully.');
    }

    public function updateFcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $user = auth()->user();
        if ($user) {
            $user->fcm_token = $request->token;
            $user->save();
        }

        return response()->json(['success' => true]);
    }

    public function checkout()
    {
        $settings = [
            'order_bump_product_id' => Setting::get('order_bump_product_id'),
            'order_bump_title' => Setting::get('order_bump_title', 'Yes, add this to my order!'),
            'order_bump_description' => Setting::get('order_bump_description', 'Special offer.'),
        ];

        $products = Product::where('status', 'published')->get();

        return view('admin.settings.checkout', compact('settings', 'products'));
    }

    public function updateCheckout(Request $request)
    {
        $keys = [
            'order_bump_product_id',
            'order_bump_title',
            'order_bump_description',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        return back()->with('success', 'Checkout settings updated successfully.');
    }

    public function marketing()
    {
        $settings = [
            'exit_popup_enabled' => Setting::get('exit_popup_enabled', '0'),
            'exit_popup_title' => Setting::get('exit_popup_title', 'Wait! Don\'t leave yet!'),
            'exit_popup_description' => Setting::get('exit_popup_description', 'Here is a 10% discount for you.'),
            'exit_popup_coupon' => Setting::get('exit_popup_coupon', 'SAVE10'),
            'exit_popup_image' => Setting::get('exit_popup_image'),
        ];

        return view('admin.settings.marketing', compact('settings'));
    }

    public function updateMarketing(Request $request)
    {
        $enabled = $request->has('exit_popup_enabled') ? '1' : '0';
        Setting::set('exit_popup_enabled', $enabled);

        $keys = [
            'exit_popup_title',
            'exit_popup_description',
            'exit_popup_coupon',
        ];

        foreach ($keys as $key) {
            Setting::set($key, $request->input($key));
        }

        if ($request->hasFile('exit_popup_image')) {
            $path = $request->file('exit_popup_image')->store('marketing', 'public');
            Setting::set('exit_popup_image', '/storage/'.$path);
        }

        return back()->with('success', 'Marketing settings updated successfully.');
    }
}

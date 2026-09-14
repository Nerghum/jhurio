<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomerAuthController extends Controller
{
    public function showPhoneLogin()
    {
        if (Auth::check()) {
            return redirect()->route('account.orders');
        }
        return view('auth.customer-login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate(['phone' => ['required', 'string', 'min:10']]);

        Session::put('otp_phone',   $request->phone);
        Session::put('otp_code',    '1234'); // Static OTP for testing
        Session::put('otp_sent_at', now()->timestamp);

        return response()->json(['success' => true, 'message' => 'OTP sent. Use 1234 for testing.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required'],
            'otp'   => ['required', 'digits:4'],
        ]);

        $sessionPhone = Session::get('otp_phone');
        $sessionOtp   = Session::get('otp_code');
        $sentAt       = Session::get('otp_sent_at');

        if (!$sessionPhone || $sessionPhone !== $request->phone) {
            return response()->json(['success' => false, 'message' => 'Phone number mismatch.'], 422);
        }

        if (!$sentAt || (now()->timestamp - $sentAt) > 600) {
            return response()->json(['success' => false, 'message' => 'OTP expired. Please retry.'], 422);
        }

        if ($sessionOtp !== $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP.'], 422);
        }

        $user = User::firstOrCreate(
            ['phone' => $request->phone],
            ['name' => 'Customer', 'phone' => $request->phone]
        );

        Auth::login($user, remember: true);
        Session::forget(['otp_phone', 'otp_code', 'otp_sent_at']);
        $request->session()->regenerate();

        return response()->json([
            'success'  => true,
            'redirect' => route('account.orders'),
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}

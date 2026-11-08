@extends('layouts.app')

@section('title', 'Affiliate Partner Program')

@section('content')
<div class="py-12 bg-gradient-to-b from-gray-50 to-white dark:from-darker dark:to-dark min-h-screen">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Hero Header -->
        <div class="text-center max-w-2xl mx-auto mb-12">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-primary/10 text-primary text-xs font-bold tracking-wide uppercase mb-3">
                <i data-lucide="sparkles" class="w-3.5 h-3.5"></i> Jhurio Partner Program
            </span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Earn with the Jhurio Affiliate Program
            </h1>
            <p class="mt-3 text-base text-gray-600 dark:text-gray-400">
                Share your favorite products and earn up to <span class="font-bold text-primary">৳300</span> on every single sale. Direct payouts to your bKash or Nagad wallet.
            </p>
        </div>

        <!-- Commission Model Highlights -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <div class="p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 flex items-center justify-center mb-4">
                    <i data-lucide="coins" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">High Commissions</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Earn <span class="font-bold text-gray-900 dark:text-white">৳150 or 10%</span> of the product price (whichever is higher), up to <span class="font-bold text-gray-900 dark:text-white">৳300 per product</span>.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                    <i data-lucide="shield-check" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Fair & Transparent</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Track every click and order live on your dashboard. Commission is finalized as soon as the order delivery is successful.
                </p>
            </div>

            <div class="p-6 rounded-2xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-purple-50 dark:bg-purple-950/50 text-purple-600 flex items-center justify-center mb-4">
                    <i data-lucide="credit-card" class="w-6 h-6"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Quick Payouts</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
                    Request payout anytime with a minimum of ৳100. Admin pays with complete Transaction ID confirmation.
                </p>
            </div>
        </div>

        @if(session('application_submitted'))
        <!-- Success State for New Applicants -->
        <div class="max-w-md mx-auto mb-10 p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-center shadow-lg">
            <div class="w-14 h-14 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center mx-auto mb-4">
                <i data-lucide="check" class="w-7 h-7"></i>
            </div>
            <h3 class="text-xl font-bold text-emerald-900 dark:text-emerald-200">Application Submitted!</h3>
            <p class="text-sm text-emerald-700 dark:text-emerald-300 mt-2">
                Thank you for applying. Your referral code is <span class="font-mono font-bold">{{ session('affiliate_code') }}</span>.
            </p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-2">
                Our admin team has received your application. Once approved, you will be able to log in to your portal and start earning.
            </p>
        </div>
        @endif

        @if(session('pending_notice'))
        <div class="max-w-md mx-auto mb-8 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 dark:bg-amber-950/50 dark:border-amber-800 dark:text-amber-300 text-sm flex items-center gap-3">
            <i data-lucide="clock" class="w-5 h-5 text-amber-600 flex-shrink-0"></i>
            <div>
                <p class="font-bold">Application Pending Review</p>
                <p class="text-xs mt-0.5">{{ session('pending_notice') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-md mx-auto mb-8 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

        <!-- Form Card Container with Tabs -->
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 rounded-3xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-xl" 
             x-data="{ mode: '{{ $errors->any() && old('password_confirmation') ? 'register' : 'register' }}' }">
            
            <!-- Tab Switcher -->
            <div class="flex p-1 bg-gray-100 dark:bg-gray-700 rounded-2xl mb-6">
                <button type="button" 
                        @click="mode = 'register'" 
                        :class="mode === 'register' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'" 
                        class="flex-1 py-2.5 text-xs sm:text-sm rounded-xl transition text-center">
                    Apply Now
                </button>
                <button type="button" 
                        @click="mode = 'login'" 
                        :class="mode === 'login' ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 font-medium'" 
                        class="flex-1 py-2.5 text-xs sm:text-sm rounded-xl transition text-center">
                    Partner Login
                </button>
            </div>

            <!-- Registration Form -->
            <form x-show="mode === 'register'" method="POST" action="{{ route('affiliate.register') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ashik Rahman" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('name') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Phone Number (Account identifier)</label>
                    <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01XXXXXXXXX" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm font-mono focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('phone') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Email Address (Optional)</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@domain.com" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                    @error('email') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Payout Method</label>
                        <select name="payout_method" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary">
                            <option value="bkash">bKash</option>
                            <option value="nagad">Nagad</option>
                            <option value="rocket">Rocket</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Wallet Number</label>
                        <input type="text" name="payout_account" value="{{ old('payout_account') }}" required placeholder="01XXXXXXXXX" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm font-mono focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Minimum 6 characters" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary">
                    @error('password') <p class="text-xs text-rose-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation" required placeholder="Repeat password" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">How will you promote? (Optional)</label>
                    <textarea name="notes" rows="2" placeholder="e.g. Facebook page, YouTube channel, TikTok, or WhatsApp groups" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="w-full py-3.5 bg-primary hover:bg-secondary text-white font-bold text-sm rounded-xl shadow-lg transition-all duration-200 mt-2 flex items-center justify-center gap-2">
                    <i data-lucide="send" class="w-4 h-4"></i>
                    <span>Submit Application</span>
                </button>
            </form>

            <!-- Login Form -->
            <form x-show="mode === 'login'" method="POST" action="{{ route('affiliate.login') }}" class="space-y-4" style="display: none;">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Registered Phone</label>
                    <input type="tel" name="phone" required placeholder="01XXXXXXXXX" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm font-mono focus:ring-2 focus:ring-primary focus:border-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Enter your password" class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                </div>

                <button type="submit" class="w-full py-3.5 bg-primary hover:bg-secondary text-white font-bold text-sm rounded-xl shadow-lg transition-all duration-200 mt-2 flex items-center justify-center gap-2">
                    <i data-lucide="log-in" class="w-4 h-4"></i>
                    <span>Log In to Partner Dashboard</span>
                </button>
            </form>

        </div>
    </div>
</div>
@endsection

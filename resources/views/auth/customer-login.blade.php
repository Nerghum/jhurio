<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – Track Your Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: { extend: { fontFamily: { sans: ['Outfit','sans-serif'] }, colors: { primary:'#0b3506', secondary:'#6bc112' } } }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-gray-50 via-green-50/30 to-gray-100 min-h-screen flex items-center justify-center p-4 font-sans">

<div class="w-full max-w-sm" x-data="{
    step: 'phone', phone: '', loading: false, error: '', otpDigits: ['','','',''],
    get fullOtp() { return this.otpDigits.join(''); },
    async sendOtp() {
        if (this.phone.length < 10) { this.error = 'Enter a valid phone number'; return; }
        this.loading = true; this.error = '';
        const res = await fetch('{{ route('account.send_otp') }}', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({ phone: this.phone })
        });
        const data = await res.json();
        if (data.success) { this.step = 'otp'; this.$nextTick(() => this.$refs.otp0?.focus()); }
        else this.error = data.message;
        this.loading = false;
    },
    async verifyOtp() {
        if (this.fullOtp.length !== 4) { this.error = 'Enter 4-digit OTP'; return; }
        this.loading = true; this.error = '';
        const res = await fetch('{{ route('account.verify_otp') }}', {
            method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({ phone: this.phone, otp: this.fullOtp })
        });
        const data = await res.json();
        if (data.success) window.location.href = data.redirect;
        else { this.error = data.message; this.loading = false; }
    },
    handleOtpInput(i, e) {
        const v = e.target.value.replace(/\D/,'');
        this.otpDigits[i] = v.slice(-1);
        if (v && i < 3) this.$nextTick(() => this.$refs['otp'+(i+1)]?.focus());
    },
    handleOtpKey(i, e) {
        if (e.key==='Backspace' && !this.otpDigits[i] && i>0) this.$nextTick(() => this.$refs['otp'+(i-1)]?.focus());
    }
}">
    <div class="bg-white rounded-3xl shadow-2xl shadow-black/10 overflow-hidden">
        <!-- Header -->
        <div class="bg-primary px-8 pt-10 pb-14 text-center relative overflow-hidden">
            <div class="absolute inset-0 opacity-10" style="background:radial-gradient(circle,white 1px,transparent 1px) 0 0/30px 30px"></div>
            <a href="/" class="inline-block mb-4 relative z-10">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Logo" class="h-12 w-auto mx-auto brightness-0 invert">
            </a>
            <h1 class="text-2xl font-bold text-white relative z-10">Track Your Orders</h1>
            <p class="text-green-200 text-sm mt-1 relative z-10">Login with your phone number</p>
        </div>

        <div class="-mt-6 bg-white rounded-t-3xl px-8 pt-8 pb-8">
            <div x-show="error" x-transition class="mb-4 p-3 bg-red-50 border border-red-100 rounded-xl text-red-600 text-sm" x-text="error"></div>

            <!-- Phone Step -->
            <div x-show="step === 'phone'" x-transition>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Mobile Number</label>
                <div class="flex rounded-xl border border-gray-200 focus-within:border-primary focus-within:ring-2 focus-within:ring-primary/20 overflow-hidden transition-all">
                    <div class="flex items-center px-4 bg-gray-50 border-r border-gray-200 text-gray-600 text-sm font-medium">🇧🇩 +880</div>
                    <input type="tel" x-model="phone" @keydown.enter="sendOtp" placeholder="01XXXXXXXXX"
                           class="flex-1 px-4 py-3.5 text-gray-900 border-0 focus:ring-0 text-base font-medium bg-white" maxlength="11">
                </div>
                <p class="mt-2 text-xs text-gray-400">Use OTP <strong>1234</strong> for testing</p>
                <button @click="sendOtp" :disabled="loading"
                        class="mt-5 w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-xl text-base transition-all shadow-lg shadow-primary/30 disabled:opacity-60 flex items-center justify-center gap-2">
                    <span x-show="!loading">Get OTP</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Sending...
                    </span>
                </button>
            </div>

            <!-- OTP Step -->
            <div x-show="step === 'otp'" x-transition>
                <div class="text-center mb-6">
                    <div class="w-14 h-14 bg-secondary/10 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7 text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </div>
                    <p class="text-sm text-gray-500">OTP sent to <span class="font-bold text-gray-900" x-text="phone"></span></p>
                    <p class="text-xs text-gray-400 mt-1">Use <strong>1234</strong> for testing</p>
                </div>
                <div class="flex gap-3 justify-center mb-5">
                    @for($i = 0; $i < 4; $i++)
                    <input type="text" inputmode="numeric" x-ref="otp{{ $i }}" x-model="otpDigits[{{ $i }}]"
                           @input="handleOtpInput({{ $i }}, $event)" @keydown="handleOtpKey({{ $i }}, $event)"
                           maxlength="1"
                           class="w-14 h-14 text-center text-2xl font-bold border-2 rounded-xl focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                           :class="otpDigits[{{ $i }}] ? 'border-primary bg-primary/5' : 'border-gray-200'">
                    @endfor
                </div>
                <button @click="verifyOtp" :disabled="loading || fullOtp.length !== 4"
                        class="w-full bg-secondary hover:bg-secondary/90 text-white font-bold py-4 rounded-xl text-base transition-all shadow-lg shadow-secondary/30 disabled:opacity-60 flex items-center justify-center gap-2">
                    <span x-show="!loading">Verify & Login</span>
                    <span x-show="loading" class="flex items-center gap-2">
                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Verifying...
                    </span>
                </button>
                <button @click="step='phone'; otpDigits=['','','','']; error=''" class="mt-3 w-full text-sm text-gray-500 hover:text-primary transition-colors py-2">← Change phone number</button>
            </div>
        </div>
    </div>
    <p class="text-center text-xs text-gray-400 mt-4"><a href="/" class="hover:text-primary transition-colors">← Back to Store</a></p>
</div>
</body>
</html>

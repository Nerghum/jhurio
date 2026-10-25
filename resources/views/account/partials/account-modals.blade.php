{{-- ══════════════════════════════════════════════════════════════
     ACCOUNT MODALS  (cancel order + add address)
     ══════════════════════════════════════════════════════════════ --}}

{{-- Cancel Order Modal --}}
<div x-show="cancelModalOpen"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display:none;"
     @keydown.escape.window="closeCancelModal()">

    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="closeCancelModal()"></div>

    <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl p-6 border border-gray-100 dark:border-gray-800 z-10"
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2">

        <div class="flex items-start gap-4 mb-5">
            <div class="w-12 h-12 rounded-2xl bg-red-50 dark:bg-red-950/50 text-red-500 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 dark:text-white text-base">Cancel Order #<span x-text="cancellingOrder?.id"></span>?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">This order is pending. Cancellation is immediate and cannot be undone.</p>
            </div>
        </div>

        <div class="mb-5">
            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Reason for cancellation</label>
            <select x-model="cancelReason"
                    class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
                <option>Changed my mind</option>
                <option>Ordered by mistake</option>
                <option>Want to change delivery address or phone</option>
                <option>Want to change items or quantities</option>
                <option>Delivery time is too long</option>
                <option>Other</option>
            </select>
        </div>

        <div class="flex items-center gap-3">
            <button type="button" @click="closeCancelModal()"
                    class="flex-1 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                Keep Order
            </button>
            <form :action="'/account/orders/' + cancellingOrder?.id + '/cancel'" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="reason" :value="cancelReason">
                <button type="submit"
                        class="w-full py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold transition shadow-sm flex items-center justify-center gap-1.5">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                    <span>Yes, Cancel</span>
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Add Address Modal --}}
<div x-show="addressModalOpen"
     class="fixed inset-0 z-[100] flex items-center justify-center p-4"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     style="display:none;"
     @keydown.escape.window="addressModalOpen = false">

    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="addressModalOpen = false"></div>

    <div class="relative w-full max-w-md bg-white dark:bg-gray-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-800 overflow-y-auto max-h-[90vh] z-10"
         x-transition:enter="transition ease-out duration-200 transform"
         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150 transform"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-95 translate-y-2">

        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between sticky top-0 bg-white dark:bg-gray-900 z-10">
            <h3 class="font-bold text-gray-900 dark:text-white text-sm">Add New Address</h3>
            <button type="button" @click="addressModalOpen = false"
                    class="w-8 h-8 rounded-xl bg-gray-100 dark:bg-gray-800 text-gray-500 flex items-center justify-center hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form action="{{ route('account.addresses.store') }}" method="POST" class="p-5 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Address Label</label>
                <select name="label" class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none" required>
                    <option value="Home">Home</option>
                    <option value="Office">Office</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Full Name</label>
                <input type="text" name="name" value="{{ auth()->user()->name }}" required
                       class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                <input type="text" name="phone" value="{{ auth()->user()->phone }}" required
                       class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">Street Address</label>
                <textarea name="address" rows="2" placeholder="House, Road, Area…" required
                          class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none resize-none"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">District / City</label>
                <input type="text" name="district" placeholder="e.g. Dhaka" required
                       class="w-full px-3 py-2.5 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary/30 focus:border-primary outline-none">
            </div>
            <div class="flex items-center gap-2.5">
                <input type="checkbox" name="is_default" id="is_default_new" value="1"
                       class="w-4 h-4 rounded border-gray-300 text-primary focus:ring-primary">
                <label for="is_default_new" class="text-xs text-gray-700 dark:text-gray-300 font-medium">Set as default shipping address</label>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="addressModalOpen = false"
                        class="flex-1 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-3 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition shadow-sm">
                    Save Address
                </button>
            </div>
        </form>
    </div>
</div>

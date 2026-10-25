@php
$statusConfig = [
    'pending'    => ['label' => 'Pending Confirmation', 'class' => 'bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/80 dark:border-amber-800', 'icon' => 'clock', 'dot' => 'bg-amber-500'],
    'processing' => ['label' => 'Processing & Packing', 'class' => 'bg-blue-50 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200/80 dark:border-blue-800',   'icon' => 'package', 'dot' => 'bg-blue-500'],
    'shipped'    => ['label' => 'Dispatched / In Transit', 'class' => 'bg-purple-50 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/80 dark:border-purple-800','icon' => 'truck', 'dot' => 'bg-purple-500'],
    'delivered'  => ['label' => 'Delivered',  'class' => 'bg-emerald-50 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/80 dark:border-emerald-800','icon' => 'map-pin', 'dot' => 'bg-emerald-500'],
    'completed'  => ['label' => 'Completed',  'class' => 'bg-green-50 text-green-800 dark:bg-green-950/60 dark:text-green-300 border-green-200/80 dark:border-green-800', 'icon' => 'check-circle-2', 'dot' => 'bg-green-500'],
    'cancelled'  => ['label' => 'Cancelled',  'class' => 'bg-red-50 text-red-800 dark:bg-red-950/60 dark:text-red-300 border-red-200/80 dark:border-red-800',     'icon' => 'x-circle', 'dot' => 'bg-red-500'],
];
$s = $statusConfig[$order->status] ?? $statusConfig['pending'];

$stages = [
    'pending'    => ['label' => 'Placed',    'icon' => 'shopping-cart'],
    'processing' => ['label' => 'Confirmed', 'icon' => 'package'],
    'shipped'    => ['label' => 'Shipped',   'icon' => 'truck'],
    'delivered'  => ['label' => 'Delivered', 'icon' => 'check-circle'],
];
$stageKeys = array_keys($stages);
$currentStageIndex = array_search($order->status, $stageKeys);
if ($order->status === 'completed') {
    $currentStageIndex = 3;
}
@endphp

<div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm mb-4 sm:mb-5 overflow-hidden transition-all hover:shadow-md">
    <!-- Card Top Header -->
    <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-gray-50/50 dark:bg-gray-800/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary dark:bg-primary/20 dark:text-secondary flex items-center justify-center flex-shrink-0 shadow-xs">
                <i data-lucide="package" class="w-5 h-5"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Order</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white text-sm sm:text-base">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="text-[11px] text-gray-400 dark:text-gray-500 flex items-center gap-1.5 mt-0.5">
                    <i data-lucide="calendar" class="w-3 h-3"></i>
                    <span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>

        <!-- Status Badge -->
        <div class="flex items-center gap-2 self-start sm:self-center">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $s['class'] }} shadow-2xs">
                @if(in_array($order->status, ['pending', 'processing', 'shipped']))
                    <span class="w-2 h-2 rounded-full {{ $s['dot'] }} animate-ping"></span>
                @else
                    <span class="w-2 h-2 rounded-full {{ $s['dot'] }}"></span>
                @endif
                <i data-lucide="{{ $s['icon'] }}" class="w-3.5 h-3.5"></i>
                <span>{{ $s['label'] }}</span>
            </span>
        </div>
    </div>

    <!-- Visual Order Tracker (for active / non-cancelled orders) -->
    @if($order->status !== 'cancelled')
    <div class="px-5 py-4 bg-gray-50/30 dark:bg-gray-900/20 border-b border-gray-100 dark:border-gray-700/80">
        <div class="max-w-md mx-auto">
            <div class="relative flex items-center justify-between">
                <!-- Track Line -->
                <div class="absolute left-4 right-4 top-1/2 -translate-y-1/2 h-1 bg-gray-200 dark:bg-gray-700 rounded-full z-0"></div>
                @if($currentStageIndex !== false)
                <div class="absolute left-4 top-1/2 -translate-y-1/2 h-1 bg-primary rounded-full transition-all duration-700 z-0"
                     style="width: calc({{ ($currentStageIndex / 3) * 100 }}% - 8px);"></div>
                @endif

                <!-- Milestones -->
                @foreach($stages as $key => $st)
                @php
                    $isPassed = ($currentStageIndex !== false && array_search($key, $stageKeys) <= $currentStageIndex);
                    $isCurrent = ($order->status === $key || ($order->status === 'completed' && $key === 'delivered'));
                @endphp
                <div class="relative z-10 flex flex-col items-center">
                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center transition-all text-xs font-bold shadow-xs
                        {{ $isPassed ? 'bg-primary text-white ring-4 ring-primary/10' : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500' }}
                        {{ $isCurrent ? 'ring-4 ring-secondary/40 scale-110' : '' }}">
                        @if($isPassed)
                            <i data-lucide="check" class="w-3.5 h-3.5 stroke-[3]"></i>
                        @else
                            <i data-lucide="{{ $st['icon'] }}" class="w-3.5 h-3.5"></i>
                        @endif
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-semibold mt-1.5 {{ $isPassed ? 'text-primary font-bold dark:text-secondary' : 'text-gray-400 dark:text-gray-500' }}">
                        {{ $st['label'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <!-- Items List Preview -->
    <div class="p-4 sm:p-5 space-y-3">
        @foreach($order->items as $item)
        <div class="flex items-center gap-3 sm:gap-4 p-2 sm:p-2.5 rounded-2xl bg-gray-50/60 dark:bg-gray-700/40 border border-gray-100 dark:border-gray-700">
            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-white dark:bg-gray-800 overflow-hidden flex-shrink-0 border border-gray-200/80 dark:border-gray-600">
                @if($item->product && $item->product->thumbnail)
                    <img src="{{ $item->product->thumbnail->url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <i data-lucide="package" class="w-6 h-6"></i>
                    </div>
                @endif
            </div>

            <div class="flex-1 min-w-0">
                <h4 class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm truncate">
                    @if($item->product)
                        <a href="{{ route('product.show', $item->product->slug) }}" class="hover:text-primary transition-colors">
                            {{ $item->product_name }}
                        </a>
                    @else
                        {{ $item->product_name }}
                    @endif
                </h4>

                @if($item->variation_name)
                    <span class="inline-block px-2 py-0.5 mt-1 rounded-md text-[10px] font-medium bg-gray-200/70 dark:bg-gray-600 text-gray-700 dark:text-gray-200">
                        {{ $item->variation_name }}
                    </span>
                @endif

                <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                    <span>Qty: <strong class="text-gray-800 dark:text-gray-200">{{ $item->quantity }}</strong></span>
                    <span>•</span>
                    <span>৳{{ number_format($item->price, 0) }} each</span>
                </div>
            </div>

            <div class="text-right flex-shrink-0">
                <span class="font-extrabold text-sm sm:text-base text-gray-900 dark:text-white">
                    ৳{{ number_format($item->price * $item->quantity, 0) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Order Footer with Delivery Info and Actions -->
    <div class="px-4 py-3.5 sm:px-5 sm:py-4 bg-gray-50/80 dark:bg-gray-800/80 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
        <div>
            <span class="text-xs text-gray-500 dark:text-gray-400">Total Paid (Cash on Delivery):</span>
            <div class="text-lg sm:text-xl font-black text-primary dark:text-secondary">
                ৳{{ number_format($order->total_amount, 0) }}
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap justify-end">
            <!-- CANCEL ORDER BUTTON (ONLY IF PENDING) -->
            @if($order->status === 'pending')
            <button type="button" 
                    @click="openCancelModal({{ json_encode(['id' => $order->id, 'status' => $order->status]) }})"
                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 hover:bg-red-600 hover:text-white dark:hover:bg-red-600 text-xs font-bold transition shadow-2xs cursor-pointer">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                <span>Cancel Order</span>
            </button>
            @endif

            <!-- REVIEW BUTTON (IF DELIVERED) -->
            @if(in_array($order->status, ['delivered', 'completed']) && $order->items->first()?->product)
            <a href="{{ route('product.show', $order->items->first()->product->slug) }}#customer-reviews"
               class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 text-xs font-bold transition shadow-2xs">
                <i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-500"></i>
                <span>Review Item</span>
            </a>
            @endif

            <!-- ORDER DETAILS / INVOICE BUTTON -->
            <a href="{{ route('account.order_detail', $order) }}"
               class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-gray-200 dark:bg-gray-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-gray-800 dark:text-gray-200 text-xs font-bold transition shadow-2xs">
                <span>View Details</span>
                <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
            </a>
        </div>
    </div>
</div>

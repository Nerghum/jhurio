@extends('layouts.admin')

@section('title', 'Order #' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . ' - Invoice & Details')

@section('header')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.index') }}" 
           class="p-2.5 text-gray-500 hover:text-gray-900 dark:hover:text-white bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl shadow-xs transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight">
                    Invoice #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                </h2>
                @php $color = $statuses[$order->status]['color'] ?? 'gray'; @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full text-xs font-bold bg-{{ $color }}-50 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400 border border-{{ $color }}-200 dark:border-{{ $color }}-800">
                    <span class="w-2 h-2 rounded-full bg-{{ $color }}-500"></span>
                    {{ Str::headline($order->status) }}
                </span>
            </div>
            <p class="text-xs text-gray-500 font-medium mt-0.5 flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-3.5 h-3.5"></i> {{ $order->created_at->format('F d, Y \a\t h:i A') }}
            </p>
        </div>
    </div>
    
    <div class="flex items-center gap-2 flex-wrap">
        {{-- Print button — opens invoice HTML page which auto-triggers window.print() --}}
        <a href="{{ route('admin.orders.invoice.print', $order) }}" 
           target="_blank" 
           class="inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs">
            <i data-lucide="printer" class="w-4 h-4 text-gray-500"></i>
            <span>Print Invoice</span>
        </a>

        {{-- PDF Download button —  same template as print --}}
        <a href="{{ route('admin.orders.invoice', $order) }}" 
           class="inline-flex items-center gap-1.5 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700 px-3.5 py-2 rounded-xl text-xs font-bold transition shadow-xs">
            <i data-lucide="download" class="w-4 h-4 text-gray-500"></i>
            <span>Download PDF</span>
        </a>
    </div>
</div>
@endsection

@section('content')
{{-- @media print removed: Print now opens invoice.blade.php HTML directly (identical to PDF) --}}

<div x-data="orderInvoiceEditor({
    orderId: {{ $order->id }},
    name: {{ json_encode($order->name) }},
    phone: {{ json_encode($order->phone) }},
    shipping_address: {{ json_encode($order->shipping_address) }},
    delivery_method_id: '{{ $order->delivery_method_id }}',
    delivery_cost: {{ floatval($order->delivery_cost) }},
    coupon_code: {{ json_encode($order->coupon_code ?? '') }},
    discount_amount: {{ floatval($order->discount_amount) }},
    notes: {{ json_encode($order->notes ?? '') }},
    items: [
        @foreach($order->items as $item)
        {
            id: {{ $item->id }},
            product_id: {{ $item->product_id ?? 'null' }},
            variation_id: {{ $item->variation_id ?? 'null' }},
            product_name: {{ json_encode($item->product_name) }},
            variation_name: {{ json_encode($item->variation_name) }},
            price: {{ floatval($item->price) }},
            quantity: {{ intval($item->quantity) }},
            thumbnail_url: {{ json_encode($item->product && $item->product->thumbnail ? $item->product->thumbnail->url : null) }}
        },
        @endforeach
    ],
    availableProducts: [
        @foreach($products as $prod)
        {
            id: {{ $prod->id }},
            name: {{ json_encode($prod->name) }},
            price: {{ floatval($prod->price) }},
            thumbnail: {{ json_encode($prod->thumbnail ? $prod->thumbnail->url : null) }},
            variations: [
                @foreach($prod->variations as $var)
                {
                    id: {{ $var->id }},
                    name: {{ json_encode($var->name) }},
                    price: {{ $var->price ? floatval($var->price) : 'null' }}
                },
                @endforeach
            ]
        },
        @endforeach
    ],
    deliveryMethods: [
        @foreach($deliveryMethods as $dm)
        {
            id: {{ $dm->id }},
            name: {{ json_encode($dm->name) }},
            cost: {{ floatval($dm->cost) }}
        },
        @endforeach
    ],
    availableCoupons: [
        @foreach($coupons as $c)
        {
            code: {{ json_encode($c->code) }},
            type: {{ json_encode($c->type) }},
            value: {{ floatval($c->value) }}
        },
        @endforeach
    ]
})" class="max-w-5xl mx-auto space-y-6">

    {{-- Top Flash Alerts --}}
    @if(session('success'))
    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center justify-between shadow-xs print:hidden">
        <div class="flex items-center gap-2.5">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    {{-- Top Action Bar / Mode Controller --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-4 border border-gray-100 dark:border-gray-700 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
        <div class="flex items-center gap-3">
            <button type="button" 
                    @click="isEditing = !isEditing"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold transition shadow-xs cursor-pointer"
                    :class="isEditing ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-300' : 'bg-primary text-white hover:bg-primary/90'">
                <i data-lucide="edit-3" class="w-4 h-4"></i>
                <span x-text="isEditing ? 'Exit Edit Mode' : 'Edit Order Invoice'"></span>
            </button>

            <span class="text-xs text-gray-500 dark:text-gray-400 hidden sm:inline" x-show="isEditing">
                You can modify products, quantities, prices, shipping, and coupon below.
            </span>
        </div>


    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         MAIN 2-COLUMN LAYOUT: INVOICE (LEFT) & VISITOR TRACKING (RIGHT)
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">
        {{-- Left Column: Main Invoice & Audit Log (8 cols on XL) --}}
        <div class="xl:col-span-8 space-y-6">
            <form id="order-invoice-form" 
                  action="{{ route('admin.orders.update', $order) }}" 
                  method="POST"
                  @submit="isSaving = true">
        @csrf
        @method('PUT')

        <div id="printable-invoice" 
             class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/90 dark:border-gray-700 shadow-xl overflow-hidden p-6 sm:p-10 relative">

            {{-- Edit Mode Floating Badge --}}
            <div x-show="isEditing" class="absolute top-4 right-6 bg-amber-500 text-white text-[10px] uppercase tracking-wider font-black px-2.5 py-1 rounded-full shadow-sm print:hidden">
                ✏️ Edit Mode Active
            </div>

            {{-- ── INVOICE HEADER ────────────────────────────────────────── --}}
            <div class="flex flex-col md:flex-row md:items-start justify-between gap-6 pb-8 border-b border-gray-200 dark:border-gray-700">
                {{-- Store Identity --}}
                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Jhurio" class="h-9 w-auto object-contain">
                        <div>
                            <h1 class="text-xl font-black text-gray-900 dark:text-white leading-tight">Jhurio Store</h1>
                            <p class="text-xs text-primary dark:text-secondary font-semibold">Premium Online Shopping</p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5 pt-1">
                        <p>Dhaka, Bangladesh</p>
                        <p>Phone: +880 1924-301474 | Email: support@jhurio.com</p>
                        <p>Web: www.jhurio.com</p>
                    </div>
                </div>

                {{-- Invoice Meta --}}
                <div class="text-left md:text-right space-y-1.5">
                    <div class="inline-block px-3 py-1 rounded-xl bg-gray-100 dark:bg-gray-700/80 text-gray-800 dark:text-gray-200 font-mono text-xs font-black uppercase tracking-wider mb-1">
                        INVOICE
                    </div>
                    <h3 class="text-lg font-black text-gray-900 dark:text-white font-mono">
                        #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                    </h3>
                    <div class="text-xs text-gray-500 dark:text-gray-400 space-y-0.5">
                        <p><strong class="text-gray-700 dark:text-gray-300">Invoice Date:</strong> {{ $order->created_at->format('d M, Y') }}</p>
                        <p><strong class="text-gray-700 dark:text-gray-300">Payment:</strong> Cash on Delivery (COD)</p>
                        <p><strong class="text-gray-700 dark:text-gray-300">Status:</strong> <span class="capitalize font-bold text-gray-900 dark:text-white">{{ $order->status }}</span></p>
                    </div>
                </div>
            </div>

            {{-- ── BILLED TO & DELIVERY DETAILS ─────────────────────────── --}}
            <div class="py-6 border-b border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Customer Info --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Billed To & Contact
                    </h4>
                    
                    {{-- VIEW MODE --}}
                    <div x-show="!isEditing" class="space-y-1.5 text-sm">
                        <div class="flex items-center gap-2">
                            <i data-lucide="user" class="w-4 h-4 text-gray-400"></i>
                            <span class="font-bold text-gray-900 dark:text-white" x-text="name"></span>
                            @if($order->user)
                                <span class="text-[10px] font-bold px-1.5 py-0.2 rounded-md bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">Member</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 text-xs text-gray-600 dark:text-gray-300">
                            <i data-lucide="phone" class="w-4 h-4 text-gray-400"></i>
                            <a :href="'tel:' + phone" class="font-mono text-primary font-semibold hover:underline" x-text="phone"></a>
                        </div>
                    </div>

                    {{-- EDIT MODE --}}
                    <div x-show="isEditing" class="space-y-3 print:hidden">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Customer Name</label>
                            <input type="text" 
                                   name="name" 
                                   x-model="name" 
                                   required 
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Phone Number</label>
                            <input type="text" 
                                   name="phone" 
                                   x-model="phone" 
                                   required 
                                   class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                        </div>
                    </div>
                </div>

                {{-- Delivery & Shipping Address --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Shipping Address & Delivery Method
                    </h4>

                    {{-- VIEW MODE --}}
                    <div x-show="!isEditing" class="space-y-2 text-sm">
                        <div class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 shrink-0 mt-0.5"></i>
                            <p class="text-gray-800 dark:text-gray-200 leading-snug" x-text="shipping_address"></p>
                        </div>
                        <div class="flex items-center gap-2 pt-1 text-xs">
                            <i data-lucide="truck" class="w-4 h-4 text-gray-400"></i>
                            <span class="font-semibold text-gray-700 dark:text-gray-300">
                                Method: 
                                <span class="text-primary dark:text-secondary font-bold" 
                                      x-text="deliveryMethods.find(m => m.id == delivery_method_id)?.name || 'Standard Delivery'"></span>
                                (৳<span x-text="delivery_cost"></span>)
                            </span>
                        </div>
                    </div>

                    {{-- EDIT MODE --}}
                    <div x-show="isEditing" class="space-y-3 print:hidden">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Shipping Address</label>
                            <textarea name="shipping_address" 
                                      x-model="shipping_address" 
                                      rows="2" 
                                      required 
                                      class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Delivery Method</label>
                                <select name="delivery_method_id" 
                                        x-model="delivery_method_id" 
                                        @change="onDeliveryMethodChange()"
                                        class="w-full text-xs font-semibold px-2.5 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Select Method</option>
                                    <template x-for="dm in deliveryMethods" :key="dm.id">
                                        <option :value="dm.id" :selected="dm.id == delivery_method_id" x-text="dm.name + ' (৳' + dm.cost + ')'"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300 mb-1">Delivery Cost (৳)</label>
                                <input type="number" 
                                       step="1" 
                                       min="0" 
                                       name="delivery_cost" 
                                       x-model.number="delivery_cost" 
                                       class="w-full text-xs font-semibold px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── INVOICE ITEMS TABLE ───────────────────────────────────── --}}
            <div class="py-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Itemized Products & Services
                    </h4>

                    {{-- Add Item button (only in edit mode) --}}
                    <div x-show="isEditing" class="print:hidden">
                        <button type="button" 
                                @click="addProductOpen = true"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition shadow-xs cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>+ Add Product to Invoice</span>
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                <th class="py-3 px-2 w-10 text-center">#</th>
                                <th class="py-3 px-3">Item Description</th>
                                <th class="py-3 px-3 text-right w-28">Unit Price</th>
                                <th class="py-3 px-3 text-center w-28">Qty</th>
                                <th class="py-3 px-3 text-right w-32">Total</th>
                                <th class="py-3 px-2 w-12 text-center" x-show="isEditing">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                            <template x-for="(item, index) in items" :key="index">
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors">
                                    {{-- Row Index --}}
                                    <td class="py-3 px-2 text-center text-xs text-gray-400 font-mono" x-text="index + 1"></td>

                                    {{-- Product info --}}
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-3">
                                            <template x-if="item.thumbnail_url">
                                                <img :src="item.thumbnail_url" 
                                                     class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                                            </template>
                                            <template x-if="!item.thumbnail_url">
                                                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 shrink-0">
                                                    <i data-lucide="package" class="w-5 h-5"></i>
                                                </div>
                                            </template>
                                            <div class="min-w-0 flex-1">
                                                <template x-if="!isEditing">
                                                    <div>
                                                        <p class="font-bold text-gray-900 dark:text-white text-xs sm:text-sm truncate" x-text="item.product_name"></p>
                                                        <template x-if="item.variation_name">
                                                            <span class="inline-block px-1.5 py-0.5 mt-0.5 rounded text-[10px] font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300" x-text="item.variation_name"></span>
                                                        </template>
                                                    </div>
                                                </template>
                                                <template x-if="isEditing">
                                                    <div class="space-y-1">
                                                        <input type="text" 
                                                               :name="'items[' + index + '][product_name]'" 
                                                               x-model="item.product_name" 
                                                               required
                                                               class="w-full text-xs font-semibold px-2 py-1 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                                                        <input type="text" 
                                                               :name="'items[' + index + '][variation_name]'" 
                                                               x-model="item.variation_name" 
                                                               placeholder="Variation (e.g. Size L / Red)"
                                                               class="w-full text-[11px] px-2 py-0.5 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300">
                                                    </div>
                                                </template>

                                                {{-- Hidden fields for form submission --}}
                                                <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">
                                                <input type="hidden" :name="'items[' + index + '][product_id]'" :value="item.product_id">
                                                <input type="hidden" :name="'items[' + index + '][variation_id]'" :value="item.variation_id">
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Unit Price --}}
                                    <td class="py-3 px-3 text-right">
                                        <template x-if="!isEditing">
                                            <span class="font-semibold text-gray-700 dark:text-gray-300 font-mono text-xs sm:text-sm">
                                                ৳<span x-text="Number(item.price).toLocaleString()"></span>
                                            </span>
                                        </template>
                                        <template x-if="isEditing">
                                            <input type="number" 
                                                   step="1" 
                                                   min="0"
                                                   :name="'items[' + index + '][price]'" 
                                                   x-model.number="item.price" 
                                                   required
                                                   class="w-24 text-right text-xs font-mono font-bold px-2 py-1 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                                        </template>
                                    </td>

                                    {{-- Quantity --}}
                                    <td class="py-3 px-3 text-center">
                                        <template x-if="!isEditing">
                                            <span class="font-bold text-gray-900 dark:text-white font-mono text-xs sm:text-sm" x-text="item.quantity"></span>
                                        </template>
                                        <template x-if="isEditing">
                                            <div class="flex items-center justify-center gap-1">
                                                <button type="button" 
                                                        @click="if(item.quantity > 1) item.quantity--" 
                                                        class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 hover:bg-gray-200 flex items-center justify-center font-bold text-xs">-</button>
                                                <input type="number" 
                                                       min="1" 
                                                       :name="'items[' + index + '][quantity]'" 
                                                       x-model.number="item.quantity" 
                                                       required
                                                       class="w-12 text-center text-xs font-bold py-1 px-0 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                                                <button type="button" 
                                                        @click="item.quantity++" 
                                                        class="w-6 h-6 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 hover:bg-gray-200 flex items-center justify-center font-bold text-xs">+</button>
                                            </div>
                                        </template>
                                    </td>

                                    {{-- Line Total --}}
                                    <td class="py-3 px-3 text-right font-black text-gray-900 dark:text-white font-mono text-xs sm:text-sm">
                                        ৳<span x-text="Number(item.price * item.quantity).toLocaleString()"></span>
                                    </td>

                                    {{-- Delete button (edit mode) --}}
                                    <td class="py-3 px-2 text-center" x-show="isEditing">
                                        <button type="button" 
                                                @click="removeItem(index)" 
                                                class="w-7 h-7 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 inline-flex items-center justify-center transition"
                                                title="Remove item">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ── INVOICE TOTALS BREAKDOWN ──────────────────────────────── --}}
            <div class="py-6 grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                {{-- Notes / Instructions --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Order & Customer Notes
                    </h4>
                    <div x-show="!isEditing">
                        <template x-if="notes">
                            <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 leading-relaxed font-medium" x-text="notes"></div>
                        </template>
                        <template x-if="!notes">
                            <p class="text-xs text-gray-400 italic">No special instructions provided for this order.</p>
                        </template>
                    </div>
                    <div x-show="isEditing" class="print:hidden">
                        <textarea name="notes" 
                                  x-model="notes" 
                                  rows="3" 
                                  placeholder="Add internal notes or customer delivery instructions..."
                                  class="w-full text-xs font-medium px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                    </div>

                    {{-- Coupon Controller (Edit Mode) --}}
                    <div x-show="isEditing" class="pt-2 space-y-2 print:hidden">
                        <label class="block text-[11px] font-bold text-gray-700 dark:text-gray-300">
                            Apply Coupon or Adjust Discount
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="text" 
                                   name="coupon_code"
                                   x-model="coupon_code" 
                                   placeholder="Coupon code (e.g. SAVE100)"
                                   class="text-xs font-mono uppercase px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white flex-1">
                            <button type="button" 
                                    @click="applyCoupon(coupon_code)"
                                    class="px-3.5 py-2 rounded-xl bg-gray-900 dark:bg-gray-700 text-white text-xs font-bold hover:bg-gray-800 transition cursor-pointer">
                                Apply
                            </button>
                        </div>
                        {{-- Active coupons quick pills --}}
                        <div class="flex items-center gap-1.5 flex-wrap pt-1">
                            <span class="text-[10px] text-gray-400">Available:</span>
                            <template x-for="c in availableCoupons" :key="c.code">
                                <button type="button" 
                                        @click="applyCoupon(c.code)"
                                        class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-primary/10 text-primary dark:text-secondary hover:bg-primary/20 transition"
                                        x-text="c.code + ' (' + (c.type==='percent' ? c.value + '%' : '৳' + c.value) + ')'"></button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Financial Calculations --}}
                <div class="bg-gray-50/70 dark:bg-gray-900/60 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/80 space-y-3">
                    {{-- Subtotal --}}
                    <div class="flex items-center justify-between text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        <span>Items Subtotal:</span>
                        <span class="font-bold text-gray-900 dark:text-white font-mono">
                            ৳<span x-text="Number(subtotal).toLocaleString()"></span>
                        </span>
                    </div>

                    {{-- Delivery Charge --}}
                    <div class="flex items-center justify-between text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                        <span>Delivery Charge:</span>
                        <span class="font-bold text-gray-900 dark:text-white font-mono">
                            ৳<span x-text="Number(delivery_cost).toLocaleString()"></span>
                        </span>
                    </div>

                    {{-- Coupon Discount --}}
                    <div class="flex items-center justify-between text-xs sm:text-sm"
                         :class="(discount_amount > 0) ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-400'">
                        <span class="flex items-center gap-1.5">
                            <span>Discount:</span>
                            <template x-if="coupon_code">
                                <span class="font-mono text-[10px] font-bold px-1.5 py-0.5 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-800 dark:text-emerald-300" x-text="coupon_code"></span>
                            </template>
                        </span>

                        <div class="flex items-center gap-1 font-mono">
                            <span>-৳</span>
                            <template x-if="!isEditing">
                                <span class="font-bold" x-text="Number(discount_amount).toLocaleString()"></span>
                            </template>
                            <template x-if="isEditing">
                                <input type="number" 
                                       step="1" 
                                       min="0"
                                       name="discount_amount" 
                                       x-model.number="discount_amount" 
                                       class="w-20 text-right text-xs font-bold py-0.5 px-1.5 rounded border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-emerald-600">
                            </template>
                        </div>
                    </div>

                    {{-- Line divider --}}
                    <div class="h-px bg-gray-200 dark:bg-gray-700 my-1"></div>

                    {{-- Grand Total --}}
                    <div class="flex items-center justify-between pt-1">
                        <div>
                            <span class="text-sm sm:text-base font-black text-gray-900 dark:text-white block">Total Payable</span>
                            <span class="text-[10px] text-gray-400">Cash on Delivery</span>
                        </div>
                        <div class="text-xl sm:text-2xl font-black text-primary dark:text-secondary font-mono">
                            ৳<span x-text="Number(grandTotal).toLocaleString()"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── INVOICE FOOTER ────────────────────────────────────────── --}}
            <div class="pt-8 border-t border-gray-200 dark:border-gray-700 text-center text-xs text-gray-400 space-y-1">
                <p class="font-medium text-gray-500 dark:text-gray-400">Thank you for your order with Jhurio!</p>
                <p>For any inquiries regarding this order, please call +880 1924-301474 or email support@jhurio.com</p>
                <p class="text-[10px] text-gray-300 dark:text-gray-600 pt-1">This is an authorized system-generated order invoice.</p>
            </div>

            {{-- ── SAVE / CANCEL FLOATING BAR (EDIT MODE) ───────────────── --}}
            <div x-show="isEditing" 
                 class="mt-8 pt-4 border-t border-amber-200 dark:border-amber-900/60 flex items-center justify-end gap-3 print:hidden">
                <button type="button" 
                        @click="isEditing = false"
                        class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-6 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-xs font-black transition shadow-md flex items-center gap-2 cursor-pointer">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Save Invoice Changes</span>
                </button>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════
             ADD PRODUCT MODAL (in Edit Mode)
             ══════════════════════════════════════════════════════════════ --}}
        <div x-show="addProductOpen" 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs print:hidden" 
             style="display:none;" 
             @keydown.escape.window="addProductOpen = false">
            <div class="bg-white dark:bg-gray-800 rounded-3xl p-6 w-full max-w-md shadow-2xl border border-gray-100 dark:border-gray-700 space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100 dark:border-gray-700">
                    <h3 class="font-bold text-base text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="plus-circle" class="w-5 h-5 text-primary"></i>
                        Add Product to Invoice
                    </h3>
                    <button type="button" @click="addProductOpen = false" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="space-y-3">
                    {{-- Product Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Select Product</label>
                        <select x-model="selectedProductId" 
                                @change="onProductSelect()"
                                class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                            <option value="">-- Choose a product --</option>
                            <template x-for="p in availableProducts" :key="p.id">
                                <option :value="p.id" x-text="p.name + ' (৳' + p.price + ')'"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Variation Selection if applicable --}}
                    <div x-show="selectedProductId && availableProducts.find(p => p.id == selectedProductId)?.variations?.length > 0">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Select Variation / Size / Color</label>
                        <select x-model="selectedVariationId"
                                class="w-full text-xs font-semibold px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                            <option value="">Default Product</option>
                            <template x-for="v in (availableProducts.find(p => p.id == selectedProductId)?.variations || [])" :key="v.id">
                                <option :value="v.id" x-text="v.name + (v.price ? ' (৳' + v.price + ')' : '')"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Quantity --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1">Quantity</label>
                        <input type="number" 
                               min="1" 
                               x-model.number="addQuantity" 
                               class="w-full text-xs font-bold px-3 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2">
                    <button type="button" 
                            @click="addProductOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-200 text-xs font-bold text-gray-600 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="addItemToOrder()"
                            :disabled="!selectedProductId"
                            class="px-5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 disabled:opacity-50 transition cursor-pointer">
                        Add to Invoice
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- ══════════════════════════════════════════════════════════════════
         STATUS CHANGE AUDIT LOG (Bottom section)
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-xs print:hidden">
        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <i data-lucide="history" class="w-4 h-4 text-primary"></i> Order Status Audit Log
        </h3>
        
        <div class="space-y-3">
            @forelse($order->logs as $log)
            <div class="flex items-start gap-3 p-3 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 text-xs">
                @php $color = $statuses[$log->status]['color'] ?? 'gray'; @endphp
                <span class="w-2.5 h-2.5 rounded-full bg-{{ $color }}-500 shrink-0 mt-1"></span>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-bold text-gray-900 dark:text-white capitalize">{{ $log->status }}</span>
                        <span class="text-[10px] text-gray-400 font-mono">{{ $log->created_at->format('d M Y, h:i A') }} ({{ $log->created_at->diffForHumans() }})</span>
                    </div>
                    @if($log->note)
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $log->note }}</p>
                    @endif
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 py-2">No activity recorded yet.</p>
            @endforelse
        </div>
    </div>
        </div>{{-- End xl:col-span-8 --}}

        {{-- ══════════════════════════════════════════════════════════════════
             RIGHT COLUMN: STATUS UPDATER & TRACKING DATA SIDEBAR (4 cols on XL)
             ══════════════════════════════════════════════════════════════════ --}}
        <div class="xl:col-span-4 space-y-6 print:hidden">

            <!-- ══════════════════════════════════════════════════════════════
                 1. ORDER STATUS & ACTIONS CARD (Sidebar Top)
                 ══════════════════════════════════════════════════════════════ -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/90 dark:border-gray-700 shadow-xl overflow-hidden"
                 x-data="{
                     newStatus: '{{ $order->status }}',
                     statusNote: '',
                     get isCancelled() {
                         return this.newStatus === 'cancelled';
                     }
                 }">
                
                <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 bg-gradient-to-r from-gray-50 to-primary/5 dark:from-gray-800 dark:to-gray-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <i data-lucide="shield-check" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Change Status</h3>
                            <p class="text-[11px] text-gray-400">Order workflow & note</p>
                        </div>
                    </div>
                    @php $currColor = $statuses[$order->status]['color'] ?? 'gray'; @endphp
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-{{ $currColor }}-50 text-{{ $currColor }}-700 dark:bg-{{ $currColor }}-900/30 dark:text-{{ $currColor }}-400 border border-{{ $currColor }}-200 dark:border-{{ $currColor }}-800">
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $currColor }}-500"></span>
                        {{ $statuses[$order->status]['label'] ?? Str::headline($order->status) }}
                    </span>
                </div>

                <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="p-5 space-y-4">
                    @csrf
                    @method('PATCH')

                    <!-- Status Select Dropdown -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1.5">
                            Order Status
                        </label>
                        <div class="relative">
                            <select name="status" 
                                    x-model="newStatus" 
                                    class="w-full text-xs font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent appearance-none cursor-pointer pr-9 shadow-2xs">
                                @foreach($statuses as $key => $status)
                                    <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>
                                        {{ $status['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-gray-400 absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none"></i>
                        </div>
                    </div>

                    <!-- Status Note (Optional normally, MANDATORY if Cancelled) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300">
                                Status Note
                                <template x-if="isCancelled">
                                    <span class="text-rose-600 font-black ml-1 text-xs">* (Required)</span>
                                </template>
                                <template x-if="!isCancelled">
                                    <span class="text-gray-400 font-normal text-[11px] ml-1">(Optional)</span>
                                </template>
                            </label>
                        </div>

                        <textarea name="note" 
                                  x-model="statusNote"
                                  :required="isCancelled"
                                  :placeholder="isCancelled ? 'Please specify reason for cancellation (Mandatory)...' : 'Add an internal note or reason for this status change (optional)...'"
                                  rows="3" 
                                  class="w-full text-xs font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent placeholder-gray-400 shadow-2xs transition"
                                  :class="isCancelled && !statusNote.trim() ? 'border-rose-300 focus:ring-rose-500 bg-rose-50/20' : ''"></textarea>

                        <!-- Cancellation Notice -->
                        <div x-show="isCancelled" x-cloak class="mt-2 p-2.5 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-[11px] text-rose-700 dark:text-rose-300 flex items-start gap-2">
                            <i data-lucide="alert-triangle" class="w-4 h-4 flex-shrink-0 mt-0.5 text-rose-500"></i>
                            <span>Cancellation reason is mandatory and will be saved in the permanent audit history.</span>
                        </div>
                    </div>

                    <!-- Update Status Button -->
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-sm hover:scale-[1.01] active:scale-[0.99]"
                            :class="isCancelled ? 'bg-rose-600 hover:bg-rose-700 shadow-rose-600/20' : 'bg-primary hover:bg-secondary shadow-primary/20'">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Update Order Status</span>
                    </button>
                </form>

                <!-- Recent Status Activity Log -->
                @if($order->logs && $order->logs->count() > 0)
                <div class="px-5 pb-5 pt-3 border-t border-gray-100 dark:border-gray-700/80">
                    <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 mb-2.5 flex items-center justify-between">
                        <span>Recent Status History</span>
                        <span class="text-[10px] text-gray-400 font-normal">{{ $order->logs->count() }} total</span>
                    </div>
                    <div class="space-y-2 max-h-44 overflow-y-auto custom-scrollbar pr-1">
                        @foreach($order->logs->take(4) as $log)
                            <div class="text-[11px] p-2.5 rounded-xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700">
                                <div class="flex items-center justify-between font-bold text-gray-800 dark:text-gray-200">
                                    <span class="capitalize">{{ $log->status }}</span>
                                    <span class="text-[10px] text-gray-400 font-normal">{{ $log->created_at->diffForHumans() }}</span>
                                </div>
                                @if($log->note)
                                    <p class="text-[10px] text-gray-600 dark:text-gray-300 mt-1 italic leading-relaxed">
                                        "{{ $log->note }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- ══════════════════════════════════════════════════════════════
                 2. VISITOR & TRACKING DATA SIDECARD (Under Status Card)
                 ══════════════════════════════════════════════════════════════ -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-200/90 dark:border-gray-700 shadow-xl overflow-hidden">
                {{-- Sidecard Header --}}
                <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 bg-gradient-to-r from-gray-50 to-primary/5 dark:from-gray-800 dark:to-gray-800/80 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <i data-lucide="radar" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Visitor & Tracking</h3>
                            <p class="text-[11px] text-gray-400">IP, Location, Device & Marketing</p>
                        </div>
                    </div>
                    @if($trackingVisitor)
                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            Tracked
                        </span>
                    @else
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400">
                            Legacy Order
                        </span>
                    @endif
                </div>

                <div class="p-5 space-y-5 text-xs">
                    @php
                        $ip = $order->ip_address ?? $trackingVisitor?->ip_address;
                        $userAgent = $order->user_agent ?? $trackingVisitor?->user_agent;
                        $country = $trackingVisitor?->country;
                        $city = $trackingVisitor?->city;
                        $division = $trackingVisitor?->division;
                        $device = $trackingVisitor?->device ?? 'Desktop';
                        $browser = $trackingVisitor?->browser;
                        $os = $trackingVisitor?->os;
                        $screen = $trackingVisitor?->screen_size;
                        $language = $trackingVisitor?->language;
                        $trafficSource = $trackingSession?->traffic_source ?? ($trackingVisitor?->first_source ? 'Direct' : null);
                        $referrer = $trackingSession?->referrer;
                        $utmSource = $trackingSession?->utm_source;
                        $utmMedium = $trackingSession?->utm_medium;
                        $utmCampaign = $trackingSession?->utm_campaign;
                    @endphp

                    {{-- 1. IP & Geolocation Section --}}
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between text-[11px] font-bold uppercase tracking-wider text-gray-400">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="map-pin" class="w-3.5 h-3.5 text-primary"></i> IP & Location
                            </span>
                            @if($ip && $ip !== '127.0.0.1')
                                <a href="https://whatismyipaddress.com/ip/{{ $ip }}" target="_blank" class="text-[10px] text-primary hover:underline">Lookup IP &rarr;</a>
                            @endif
                        </div>

                        <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/80 space-y-2.5">
                            {{-- IP Address --}}
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Visitor IP:</span>
                                @if($ip)
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-gray-900 dark:text-white">{{ $ip }}</span>
                                        <button type="button" onclick="navigator.clipboard.writeText('{{ $ip }}'); alert('IP address copied!')" title="Copy IP" class="text-gray-400 hover:text-primary transition-colors">
                                            <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Not Recorded</span>
                                @endif
                            </div>

                            {{-- Location details --}}
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Geo Location:</span>
                                <div class="text-right">
                                    @if($city || $country)
                                        <span class="font-semibold text-gray-900 dark:text-white block">
                                            {{ $city ?: 'City' }}@if($division), {{ $division }}@endif
                                        </span>
                                        <span class="text-[10px] text-gray-400 block">{{ $country ?: 'Bangladesh' }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Localhost / Unknown</span>
                                    @endif
                                </div>
                            </div>

                            {{-- Coordinates / Map Link if available --}}
                            @if($trackingVisitor && $trackingVisitor->lat && $trackingVisitor->lon)
                                <div class="flex items-center justify-between pt-1 border-t border-gray-100 dark:border-gray-800">
                                    <span class="text-gray-500">Coordinates:</span>
                                    <a href="https://www.google.com/maps?q={{ $trackingVisitor->lat }},{{ $trackingVisitor->lon }}" target="_blank" class="font-mono text-primary hover:underline flex items-center gap-1">
                                        <span>{{ round($trackingVisitor->lat, 4) }}, {{ round($trackingVisitor->lon, 4) }}</span>
                                        <i data-lucide="external-link" class="w-3 h-3"></i>
                                    </a>
                                </div>
                            @endif

                            {{-- Location vs Shipping Verification --}}
                            @php
                                $addressText = strtolower($order->shipping_address ?? '');
                                $cityCheck = $city ? strtolower($city) : '';
                                $hasCityMatch = $cityCheck && str_contains($addressText, $cityCheck);
                            @endphp
                            @if($hasCityMatch)
                                <div class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 text-[11px] font-semibold flex items-center gap-1.5 border border-emerald-200/80 dark:border-emerald-900/50">
                                    <i data-lucide="shield-check" class="w-4 h-4 shrink-0 text-emerald-600"></i>
                                    <span>Verified: IP matches shipping city ({{ ucfirst($city) }})</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 2. Device & Environment --}}
                    <div class="space-y-2.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                            <i data-lucide="laptop" class="w-3.5 h-3.5 text-primary"></i> Device & Environment
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            {{-- Device Type --}}
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/80">
                                <span class="text-[10px] text-gray-400 block mb-1">Device</span>
                                <div class="flex items-center gap-1.5 font-bold text-gray-900 dark:text-white">
                                    @if(strtolower($device) === 'mobile')
                                        <i data-lucide="smartphone" class="w-3.5 h-3.5 text-primary"></i>
                                    @elseif(strtolower($device) === 'tablet')
                                        <i data-lucide="tablet" class="w-3.5 h-3.5 text-primary"></i>
                                    @else
                                        <i data-lucide="monitor" class="w-3.5 h-3.5 text-primary"></i>
                                    @endif
                                    <span>{{ $device }}</span>
                                </div>
                            </div>

                            {{-- Browser --}}
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/80">
                                <span class="text-[10px] text-gray-400 block mb-1">Browser</span>
                                <div class="font-bold text-gray-900 dark:text-white truncate">
                                    {{ $browser ?: 'Web Browser' }}
                                </div>
                            </div>

                            {{-- OS --}}
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/80">
                                <span class="text-[10px] text-gray-400 block mb-1">OS</span>
                                <div class="font-bold text-gray-900 dark:text-white truncate">
                                    {{ $os ?: 'Standard OS' }}
                                </div>
                            </div>

                            {{-- Screen Resolution --}}
                            <div class="p-3 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/80">
                                <span class="text-[10px] text-gray-400 block mb-1">Screen / Language</span>
                                <div class="font-mono text-gray-900 dark:text-white font-semibold truncate text-[11px]">
                                    {{ $screen ?: 'Unknown' }}
                                    @if($language)<span class="text-gray-400">({{ $language }})</span>@endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Traffic Source & Acquisition --}}
                    <div class="space-y-2.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 flex items-center gap-1.5">
                            <i data-lucide="compass" class="w-3.5 h-3.5 text-primary"></i> Traffic Source & Marketing
                        </div>

                        <div class="p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-900/60 border border-gray-100 dark:border-gray-700/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-gray-500">Channel:</span>
                                <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] 
                                    @if(str_contains(strtolower($trafficSource ?? ''), 'facebook')) bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400
                                    @elseif(str_contains(strtolower($trafficSource ?? ''), 'google')) bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400
                                    @elseif(str_contains(strtolower($trafficSource ?? ''), 'direct')) bg-purple-50 text-purple-700 dark:bg-purple-950/40 dark:text-purple-400
                                    @else bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300
                                    @endif">
                                    {{ $trafficSource ?: 'Direct Traffic' }}
                                </span>
                            </div>

                            @if($referrer)
                                <div class="flex items-start justify-between gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
                                    <span class="text-gray-500 whitespace-nowrap">Referrer:</span>
                                    <span class="text-gray-700 dark:text-gray-300 font-mono text-[10px] truncate max-w-[190px]" title="{{ $referrer }}">
                                        {{ $referrer }}
                                    </span>
                                </div>
                            @endif

                            @if($utmCampaign || $utmSource || $utmMedium)
                                <div class="pt-2 border-t border-gray-100 dark:border-gray-800 space-y-1">
                                    <span class="text-[10px] text-gray-400 block font-semibold">Campaign Tags:</span>
                                    @if($utmCampaign)
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-gray-500 font-mono">utm_campaign:</span>
                                            <span class="font-bold text-primary">{{ $utmCampaign }}</span>
                                        </div>
                                    @endif
                                    @if($utmSource)
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-gray-500 font-mono">utm_source:</span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $utmSource }}</span>
                                        </div>
                                    @endif
                                    @if($utmMedium)
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-gray-500 font-mono">utm_medium:</span>
                                            <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $utmMedium }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- 4. Visitor Journey Timeline --}}
                    <div class="space-y-2.5">
                        <div class="text-[11px] font-bold uppercase tracking-wider text-gray-400 flex items-center justify-between">
                            <span class="flex items-center gap-1.5">
                                <i data-lucide="activity" class="w-3.5 h-3.5 text-primary"></i> Pre-Purchase Activity
                            </span>
                            <span class="text-[10px] text-gray-400 font-semibold">
                                {{ $visitorEvents->count() }} events
                            </span>
                        </div>

                        <div class="space-y-1.5">
                            @forelse($visitorEvents as $evt)
                                <div class="flex items-start gap-2.5 p-2 rounded-xl bg-gray-50/70 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60 text-[11px]">
                                    @php
                                        $evtIcon = 'mouse-pointer';
                                        $evtColor = 'text-gray-500';
                                        if ($evt->event_type === 'purchase') { $evtIcon = 'check-circle'; $evtColor = 'text-emerald-500'; }
                                        elseif ($evt->event_type === 'checkout_start') { $evtIcon = 'shopping-cart'; $evtColor = 'text-amber-500'; }
                                        elseif ($evt->event_type === 'add_to_cart') { $evtIcon = 'plus-circle'; $evtColor = 'text-blue-500'; }
                                        elseif ($evt->event_type === 'product_view') { $evtIcon = 'eye'; $evtColor = 'text-purple-500'; }
                                    @endphp
                                    <i data-lucide="{{ $evtIcon }}" class="w-3.5 h-3.5 {{ $evtColor }} mt-0.5 shrink-0"></i>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center justify-between">
                                            <span class="font-bold text-gray-800 dark:text-gray-200 capitalize">
                                                {{ str_replace('_', ' ', $evt->event_type) }}
                                            </span>
                                            <span class="text-[10px] text-gray-400">
                                                {{ $evt->created_at->format('h:i A') }}
                                            </span>
                                        </div>
                                        @if($evt->page_url)
                                            <span class="text-[10px] text-gray-400 truncate block mt-0.5" title="{{ $evt->page_url }}">
                                                {{ parse_url($evt->page_url, PHP_URL_PATH) ?: '/' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <p class="text-[11px] text-gray-400 italic py-1">No detailed action events recorded prior to order.</p>
                            @endforelse
                        </div>
                    </div>

                    {{-- 5. Customer Profile Quick Link --}}
                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
                        <span class="text-[11px] text-gray-400 font-mono truncate max-w-[170px]">
                            VID: {{ substr($order->tracking_visitor_id ?? 'N/A', 0, 16) }}...
                        </span>
                        <a href="{{ route('admin.analytics.overview') }}" class="text-xs text-primary font-bold hover:underline flex items-center gap-1">
                            <span>Analytics</span>
                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>{{-- End xl:col-span-4 (Right Column) --}}
    </div>{{-- End grid-cols-12 --}}
</div>
@endsection

@push('scripts')
<script>
function orderInvoiceEditor(initialData) {
    return {
        isEditing: false,
        isSaving: false,
        name: initialData.name,
        phone: initialData.phone,
        shipping_address: initialData.shipping_address,
        delivery_method_id: initialData.delivery_method_id,
        delivery_cost: parseFloat(initialData.delivery_cost || 0),
        coupon_code: initialData.coupon_code || '',
        discount_amount: parseFloat(initialData.discount_amount || 0),
        notes: initialData.notes || '',
        items: initialData.items || [],

        // Add Product Modal
        addProductOpen: false,
        selectedProductId: '',
        selectedVariationId: '',
        addQuantity: 1,

        availableProducts: initialData.availableProducts || [],
        deliveryMethods: initialData.deliveryMethods || [],
        availableCoupons: initialData.availableCoupons || [],

        get subtotal() {
            return this.items.reduce((sum, item) => sum + (parseFloat(item.price || 0) * parseInt(item.quantity || 1)), 0);
        },

        get grandTotal() {
            return Math.max(0, this.subtotal - parseFloat(this.discount_amount || 0) + parseFloat(this.delivery_cost || 0));
        },

        onDeliveryMethodChange() {
            const dm = this.deliveryMethods.find(m => m.id == this.delivery_method_id);
            if (dm) {
                this.delivery_cost = parseFloat(dm.cost || 0);
            }
        },

        applyCoupon(code) {
            const couponToApply = (code || this.coupon_code || '').trim().toUpperCase();
            if (!couponToApply) return;

            const found = this.availableCoupons.find(c => c.code.toUpperCase() === couponToApply);
            if (found) {
                this.coupon_code = found.code;
                if (found.type === 'percent') {
                    this.discount_amount = Math.round((this.subtotal * parseFloat(found.value)) / 100);
                } else {
                    this.discount_amount = parseFloat(found.value);
                }
            } else {
                alert('Coupon code "' + couponToApply + '" not found in active coupons. You can manually enter a discount amount below.');
            }
        },

        removeItem(index) {
            if (this.items.length <= 1) {
                alert('An order invoice must have at least one product.');
                return;
            }
            this.items.splice(index, 1);
        },

        onProductSelect() {
            this.selectedVariationId = '';
        },

        addItemToOrder() {
            const prod = this.availableProducts.find(p => p.id == this.selectedProductId);
            if (!prod) return;

            let varName = null;
            let price = parseFloat(prod.price || 0);

            if (this.selectedVariationId) {
                const variation = prod.variations.find(v => v.id == this.selectedVariationId);
                if (variation) {
                    varName = variation.name;
                    if (variation.price) price = parseFloat(variation.price);
                }
            }

            this.items.push({
                id: null,
                product_id: prod.id,
                variation_id: this.selectedVariationId ? parseInt(this.selectedVariationId) : null,
                product_name: prod.name,
                variation_name: varName,
                price: price,
                quantity: parseInt(this.addQuantity || 1),
                thumbnail_url: prod.thumbnail || null
            });

            this.selectedProductId = '';
            this.selectedVariationId = '';
            this.addQuantity = 1;
            this.addProductOpen = false;
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        }
    };
}
</script>
@endpush

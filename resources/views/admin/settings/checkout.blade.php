@extends('layouts.admin')

@section('title', 'Checkout Settings')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Checkout Settings
        </h2>
        <p class="text-sm text-gray-500 mt-1">Configure order bumps and checkout behavior</p>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-3xl">
    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 p-4 border-l-4 border-green-500 rounded-r-xl flex items-center shadow-sm">
            <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <form action="{{ route('admin.settings.checkout.update') }}" method="POST">
            @csrf
            
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Order Bump</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Display a special offer product during the checkout process.</p>
                
                <div class="space-y-5">
                    <div>
                        <label for="order_bump_product_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bump Product</label>
                        <select name="order_bump_product_id" id="order_bump_product_id" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                            <option value="">None (Disable Order Bump)</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ $settings['order_bump_product_id'] == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ number_format($product->price, 0) }} BDT)
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">Select a product to offer at checkout.</p>
                    </div>

                    <div>
                        <label for="order_bump_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Offer Title</label>
                        <input type="text" name="order_bump_title" id="order_bump_title" value="{{ $settings['order_bump_title'] }}" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                        <p class="mt-1 text-xs text-gray-500">E.g., "Yes, add this to my order!"</p>
                    </div>

                    <div>
                        <label for="order_bump_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Offer Description</label>
                        <textarea name="order_bump_description" id="order_bump_description" rows="2" class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-primary focus:border-primary">{{ $settings['order_bump_description'] }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">A short description to persuade the user.</p>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 flex justify-end items-center">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-colors font-medium flex items-center shadow-sm">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

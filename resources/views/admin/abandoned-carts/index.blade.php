@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Abandoned Carts</h1>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Customer</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Product Left Behind</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total Value</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($carts as $cart)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $cart->created_at->format('d M Y, h:i A') }}
                        <div class="text-xs text-gray-400">{{ $cart->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="py-3 px-4 text-sm">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $cart->name ?? 'Unknown' }}</div>
                        <div class="text-gray-500 flex items-center mt-1">
                            <i data-lucide="phone" class="w-3 h-3 mr-1"></i> {{ $cart->phone }}
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-300">
                        @if(isset($cart->cart_data['product_name']))
                            <div class="flex items-center gap-2">
                                @if(isset($cart->cart_data['image']) && $cart->cart_data['image'])
                                    <img src="{{ $cart->cart_data['image'] }}" class="w-8 h-8 rounded object-cover border border-gray-200">
                                @endif
                                <div>
                                    <div class="font-medium">{{ $cart->cart_data['product_name'] }}</div>
                                    <div class="text-xs text-gray-500">Qty: {{ $cart->cart_data['quantity'] ?? 1 }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-400 italic">Unknown Data</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">
                        @if(isset($cart->cart_data['price']) && isset($cart->cart_data['quantity']))
                            ৳{{ number_format((float)$cart->cart_data['price'] * (int)$cart->cart_data['quantity'], 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="py-3 px-4 text-sm text-right">
                        <form action="{{ route('admin.abandoned-carts.destroy', $cart->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this record?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300" title="Delete">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                        <div class="flex flex-col items-center justify-center">
                            <i data-lucide="shopping-cart" class="w-8 h-8 mb-2 text-gray-400"></i>
                            <p>No abandoned carts found.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($carts->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $carts->links() }}
    </div>
    @endif
</div>
@endsection

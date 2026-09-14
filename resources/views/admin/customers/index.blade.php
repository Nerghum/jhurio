@extends('layouts.admin')

@section('title', 'Manage Customers')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Customers
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage your storefront customers</p>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    
    <!-- Filter Bar -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-center gap-4">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex-1 max-w-md relative">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, or email..." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary pl-10">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-2.5 text-gray-400"></i>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">Customer</th>
                    <th class="px-6 py-4">Contact</th>
                    <th class="px-6 py-4">Joined At</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($customers as $customer)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold flex-shrink-0">
                                {{ substr($customer->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white">{{ $customer->name }}</div>
                                <div class="text-xs text-gray-500">ID: #{{ $customer->id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        <div class="flex items-center gap-1.5 mb-1">
                            <i data-lucide="phone" class="w-3.5 h-3.5"></i> 
                            {{ $customer->phone ?? 'N/A' }}
                        </div>
                        <div class="flex items-center gap-1.5 text-xs">
                            <i data-lucide="mail" class="w-3.5 h-3.5"></i> 
                            {{ $customer->email ?? 'N/A' }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $customer->created_at->format('d M Y') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.orders.index', ['search' => $customer->phone]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition-colors">
                            <i data-lucide="package" class="w-3.5 h-3.5"></i> View Orders
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="users" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                        <p>No customers found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($customers->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
        {{ $customers->links() }}
    </div>
    @endif
</div>
@endsection

@extends('layouts.admin')
@section('title', 'Delivery Methods')
@section('header', 'Delivery Methods')

@section('content')
<div x-data="{ addModalOpen: false, editModalOpen: false, editData: {} }">
    <div class="mb-6 flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Delivery Methods</h2>
        <button @click="addModalOpen = true" class="flex items-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary transition-colors">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Method
        </button>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-100 dark:border-gray-700">
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Method Name</th>
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Cost (BDT)</th>
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-center">Status</th>
                    <th class="p-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($methods as $method)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="p-4 text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $method->name }}</td>
                        <td class="p-4 text-sm text-gray-600 dark:text-gray-300 text-right">৳{{ number_format($method->cost, 2) }}</td>
                        <td class="p-4 text-center">
                            @if($method->is_active)
                                <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Active</span>
                            @else
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <button @click="editData = { id: {{ $method->id }}, name: '{{ addslashes($method->name) }}', cost: '{{ $method->cost }}', is_active: {{ $method->is_active ? 'true' : 'false' }} }; editModalOpen = true" class="text-blue-500 hover:text-blue-700 transition-colors p-1">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </button>
                            <form action="{{ route('admin.delivery-methods.destroy', $method->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this delivery method?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors p-1">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">No delivery methods found. Add one to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Add Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Add Delivery Method</h3>
            <form action="{{ route('admin.delivery-methods.store') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Method Name (e.g. Inside Dhaka)</label>
                    <input type="text" name="name" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost (BDT)</label>
                    <input type="number" step="0.01" name="cost" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                </div>
                <div class="mb-6 flex items-center">
                    <input type="checkbox" name="is_active" id="add_is_active" checked class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                    <label for="add_is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50" style="display: none;">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-6">
            <h3 class="text-lg font-bold mb-4 text-gray-900 dark:text-white">Edit Delivery Method</h3>
            <form :action="'{{ url('admin/delivery-methods') }}/' + editData.id" method="POST">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Method Name</label>
                    <input type="text" name="name" x-model="editData.name" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Cost (BDT)</label>
                    <input type="number" step="0.01" name="cost" x-model="editData.cost" required class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white">
                </div>
                <div class="mb-6 flex items-center">
                    <input type="checkbox" name="is_active" id="edit_is_active" x-model="editData.is_active" class="rounded border-gray-300 text-primary focus:ring-primary h-4 w-4">
                    <label for="edit_is_active" class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</label>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-gray-500 hover:bg-gray-100 rounded-lg">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

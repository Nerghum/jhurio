@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Users & Roles</h1>
    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-primary text-white font-medium rounded-xl hover:bg-secondary transition-colors inline-flex items-center gap-2 shadow-sm shadow-primary/20">
        <i data-lucide="plus" class="w-4 h-4"></i> Add User
    </a>
</div>

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email & Phone</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Role</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider">Created</th>
                    <th class="py-3 px-4 text-xs font-semibold text-gray-500 dark:text-gray-300 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($users as $user)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">#{{ $user->id }}</td>
                    <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">
                        {{ $user->name }}
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-700 dark:text-gray-300">
                        <div>{{ $user->email }}</div>
                        <div class="text-xs text-gray-500">{{ $user->phone ?? '-' }}</div>
                    </td>
                    <td class="py-3 px-4 text-sm">
                        @if($user->isAdmin())
                            <span class="px-2 py-1 text-xs rounded bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300 font-medium">Admin</span>
                        @elseif($user->isManager())
                            <span class="px-2 py-1 text-xs rounded bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300 font-medium">Manager</span>
                        @else
                            <span class="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 font-medium">Customer</span>
                        @endif
                    </td>
                    <td class="py-3 px-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                        {{ $user->created_at->format('M d, Y') }}
                    </td>
                    <td class="py-3 px-4 text-sm text-right">
                        <div class="flex items-center justify-end gap-3">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-gray-500 hover:text-primary transition-colors" title="Edit">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                            
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Delete">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                        No users found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($users->hasPages())
    <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection

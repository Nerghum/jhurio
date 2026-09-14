@extends('layouts.admin')

@section('title', 'Messages')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Messages
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage contact form submissions</p>
    </div>
</div>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    
    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 p-4 border-b border-green-200 dark:border-green-800 flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4 w-12">Status</th>
                    <th class="px-6 py-4">Sender</th>
                    <th class="px-6 py-4">Subject & Message</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($messages as $message)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors {{ $message->is_read ? 'opacity-80' : 'bg-primary/5 dark:bg-primary/10 font-medium' }}">
                    <td class="px-6 py-4">
                        @if($message->is_read)
                            <i data-lucide="mail-open" class="w-5 h-5 text-gray-400" title="Read"></i>
                        @else
                            <i data-lucide="mail" class="w-5 h-5 text-primary" title="Unread"></i>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $message->name }}</div>
                        <div class="text-xs text-gray-500">{{ $message->email }}</div>
                    </td>
                    <td class="px-6 py-4 max-w-md truncate">
                        @if($message->subject)
                            <div class="font-medium text-gray-900 dark:text-gray-200 mb-0.5 truncate">{{ $message->subject }}</div>
                        @endif
                        <div class="text-gray-500 text-xs truncate">{{ Str::limit($message->message, 80) }}</div>
                    </td>
                    <td class="px-6 py-4 text-gray-500 text-xs whitespace-nowrap">
                        {{ $message->created_at->diffForHumans() }}
                    </td>
                    <td class="px-6 py-4 text-right whitespace-nowrap">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.contact.show', $message->id) }}" class="p-2 text-gray-400 hover:text-primary bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.contact.destroy', $message->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this message?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition-colors">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="mail-search" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                        <p>No messages found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($messages->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
        {{ $messages->links() }}
    </div>
    @endif
</div>
@endsection

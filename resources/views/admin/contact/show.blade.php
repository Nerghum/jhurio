@extends('layouts.admin')

@section('title', 'View Message')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <a href="{{ route('admin.contact.index') }}" class="text-gray-400 hover:text-primary transition-colors">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            Message Details
        </h2>
        <p class="text-sm text-gray-500 mt-1">From: {{ $contactMessage->name }}</p>
    </div>
    
    <div>
        <form action="{{ route('admin.contact.destroy', $contactMessage->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this message?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-lg text-sm font-medium flex items-center transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i> Delete Message
            </button>
        </form>
    </div>
</div>
@endsection

@section('content')
<div class="max-w-4xl bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    
    <div class="p-6 md:p-8 border-b border-gray-100 dark:border-gray-700">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg flex-shrink-0">
                    {{ substr($contactMessage->name, 0, 1) }}
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $contactMessage->name }}</h3>
                    <div class="flex items-center text-sm text-gray-500 mt-1 gap-4">
                        <a href="mailto:{{ $contactMessage->email }}" class="flex items-center hover:text-primary transition-colors">
                            <i data-lucide="mail" class="w-3.5 h-3.5 mr-1.5"></i>
                            {{ $contactMessage->email }}
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="text-sm text-gray-500 md:text-right">
                <div class="flex items-center md:justify-end gap-1.5 mb-1">
                    <i data-lucide="calendar" class="w-3.5 h-3.5"></i>
                    {{ $contactMessage->created_at->format('M d, Y') }}
                </div>
                <div class="flex items-center md:justify-end gap-1.5 text-xs">
                    <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                    {{ $contactMessage->created_at->format('h:i A') }}
                </div>
            </div>
        </div>
        
        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-6">
            @if($contactMessage->subject)
                <h4 class="text-base font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-200 dark:border-gray-700 pb-3">
                    Subject: {{ $contactMessage->subject }}
                </h4>
            @endif
            
            <div class="prose prose-sm sm:prose dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $contactMessage->message }}</div>
        </div>
    </div>
    
    <div class="p-6 bg-gray-50 dark:bg-gray-900/50 flex justify-end">
        <a href="mailto:{{ $contactMessage->email }}?subject=RE: {{ $contactMessage->subject ?? 'Your message to LuxeCart' }}" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-colors font-medium flex items-center shadow-sm">
            <i data-lucide="reply" class="w-4 h-4 mr-2"></i> Reply via Email
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="max-w-3xl mx-auto">
        <div class="text-center mb-6 sm:mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">Get in Touch</h1>
            <p class="mt-4 text-lg text-gray-500 dark:text-gray-400">
                Have a question, feedback, or need assistance? We'd love to hear from you. Fill out the form below and our team will get back to you as soon as possible.
            </p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 p-4 rounded-xl flex items-center border border-green-200 dark:border-green-800 mb-8">
                <i data-lucide="check-circle" class="w-5 h-5 mr-3 flex-shrink-0"></i>
                <p>{{ session('success') }}</p>
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 sm:p-10">
            <form action="{{ route('contact.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Full Name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('name') border-red-500 ring-red-500 @enderror">
                        @error('name')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email Address <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                               class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('email') border-red-500 ring-red-500 @enderror">
                        @error('email')
                            <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Subject (Optional)</label>
                    <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    @error('subject')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Your Message <span class="text-red-500">*</span></label>
                    <textarea name="message" id="message" rows="5" required
                              class="w-full rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white px-4 py-3 focus:ring-2 focus:ring-primary focus:border-transparent transition-all @error('message') border-red-500 ring-red-500 @enderror">{{ old('message') }}</textarea>
                    @error('message')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full flex items-center justify-center px-8 py-4 border border-transparent rounded-xl shadow-sm text-base font-medium text-white bg-primary hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors">
                        Send Message
                        <i data-lucide="send" class="ml-2 -mr-1 w-5 h-5"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="mt-12 grid grid-cols-1 sm:grid-cols-3 gap-6 text-center">
            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800/50">
                <i data-lucide="mail" class="w-8 h-8 text-primary mx-auto mb-3"></i>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Email</h3>
                <a href="mailto:support@luxe.com" class="text-sm text-gray-500 hover:text-primary transition-colors">support@luxe.com</a>
            </div>
            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800/50">
                <i data-lucide="phone" class="w-8 h-8 text-primary mx-auto mb-3"></i>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Phone</h3>
                <a href="tel:+1234567890" class="text-sm text-gray-500 hover:text-primary transition-colors">+1 (234) 567-890</a>
            </div>
            <div class="p-6 rounded-2xl bg-gray-50 dark:bg-gray-800/50">
                <i data-lucide="map-pin" class="w-8 h-8 text-primary mx-auto mb-3"></i>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">Office</h3>
                <p class="text-sm text-gray-500">123 Commerce St.<br>New York, NY 10001</p>
            </div>
        </div>
    </div>
</div>
@endsection

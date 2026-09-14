@extends('layouts.app')

@section('title', 'আমাদের সম্পর্কে - Jhurio')

@push('scripts')
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
    .bangla-text {
        font-family: 'Noto Sans Bengali', sans-serif;
    }
</style>
@endpush

@section('content')
<div class="bg-gray-50 dark:bg-gray-900 py-16 min-h-screen bangla-text">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="text-center mb-16">
            <h1 class="text-3xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">আমাদের সম্পর্কে</h1>
            <div class="w-24 h-1 bg-primary mx-auto rounded-full"></div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-8 md:p-12 mb-12">
            <div class="prose prose-lg dark:prose-invert max-w-none text-gray-700 dark:text-gray-300 space-y-6">
                
                <p class="text-xl leading-relaxed">
                    <strong>ঝুড়িও (Jhurio)</strong>-তে আপনাকে স্বাগতম! আমরা একটি প্রিমিয়াম ই-কমার্স প্ল্যাটফর্ম, যা আপনাদের দোরগোড়ায় সেরা মানের পণ্য পৌঁছে দিতে প্রতিশ্রুতিবদ্ধ।
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">আমাদের লক্ষ্য</h2>
                <p class="leading-relaxed">
                    আমাদের প্রধান লক্ষ্য হলো গ্রাহকদের কেনাকাটার অভিজ্ঞতাকে সহজ, নিরাপদ এবং আনন্দদায়ক করা। আমরা বিশ্বাস করি গুণগত মান এবং গ্রাহক সন্তুষ্টির ওপর। তাই আমরা দেশ-বিদেশের বাছাইকৃত সেরা পণ্যগুলো সংগ্রহ করে আপনাদের জন্য একটি নির্ভরযোগ্য প্ল্যাটফর্ম তৈরি করেছি।
                </p>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">কেন আমাদের বেছে নিবেন?</h2>
                <ul class="space-y-3 list-none pl-0">
                    <li class="flex items-start">
                        <i data-lucide="check-circle" class="w-6 h-6 text-primary mr-3 flex-shrink-0 mt-0.5"></i>
                        <span><strong>গুণগত মান:</strong> প্রতিটি পণ্যের গুণগত মান আমরা সতর্কতার সাথে যাচাই করি।</span>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="check-circle" class="w-6 h-6 text-primary mr-3 flex-shrink-0 mt-0.5"></i>
                        <span><strong>দ্রুত ডেলিভারি:</strong> দেশের যেকোনো প্রান্তে দ্রুততম সময়ে পণ্য পৌঁছে দেওয়ার নিশ্চয়তা।</span>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="check-circle" class="w-6 h-6 text-primary mr-3 flex-shrink-0 mt-0.5"></i>
                        <span><strong>নিরাপদ লেনদেন:</strong> আপনার প্রতিটি লেনদেন ১০০% সুরক্ষিত।</span>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="check-circle" class="w-6 h-6 text-primary mr-3 flex-shrink-0 mt-0.5"></i>
                        <span><strong>সহজ রিটার্ন পলিসি:</strong> পণ্য পছন্দ না হলে বা কোনো সমস্যা থাকলে রয়েছে সহজ রিটার্নের সুবিধা।</span>
                    </li>
                </ul>

                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mt-8 mb-4">আমাদের সাথে থাকুন</h2>
                <p class="leading-relaxed">
                    আপনাদের ভালোবাসা ও সমর্থনই আমাদের এগিয়ে যাওয়ার মূল অনুপ্রেরণা। যেকোনো প্রয়োজনে আমাদের কাস্টমার সাপোর্ট টিমের সাথে যোগাযোগ করতে পারেন। আমরা সবসময় আপনাদের সেবায় নিয়োজিত।
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="users" class="w-6 h-6 text-primary"></i>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">বিশ্বস্ত গ্রাহক</h3>
                <p class="text-sm text-gray-500">হাজারো গ্রাহকের আস্থার প্রতীক</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="shopping-bag" class="w-6 h-6 text-primary"></i>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">প্রচুর কালেকশন</h3>
                <p class="text-sm text-gray-500">আপডেট ও নতুন সব পণ্যের সমাহার</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="w-12 h-12 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="headphones" class="w-6 h-6 text-primary"></i>
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white mb-2">২৪/৭ সাপোর্ট</h3>
                <p class="text-sm text-gray-500">সার্বক্ষণিক কাস্টমার সেবা</p>
            </div>
        </div>
        
    </div>
</div>
@endsection

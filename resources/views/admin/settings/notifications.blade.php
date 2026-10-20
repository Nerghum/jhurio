@extends('layouts.admin')
@section('title', 'Push Notifications Settings')
@section('header', 'Push Notifications')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Firebase Web Push Credentials</h3>
            <p class="text-sm text-gray-500 mt-1">Configure Firebase to receive live order notifications directly in your browser.</p>
        </div>

        <div class="p-6">
            <form action="{{ route('admin.settings.notifications.update') }}" method="POST">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div class="col-span-full border-b pb-2 mb-2 border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-white">1. Public SDK Config</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Go to <a href="https://console.firebase.google.com/" target="_blank" class="text-primary hover:underline">Firebase Console</a> > Select Project > Project Settings (Gear Icon) > General tab.<br>
                            Scroll down to "Your apps". If you don't have a Web App, click the <b>&lt;/&gt;</b> icon to create one. Copy the values from the <code>firebaseConfig</code> object.
                        </p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">API Key</label>
                        <input type="text" name="firebase_api_key" value="{{ old('firebase_api_key', $firebase['apiKey']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Value of <code>apiKey</code> in the config object.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Auth Domain</label>
                        <input type="text" name="firebase_auth_domain" value="{{ old('firebase_auth_domain', $firebase['authDomain']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Value of <code>authDomain</code> (e.g. <i>project-id.firebaseapp.com</i>).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Project ID</label>
                        <input type="text" name="firebase_project_id" value="{{ old('firebase_project_id', $firebase['projectId']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Value of <code>projectId</code> (e.g. <i>my-store-12345</i>).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Storage Bucket</label>
                        <input type="text" name="firebase_storage_bucket" value="{{ old('firebase_storage_bucket', $firebase['storageBucket']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Value of <code>storageBucket</code> (e.g. <i>project-id.appspot.com</i>).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Messaging Sender ID</label>
                        <input type="text" name="firebase_messaging_sender_id" value="{{ old('firebase_messaging_sender_id', $firebase['messagingSenderId']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Value of <code>messagingSenderId</code> (usually numbers).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">App ID</label>
                        <input type="text" name="firebase_app_id" value="{{ old('firebase_app_id', $firebase['appId']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary">
                        <p class="text-xs text-gray-500 mt-1">Value of <code>appId</code> (e.g. <i>1:12345:web:abcde</i>).</p>
                    </div>

                    <div class="col-span-full mt-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">VAPID Key (Web Push Certificate)</label>
                        <input type="text" name="firebase_vapid_key" value="{{ old('firebase_vapid_key', $firebase['vapidKey']) }}" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary" placeholder="B... ">
                        <p class="text-xs text-gray-500 mt-1">
                            Go to <a href="https://console.firebase.google.com/" target="_blank" class="text-primary hover:underline">Project Settings</a> > Cloud Messaging tab.<br>
                            Scroll down to "Web configuration". If there is no key pair, click <b>Generate key pair</b>. Copy the long key starting with <b>B...</b>
                        </p>
                    </div>

                    <div class="col-span-full border-b pb-2 mb-2 mt-6 border-gray-200 dark:border-gray-700">
                        <h4 class="font-medium text-gray-900 dark:text-white">2. Server API Key (FCM HTTP v1)</h4>
                        <p class="text-xs text-gray-500 mt-1">
                            Go to <a href="https://console.firebase.google.com/" target="_blank" class="text-primary hover:underline">Project Settings</a> > Service accounts tab.<br>
                            Click the <b>Generate new private key</b> button. This will download a <code>.json</code> file to your computer. Open it with any text editor and paste the ENTIRE contents below.
                        </p>
                    </div>

                    <div class="col-span-full">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Service Account JSON</label>
                        <textarea name="firebase_service_account" rows="8" class="w-full rounded-lg border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary font-mono text-sm" placeholder='{&#10;  "type": "service_account",&#10;  "project_id": "...",&#10;  "private_key_id": "...",&#10;  "private_key": "-----BEGIN PRIVATE KEY-----\n...\n-----END PRIVATE KEY-----\n",&#10;  ...&#10;}'>{{ old('firebase_service_account', $firebase['serviceAccount']) }}</textarea>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-6 py-2.5 rounded-lg text-sm font-medium transition-colors">
                        Save Configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

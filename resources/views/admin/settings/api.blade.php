@extends('layouts.admin')

@section('title', 'API Settings')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            API Settings
        </h2>
        <p class="text-sm text-gray-500 mt-1">Configure programmatic access to your store</p>
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
        <form action="{{ route('admin.settings.api.update') }}" method="POST">
            @csrf
            
            <div class="p-6 border-b border-gray-100 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">API Access</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Enable or disable API endpoints for external product synchronization.</p>
                
                <label class="relative inline-flex items-center cursor-pointer mb-6">
                    <input type="checkbox" name="api_enabled" class="sr-only peer" {{ $apiEnabled ? 'checked' : '' }}>
                    <div class="w-14 h-7 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-primary/20 dark:peer-focus:ring-primary/30 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    <span class="ml-3 text-sm font-medium text-gray-900 dark:text-gray-300">Enable Product API</span>
                </label>

                <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-5 border border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">Your API Token</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Include this token in the `Authorization: Bearer` header of your API requests.</p>
                    
                    <div class="flex items-center gap-3">
                        <input type="text" readonly value="{{ $apiToken }}" class="flex-1 font-mono text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2 text-gray-700 dark:text-gray-300 focus:outline-none" id="apiTokenInput">
                        
                        <button type="button" onclick="copyToken()" class="p-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg transition-colors" title="Copy to clipboard">
                            <i data-lucide="copy" class="w-4 h-4"></i>
                        </button>
                        
                        <label class="flex items-center gap-2 p-2 bg-red-50 hover:bg-red-100 text-red-600 rounded-lg transition-colors cursor-pointer text-sm font-medium">
                            <input type="checkbox" name="generate_token" value="1" class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                            Regenerate Token
                        </label>
                    </div>
                </div>
            </div>

            <div class="p-6 bg-gray-50 dark:bg-gray-900/50 flex justify-between items-center">
                <div class="text-xs text-gray-500">
                    <span class="font-bold">Endpoint:</span> <code>POST {{ url('/api/products') }}</code>
                </div>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-secondary transition-colors font-medium flex items-center shadow-sm">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i> Save Settings
                </button>
            </div>
        </form>
    </div>

    <!-- API Documentation Card -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">API Documentation: Create Product</h3>
        
        <div class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
            <p><strong>URL:</strong> <code>POST /api/products</code></p>
            <p><strong>Headers:</strong></p>
            <pre class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-xs">Authorization: Bearer YOUR_API_TOKEN
Accept: application/json
Content-Type: application/json</pre>

            <p><strong>Payload (JSON):</strong></p>
            <pre class="bg-gray-50 dark:bg-gray-900/50 p-3 rounded-lg border border-gray-200 dark:border-gray-700 font-mono text-xs">{
  "name": "Handmade Wooden Bowl",
  "price": 1200.50,
  "compare_at_price": 1500.00, // Optional
  "description": "Beautiful handcrafted bowl...", // Optional
  "status": "published", // default: published
  "image_urls": [ // Optional, will download and link images automatically
    "https://example.com/image1.jpg",
    "https://example.com/image2.jpg"
  ]
}</pre>
        </div>
    </div>
</div>

<script>
    function copyToken() {
        const input = document.getElementById('apiTokenInput');
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value);
        alert("API Token copied to clipboard!");
    }
</script>
@endsection

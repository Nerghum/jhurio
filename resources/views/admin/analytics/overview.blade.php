@extends('layouts.admin')
@section('title', 'Analytics Overview')
@section('header', 'Analytics Overview')
@section('content')

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Date Filter -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Last {{ $days }} Days</h2>
        <form method="GET" action="{{ route('admin.analytics.overview') }}" class="flex items-center space-x-2">
            <select name="days" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
            </select>
            <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Apply
            </button>
        </form>
    </div>

    <!-- Aggregate Metric Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Visitors</p>
                <i data-lucide="users" class="w-4 h-4 text-blue-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalVisitors) }}</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Sessions</p>
                <i data-lucide="activity" class="w-4 h-4 text-green-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSessions) }}</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Pageviews</p>
                <i data-lucide="file-text" class="w-4 h-4 text-purple-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPageviews) }}</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Purchases</p>
                <i data-lucide="shopping-bag" class="w-4 h-4 text-orange-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalPurchases) }}</h4>
        </div>
    </div>

    <!-- Main Chart -->
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Traffic Over Time</h3>
        <div class="relative h-72">
            <canvas id="overviewChart"></canvas>
        </div>
    </div>

    <!-- Data Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        <!-- Top Pages -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Landing Pages</h3>
            </div>
            <div class="p-0">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-500 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 font-medium">Page URL</th>
                            <th class="px-6 py-3 font-medium text-right">Views</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($topPages as $page)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-3 truncate max-w-xs" title="{{ $page->page_url }}">{{ \Illuminate\Support\Str::limit(str_replace(url('/'), '', $page->page_url) ?: '/', 50) }}</td>
                            <td class="px-6 py-3 text-right font-medium text-gray-900 dark:text-white">{{ number_format($page->views) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-4 text-center text-gray-500">No page views recorded yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Devices -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Device Breakdown</h3>
            </div>
            <div class="p-6 flex flex-col items-center justify-center h-[300px]">
                @if($devices->count() > 0)
                    <canvas id="deviceChart"></canvas>
                @else
                    <p class="text-gray-500">No device data available.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Timeline Chart
        const ctx = document.getElementById('overviewChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode(array_reverse($chartDates)) !!},
                datasets: [
                    {
                        label: 'Pageviews',
                        data: {!! json_encode(array_values(array_reverse($pageviewData))) !!},
                        borderColor: '#3b82f6', // Blue
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Unique Visitors',
                        data: {!! json_encode(array_values(array_reverse($visitorData))) !!},
                        borderColor: '#8b5cf6', // Purple
                        backgroundColor: 'rgba(139, 92, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });

        // Device Chart
        @if($devices->count() > 0)
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($devices->pluck('device')) !!},
                datasets: [{
                    data: {!! json_encode($devices->pluck('total')) !!},
                    backgroundColor: [
                        '#3b82f6', // Mobile
                        '#10b981', // Desktop
                        '#f59e0b', // Tablet
                        '#6b7280'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
        @endif
    });
</script>
@endpush

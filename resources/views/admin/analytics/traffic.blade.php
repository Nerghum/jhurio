@extends('layouts.admin')
@section('title', 'Traffic Sources')
@section('header', 'Traffic Sources')
@section('content')

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Date Filter -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Last {{ $days }} Days</h2>
        <form method="GET" action="{{ route('admin.analytics.traffic') }}" class="flex items-center space-x-2">
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

    <!-- Charts Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Sources Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">UTM Sources</h3>
            </div>
            <div class="p-6 h-[300px] flex items-center justify-center">
                @if($sources->count() > 0)
                    <canvas id="sourceChart"></canvas>
                @else
                    <p class="text-gray-500">No UTM Source data available.</p>
                @endif
            </div>
        </div>

        <!-- Mediums Chart -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">UTM Mediums</h3>
            </div>
            <div class="p-6 h-[300px] flex items-center justify-center">
                @if($mediums->count() > 0)
                    <canvas id="mediumChart"></canvas>
                @else
                    <p class="text-gray-500">No UTM Medium data available.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Tables Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Top Campaigns -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Campaigns</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 font-medium">Campaign Name</th>
                            <th class="px-6 py-3 font-medium">Source</th>
                            <th class="px-6 py-3 font-medium text-right">Sessions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $campaign->utm_campaign }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $campaign->utm_source ?: 'Unknown' }}</td>
                            <td class="px-6 py-3 text-right font-medium text-blue-600 dark:text-blue-400">{{ number_format($campaign->total) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">No campaign data available.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Referrers -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">External Referrers</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-3 font-medium">Domain</th>
                            <th class="px-6 py-3 font-medium text-right">Sessions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($referrers as $domain => $count)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-3 font-medium text-gray-900 dark:text-white">{{ $domain }}</td>
                            <td class="px-6 py-3 text-right font-medium text-purple-600 dark:text-purple-400">{{ number_format($count) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-gray-500">No external referrers found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bgColors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];

        @if($sources->count() > 0)
        new Chart(document.getElementById('sourceChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($sources->pluck('utm_source')) !!},
                datasets: [{
                    data: {!! json_encode($sources->pluck('total')) !!},
                    backgroundColor: bgColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
        @endif

        @if($mediums->count() > 0)
        new Chart(document.getElementById('mediumChart').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($mediums->pluck('utm_medium')) !!},
                datasets: [{
                    data: {!! json_encode($mediums->pluck('total')) !!},
                    backgroundColor: bgColors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right' } }
            }
        });
        @endif
    });
</script>
@endpush

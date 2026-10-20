@extends('layouts.admin')
@section('title', 'Geographic Analytics')
@section('header', 'Geographic Analytics')
@section('content')

<!-- Leaflet CSS & JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<style>
    /* Pulsing marker CSS */
    .pulse-marker {
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(59, 130, 246, 0.4);
        animation: pulse 2s infinite;
    }
    .pulse-marker-desktop { background-color: #3b82f6; }
    .pulse-marker-mobile { background-color: #10b981; }
    .pulse-marker-tablet { background-color: #f59e0b; }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); }
        100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); }
    }
</style>

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Date Filter -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Last {{ $days }} Days</h2>
        <form method="GET" action="{{ route('admin.analytics.geography') }}" class="flex items-center space-x-2">
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

    <!-- Map and Top Countries -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Bangladesh Map (Live) -->
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Live View (Bangladesh)</h3>
                <div class="flex items-center text-xs text-gray-500">
                    <span class="relative flex h-2 w-2 mr-2">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    Live Updates
                </div>
            </div>
            <div class="p-0" style="height: 500px; position: relative;">
                <div id="bd-map" class="w-full h-full z-10"></div>
                
                <!-- Map Legend -->
                <div class="absolute bottom-4 right-4 bg-white dark:bg-gray-800 p-3 rounded-lg shadow-md z-[400] border border-gray-200 dark:border-gray-700 text-xs">
                    <div class="flex items-center mb-1"><div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div> Desktop</div>
                    <div class="flex items-center mb-1"><div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div> Mobile</div>
                    <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div> Tablet</div>
                </div>
            </div>
        </div>

        <!-- Top Cities List -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Top Bangladesh Cities</h3>
            </div>
            <div class="p-0">
                <ul class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[500px] overflow-y-auto custom-scrollbar">
                    @forelse($cities as $city)
                    <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <div class="flex items-center">
                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400 mr-3"></i>
                            <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $city->city ?: 'Unknown' }}</span>
                        </div>
                        <span class="bg-primary/10 text-primary dark:bg-primary/20 text-sm font-semibold px-2.5 py-0.5 rounded-full">
                            {{ number_format($city->total) }}
                        </span>
                    </li>
                    @empty
                    <li class="px-6 py-8 text-center text-gray-500">No data available</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Map centered on Bangladesh
        var map = L.map('bd-map').setView([23.6850, 90.3563], 7);

        // Add CartoDB Positron basemap (clean, modern look suitable for dashboards)
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; OpenStreetMap & CartoDB',
            subdomains: 'abcd',
            maxZoom: 20
        }).addTo(map);

        var markers = {};

        function fetchLiveLocations() {
            fetch('{{ route('admin.analytics.live_locations') }}')
                .then(response => response.json())
                .then(data => {
                    // Track valid IDs to remove old markers
                    var currentIds = [];

                    data.forEach(function(visitor) {
                        currentIds.push(visitor.id);

                        // If marker exists, update it if needed or just leave it
                        if (markers[visitor.id]) {
                            // Already plotted
                        } else {
                            // Create new pulsing marker
                            let typeClass = 'pulse-marker-desktop';
                            if(visitor.device === 'Mobile') typeClass = 'pulse-marker-mobile';
                            if(visitor.device === 'Tablet') typeClass = 'pulse-marker-tablet';

                            var customIcon = L.divIcon({
                                className: 'pulse-marker ' + typeClass,
                                iconSize: [12, 12],
                                iconAnchor: [6, 6]
                            });

                            // Add a tiny bit of random jitter so multiple users in same city don't perfectly overlap
                            var jitterLat = visitor.lat + (Math.random() - 0.5) * 0.05;
                            var jitterLng = visitor.lng + (Math.random() - 0.5) * 0.05;

                            var marker = L.marker([jitterLat, jitterLng], {icon: customIcon}).addTo(map);
                            marker.bindPopup(`<b>User (${visitor.device})</b><br>${visitor.city}<br>Active ${visitor.active_seconds_ago}s ago`);
                            
                            markers[visitor.id] = marker;
                        }
                    });

                    // Remove offline markers
                    Object.keys(markers).forEach(function(id) {
                        if (!currentIds.includes(id)) {
                            map.removeLayer(markers[id]);
                            delete markers[id];
                        }
                    });
                })
                .catch(err => console.error(err));
        }

        // Initial fetch and poll every 5 seconds
        fetchLiveLocations();
        setInterval(fetchLiveLocations, 5000);
    });
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Live User Tracking & Analytics')
@section('header', 'Live User Tracking')

@section('content')
<div class="max-w-7xl mx-auto space-y-6 pb-12" x-data="liveDashboard()" x-init="init()">
    
    <!-- Top Live Control Header -->
    <div class="bg-gradient-to-r from-gray-900 via-gray-800 to-gray-900 text-white rounded-2xl p-6 shadow-xl border border-gray-700/50 relative overflow-hidden">
        <!-- Background subtle glow decoration -->
        <div class="absolute -right-16 -top-16 w-64 h-64 bg-primary/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-16 -bottom-16 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <div class="relative flex h-6 w-6 shrink-0 mt-1 sm:mt-0">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-6 w-6 bg-emerald-500 items-center justify-center text-[10px] font-bold text-black">●</span>
                </div>
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight flex items-center gap-2">
                            <span x-text="data.live_users" class="text-emerald-400 text-3xl sm:text-4xl font-mono">0</span>
                            <span>Live Online <span class="hidden sm:inline">Users</span></span>
                        </h2>
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Active Now
                        </span>
                    </div>
                    <p class="text-xs sm:text-sm text-gray-400 mt-1">
                        Real-time visitor monitoring with live IP, location, device, and active browsing activity.
                    </p>
                </div>
            </div>

            <!-- Controls: Auto-refresh & Manual Refresh -->
            <div class="flex flex-wrap items-center gap-3 bg-gray-800/80 p-2.5 rounded-xl border border-gray-700/70 backdrop-blur-sm self-start lg:self-center">
                <div class="text-xs text-gray-300 px-2 flex items-center gap-1.5">
                    <i data-lucide="clock" class="w-3.5 h-3.5 text-gray-400"></i>
                    <span>Next update in <strong class="font-mono text-emerald-400" x-text="countdown + 's'">10s</strong></span>
                </div>

                <button type="button" 
                        @click="toggleAutoRefresh()" 
                        class="px-2.5 py-1.5 rounded-lg text-xs font-medium transition-colors border"
                        :class="autoRefresh ? 'bg-gray-700/60 text-gray-300 border-gray-600 hover:bg-gray-700' : 'bg-amber-500/20 text-amber-300 border-amber-500/30'">
                    <span x-text="autoRefresh ? 'Pause' : 'Resume'">Pause</span>
                </button>

                <button type="button" 
                        @click="fetchData(true)" 
                        :disabled="loading"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-500 hover:bg-emerald-600 text-gray-950 transition-all flex items-center gap-1.5 shadow-sm disabled:opacity-50">
                    <i data-lucide="refresh-cw" class="w-3.5 h-3.5" :class="loading ? 'animate-spin' : ''"></i>
                    <span>Refresh</span>
                </button>

                <a href="{{ route('home') }}" target="_blank" 
                   class="px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-700 hover:bg-gray-600 text-white transition-colors flex items-center gap-1 border border-gray-600"
                   title="Open storefront in a new tab to test live tracking">
                    <span>Test Store</span>
                    <i data-lucide="external-link" class="w-3 h-3 text-gray-400"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Stats Summary Banner -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Today's Visitors</p>
            <h4 class="text-xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_visitors">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">New Visitors</p>
            <h4 class="text-xl font-bold text-blue-600 dark:text-blue-400 mt-1" x-text="data.today_new">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Returning</p>
            <h4 class="text-xl font-bold text-purple-600 dark:text-purple-400 mt-1" x-text="data.today_returning">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Sessions</p>
            <h4 class="text-xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_sessions">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pageviews</p>
            <h4 class="text-xl font-bold text-emerald-600 dark:text-emerald-400 mt-1" x-text="data.today_pageviews">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Purchases</p>
            <h4 class="text-xl font-bold text-amber-600 dark:text-amber-400 mt-1" x-text="data.today_purchases">0</h4>
        </div>
    </div>

    <!-- MAIN CONSOLE: Live Users & Visitors with IP, Location & Device Info -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        
        <!-- Tab Navigation & Toolbar -->
        <div class="p-5 border-b border-gray-200 dark:border-gray-700 bg-gray-50/70 dark:bg-gray-800/60 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            
            <!-- Tabs -->
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" 
                        @click="activeTab = 'live'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-sm"
                        :class="activeTab === 'live' ? 'bg-primary text-white shadow-primary/20' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600'">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    <span>Live Online Users</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                          :class="activeTab === 'live' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300'"
                          x-text="data.live_visitors.length">0</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'recent'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-sm"
                        :class="activeTab === 'recent' ? 'bg-primary text-white shadow-primary/20' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600'">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Recent Visitors</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                          :class="activeTab === 'recent' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'"
                          x-text="data.recent_visitors.length">0</span>
                </button>

                <button type="button" 
                        @click="activeTab = 'feed'"
                        class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 shadow-sm"
                        :class="activeTab === 'feed' ? 'bg-primary text-white shadow-primary/20' : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600'">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                    <span>Real-Time Feed</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-bold"
                          :class="activeTab === 'feed' ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'"
                          x-text="data.feed.length">0</span>
                </button>
            </div>

            <!-- Search & Filters Toolbar -->
            <div class="flex flex-wrap items-center gap-3">
                <!-- Search input -->
                <div class="relative w-full sm:w-64">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Search IP, City, Device..."
                           class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary">
                    <button x-show="searchQuery" 
                            @click="searchQuery = ''" 
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs">✕</button>
                </div>

                <!-- Device Filter Pills -->
                <div class="flex items-center gap-1 bg-white dark:bg-gray-700 p-1 rounded-xl border border-gray-200 dark:border-gray-600 text-xs">
                    <button type="button" 
                            @click="deviceFilter = 'all'" 
                            class="px-2.5 py-1 rounded-lg font-medium transition-colors"
                            :class="deviceFilter === 'all' ? 'bg-primary text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                        All
                    </button>
                    <button type="button" 
                            @click="deviceFilter = 'mobile'" 
                            class="px-2.5 py-1 rounded-lg font-medium transition-colors flex items-center gap-1"
                            :class="deviceFilter === 'mobile' ? 'bg-primary text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                        <i data-lucide="smartphone" class="w-3 h-3"></i> Mobile
                    </button>
                    <button type="button" 
                            @click="deviceFilter = 'desktop'" 
                            class="px-2.5 py-1 rounded-lg font-medium transition-colors flex items-center gap-1"
                            :class="deviceFilter === 'desktop' ? 'bg-primary text-white' : 'text-gray-600 dark:text-gray-300 hover:text-gray-900'">
                        <i data-lucide="monitor" class="w-3 h-3"></i> Desktop
                    </button>
                </div>

                <!-- View Toggle: Cards vs Table -->
                <div class="hidden sm:flex items-center gap-1 bg-white dark:bg-gray-700 p-1 rounded-xl border border-gray-200 dark:border-gray-600">
                    <button type="button" 
                            @click="viewMode = 'cards'" 
                            title="Detailed Cards View"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="viewMode === 'cards' ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'">
                        <i data-lucide="layout-grid" class="w-4 h-4"></i>
                    </button>
                    <button type="button" 
                            @click="viewMode = 'table'" 
                            title="Compact Table View"
                            class="p-1.5 rounded-lg transition-colors"
                            :class="viewMode === 'table' ? 'bg-primary text-white' : 'text-gray-500 hover:text-gray-900 dark:text-gray-400'">
                        <i data-lucide="table" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- TAB 1: LIVE ONLINE USERS & TAB 2: RECENT VISITORS -->
        <div x-show="activeTab === 'live' || activeTab === 'recent'" class="p-6">
            
            <!-- Empty State for Live Users -->
            <template x-if="getCurrentVisitors().length === 0">
                <div class="py-14 px-6 text-center max-w-md mx-auto">
                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-700/50 flex items-center justify-center mx-auto mb-4 text-gray-400">
                        <i data-lucide="user-x" class="w-8 h-8"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white" 
                        x-text="activeTab === 'live' ? 'No users active in the last 10 minutes' : 'No visitors match your criteria'"></h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
                        <span x-show="activeTab === 'live'">As soon as someone visits your store, their live IP, city, country, device, and active browsing page will show up here automatically.</span>
                        <span x-show="activeTab === 'recent'">Try clearing your search query or filters.</span>
                    </p>
                    <div class="mt-6 flex flex-wrap justify-center gap-3">
                        <button x-show="activeTab === 'live'" 
                                type="button" 
                                @click="activeTab = 'recent'" 
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors">
                            View Recent Visitors
                        </button>
                        <a href="{{ route('home') }}" target="_blank" 
                           class="px-4 py-2 rounded-xl text-xs font-semibold bg-primary hover:bg-primary/90 text-white transition-colors flex items-center gap-1.5 shadow-sm shadow-primary/20">
                            <span>Open Storefront to Test</span>
                            <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                        </a>
                    </div>
                </div>
            </template>

            <!-- 1. DETAILED CARDS VIEW -->
            <div x-show="viewMode === 'cards' && getCurrentVisitors().length > 0" 
                 class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                
                <template x-for="visitor in getCurrentVisitors()" :key="visitor.visitor_id">
                    <div class="bg-white dark:bg-gray-900/70 rounded-2xl p-5 border transition-all duration-200 hover:shadow-md relative overflow-hidden flex flex-col justify-between"
                         :class="visitor.is_active_now ? 'border-emerald-500/40 shadow-emerald-500/5 ring-1 ring-emerald-500/20' : 'border-gray-200 dark:border-gray-700'">
                        
                        <!-- Top Active Banner & Badges -->
                        <div>
                            <div class="flex items-center justify-between mb-3.5">
                                <div class="flex items-center gap-2">
                                    <!-- Live Beacon -->
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold"
                                          :class="visitor.is_active_now ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400'">
                                        <span class="w-2 h-2 rounded-full mr-1.5" 
                                              :class="visitor.is_active_now ? 'bg-emerald-500 animate-ping' : 'bg-gray-400'"></span>
                                        <span x-text="visitor.is_active_now ? 'ACTIVE NOW' : visitor.last_seen_human">Active Now</span>
                                    </span>

                                    <!-- Visitor Status Badge -->
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                          :class="visitor.is_returning ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'"
                                          x-text="visitor.is_returning ? 'Returning (' + visitor.total_sessions + ' visits)' : 'New Visitor'"></span>
                                </div>

                                <div class="text-[11px] font-mono text-gray-400 flex items-center gap-1">
                                    <i data-lucide="timer" class="w-3 h-3"></i>
                                    <span x-text="visitor.session_duration">0s</span>
                                </div>
                            </div>

                            <!-- User IP Address Box (High Visibility) -->
                            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/80 mb-3.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">User IP Address</span>
                                    <a :href="'https://whatismyipaddress.com/ip/' + visitor.ip_address" target="_blank" 
                                       class="text-[10px] text-primary hover:underline flex items-center gap-1"
                                       title="External IP Lookup">
                                        <span>Lookup IP</span>
                                        <i data-lucide="external-link" class="w-2.5 h-2.5"></i>
                                    </a>
                                </div>
                                <div class="flex items-center justify-between mt-1">
                                    <span class="font-mono text-base font-extrabold text-gray-900 dark:text-white tracking-wide" x-text="visitor.ip_address">127.0.0.1</span>
                                    <button type="button" 
                                            @click="copyText(visitor.ip_address)" 
                                            title="Copy IP Address"
                                            class="px-2 py-1 rounded-md text-xs font-medium bg-white dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 transition-colors flex items-center gap-1">
                                        <i data-lucide="copy" class="w-3 h-3"></i>
                                        <span class="text-[10px]">Copy</span>
                                    </button>
                                </div>
                            </div>

                            <!-- User Location Box -->
                            <div class="p-3.5 rounded-xl bg-gray-50 dark:bg-gray-800/80 border border-gray-100 dark:border-gray-700/80 mb-3.5">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] uppercase font-bold tracking-wider text-gray-400">Location</span>
                                    <template x-if="visitor.has_coordinates">
                                        <a :href="'https://www.google.com/maps?q=' + visitor.lat + ',' + visitor.lon" target="_blank"
                                           class="text-[10px] text-emerald-600 dark:text-emerald-400 hover:underline flex items-center gap-0.5">
                                            <i data-lucide="map-pin" class="w-3 h-3"></i>
                                            <span>Map</span>
                                        </a>
                                    </template>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl" x-text="visitor.flag">🇧🇩</span>
                                    <div class="truncate">
                                        <div class="font-bold text-gray-900 dark:text-white text-sm truncate" x-text="visitor.city || 'City Unknown'">Dhaka</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                            <span x-text="visitor.division ? visitor.division + ', ' : ''"></span>
                                            <span x-text="visitor.country || 'Bangladesh'"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Device & Environment Grid (4 Specs) -->
                            <div class="grid grid-cols-2 gap-2 mb-3.5 text-xs">
                                <!-- Device -->
                                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
                                    <span class="text-[10px] text-gray-400 block">Device</span>
                                    <div class="font-bold text-gray-800 dark:text-gray-200 flex items-center gap-1.5 mt-0.5">
                                        <i :data-lucide="visitor.device_icon" class="w-3.5 h-3.5 text-primary"></i>
                                        <span x-text="visitor.device">Desktop</span>
                                    </div>
                                </div>

                                <!-- OS -->
                                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
                                    <span class="text-[10px] text-gray-400 block">Operating System</span>
                                    <div class="font-bold text-gray-800 dark:text-gray-200 mt-0.5 truncate" x-text="visitor.os">OS X</div>
                                </div>

                                <!-- Browser -->
                                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
                                    <span class="text-[10px] text-gray-400 block">Browser</span>
                                    <div class="font-bold text-gray-800 dark:text-gray-200 mt-0.5 truncate" x-text="visitor.browser">Chrome</div>
                                </div>

                                <!-- Screen & Language -->
                                <div class="p-2.5 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-100 dark:border-gray-700/50">
                                    <span class="text-[10px] text-gray-400 block">Screen / Lang</span>
                                    <div class="font-mono font-medium text-gray-800 dark:text-gray-200 mt-0.5 text-[11px] truncate">
                                        <span x-text="visitor.screen_size">1920x1080</span>
                                        <span class="text-gray-400 text-[10px]" x-text="'(' + visitor.language + ')'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Currently Viewing / Last Page -->
                            <div class="p-3 rounded-xl bg-primary/5 dark:bg-primary/10 border border-primary/20 mb-4">
                                <div class="flex items-center justify-between text-[10px] font-bold uppercase tracking-wider text-primary mb-1">
                                    <span class="flex items-center gap-1">
                                        <i data-lucide="compass" class="w-3 h-3"></i> Currently Viewing
                                    </span>
                                    <span class="px-1.5 py-0.5 rounded text-[9px] bg-primary/20 text-primary font-bold uppercase" x-text="visitor.latest_event_type">page_view</span>
                                </div>
                                <div class="font-semibold text-gray-900 dark:text-white text-xs truncate" x-text="visitor.current_page_title">Homepage</div>
                                <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5 font-mono" x-text="visitor.current_page">https://jhurio.com/</div>
                            </div>
                        </div>

                        <!-- Card Footer: Inspect Button & Traffic Source -->
                        <div class="pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between text-xs">
                            <span class="text-[11px] text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                <i data-lucide="share-2" class="w-3 h-3 text-gray-400"></i>
                                <span x-text="visitor.traffic_source">Direct</span>
                            </span>

                            <button type="button" 
                                    @click="openVisitorModal(visitor)"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors flex items-center gap-1.5">
                                <i data-lucide="info" class="w-3.5 h-3.5 text-primary"></i>
                                <span>Session Log</span>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- 2. COMPACT TABLE VIEW -->
            <div x-show="viewMode === 'table' && getCurrentVisitors().length > 0" 
                 class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 uppercase font-semibold">
                        <tr>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">User IP Address</th>
                            <th class="px-4 py-3">Location</th>
                            <th class="px-4 py-3">Device / OS</th>
                            <th class="px-4 py-3">Browser</th>
                            <th class="px-4 py-3">Active Page</th>
                            <th class="px-4 py-3">Time on Site</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        <template x-for="visitor in getCurrentVisitors()" :key="visitor.visitor_id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                                <!-- Status -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold"
                                          :class="visitor.is_active_now ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1" :class="visitor.is_active_now ? 'bg-emerald-500 animate-ping' : 'bg-gray-400'"></span>
                                        <span x-text="visitor.is_active_now ? 'Active Now' : visitor.last_seen_human"></span>
                                    </span>
                                </td>

                                <!-- IP -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="visitor.ip_address"></span>
                                        <button type="button" @click="copyText(visitor.ip_address)" class="text-gray-400 hover:text-primary p-0.5">
                                            <i data-lucide="copy" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                </td>

                                <!-- Location -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-lg" x-text="visitor.flag">🇧🇩</span>
                                        <div>
                                            <span class="font-semibold text-gray-900 dark:text-white block" x-text="visitor.city"></span>
                                            <span class="text-[10px] text-gray-400 block" x-text="visitor.country"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Device & OS -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <i :data-lucide="visitor.device_icon" class="w-4 h-4 text-primary"></i>
                                        <div>
                                            <span class="font-bold text-gray-900 dark:text-white block" x-text="visitor.device"></span>
                                            <span class="text-[10px] text-gray-400 block" x-text="visitor.os"></span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Browser -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-medium text-gray-800 dark:text-gray-200 block" x-text="visitor.browser"></span>
                                    <span class="text-[10px] font-mono text-gray-400 block" x-text="visitor.screen_size"></span>
                                </td>

                                <!-- Current Page -->
                                <td class="px-4 py-3.5 max-w-xs truncate">
                                    <div class="font-semibold text-gray-900 dark:text-white truncate" x-text="visitor.current_page_title"></div>
                                    <div class="text-[10px] text-gray-400 truncate font-mono" x-text="visitor.current_page"></div>
                                </td>

                                <!-- Time on Site -->
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="font-mono text-gray-700 dark:text-gray-300 font-semibold" x-text="visitor.session_duration"></span>
                                    <span class="text-[10px] text-gray-400 block" x-text="visitor.traffic_source"></span>
                                </td>

                                <!-- Actions -->
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <button type="button" 
                                            @click="openVisitorModal(visitor)"
                                            class="p-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 transition-colors">
                                        <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 3: REAL-TIME ACTIVITY FEED -->
        <div x-show="activeTab === 'feed'" class="p-6">
            <div class="overflow-hidden">
                <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                    <template x-for="event in data.feed" :key="event.id">
                        <li class="py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors rounded-xl px-4">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-start gap-3">
                                    <span class="inline-flex items-center justify-center h-9 w-9 rounded-xl shrink-0 mt-0.5" 
                                          :class="getIconBg(event.type)">
                                        <i :data-lucide="getIcon(event.type)" class="h-4 w-4" :class="getIconColor(event.type)"></i>
                                    </span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            User <span x-text="getEventText(event)" class="text-primary font-bold"></span>
                                        </p>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex flex-wrap items-center gap-x-3 gap-y-1">
                                            <!-- IP Badge -->
                                            <span class="font-mono font-bold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded text-[11px]" x-text="event.ip"></span>
                                            <!-- Location -->
                                            <span class="flex items-center gap-1">
                                                <span x-text="event.flag">🇧🇩</span>
                                                <span x-text="event.city + ', ' + event.country"></span>
                                            </span>
                                            <!-- Device -->
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="monitor" class="w-3 h-3"></i>
                                                <span x-text="event.device + (event.browser ? ' (' + event.browser + ')' : '')"></span>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <span class="text-xs text-gray-400 font-mono shrink-0" x-text="event.time"></span>
                            </div>
                        </li>
                    </template>
                    <li x-show="data.feed.length === 0" class="py-12 text-center text-gray-500 dark:text-gray-400">
                        Waiting for real-time visitor activity...
                    </li>
                </ul>
            </div>
        </div>

    </div>

    <!-- Ecommerce Funnel Overview Cards -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 shadow-sm border border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-4">Today's Conversion Funnel</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-4 border border-blue-100 dark:border-blue-800/30">
                <div class="flex items-center justify-between text-blue-600 dark:text-blue-400 text-xs font-semibold mb-1">
                    <span>Product Views</span>
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_product_views">0</h4>
            </div>
            <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-4 border border-yellow-100 dark:border-yellow-800/30">
                <div class="flex items-center justify-between text-yellow-600 dark:text-yellow-400 text-xs font-semibold mb-1">
                    <span>Add to Cart</span>
                    <i data-lucide="shopping-cart" class="w-4 h-4"></i>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_add_cart">0</h4>
            </div>
            <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-4 border border-orange-100 dark:border-orange-800/30">
                <div class="flex items-center justify-between text-orange-600 dark:text-orange-400 text-xs font-semibold mb-1">
                    <span>Checkouts</span>
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_checkout">0</h4>
            </div>
            <div class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-800/30">
                <div class="flex items-center justify-between text-emerald-600 dark:text-emerald-400 text-xs font-semibold mb-1">
                    <span>Purchases</span>
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                </div>
                <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_purchases">0</h4>
            </div>
        </div>
    </div>

    <!-- DETAIL MODAL: Visitor Session Timeline & Technical Specs -->
    <div x-show="selectedVisitor !== null" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4"
         style="display: none;">
        
        <div @click.away="selectedVisitor = null" 
             class="bg-white dark:bg-gray-800 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-700 max-h-[90vh] overflow-y-auto custom-scrollbar">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span x-text="selectedVisitor?.flag"></span>
                        <span>Visitor Intelligence Dossier</span>
                    </h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-mono mt-0.5" x-text="'ID: ' + selectedVisitor?.visitor_id"></p>
                </div>
                <button type="button" @click="selectedVisitor = null" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 p-1 rounded-lg">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="mt-5 space-y-5 text-xs" x-show="selectedVisitor">
                
                <!-- IP & Location Banner -->
                <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900/60 border border-gray-200 dark:border-gray-700 grid grid-cols-2 gap-4">
                    <div>
                        <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">IP Address</span>
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-base font-bold text-gray-900 dark:text-white" x-text="selectedVisitor?.ip_address"></span>
                            <button type="button" @click="copyText(selectedVisitor?.ip_address)" class="text-primary hover:underline text-[11px]">Copy</button>
                        </div>
                    </div>
                    <div>
                        <span class="text-[10px] uppercase font-bold text-gray-400 block mb-1">Geographic Location</span>
                        <div class="font-bold text-gray-900 dark:text-white text-sm" x-text="selectedVisitor?.location_display"></div>
                    </div>
                </div>

                <!-- Technical Specs Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] text-gray-400 block">Device</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200" x-text="selectedVisitor?.device"></span>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] text-gray-400 block">Operating System</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200" x-text="selectedVisitor?.os"></span>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] text-gray-400 block">Browser</span>
                        <span class="font-bold text-gray-800 dark:text-gray-200" x-text="selectedVisitor?.browser"></span>
                    </div>
                    <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                        <span class="text-[10px] text-gray-400 block">Resolution</span>
                        <span class="font-mono font-bold text-gray-800 dark:text-gray-200" x-text="selectedVisitor?.screen_size"></span>
                    </div>
                </div>

                <!-- Full User Agent String -->
                <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-900/40 border border-gray-100 dark:border-gray-700/60">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-[10px] uppercase font-bold text-gray-400">User Agent String</span>
                        <button type="button" @click="copyText(selectedVisitor?.user_agent)" class="text-primary hover:underline text-[10px]">Copy String</button>
                    </div>
                    <div class="font-mono text-[11px] text-gray-600 dark:text-gray-300 break-all bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-200 dark:border-gray-700" 
                         x-text="selectedVisitor?.user_agent"></div>
                </div>

                <!-- Recent Browsing Timeline -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400 mb-2.5">Session Action Timeline</h4>
                    <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-3.5 border border-gray-100 dark:border-gray-700/60">
                        <ul class="space-y-2.5" x-show="selectedVisitor?.recent_events?.length > 0">
                            <template x-for="(ev, idx) in selectedVisitor?.recent_events" :key="ev.id || idx">
                                <li class="flex items-center justify-between py-1 border-b border-gray-200/50 dark:border-gray-700/50 last:border-0">
                                    <div class="flex items-center gap-2 truncate">
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase" 
                                              :class="getIconBg(ev.type) + ' ' + getIconColor(ev.type)"
                                              x-text="ev.type"></span>
                                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate" x-text="ev.title || ev.product_name || ev.url"></span>
                                    </div>
                                    <span class="text-[10px] font-mono text-gray-400 shrink-0" x-text="ev.time"></span>
                                </li>
                            </template>
                        </ul>
                        <div x-show="!selectedVisitor?.recent_events || selectedVisitor?.recent_events?.length === 0" class="text-gray-400 py-3 text-center">
                            No detailed event history recorded for this session.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                <button type="button" @click="selectedVisitor = null" 
                        class="px-4 py-2 rounded-xl text-xs font-semibold bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 transition-colors">
                    Close Dossier
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Copy -->
    <div x-show="toastMessage" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 bg-gray-900 text-white px-4 py-2.5 rounded-xl shadow-xl text-xs font-semibold border border-gray-700 flex items-center gap-2"
         style="display: none;">
        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
        <span x-text="toastMessage"></span>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function liveDashboard() {
        return {
            data: {
                live_users: 0,
                live_visitors: [],
                recent_visitors: [],
                today_visitors: 0,
                today_new: 0,
                today_returning: 0,
                today_sessions: 0,
                today_pageviews: 0,
                today_product_views: 0,
                today_add_cart: 0,
                today_checkout: 0,
                today_purchases: 0,
                feed: []
            },
            activeTab: 'live',
            viewMode: 'cards', // 'cards' or 'table'
            searchQuery: '',
            deviceFilter: 'all', // 'all', 'mobile', 'desktop'
            loading: false,
            autoRefresh: true,
            countdown: 10,
            timer: null,
            countdownTimer: null,
            selectedVisitor: null,
            toastMessage: '',

            init() {
                this.fetchData();
                this.startTimers();
            },

            startTimers() {
                clearInterval(this.timer);
                clearInterval(this.countdownTimer);

                // Polling timer
                this.timer = setInterval(() => {
                    if (this.autoRefresh) {
                        this.fetchData();
                    }
                }, 10000);

                // Countdown ticker
                this.countdownTimer = setInterval(() => {
                    if (this.autoRefresh) {
                        this.countdown--;
                        if (this.countdown <= 0) {
                            this.countdown = 10;
                        }
                    }
                }, 1000);
            },

            toggleAutoRefresh() {
                this.autoRefresh = !this.autoRefresh;
                if (this.autoRefresh) {
                    this.countdown = 10;
                    this.fetchData();
                }
            },

            fetchData(manual = false) {
                this.loading = true;
                if (manual) this.countdown = 10;

                fetch('{{ route('admin.analytics.live_data') }}')
                    .then(res => res.json())
                    .then(res => {
                        this.data = res;
                        this.loading = false;
                        setTimeout(() => {
                            if (window.lucide) {
                                lucide.createIcons();
                            }
                        }, 50);
                    })
                    .catch(err => {
                        console.error('Error fetching live data:', err);
                        this.loading = false;
                    });
            },

            getCurrentVisitors() {
                const list = this.activeTab === 'live' ? (this.data.live_visitors || []) : (this.data.recent_visitors || []);
                
                return list.filter(v => {
                    // Device filter
                    if (this.deviceFilter === 'mobile' && v.device?.toLowerCase() !== 'mobile') return false;
                    if (this.deviceFilter === 'desktop' && v.device?.toLowerCase() !== 'desktop') return false;

                    // Search filter
                    if (this.searchQuery) {
                        const q = this.searchQuery.toLowerCase();
                        const ipMatch = (v.ip_address || '').toLowerCase().includes(q);
                        const cityMatch = (v.city || '').toLowerCase().includes(q);
                        const countryMatch = (v.country || '').toLowerCase().includes(q);
                        const deviceMatch = (v.device || '').toLowerCase().includes(q);
                        const osMatch = (v.os || '').toLowerCase().includes(q);
                        const browserMatch = (v.browser || '').toLowerCase().includes(q);
                        const pageMatch = (v.current_page || '').toLowerCase().includes(q) || (v.current_page_title || '').toLowerCase().includes(q);

                        return ipMatch || cityMatch || countryMatch || deviceMatch || osMatch || browserMatch || pageMatch;
                    }

                    return true;
                });
            },

            copyText(text) {
                if (!text) return;
                navigator.clipboard.writeText(text).then(() => {
                    this.showToast('Copied: ' + text);
                }).catch(() => {
                    this.showToast('Copied to clipboard');
                });
            },

            showToast(msg) {
                this.toastMessage = msg;
                setTimeout(() => {
                    this.toastMessage = '';
                }, 2500);
            },

            openVisitorModal(visitor) {
                this.selectedVisitor = visitor;
                setTimeout(() => {
                    if (window.lucide) {
                        lucide.createIcons();
                    }
                }, 50);
            },

            getIcon(type) {
                switch(type) {
                    case 'page_view': return 'file-text';
                    case 'product_view': return 'eye';
                    case 'add_to_cart': return 'shopping-cart';
                    case 'checkout_start': return 'credit-card';
                    case 'purchase': return 'check-circle';
                    case 'search': return 'search';
                    default: return 'activity';
                }
            },

            getIconBg(type) {
                switch(type) {
                    case 'page_view': return 'bg-gray-100 dark:bg-gray-800';
                    case 'product_view': return 'bg-blue-100 dark:bg-blue-900/30';
                    case 'add_to_cart': return 'bg-yellow-100 dark:bg-yellow-900/30';
                    case 'checkout_start': return 'bg-orange-100 dark:bg-orange-900/30';
                    case 'purchase': return 'bg-emerald-100 dark:bg-emerald-900/30';
                    case 'search': return 'bg-purple-100 dark:bg-purple-900/30';
                    default: return 'bg-gray-100 dark:bg-gray-800';
                }
            },

            getIconColor(type) {
                switch(type) {
                    case 'page_view': return 'text-gray-600 dark:text-gray-400';
                    case 'product_view': return 'text-blue-600 dark:text-blue-400';
                    case 'add_to_cart': return 'text-yellow-600 dark:text-yellow-400';
                    case 'checkout_start': return 'text-orange-600 dark:text-orange-400';
                    case 'purchase': return 'text-emerald-600 dark:text-emerald-400';
                    case 'search': return 'text-purple-600 dark:text-purple-400';
                    default: return 'text-gray-600 dark:text-gray-400';
                }
            },

            getEventText(event) {
                switch(event.type) {
                    case 'page_view': return 'viewed ' + (event.title || event.url);
                    case 'product_view': return 'viewed product: ' + (event.product_name || 'Item');
                    case 'add_to_cart': return 'added to cart: ' + (event.product_name || 'Item');
                    case 'checkout_start': return 'started checkout';
                    case 'purchase': return 'completed purchase!';
                    case 'search': return 'searched products';
                    default: return 'action: ' + event.type;
                }
            }
        }
    }
</script>
@endpush

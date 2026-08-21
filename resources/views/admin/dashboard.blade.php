@extends('layouts.admin')

@section('title', 'Dashboard Overview - Outfitt')
@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8">

    <!-- Welcome Section -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl p-6 md:p-8 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-purple-500/5 rounded-full blur-3xl -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-pink-500/5 rounded-full blur-3xl translate-y-1/2 -translate-x-1/2"></div>
        <div class="relative">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-slate-400">Welcome back,</p>
                    <h2 class="text-2xl md:text-3xl font-extrabold text-white mt-1">{{ auth()->user()->name }}</h2>
                    <p class="text-slate-300 text-sm mt-1">Here's what's happening with your store today.</p>
                </div>
                <div class="hidden md:flex items-center space-x-2 px-4 py-2 bg-slate-800/50 border border-slate-700/50 rounded-xl">
                    <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-sm font-semibold text-slate-400">{{ now()->format('l, F d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Statistics Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

        <!-- Total Revenue -->
        <div class="group bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-6 rounded-2xl shadow-lg hover:border-purple-500/30 hover:shadow-purple-500/5 transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-purple-500/5 rounded-full blur-2xl group-hover:bg-purple-500/10 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-purple-500/20 to-purple-600/10 border border-purple-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full border border-emerald-500/20">+12.5%</span>
            </div>
            <p class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Total Revenue</p>
            <h3 class="text-3xl font-extrabold text-white mt-1.5">৳{{ number_format($totalRevenue, 2) }}</h3>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full w-3/4 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full"></div>
            </div>
            <p class="text-sm text-slate-600 mt-2">Revenue from delivered orders</p>
        </div>

        <!-- Total Orders -->
        <div class="group bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-6 rounded-2xl shadow-lg hover:border-blue-500/30 hover:shadow-blue-500/5 transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-blue-500/20 to-blue-600/10 border border-blue-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-blue-400 bg-blue-500/10 px-2 py-1 rounded-full border border-blue-500/20">+5.2%</span>
            </div>
            <p class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Total Orders</p>
            <h3 class="text-3xl font-extrabold text-white mt-1.5">{{ $ordersCount }}</h3>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full w-1/2 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full"></div>
            </div>
            <p class="text-sm text-slate-600 mt-2">Received through store checkout</p>
        </div>

        <!-- Active Products -->
        <div class="group bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-6 rounded-2xl shadow-lg hover:border-emerald-500/30 hover:shadow-emerald-500/5 transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-emerald-500/20 to-emerald-600/10 border border-emerald-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <span class="text-sm font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full border border-emerald-500/20">Active</span>
            </div>
            <p class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Active Products</p>
            <h3 class="text-3xl font-extrabold text-white mt-1.5">{{ $productsCount }}</h3>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full w-[60%] bg-gradient-to-r from-emerald-500 to-teal-500 rounded-full"></div>
            </div>
            <p class="text-sm text-slate-600 mt-2">Listed in catalog</p>
        </div>

        <!-- Out Of Stock -->
        <div class="group bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-6 rounded-2xl shadow-lg hover:border-pink-500/30 hover:shadow-pink-500/5 transition-all duration-300 relative overflow-hidden">
            <div class="absolute -right-8 -top-8 w-24 h-24 bg-pink-500/5 rounded-full blur-2xl group-hover:bg-pink-500/10 transition-all duration-500"></div>
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-gradient-to-br from-pink-500/20 to-pink-600/10 border border-pink-500/20 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                @if($outOfStockCount > 0)
                    <span class="text-sm font-semibold text-pink-400 bg-pink-500/10 px-2 py-1 rounded-full border border-pink-500/20">Needs attention</span>
                @else
                    <span class="text-sm font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-1 rounded-full border border-emerald-500/20">All good</span>
                @endif
            </div>
            <p class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Out of Stock</p>
            <h3 class="text-3xl font-extrabold text-white mt-1.5">{{ $outOfStockCount }}</h3>
            <div class="mt-3 h-1.5 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full {{ $outOfStockCount > 0 ? 'w-1/4 bg-gradient-to-r from-pink-500 to-rose-500' : 'w-0' }} rounded-full"></div>
            </div>
            <p class="text-sm text-slate-600 mt-2">Requires stock replenishment</p>
        </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 md:px-8 py-5 border-b border-slate-800/50 flex justify-between items-center bg-slate-900/30">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500/20 to-pink-500/10 border border-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="font-bold text-white">Recent Orders</h3>
            </div>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-bold uppercase text-purple-400 hover:text-purple-300 transition-all duration-200 flex items-center space-x-1">
                <span>View All</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-6 md:px-8 py-4">Order ID</th>
                        <th class="px-6 md:px-8 py-4">Customer</th>
                        <th class="px-6 md:px-8 py-4">Date</th>
                        <th class="px-6 md:px-8 py-4">Zone</th>
                        <th class="px-6 md:px-8 py-4">Total</th>
                        <th class="px-6 md:px-8 py-4">Status</th>
                        <th class="px-6 md:px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($recentOrders as $order)
                        <tr class="hover:bg-slate-800/30 transition-all duration-150">
                            <td class="px-6 md:px-8 py-4 font-bold text-white">#{{ $order->id }}</td>
                            <td class="px-6 md:px-8 py-4">
                                <div class="font-semibold text-slate-200">{{ $order->customer_name }}</div>
                                <div class="text-sm text-slate-600">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="px-6 md:px-8 py-4 text-slate-300">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 md:px-8 py-4 text-sm font-semibold">
                                <span class="text-slate-300">{{ $order->deliveryCharge?->zone ?? '—' }}</span>
                            </td>
                            <td class="px-6 md:px-8 py-4 font-bold text-white">৳{{ number_format($order->total_amount, 2) }}</td>
                            <td class="px-6 md:px-8 py-4">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                        'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'completed' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'cancelled' => 'bg-pink-500/10 text-pink-400 border-pink-500/20',
                                    ];
                                    $statusDots = [
                                        'pending' => 'bg-amber-400',
                                        'processing' => 'bg-blue-400',
                                        'completed' => 'bg-emerald-400',
                                        'cancelled' => 'bg-pink-400',
                                    ];
                                    $class = $statusClasses[$order->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/20';
                                    $dot = $statusDots[$order->status] ?? 'bg-slate-400';
                                @endphp
                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-full text-sm font-semibold border {{ $class }} uppercase tracking-wider">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                                    <span>{{ $order->status }}</span>
                                </span>
                            </td>
                            <td class="px-6 md:px-8 py-4 text-right">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/20 rounded-lg text-sm font-bold transition-all duration-200">
                                    <span>Manage</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 md:px-8 py-12 text-center text-slate-600">No orders received yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
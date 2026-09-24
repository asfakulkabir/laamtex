@extends('layouts.store')

@section('title', 'My Account - ' . site_name())

@section('content')
<div class="max-w-5xl mx-auto px-3 sm:px-4 lg:px-6 py-6 space-y-5">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Header -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">👋 Hello, {{ $user->name }}</h1>
            <p class="text-sm text-gray-500">Manage your profile and track your orders</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customer.profile.edit') }}"
               class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-bold transition shadow active:scale-95">
                ✏️ Edit Profile
            </a>
            <a href="{{ route('customer.logout') }}"
               onclick="event.preventDefault(); document.getElementById('dash-logout').submit();"
               class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 rounded-lg text-sm font-bold transition active:scale-95">
                Logout
            </a>
            <form id="dash-logout" action="{{ route('customer.logout') }}" method="POST" class="hidden">@csrf</form>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Full Name</p>
                <p class="font-bold text-gray-900 mt-0.5">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Email</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $user->email }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Phone</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $user->phone }}</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Member Since</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $user->created_at->format('M d, Y') }}</p>
            </div>
            @if($user->address)
            <div class="sm:col-span-2">
                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Address</p>
                <p class="font-semibold text-gray-800 mt-0.5">{{ $user->address }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- Orders -->
    <div>
        <h2 class="text-lg font-extrabold text-gray-900 mb-3">📦 My Orders</h2>

        @forelse($orders as $order)
            <details class="bg-white border border-gray-200 rounded-2xl shadow-sm mb-3 overflow-hidden group">
                <summary class="flex flex-wrap items-center justify-between gap-3 cursor-pointer px-4 py-3.5 list-none">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center font-extrabold text-purple-700">#{{ $order->id }}</div>
                        <div>
                            <p class="font-bold text-gray-900">{{ $order->created_at->format('M d, Y, h:i A') }}</p>
                            <p class="text-xs text-gray-500">{{ $order->items_count }} item(s) · {{ $order->deliveryCharge?->zone ?? 'Delivery' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="px-3 py-1 rounded-full text-xs font-bold text-purple-700 bg-purple-50 border border-purple-200">{{ $order->status_label }}</span>
                        <span class="font-extrabold text-gray-900">৳{{ number_format($order->total_amount, 0) }}</span>
                    </div>
                </summary>
                <div class="border-t border-gray-100 px-4 py-3 space-y-2 bg-gray-50 rounded-b-2xl">
                    @foreach($order->items as $item)
                        <div class="flex justify-between items-start text-sm">
                            <div>
                                <span class="font-semibold text-gray-900">{{ $item['name'] ?? 'Product' }}</span>
                                @if(!empty($item['variation_details']))
                                    <span class="text-xs text-purple-600 font-semibold block">{{ $item['variation_details'] }}</span>
                                @endif
                                <span class="text-gray-400 text-xs">× {{ $item['quantity'] }}</span>
                            </div>
                            <span class="font-semibold text-gray-900 whitespace-nowrap">৳{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                        </div>
                    @endforeach
                    <div class="flex justify-between border-t border-gray-200 pt-2 text-sm font-bold text-gray-900">
                        <span>Delivery</span>
                        <span>৳{{ number_format($order->deliveryCharge?->charge ?? 0, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-extrabold text-gray-900">
                        <span>Total</span>
                        <span>৳{{ number_format($order->total_amount, 0) }}</span>
                    </div>
                    <p class="text-xs text-gray-500 font-semibold mt-1">📍 {{ $order->customer_address }}</p>
                </div>
            </details>
        @empty
            <div class="bg-white border border-dashed border-gray-300 rounded-2xl p-10 text-center">
                <p class="text-4xl mb-2">🛍️</p>
                <p class="font-bold text-gray-900">You haven't placed any orders yet.</p>
                <p class="text-sm text-gray-500 mt-1">Once you place an order while logged in, it will show up here.</p>
                <a href="{{ route('shop') }}" class="inline-block mt-4 px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white rounded-lg text-sm font-bold transition shadow active:scale-95">
                    Start Shopping
                </a>
            </div>
        @endforelse

        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>
</div>
@endsection
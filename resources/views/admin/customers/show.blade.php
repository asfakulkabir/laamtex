@extends('layouts.admin')

@section('title', 'Customer - ' . $customer->name)
@section('page_title', 'Customer Details')

@section('content')
<div class="space-y-6">

    <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-purple-400 hover:text-purple-300 hover:underline">
        ← Back to Customers
    </a>

    <!-- Customer Info -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex items-center space-x-4">
                <div class="w-14 h-14 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center font-extrabold text-white text-xl flex-shrink-0">
                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-xl font-bold text-white">{{ $customer->name }}</h2>
                    <p class="text-sm text-slate-400">Joined {{ $customer->created_at->format('M d, Y h:i A') }}</p>
                </div>
            </div>
            <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Delete this customer and keep their orders?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-pink-600/20 hover:bg-pink-600/30 text-pink-400 border border-pink-500/30 rounded-lg text-sm font-bold transition">
                    Delete Customer
                </button>
            </form>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6 text-sm">
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Email</p>
                <p class="font-semibold text-slate-200 mt-1">{{ $customer->email }}</p>
            </div>
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Phone</p>
                <p class="font-semibold text-slate-200 mt-1">{{ $customer->phone ?: '—' }}</p>
            </div>
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Orders</p>
                <p class="font-semibold text-slate-200 mt-1">{{ $orders->count() }}</p>
            </div>
            <div class="bg-slate-800/40 border border-slate-700/50 rounded-xl p-4 sm:col-span-3">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Address</p>
                <p class="font-semibold text-slate-200 mt-1">{{ $customer->address ?: 'No address saved' }}</p>
            </div>
        </div>
    </div>

    <!-- Customer Orders -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-800/50 flex items-center justify-between">
            <h3 class="font-bold text-white text-base">📦 Order History</h3>
            <span class="text-sm text-slate-400">{{ $orders->count() }} orders</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-6 py-3">Order</th>
                        <th class="px-6 py-3">Date</th>
                        <th class="px-6 py-3">Zone</th>
                        <th class="px-6 py-3">Status</th>
                        <th class="px-6 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/30 transition-all duration-150">
                            <td class="px-6 py-3">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-bold text-purple-400 hover:text-purple-300 hover:underline">#{{ $order->id }}</a>
                            </td>
                            <td class="px-6 py-3 text-slate-300">{{ $order->created_at->format('M d, Y h:i A') }}</td>
                            <td class="px-6 py-3 text-slate-300">{{ $order->deliveryCharge?->zone ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/30">{{ $order->status_label }}</span>
                            </td>
                            <td class="px-6 py-3 text-right font-bold text-slate-200">৳{{ number_format($order->total_amount, 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-slate-300">This customer hasn't placed any orders yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Discount Coupons - laamtex')
@section('page_title', 'Discount Coupons')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <p class="text-sm text-slate-300">Create coupon codes customers can apply on the checkout page.</p>
        <a href="{{ route('admin.coupons.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
            + New Coupon
        </a>
    </div>

    <form action="{{ route('admin.coupons.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
        <div class="relative w-64">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or description..."
                   class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 pl-9 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 placeholder:text-slate-300">
            <svg class="w-4 h-4 text-slate-300 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <select name="status" onchange="this.form.submit()"
                class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
            <option value="">All Coupons</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Enabled only</option>
            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled only</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold transition">Filter</button>
        @if(request()->filled('search') || request()->filled('status'))
            <a href="{{ route('admin.coupons.index') }}" class="text-sm font-semibold text-pink-400 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-6 py-4">Code</th>
                        <th class="px-6 py-4">Discount</th>
                        <th class="px-6 py-4">Conditions</th>
                        <th class="px-6 py-4">Usage</th>
                        <th class="px-6 py-4">Validity</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($coupons as $coupon)
                        <tr class="hover:bg-slate-800/30 transition-all duration-150">
                            <td class="px-6 py-4">
                                <span class="font-mono font-bold text-white text-base">{{ $coupon->code }}</span>
                                @if($coupon->description)
                                    <div class="text-xs text-slate-400 mt-0.5">{{ $coupon->description }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-purple-400">{{ $coupon->value_label }}</span>
                                <div class="text-xs text-slate-400">{{ $coupon->type === 'percent' ? 'on subtotal' : 'flat off' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($coupon->min_order_amount)
                                    <div>Min order ৳{{ number_format($coupon->min_order_amount, 0) }}</div>
                                @else
                                    <div class="text-slate-500">No minimum</div>
                                @endif
                                @if($coupon->type === 'percent' && $coupon->max_discount_amount)
                                    <div class="text-emerald-400">Max ৳{{ number_format($coupon->max_discount_amount, 0) }}</div>
                                @endif
                                @if($coupon->per_user_limit)
                                    <div class="text-slate-400">{{ $coupon->per_user_limit }} per customer</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="font-semibold text-slate-200">{{ $coupon->used_count }}{{ $coupon->usage_limit ? ' / ' . $coupon->usage_limit : '' }}</span>
                                <div class="text-xs text-slate-400">{{ $coupon->orders_count }} order{{ $coupon->orders_count === 1 ? '' : 's' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($coupon->starts_at)
                                    <div class="text-slate-400">From {{ $coupon->starts_at->format('d M Y') }}</div>
                                @endif
                                @if($coupon->expires_at)
                                    <div class="{{ $coupon->isExpired() ? 'text-red-400' : 'text-slate-300' }}">
                                        Until {{ $coupon->expires_at->format('d M Y') }}
                                    </div>
                                @else
                                    <div class="text-slate-500">No expiry</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusStyles = [
                                        'Active'        => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'Disabled'      => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                                        'Expired'       => 'bg-red-500/10 text-red-400 border-red-500/20',
                                        'Scheduled'     => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'Limit Reached' => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                                    ];
                                    $label = $coupon->status_label;
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border uppercase tracking-wider {{ $statusStyles[$label] ?? 'bg-slate-500/10 text-slate-300 border-slate-500/20' }}">
                                    {{ $label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.coupons.edit', $coupon->id) }}" class="p-2 text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition font-semibold">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.coupons.destroy', $coupon->id) }}" method="POST" onsubmit="return confirm('Delete coupon {{ $coupon->code }}? Past orders keep their discount record.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-pink-400 bg-pink-500/10 hover:bg-pink-500/20 rounded-lg transition font-semibold">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-8 py-12 text-center text-slate-300">
                                No coupons yet.
                                <a href="{{ route('admin.coupons.create') }}" class="text-pink-400 font-semibold hover:underline">Create your first one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($coupons->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/50">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

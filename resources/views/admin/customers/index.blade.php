@extends('layouts.admin')

@section('title', 'Customers - ' . site_name())
@section('page_title', 'Customers')

@section('content')
<div class="space-y-6">

    <!-- Filter Bar -->
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <form action="{{ route('admin.customers.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow">
            <div class="relative w-64">
                <input type="text" name="search" placeholder="Search name, email, phone..." value="{{ request('search') }}"
                       class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 pl-9 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all placeholder:text-slate-300">
                <svg class="w-4 h-4 text-slate-300 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select name="has_orders" class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                <option value="">-- All Customers --</option>
                <option value="yes" {{ request('has_orders') === 'yes' ? 'selected' : '' }}>🛒 With Orders</option>
                <option value="no"  {{ request('has_orders') === 'no'  ? 'selected' : '' }}>🚫 No Orders</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-sm font-semibold transition">
                Filter
            </button>
            @if(request()->filled('search') || request()->filled('has_orders'))
                <a href="{{ route('admin.customers.index') }}" class="text-sm font-semibold text-pink-400 hover:underline">Clear Filters</a>
            @endif
        </form>
        <span class="text-sm text-slate-400">Total: <strong class="text-white">{{ $customers->total() }}</strong> customers</span>
    </div>

    <!-- Table Card -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Orders</th>
                        <th class="px-6 py-4">Joined</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-slate-800/30 transition-all duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-gradient-to-r from-purple-500 to-pink-500 flex items-center justify-center font-bold text-white text-sm flex-shrink-0">
                                        {{ strtoupper(substr($customer->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-200 truncate">{{ $customer->name }}</p>
                                        <p class="text-xs text-slate-500 truncate">{{ $customer->address ?: 'No address' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-300">{{ $customer->phone ?: '—' }}</td>
                            <td class="px-6 py-4">{{ $customer->email }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold {{ $customer->orders_count > 0 ? 'bg-purple-500/10 text-purple-400 border border-purple-500/30' : 'bg-slate-800/50 text-slate-400 border border-slate-700/50' }}">
                                    {{ $customer->orders_count }} order{{ $customer->orders_count == 1 ? '' : 's' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-300">{{ $customer->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.customers.show', $customer->id) }}" class="p-2 text-purple-400 hover:text-purple-300 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition">
                                        View
                                    </a>
                                    <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Delete this customer? Their order history will be kept.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded-lg transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-300">
                                No registered customers found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div>
        {{ $customers->links() }}
    </div>
</div>
@endsection
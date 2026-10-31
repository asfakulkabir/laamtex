@extends('layouts.admin')

@section('title', 'Manage Orders - laamtex')
@section('page_title', 'Orders Management')
@section('content')
<div class="space-y-6">

    @php
        $periods = \App\Http\Controllers\Admin\OrderController::PERIODS;
        $sorts = \App\Http\Controllers\Admin\OrderController::SORTS;
        $isCustomPeriod = $period['key'] === 'custom';
        $baseQuery = request()->except(['page', 'status']);
        $statusCards = [
            '' => ['label' => 'All Statuses', 'class' => 'text-white', 'ring' => 'ring-purple-500/60 bg-purple-500/10'],
            'processing' => ['label' => 'Processing', 'class' => 'text-blue-300', 'ring' => 'ring-blue-500/60 bg-blue-500/10'],
            'shipped' => ['label' => 'Shipped', 'class' => 'text-purple-300', 'ring' => 'ring-purple-500/60 bg-purple-500/10'],
            'delivered' => ['label' => 'Delivered', 'class' => 'text-emerald-300', 'ring' => 'ring-emerald-500/60 bg-emerald-500/10'],
            'cancelled' => ['label' => 'Cancelled', 'class' => 'text-pink-300', 'ring' => 'ring-pink-500/60 bg-pink-500/10'],
        ];
    @endphp

    <!-- Totals for the selected period -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 md:gap-4">
        <a href="{{ route('admin.orders.index', array_merge($baseQuery, ['status' => null])) }}"
           class="rounded-2xl border p-4 md:p-5 transition-all {{ request('status') === null ? 'border-purple-500/60 bg-purple-500/10' : 'border-slate-800/50 bg-slate-900/60 hover:border-purple-500/40' }}">
            <p class="text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-400">Total Orders</p>
            <p class="text-2xl md:text-3xl font-extrabold text-white mt-1">{{ number_format($stats['total']) }}</p>
            <p class="text-[10px] md:text-xs text-slate-400 mt-1">{{ $period['label'] }}</p>
        </a>

        <div class="rounded-2xl border border-slate-800/50 bg-slate-900/60 p-4 md:p-5">
            <p class="text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-400">Revenue (excl. cancelled)</p>
            <p class="text-2xl md:text-3xl font-extrabold text-emerald-400 mt-1">৳{{ number_format($stats['revenue'], 0) }}</p>
            <p class="text-[10px] md:text-xs text-slate-400 mt-1">Discounts given: ৳{{ number_format($stats['discount'], 0) }}</p>
        </div>

        @foreach(['delivered' => 'Delivered', 'cancelled' => 'Cancelled'] as $key => $label)
            <a href="{{ route('admin.orders.index', array_merge($baseQuery, ['status' => $key])) }}"
               class="rounded-2xl border p-4 md:p-5 transition-all {{ request('status') === $key ? $statusCards[$key]['ring'] . ' ' . $statusCards[$key]['class'] : 'border-slate-800/50 bg-slate-900/60 hover:border-slate-600' }}">
                <p class="text-[10px] md:text-xs font-bold uppercase tracking-wider text-slate-400">{{ $label }}</p>
                <p class="text-2xl md:text-3xl font-extrabold mt-1 {{ $statusCards[$key]['class'] }}">{{ number_format($stats['byStatus'][$key] ?? 0) }}</p>
                <p class="text-[10px] md:text-xs text-slate-400 mt-1">in {{ strtolower($period['label']) }}</p>
            </a>
        @endforeach
    </div>

    <!-- Single filter form: search, status, period, custom dates, payment, sort -->
    <form action="{{ route('admin.orders.index') }}" method="GET" class="rounded-2xl border border-slate-800/50 bg-slate-900/60 p-4 space-y-4">

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
            <!-- Search -->
            <div class="relative lg:col-span-4">
                <input type="text" name="search" placeholder="Search order ID, name, phone..." value="{{ request('search') }}"
                       class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2.5 pl-9 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all placeholder:text-slate-300">
                <svg class="w-4 h-4 text-slate-300 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <!-- Status -->
            <div class="lg:col-span-3">
                <select name="status" onchange="this.form.submit()"
                        class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Order::STATUSES as $val => $label)
                        <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Payment method -->
            <div class="lg:col-span-2">
                <select name="payment" onchange="this.form.submit()"
                        class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">All Payments</option>
                    <option value="Cash on Delivery" {{ request('payment') === 'Cash on Delivery' ? 'selected' : '' }}>Cash on Delivery</option>
                    <option value="bKash Send Money" {{ request('payment') === 'bKash Send Money' ? 'selected' : '' }}>bKash Send Money</option>
                </select>
            </div>

            <!-- Sort -->
            <div class="lg:col-span-3">
                <select name="sort" onchange="this.form.submit()"
                        class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @foreach($sorts as $val => $label)
                        <option value="{{ $val }}" {{ $sort === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Period presets -->
        <div class="flex flex-wrap items-center gap-x-4 gap-y-3">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Period:</span>
            <div class="flex flex-wrap items-center gap-1 rounded-lg border border-slate-700/50 bg-slate-900/80 p-1">
                @foreach($periods as $val => $label)
                    @continue($val === 'custom')
                    <label class="cursor-pointer">
                        <input type="radio" name="range" value="{{ $val }}" class="sr-only peer"
                               onchange="document.getElementById('orderCustomDates').classList.add('hidden'); this.form.querySelectorAll('input[name=from],input[name=to]').forEach(function(i){i.value=''}); this.form.submit();"
                               @checked(!$isCustomPeriod && $period['key'] === $val)>
                        <span class="px-3 py-1.5 rounded-md text-sm font-semibold text-slate-300 peer-checked:bg-purple-500 peer-checked:text-white transition">{{ $label }}</span>
                    </label>
                @endforeach
                <label class="cursor-pointer">
                    <input type="radio" name="range" value="custom" class="sr-only peer"
                           onchange="document.getElementById('orderCustomDates').classList.remove('hidden')"
                           @checked($isCustomPeriod)>
                    <span class="px-3 py-1.5 rounded-md text-sm font-semibold text-slate-300 peer-checked:bg-purple-500 peer-checked:text-white transition">Custom</span>
                </label>
            </div>

            <div id="orderCustomDates" class="flex flex-wrap items-center gap-2 {{ $isCustomPeriod ? '' : 'hidden' }}">
                <input type="date" name="from" value="{{ $period['from'] ?? request('from') }}" aria-label="From date"
                       class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 [color-scheme:dark]">
                <span class="text-slate-500 text-sm">→</span>
                <input type="date" name="to" value="{{ $period['to'] ?? request('to') }}" aria-label="To date"
                       class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500 [color-scheme:dark]">
                <button type="submit"
                        class="px-4 py-2 bg-purple-600/20 hover:bg-purple-600/30 text-purple-400 border border-purple-500/30 rounded-lg text-sm font-bold transition">
                    Apply Dates
                </button>
            </div>

            <div class="flex flex-wrap items-center gap-2 ml-auto">
                <select name="per_page" onchange="this.form.submit()"
                        class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-purple-500">
                    @foreach([10, 15, 25, 50, 100] as $size)
                        <option value="{{ $size }}" {{ $perPage === $size ? 'selected' : '' }}>{{ $size }} / page</option>
                    @endforeach
                </select>
                <button type="submit"
                        class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold uppercase tracking-wider transition">
                    Apply
                </button>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-pink-400 hover:underline">Reset</a>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-400 border-t border-slate-800/50 pt-3">
            <span>
                Period: <strong class="text-white">{{ $period['label'] }}</strong>
                @if($period['from'] || $period['to'])
                    <span class="text-slate-300">({{ $period['from'] ?? 'start' }} → {{ $period['to'] ?? 'today' }})</span>
                @endif
            </span>
            <span>·</span>
            <span>Matching orders: <strong class="text-white">{{ $orders->total() }}</strong></span>
            <span>·</span>
            <span>Revenue on this view: <strong class="text-emerald-400">৳{{ number_format($stats['revenue'], 0) }}</strong></span>
        </div>
    </form>

    <!-- Export / Import -->
    <div class="flex items-center justify-end gap-3 flex-wrap">
        <a href="{{ route('admin.orders.export-csv', request()->query()) }}"
           class="px-5 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 border border-blue-500/30 rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
            ⬇ Export CSV (this view)
        </a>
        <label class="px-5 py-2.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 rounded-lg text-sm font-bold shadow transition-all cursor-pointer whitespace-nowrap">
            ⬆ Import CSV
            <input type="file" accept=".csv,.txt" class="hidden" id="csvFileInput">
        </label>
    </div>

    <!-- Hidden Import Form -->
    <form id="importForm" action="{{ route('admin.orders.import-csv') }}" method="POST" enctype="multipart/form-data" class="hidden">
        @csrf
        <input type="file" name="csv_file" id="csvFileInputHidden" accept=".csv,.txt">
    </form>

    <!-- Table Card -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-8 py-4">Order ID</th>
                        <th class="px-8 py-4">Customer</th>
                        <th class="px-8 py-4">Date</th>
                        <th class="px-8 py-4">Delivery Zone</th>
                        <th class="px-8 py-4">Total</th>
                        <th class="px-8 py-4">Status</th>
                        <th class="px-8 py-4">Steadfast</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                @forelse($orders as $order)
                    @php
                        $deliveryCharge = $order->deliveryCharge;
                        $chargeAmount   = $deliveryCharge ? $deliveryCharge->charge : 0;
                        $zoneName       = $deliveryCharge ? $deliveryCharge->zone   : 'N/A';
                    @endphp
                    <tbody class="divide-y divide-slate-800/30 text-slate-400">
                        <tr class="hover:bg-slate-800/30 transition-all duration-150">
                            <td class="px-8 py-4 font-bold">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="text-white hover:text-purple-400 hover:underline">#{{ $order->id }}</a>
                            </td>
                            <td class="px-8 py-4">
                                <a href="{{ route('admin.orders.show', $order->id) }}" class="font-semibold text-slate-200 hover:text-purple-400 hover:underline transition-colors">
                                    {{ $order->customer_name }}
                                </a>
                                <div class="text-sm text-slate-300 font-mono">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="px-8 py-4 text-sm">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                            <td class="px-8 py-4 text-sm font-semibold text-purple-400">
                                {{ $zoneName }}
                            </td>
                            <td class="px-8 py-4 font-bold text-white">
                                ৳{{ number_format($order->total_amount, 0) }}
                                @if($order->discount_amount > 0)
                                    <div class="text-xs font-semibold text-emerald-400">Discount: -৳{{ number_format($order->discount_amount, 0) }}</div>
                                @endif
                            </td>
                            <td class="px-8 py-4">
                                @php
                                    $statusClasses = [
                                        'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'shipped'    => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                        'delivered'  => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'cancelled'  => 'bg-pink-500/10 text-pink-400 border-pink-500/20',
                                    ];
                                    $class = $statusClasses[$order->status] ?? 'bg-slate-800/50 text-slate-300 border-slate-700/50';
                                @endphp
                                <span class="px-2.5 py-1 rounded-full text-sm font-semibold border {{ $class }} uppercase tracking-wider">
                                    {{ \App\Models\Order::STATUSES[$order->status] ?? ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-center">
                                @if($order->is_sent_to_steadfast)
                                    <span class="text-emerald-400 text-sm font-bold">✅ Sent</span>
                                    @if($order->steadfast_consignment_id)
                                        <div class="text-sm text-slate-300 font-mono">{{ $order->steadfast_consignment_id }}</div>
                                    @endif
                                @else
                                    <span class="text-slate-600 text-sm">—</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex justify-end items-center gap-2">
                                    <a href="{{ route('admin.orders.show', $order->id) }}"
                                       class="px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/20 rounded-lg text-sm font-bold transition">
                                        View
                                    </a>
                                    @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete Order #{{ $order->id }}? This action cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/30 text-red-400 border border-red-500/30 rounded-lg text-sm font-bold transition">
                                                Delete
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    </tbody>
                @empty
                    <tbody>
                        <tr>
                            <td colspan="8" class="px-8 py-12 text-center text-slate-300">No orders found.</td>
                        </tr>
                    </tbody>
                @endforelse
            </table>
        </div>

        <!-- Pagination -->
        @if($orders->hasPages())
            <div class="px-8 py-4 border-t border-slate-800/50">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<style>
    [x-cloak] { display: none !important; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form[action*="send-to-steadfast"]').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = this.querySelector('button[type="submit"]');
                if (btn) {
                    btn.disabled = true;
                    btn.textContent = 'Sending...';
                    btn.classList.add('opacity-50', 'cursor-not-allowed');
                }
            });
        });
    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var csvInput = document.getElementById('csvFileInput');
        if (csvInput) {
            csvInput.addEventListener('change', function () {
                if (this.files && this.files.length > 0) {
                    var hiddenInput = document.getElementById('csvFileInputHidden');
                    var dataTransfer = new DataTransfer();
                    dataTransfer.items.add(this.files[0]);
                    hiddenInput.files = dataTransfer.files;
                    document.getElementById('importForm').submit();
                }
            });
        }
    });
</script>
@endsection

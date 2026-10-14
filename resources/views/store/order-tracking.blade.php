@extends('layouts.store')

@section('title', 'Track Your Order - ' . site_name())

@php
    $statusStyles = [
        'processing' => ['dot' => 'bg-amber-500', 'chip' => 'bg-amber-50 text-amber-700 border-amber-200', 'label' => 'Processing'],
        'shipped'    => ['dot' => 'bg-blue-600', 'chip' => 'bg-blue-50 text-blue-700 border-blue-200', 'label' => 'Shipped'],
        'delivered'  => ['dot' => 'bg-emerald-600', 'chip' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'label' => 'Delivered'],
        'cancelled'  => ['dot' => 'bg-rose-700', 'chip' => 'bg-rose-50 text-rose-700 border-rose-200', 'label' => 'Cancelled'],
    ];
    $steps = ['processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
    $stepOrder = ['processing' => 0, 'shipped' => 1, 'delivered' => 2];

    $waDigits = whatsapp_number_digits();
    $waBase = $waDigits !== '' ? 'https://wa.me/' . $waDigits : '';
@endphp

@section('content')
<div class="bg-gray-50 min-h-screen">

    <!-- Hero -->
    <div class="bg-gray-950 text-white pt-8 pb-12 md:pb-16 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(225,29,72,0.18),transparent_60%)]"></div>
        <div class="max-w-4xl mx-auto relative z-10">
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight leading-none">
                Track your <span class="text-rose-500">order</span>
            </h1>
            <p class="text-gray-400 font-semibold mt-3 text-sm md:text-base">
                Enter the phone number you ordered with — no login needed. / অর্ডার দেওয়ার ফোন নম্বর দিন, লগইন ছাড়াই ফলাফল দেখুন।
            </p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10 space-y-5">

        <!-- Search -->
        <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-[0_24px_50px_-30px_rgba(0,0,0,0.25)]">
            <form action="{{ route('order.tracking') }}" method="GET">
                <label for="phone" class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-2">Phone number / ফোন নম্বর</label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input type="tel" name="phone" id="phone" value="{{ $phone }}" inputmode="numeric"
                           autocomplete="tel" placeholder="01700000000"
                           class="flex-1 bg-gray-50 border-2 @if($error) border-rose-400 @else border-gray-200 @endif rounded-xl px-4 py-3 text-base font-bold text-gray-900 outline-none transition-all placeholder:text-gray-400 placeholder:font-semibold hover:border-gray-300 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 px-7 py-3 rounded-2xl bg-rose-700 hover:bg-rose-800 text-white font-extrabold text-base shadow-[0_20px_40px_-15px_rgba(225,29,72,0.5)] transition-all hover:-translate-y-0.5 active:scale-[0.98] whitespace-nowrap">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                        Track
                    </button>
                </div>
            </form>
        </div>

        @if($error)
            <div class="flex items-start gap-3 bg-rose-50 border-2 border-rose-200 text-rose-800 px-5 py-4 rounded-2xl">
                <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M12 8v4M12 16h.01"></path></svg>
                <p class="text-sm md:text-base font-bold">{{ $error }}</p>
            </div>
        @endif

        @if($phone !== '' && !$error && $orders->isEmpty())
            <div class="flex flex-col items-center text-center bg-white px-6 py-14 rounded-2xl border border-gray-200 shadow-[0_24px_50px_-30px_rgba(0,0,0,0.25)] space-y-4">
                <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"></circle><path d="m21 21-4.3-4.3"></path></svg>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-gray-900">No orders found</h2>
                    <p class="text-sm md:text-base text-gray-500 font-semibold mt-1">
                        We could not find any order for this number. Please check the number and try again.
                    </p>
                </div>
                @if($waBase)
                    <a href="{{ $waBase }}?text={{ rawurlencode('Hello ' . site_name() . ', I need help tracking my order.') }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm transition-all">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Zm5.04 11.06c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z"/></svg>
                        Ask on WhatsApp
                    </a>
                @endif
            </div>
        @endif

        @if($orders->isNotEmpty())
            <!-- Results header -->
            <div class="flex items-center justify-between gap-3 pt-1">
                <h2 class="text-base md:text-lg font-extrabold text-gray-900 tracking-tight">
                    {{ $orders->count() }} {{ $orders->count() > 1 ? 'orders' : 'order' }} found
                </h2>
                <span class="text-xs font-bold uppercase tracking-wider text-gray-400">Latest first</span>
            </div>

            <div class="space-y-4">
                @foreach($orders as $order)
                    @php
                        $style = $statusStyles[$order->status] ?? ['dot' => 'bg-gray-400', 'chip' => 'bg-gray-50 text-gray-700 border-gray-200', 'label' => ucfirst($order->status)];
                        $currentStep = $stepOrder[$order->status] ?? 0;
                        $itemCount = count($order->items);
                    @endphp

                    <div class="bg-white rounded-2xl border border-gray-200 shadow-[0_18px_40px_-30px_rgba(0,0,0,0.3)] overflow-hidden">

                        <!-- Head -->
                        <div class="flex flex-wrap items-start justify-between gap-3 px-5 sm:px-6 pt-5 pb-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-lg sm:text-xl font-extrabold text-gray-900 tracking-tight">#{{ $order->id }}</span>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-extrabold {{ $style['chip'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $style['dot'] }}"></span>
                                        {{ $style['label'] }}
                                    </span>
                                </div>
                                <p class="text-base sm:text-lg font-bold text-gray-900 mt-1.5 break-words">{{ $order->customer_name }}</p>
                                <p class="text-xs sm:text-sm text-gray-400 font-semibold mt-0.5">
                                    {{ $order->created_at->format('d M Y, h:i A') }}
                                    @if($itemCount > 0) · {{ $itemCount }} {{ $itemCount > 1 ? 'items' : 'item' }} @endif
                                </p>
                            </div>

                            <div class="text-right shrink-0">
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Total</p>
                                <p class="text-2xl sm:text-3xl font-extrabold text-rose-700 leading-none mt-1">৳{{ number_format($order->total_amount, 0) }}</p>
                            </div>
                        </div>

                        <!-- Meta strip -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 border-y border-gray-100 divide-y sm:divide-y-0 sm:divide-x">
                            <div class="flex items-start gap-2.5 px-5 sm:px-6 py-3.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0 mt-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Delivery address</p>
                                    <p class="font-mono text-sm font-bold text-gray-700 tracking-wider break-all mt-0.5">{{ mask_address($order->customer_address) }}</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-2.5 px-5 sm:px-6 py-3.5">
                                <svg class="w-4 h-4 text-gray-400 shrink-0 mt-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wider text-gray-400">Payment</p>
                                    <p class="text-sm font-bold text-gray-700 mt-0.5">{{ $order->payment_method }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress -->
                        <div class="px-5 sm:px-6 py-4">
                            @if($order->status === 'cancelled')
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-full bg-rose-700 flex items-center justify-center text-white shrink-0">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    </span>
                                    <p class="text-sm md:text-base font-extrabold text-rose-700">This order was cancelled.</p>
                                </div>
                            @else
                                <div class="flex items-start gap-2 sm:gap-3">
                                    @foreach(array_keys($steps) as $index => $key)
                                        @php
                                            $done = $stepOrder[$key] <= $currentStep;
                                            $nextKey = array_keys($steps)[$index + 1] ?? null;
                                        @endphp
                                        <div class="flex items-start gap-2 sm:gap-3 flex-1 last:flex-none">
                                            <div class="flex flex-col items-center gap-1.5 shrink-0">
                                                <span class="w-6 h-6 rounded-full flex items-center justify-center border-2 {{ $done ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-gray-200 bg-white' }}">
                                                    @if($done)
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                                    @endif
                                                </span>
                                                <span class="text-[11px] font-bold uppercase tracking-wide text-center {{ $done ? 'text-gray-900' : 'text-gray-300' }}">{{ $steps[$key] }}</span>
                                            </div>
                                            @if($nextKey !== null)
                                                <span class="flex-1 h-0.5 rounded-full mt-3 {{ $stepOrder[$nextKey] <= $currentStep ? 'bg-emerald-600' : 'bg-gray-200' }}"></span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2.5 px-5 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/60">
                            @if($waBase)
                                <a href="{{ $waBase }}?text={{ rawurlencode('Hello ' . site_name() . ', I need help with my order #' . $order->id . '.') }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm transition-colors">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="w-4.5 h-4.5 w-[18px] h-[18px]"><path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Zm5.04 11.06c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z"/></svg>
                                    Help on WhatsApp
                                </a>
                            @endif
                            <a href="{{ route('contact.page') }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white border-2 border-gray-200 hover:border-gray-300 text-gray-700 font-extrabold text-sm transition-colors">
                                Contact us
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
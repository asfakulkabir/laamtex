@extends('layouts.store')

@section('title', 'Shopping Cart - ' . site_name())

@section('content')
@php
    $cartTotalQty = 0;
    $cartSubtotal = 0;
    foreach ($cart as $item) {
        $cartTotalQty += $item['quantity'];
        $cartSubtotal += $item['price'] * $item['quantity'];
    }
@endphp

<div class="bg-gray-50">

    <!-- Hero -->
    <div class="bg-gray-950 text-white pt-8 pb-14 md:pb-20 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(225,29,72,0.18),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto relative z-10 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-none">
                    Your <span class="text-rose-500">cart</span>
                </h1>
                <p class="text-gray-400 font-semibold mt-3 text-sm md:text-base">
                    @if($cartTotalQty > 0)
                        {{ $cartTotalQty }} {{ $cartTotalQty > 1 ? 'items' : 'item' }} · ৳{{ number_format($cartSubtotal, 2) }}
                    @else
                        Nothing here yet — let&rsquo;s fix that.
                    @endif
                </p>
            </div>

            <a href="{{ route('shop') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-white text-sm font-bold tracking-wide transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M19 12H5m7 7-7-7 7-7"/>
                </svg>
                Continue Shopping
            </a>
        </div>
    </div>

    @if($cartTotalQty > 0)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-10">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

                <!-- Cart Items -->
                <div class="lg:col-span-7 xl:col-span-8 space-y-4" id="cart-items-container">
                    @foreach($cart as $key => $item)
                        <div class="bg-white p-3 sm:p-4 rounded-2xl border border-gray-200 shadow-[0_18px_40px_-28px_rgba(0,0,0,0.35)] flex gap-4 sm:gap-5 items-start hover:border-gray-300 hover:shadow-[0_24px_50px_-28px_rgba(0,0,0,0.4)] transition-all"
                             id="cart-item-{{ md5($key) }}" data-cart-key="{{ $key }}">

                            <!-- Product Image -->
                            <div class="w-20 h-20 sm:w-28 sm:h-28 bg-gray-50 border border-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                                @if($item['image'])
                                    <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-purple-300">
                                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0 flex flex-col gap-3">
                                <!-- Name + line total -->
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0">
                                        <h3 class="font-bold text-gray-900 text-sm sm:text-base leading-snug">{{ $item['name'] }}</h3>
                                        @if($item['variation_details'])
                                            <p class="text-xs sm:text-sm font-bold text-rose-700 mt-0.5">{{ $item['variation_details'] }}</p>
                                        @endif
                                    </div>

                                    <!-- Line Total -->
                                    <span class="font-extrabold text-gray-900 text-lg sm:text-2xl leading-none pt-0.5 shrink-0 whitespace-nowrap"
                                          id="item-total-{{ md5($key) }}">৳{{ number_format($item['price'] * $item['quantity'], 2) }}</span>
                                </div>

                                <!-- Quantity + actions + unit price -->
                                <div class="flex flex-wrap items-center justify-between gap-3 mt-auto">
                                    <div class="flex items-center gap-3">
                                        <!-- Quantity Controls -->
                                        <div class="flex items-center bg-gray-50 border-2 border-gray-200 rounded-xl overflow-hidden">
                                            <button type="button"
                                                    class="cart-qty-btn w-9 sm:w-10 py-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 focus:outline-none text-base font-bold leading-none transition"
                                                    data-key="{{ $key }}" data-action="decrease" data-item-id="{{ md5($key) }}" aria-label="Decrease quantity">&minus;</button>
                                            <span class="px-2 sm:px-3 text-base sm:text-lg font-extrabold text-gray-900 qty-display" id="qty-{{ md5($key) }}">{{ $item['quantity'] }}</span>
                                            <button type="button"
                                                    class="cart-qty-btn w-9 sm:w-10 py-2 text-gray-500 hover:text-purple-600 hover:bg-purple-50 focus:outline-none text-base font-bold leading-none transition"
                                                    data-key="{{ $key }}" data-action="increase" data-item-id="{{ md5($key) }}" aria-label="Increase quantity">+</button>
                                        </div>

                                        <!-- Remove -->
                                        <form action="{{ route('cart.remove') }}" method="POST" class="cart-remove-form" data-key="{{ $key }}" data-item-id="{{ md5($key) }}">
                                            @csrf
                                            <input type="hidden" name="key" value="{{ $key }}">
                                            <button type="submit" class="inline-flex items-center gap-1 text-xs font-bold text-pink-600 hover:text-pink-700 uppercase tracking-wider transition">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" viewBox="0 0 24 24">
                                                    <path d="M6 18 18 6M6 6l12 12"/>
                                                </svg>
                                                Remove
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Unit price -->
                                    <p class="text-sm sm:text-base text-gray-500 font-bold">
                                        ৳{{ number_format($item['price'], 2) }} <span class="text-gray-400 font-semibold">each</span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Summary Sidebar -->
                <aside class="lg:col-span-5 xl:col-span-4">
                    <div class="lg:sticky lg:top-24 bg-white p-5 sm:p-6 md:p-7 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)] space-y-4">

                        <h3 class="text-lg md:text-xl font-extrabold text-gray-900 border-b-2 border-gray-100 pb-3 flex items-center justify-between tracking-tight">
                            Order Summary
                            <span class="text-sm bg-gray-100 text-gray-600 px-3 py-1 rounded-full font-bold">{{ $cartTotalQty }} {{ $cartTotalQty > 1 ? 'items' : 'item' }}</span>
                        </h3>

                        <div class="space-y-3 text-base sm:text-lg">
                            <div class="flex justify-between items-center text-gray-500 font-semibold gap-4">
                                <span>Subtotal</span>
                                <span class="font-extrabold text-gray-900" id="cart-subtotal">
                                    ৳{{ number_format($cartSubtotal, 2) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center text-gray-400 font-semibold gap-4 text-sm sm:text-base">
                                <span>Shipping</span>
                                <span>Calculated at checkout</span>
                            </div>
                        </div>

                        <div class="border-t-2 border-dashed border-gray-200 pt-4 flex items-end justify-between gap-3">
                            <span class="text-lg font-extrabold text-gray-900">Estimated Total</span>
                            <span class="text-3xl sm:text-4xl font-extrabold text-rose-700 leading-none whitespace-nowrap" id="cart-grand-total">
                                ৳{{ number_format($cartSubtotal, 2) }}
                            </span>
                        </div>

                        <div class="border-t-2 border-dashed border-gray-200 pt-4 space-y-3">
                            <a href="{{ route('checkout') }}"
                               class="w-full bg-rose-700 hover:bg-rose-800 text-white py-4 px-4 rounded-2xl font-extrabold text-base sm:text-lg shadow-[0_20px_40px_-15px_rgba(225,29,72,0.5)] transition-all hover:-translate-y-0.5 active:scale-[0.98] flex flex-col items-center leading-tight">
                                <span>Proceed to Checkout</span>
                                <span class="text-2xl sm:text-3xl" id="cart-checkout-total">৳{{ number_format($cartSubtotal, 2) }}</span>
                            </a>

                            <div class="flex items-center justify-center gap-2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                <p class="text-[11px] font-bold uppercase tracking-wide">Secure checkout · {{ site_name() }}</p>
                            </div>

                            <a href="{{ route('shop') }}" class="block w-full text-center text-xs font-bold text-gray-400 hover:text-gray-700 uppercase tracking-wider transition">
                                ← Continue Shopping
                            </a>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    @else
        <!-- Empty Cart State -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 md:py-20">
            <div class="bg-white rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)] flex flex-col items-center text-center px-6 py-16 md:py-20 space-y-6">
                <div class="w-24 h-24 bg-purple-50 rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-2xl font-extrabold text-gray-900">Your cart is empty</h2>
                    <p class="text-gray-400 text-base font-semibold mt-2">Looks like you haven&rsquo;t added anything to your cart yet.</p>
                </div>
                <a href="{{ route('shop') }}" class="px-8 py-3.5 bg-primary hover:bg-primary text-white rounded-2xl font-extrabold shadow-[0_20px_40px_-15px_rgba(124,58,237,0.5)] transition-all hover:-translate-y-0.5 active:scale-[0.98]">Start Shopping</a>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Cart Badge update
    function updateCartBadge(count) {
        const badge = document.getElementById('cart-badge');
        if (badge) {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    // Keep every total on the page in sync
    function updateTotals(subtotal) {
        document.getElementById('cart-subtotal').textContent = `৳${subtotal}`;
        document.getElementById('cart-grand-total').textContent = `৳${subtotal}`;
        document.getElementById('cart-checkout-total').textContent = `৳${subtotal}`;
    }

    // Quantity button listeners
    document.querySelectorAll('.cart-qty-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const key = this.dataset.key;
            const action = this.dataset.action;
            const itemId = this.dataset.itemId;
            const qtySpan = document.getElementById(`qty-${itemId}`);
            let currentQty = parseInt(qtySpan.textContent);
            let newQty = action === 'increase' ? currentQty + 1 : Math.max(1, currentQty - 1);

            this.disabled = true;

            const response = await fetch('{{ route("cart.update") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ key, quantity: newQty }),
            });

            const data = await response.json();

            if (data.success) {
                qtySpan.textContent = newQty;
                document.getElementById(`item-total-${itemId}`).textContent = `৳${data.item_total}`;
                updateTotals(data.cart_subtotal);
                updateCartBadge(data.cart_count);
            } else {
                alert(data.message || 'Cannot update quantity.');
            }

            this.disabled = false;
        });
    });

    // Remove item listeners
    document.querySelectorAll('.cart-remove-form').forEach(form => {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const itemId = this.dataset.itemId;

            const formData = new FormData(this);
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData,
            });

            const data = await response.json();
            if (data.success) {
                // Remove item from UI
                const itemEl = document.getElementById(`cart-item-${itemId}`);
                if (itemEl) {
                    itemEl.remove();
                }
                updateTotals(data.cart_subtotal);
                updateCartBadge(data.cart_count);

                if (data.cart_count === 0) {
                    location.reload();
                }
            }
        });
    });
</script>
@endsection
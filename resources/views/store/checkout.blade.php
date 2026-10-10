@extends('layouts.store')

@section('title', 'Checkout - ' . site_name())

@section('pixel_events')
<script>
fbq('track', 'AddPaymentInfo', {
    content_type: 'product',
    value: {{ $subtotal }},
    currency: 'BDT'
});
fbq('track', 'InitiateCheckout', {
    content_type: 'product',
    value: {{ $subtotal }},
    currency: 'BDT',
    num_items: {{ count($cart) }}
});
</script>
@endsection

@section('content')
@php $bkashNumber = App\Models\Setting::getValue('bkash_number', ''); @endphp
<div class="-mx-3 sm:-mx-4 lg:-mx-6 bg-gray-50 md:pb-10" x-data="checkoutPage({{ $deliveryZones }})">

    <!-- Hero -->
    <div class="bg-gray-950 text-white pt-8 pb-12 md:pb-24 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(225,29,72,0.18),transparent_60%)]"></div>
        <div class="max-w-6xl mx-auto relative z-10">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-none">Complete your <span class="text-rose-500">order</span></h1>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 md:-mt-16 relative z-20">

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-semibold">
                {{ session('error') }}
            </div>
        @endif

        @auth
            <div class="mb-4 bg-white border border-gray-200 text-gray-700 px-4 py-3 rounded-xl text-sm font-semibold flex flex-wrap items-center gap-2 shadow-sm">
                <span>Your saved details have been filled in automatically.</span>
                <a href="{{ route('customer.profile.edit') }}" class="text-rose-700 underline font-bold">Edit profile</a>
            </div>
        @endauth

        <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form"
              x-on:submit="submitting = true"
              class="grid grid-cols-1 lg:grid-cols-12 gap-3 lg:gap-12">

            @csrf

            <!-- Left: form -->
            <div class="lg:col-span-7 space-y-3 lg:space-y-8">

                <!-- Delivery information -->
                <section class="bg-white p-3 sm:p-6 md:p-10 rounded-2xl border border-gray-200 shadow-[0_20px_50px_-20px_rgba(0,0,0,0.1)] space-y-4">
                    <h2 class="text-lg md:text-2xl font-extrabold text-gray-900 flex items-center gap-4 tracking-tight">
                        <span class="bg-rose-50 text-rose-700 p-3 rounded-2xl shadow-sm shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        </span>
                        Delivery Information
                    </h2>

                    <div class="space-y-2 md:space-y-4">
                        <!-- Full Name -->
                        <div>
                            <label for="customer_name" class="flex items-baseline gap-2 flex-wrap text-sm font-bold text-gray-600 mb-1.5 ml-1">
                                <span>Your Name *</span>
                                <span lang="bn" class="text-[13px] font-semibold text-gray-600">আপনার নাম</span>
                            </label>
                            <div class="relative">
                                <input type="text" id="customer_name" name="customer_name"
                                       value="{{ old('customer_name', $customer->name ?? '') }}" required
                                       class="w-full bg-emerald-50/70 border-[3px] border-emerald-400 rounded-xl md:rounded-2xl pl-12 pr-4 py-2 md:py-3.5 text-base font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 hover:border-emerald-500 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-200"
                                       placeholder="Enter your full name">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            @error('customer_name')
                                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="customer_phone" class="flex items-baseline gap-2 flex-wrap text-sm font-bold text-gray-600 mb-1.5 ml-1">
                                <span>Mobile Number *</span>
                                <span lang="bn" class="text-[13px] font-semibold text-gray-600">মোবাইল নম্বর</span>
                            </label>
                            <div class="relative">
                                <input type="tel" id="customer_phone" name="customer_phone"
                                       value="{{ old('customer_phone', $customer->phone ?? '') }}" required
                                       pattern="^(\+?88)?01[3-9]\d{8}$"
                                       class="w-full bg-emerald-50/70 border-[3px] border-emerald-400 rounded-xl md:rounded-2xl pl-12 pr-4 py-2 md:py-3.5 text-base font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 hover:border-emerald-500 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-200"
                                       placeholder="01XXXXXXXXX">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-1/2 -translate-y-1/2 text-emerald-500"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            </div>
                            @error('customer_phone')
                                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                            <p class="text-sm text-gray-500 font-semibold mt-1 ml-1">Format: 01XXXXXXXXX (Bangladeshi number)</p>
                        </div>

                        <!-- Address -->
                        <div>
                            <label for="customer_address" class="flex items-baseline gap-2 flex-wrap text-sm font-bold text-gray-600 mb-1.5 ml-1">
                                <span>Full Address *</span>
                                <span lang="bn" class="text-[13px] font-semibold text-gray-600">সম্পূর্ণ ঠিকানা</span>
                            </label>
                            <div class="relative">
                                <textarea id="customer_address" name="customer_address" rows="3" required
                                          class="w-full bg-emerald-50/70 border-[3px] border-emerald-400 rounded-xl md:rounded-2xl pl-12 pr-4 py-2 md:py-3.5 text-base font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 hover:border-emerald-500 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-200 resize-none min-h-[7rem] md:min-h-[9rem]"
                                          placeholder="House, Road, Area, City">{{ old('customer_address', $customer->address ?? '') }}</textarea>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="absolute left-4 top-5 text-emerald-500"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-6a2 2 0 0 1 2.582 0l7 6A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                            </div>
                            @error('customer_address')
                                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                                            <!-- Delivery area -->
                    <div class="border-t-2 border-dashed border-gray-200 pt-1 space-y-3">
                        <h4 class="text-sm font-extrabold text-gray-700 flex items-center uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-rose-600"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                            Delivery Zone
                        </h4>

                        <div class="space-y-1">
                            @foreach($deliveryZones as $zone)
                                <label class="flex items-center gap-3 px-2 py-1 rounded-xl border-2 border-lime-700 cursor-pointer transition-all"
                                       :class="selectedZone === '{{ $zone->zone }}' ? 'border-rose-500 bg-rose-50/50 shadow-[0_12px_24px_-16px_rgba(225,29,72,0.6)]' : 'border-gray-200 bg-gray-50 hover:border-gray-300'">
                                    <input type="radio" name="delivery_zone" value="{{ $zone->zone }}"
                                           @change="selectZone('{{ $zone->zone }}')"
                                           x-model="selectedZone"
                                           class="h-4 w-4 border-gray-300 text-rose-600 focus:ring-rose-500"
                                           {{ old('delivery_zone') === $zone->zone ? 'checked' : '' }}>
                                    <div class="flex-1 min-w-0">
                                        <span class="block text-sm font-bold text-gray-900">{{ $zone->zone }}</span>
                                        @if($zone->estimated_days)
                                            <span class="block text-sm text-gray-500 font-semibold">Delivery in {{ $zone->estimated_days }}</span>
                                        @endif
                                    </div>
                                    <span class="text-base font-extrabold text-gray-900" :class="selectedZone === '{{ $zone->zone }}' && 'text-rose-700'">৳{{ number_format($zone->charge, 0) }}</span>
                                </label>
                            @endforeach
                        </div>

                        @error('delivery_zone')
                            <span class="text-sm text-red-500 block">{{ $message }}</span>
                        @enderror

                        <div x-show="selectedZoneDays" x-cloak x-transition
                             class="text-base font-extrabold text-rose-700 bg-rose-50 border border-rose-100 px-3 py-2 rounded-lg">
                            Estimated delivery: <span x-text="selectedZoneDays"></span>
                        </div>

                    </div>
                    </div>
                </section>

                <!-- Custom note (collapsible, optional) -->
                <div x-data="{ noteOpen: {{ old('customer_note') ? 'true' : 'false' }} }">
                    <button type="button" @click="noteOpen = !noteOpen" :aria-expanded="noteOpen"
                            class="flex items-center gap-2 text-sm font-bold text-gray-500 hover:text-rose-700 transition ml-1">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                             class="transition-transform duration-200" :class="noteOpen && 'rotate-45'"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        <span x-text="noteOpen ? 'Hide note' : 'Add a note (optional)'"></span>
                    </button>

                    <div x-show="noteOpen" x-cloak x-transition class="mt-2">
                        <textarea name="customer_note" id="customer_note" rows="3" maxlength="500"
                                  placeholder="e.g. Please deliver after 6 PM..."
                                  class="w-full bg-emerald-50/70 border-[3px] border-emerald-400 rounded-xl px-4 py-3 text-sm font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 hover:border-emerald-500 focus:border-emerald-600 focus:bg-white focus:ring-4 focus:ring-emerald-200 resize-none min-h-[7rem]">{{ old('customer_note') }}</textarea>
                        @error('customer_note')
                            <span class="text-sm text-red-500 mt-1 block ml-1">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Right: summary -->
            <div class="lg:col-span-5 h-fit lg:sticky lg:top-24">
                <div class="bg-white p-3 sm:p-6 md:p-8 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)] space-y-2">

                    <h3 class="text-lg md:text-xl font-extrabold text-gray-900 border-b-2 border-gray-100 pb-3 flex items-center justify-between tracking-tight">
                        Order Summary
                        <span class="text-sm bg-gray-100 text-gray-600 px-3 py-1 rounded-full font-bold">{{ count($cart) }} {{ count($cart) > 1 ? 'items' : 'item' }}</span>
                    </h3>

                    <!-- Items -->
                    <div class="space-y-2 max-h-[40vh] overflow-y-auto pr-1">
                        @foreach($cart as $key => $item)
                            <div class="flex gap-4 p-2 rounded-xl bg-gray-50/70 hover:bg-gray-50 border border-transparent hover:border-gray-100 transition-all">
                                <div class="w-16 h-16 rounded-xl overflow-hidden bg-white shrink-0 shadow-sm">
                                    @if($item['image'])
                                        <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start gap-2">
                                        <span class="font-bold text-gray-900 text-base truncate">{{ $item['name'] }}</span>
                                        <span class="text-base font-extrabold text-gray-900 shrink-0">৳{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                                    </div>
                                    @if($item['variation_details'])
                                        <span class="text-sm text-rose-700 font-bold block mt-0.5">{{ $item['variation_details'] }}</span>
                                    @endif
                                    <span class="text-sm text-gray-500 font-semibold">Qty: {{ $item['quantity'] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Payment method -->
                    <div class="border-t-2 border-dashed border-gray-200 pt-4 space-y-3">
                        <h4 class="text-sm font-extrabold text-gray-700 flex items-center uppercase tracking-wider">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-2 text-rose-600"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                            Payment Method
                        </h4>

                        <div class="space-y-2">
                            <!-- Cash on Delivery -->
                            <label class="flex items-center gap-3 px-2 py-1 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="selectedPayment === 'cod' ? 'border-rose-500 bg-rose-50/50' : 'border-gray-200 bg-gray-50 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="cod" x-model="selectedPayment"
                                       class="h-4 w-4 border-gray-300 text-rose-600 focus:ring-rose-500"
                                       {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                                <div class="flex-1">
                                    <span class="block font-bold text-gray-900 text-sm">Cash on Delivery</span>
                                    <span class="block text-sm font-semibold text-gray-600 mt-0.5">Pay when the order arrives at your door</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" :class="selectedPayment === 'cod' ? 'text-rose-600' : 'text-gray-300'"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            </label>

                            <!-- bKash -->
                            <label class="flex items-center gap-3 px-2 py-1 rounded-xl border-2 cursor-pointer transition-all"
                                   :class="selectedPayment === 'bkash' ? 'border-pink-500 bg-pink-50/60' : 'border-gray-200 bg-gray-50 hover:border-gray-300'">
                                <input type="radio" name="payment_method" value="bkash" x-model="selectedPayment"
                                       class="h-4 w-4 border-gray-300 text-pink-600 focus:ring-pink-500"
                                       {{ old('payment_method') === 'bkash' ? 'checked' : '' }}>
                                <div class="flex-1">
                                    <span class="block font-bold text-gray-900 text-sm">bKash Send Money</span>
                                    <span class="block text-sm font-semibold text-gray-600 mt-0.5">Send the full payment using Send Money</span>
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0" :class="selectedPayment === 'bkash' ? 'text-pink-600' : 'text-gray-300'"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                            </label>
                        </div>

                        <!-- bKash details -->
                        <div x-show="selectedPayment === 'bkash'" x-cloak x-transition
                             class="rounded-xl border-2 border-pink-200 bg-pink-50 p-4 space-y-3"
                             @keydown.escape.window="selectedPayment = 'cod'">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-pink-700 uppercase tracking-wide">Send Money to this number</p>
                                    <p class="text-lg font-extrabold text-gray-900 mt-0.5 tracking-wide">{{ $bkashNumber }}</p>
                                </div>
                                <button type="button" @click="copyBkashNumber()"
                                        class="text-xs font-bold text-pink-600 border border-pink-300 bg-white px-3 py-1.5 rounded-lg hover:bg-pink-100 transition">
                                    Copy
                                </button>
                            </div>

                            <div>
                                <label for="bkash_sender_last4" class="block text-sm font-bold text-gray-700 mb-1">
                                    bKash থেকে কোন নম্বর থেকে টাকা পাঠিয়েছেন? (শেষ ৪ ডিজিট)
                                </label>
                                <input type="text" id="bkash_sender_last4" name="bkash_sender_last4"
                                       inputmode="numeric" maxlength="4" autocomplete="off"
                                       value="{{ old('bkash_sender_last4') }}" x-model="bkashLast4"
                                       :required="selectedPayment === 'bkash'"
                                       :disabled="selectedPayment !== 'bkash'"
                                       :pattern="selectedPayment === 'bkash' ? '[0-9]{4}' : null"
                                       class="w-full bg-white border-2 border-gray-300 rounded-xl px-3 py-2.5 text-base text-center font-bold tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-pink-200 focus:border-pink-500 transition-all"
                                       placeholder="XXXX">
                                <div x-show="bkashLast4.length > 0" x-cloak class="text-sm text-pink-600 mt-1 font-semibold">
                                    শেষ ৪ ডিজিট: <span class="font-extrabold" x-text="bkashLast4"></span>
                                </div>
                                @error('bkash_sender_last4')
                                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between items-center text-gray-500 font-bold">
                            <span>Subtotal</span>
                            <span class="text-gray-900">৳{{ number_format($subtotal, 0) }}</span>
                        </div>

                        <div class="flex justify-between items-center text-gray-500 font-bold">
                            <span>Delivery Charge</span>
                            <span class="text-gray-900" x-text="formattedShippingCharge">Select a zone</span>
                        </div>

                        <div class="flex items-end justify-between border-t border-gray-100">
                            <span class="text-lg font-extrabold text-gray-900">Total</span>
                            <span class="text-3xl font-extrabold text-rose-700" x-text="formattedGrandTotal">৳{{ number_format($subtotal, 0) }}</span>
                        </div>
                    </div>

                    <!-- Confirm Order -->
                    <div class="border-t-2 border-dashed border-gray-200 pt-4">
                        <button type="submit" form="checkout-form" :disabled="submitting"
                                class="w-full bg-rose-700 text-white py-4 md:py-5 px-4 rounded-2xl font-extrabold text-lg md:text-xl hover:bg-rose-800 shadow-[0_20px_40px_-15px_rgba(225,29,72,0.5)] transition-all hover:-translate-y-0.5 active:scale-[0.98] flex items-center justify-center gap-3 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0">
                            <span x-show="!submitting" class="flex items-center gap-3">Confirm Order <span class="text-xl md:text-2xl" x-text="formattedGrandTotal">৳{{ number_format($subtotal, 0) }}</span></span>
                            <span x-show="submitting" x-cloak>Processing your order...</span>
                        </button>

                        <div class="flex items-center justify-center gap-2 text-gray-400 mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            <p class="text-[11px] font-bold uppercase tracking-wide">Secure checkout · {{ site_name() }}</p>
                        </div>

                        <a href="{{ route('cart') }}" class="block w-full text-center mt-3 text-xs font-bold text-gray-400 hover:text-gray-700 transition">
                            ← Edit Cart
                        </a>
                    </div>



                </div>
            </div>
        </form>
    </div>

</div>
@endsection

@section('scripts')
<script>
    function checkoutPage(zones) {
        const subtotal = {{ $subtotal ?? 0 }};

        const zoneMap = {};
        zones.forEach(z => {
            zoneMap[z.zone] = { charge: parseFloat(z.charge), days: z.estimated_days };
        });

        return {
            subtotal: subtotal,
            shippingCharge: 0,
            selectedZoneDays: '',
            selectedZone: '{{ old('delivery_zone') }}',
            selectedPayment: '{{ old('payment_method', 'cod') }}',
            bkashLast4: '{{ old('bkash_sender_last4') }}',
            bkashNumber: @json($bkashNumber ?? ''),
            submitting: false,

            copyBkashNumber() {
                if (!this.bkashNumber) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(this.bkashNumber);
                } else {
                    const el = document.createElement('textarea');
                    el.value = this.bkashNumber;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                }
            },

            init() {
                if (this.selectedZone && zoneMap[this.selectedZone]) {
                    this.selectZone(this.selectedZone);
                }
            },

            get formattedShippingCharge() {
                if (this.shippingCharge === 0) return 'Select a zone';
                return '৳' + Math.round(this.shippingCharge);
            },

            get formattedGrandTotal() {
                return '৳' + Math.max(0, Math.round(this.subtotal + this.shippingCharge));
            },

            selectZone(zoneName) {
                if (zoneName && zoneMap[zoneName]) {
                    this.shippingCharge = zoneMap[zoneName].charge;
                    this.selectedZoneDays = zoneMap[zoneName].days || '';
                } else {
                    this.shippingCharge = 0;
                    this.selectedZoneDays = '';
                }
            }
        }
    }
</script>
@endsection

@extends('layouts.store')

@section('title', 'চেকআউট - ' . site_name())

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
<div class="max-w-7xl mx-auto px-3 sm:px-4 lg:px-6 py-3 sm:py-4" x-data="checkoutPage({{ $deliveryZones }})">

    <h1 class="text-xl font-extrabold text-gray-900 mb-2 sm:mb-3">🔒 চেকআউট</h1>

    @if(session('error'))
        <div class="mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-semibold">
            ⚠️ {{ session('error') }}
        </div>
    @endif

    @auth
        <div class="mb-3 bg-purple-50 border border-purple-200 text-purple-800 px-3 py-2 rounded-lg text-sm font-semibold flex flex-wrap items-center gap-2">
            <span>👤 আপনার সংরক্ষিত তথ্য দিয়ে ফর্মটি পূরণ করা হয়েছে।</span>
            <a href="{{ route('customer.profile.edit') }}" class="text-purple-600 underline font-bold">প্রোফাইল এডিট করুন</a>
        </div>
    @endauth

    <form action="{{ route('checkout.place') }}" method="POST" id="checkout-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-2 sm:gap-3 lg:gap-4">

            <!-- Left: Customer Form -->
            <div class="lg:col-span-2 space-y-2 sm:space-y-3">

                <!-- Customer Information -->
                <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 space-y-2.5 sm:space-y-3 shadow-sm">
                    <h2 class="font-bold text-gray-900 text-base border-b border-gray-200 pb-2">📋 আপনার তথ্য</h2>

                    <div class="space-y-2.5 sm:space-y-3">

                        <!-- Full Name -->
                        <div>
                            <label for="customer_name" class="block text-sm font-bold text-gray-600 mb-1">
                                আপনার নাম <span class="text-pink-500">*</span>
                            </label>
                            <input type="text" id="customer_name" name="customer_name"
                                   value="{{ old('customer_name', $customer->name ?? '') }}" required
                                   class="w-full bg-white border-2 border-gray-400 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                   placeholder="আপনার পুরো নাম লিখুন">
                            @error('customer_name')
                                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div>
                            <label for="customer_phone" class="block text-sm font-bold text-gray-600 mb-1">
                                মোবাইল নম্বর <span class="text-pink-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-base font-semibold select-none">🇧🇩</span>
                                <input type="tel" id="customer_phone" name="customer_phone"
                                       value="{{ old('customer_phone', $customer->phone ?? '') }}" required
                                       pattern="^(\+?88)?01[3-9]\d{8}$"
                                       class="w-full bg-white border-2 border-gray-400 rounded-lg pl-9 pr-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                       placeholder="01XXXXXXXXX">
                            </div>
                            @error('customer_phone')
                                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                            @enderror
                            <p class="text-xs text-gray-400 mt-0.5">ফরম্যাট: 01XXXXXXXXX (বাংলাদেশি নম্বর)</p>
                        </div>

                        <!-- Address (reduced height) -->
                        <div>
                            <label for="customer_address" class="block text-sm font-bold text-gray-600 mb-1">
                                ঠিকানা <span class="text-pink-500">*</span>
                            </label>
                            <textarea id="customer_address" name="customer_address" rows="2" required
                                      class="w-full bg-white border-2 border-gray-400 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                                      placeholder="বাসা / রাস্তা / এলাকা / শহর / জেলা">{{ old('customer_address', $customer->address ?? '') }}</textarea>
                            @error('customer_address')
                                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Delivery Zone (radio buttons) -->
                        <div>
                            <label class="block text-sm font-bold text-gray-600 mb-2">
                                ডেলিভারি জোন <span class="text-pink-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($deliveryZones as $zone)
                                    <label class="relative flex items-start p-3 cursor-pointer rounded-lg border-2 transition-all"
                                           :class="selectedZone === '{{ $zone->zone }}' ? 'border-purple-500 bg-purple-50' : 'border-gray-300 bg-white hover:border-purple-400 hover:bg-gray-50'">
                                        <div class="flex items-center h-5">
                                            <input type="radio" name="delivery_zone" value="{{ $zone->zone }}"
                                                   @change="selectZone('{{ $zone->zone }}')"
                                                   x-model="selectedZone"
                                                   class="h-4 w-4 border-gray-400 text-purple-600 focus:ring-purple-500"
                                                   {{ old('delivery_zone') === $zone->zone ? 'checked' : '' }}>
                                        </div>
                                        <div class="ml-2 flex-1">
                                            <div class="flex justify-between items-center">
                                                <span class="font-bold text-gray-900 text-sm">{{ $zone->zone }}</span>
                                                <span class="font-bold text-primary text-sm">৳{{ number_format($zone->charge, 0) }}</span>
                                            </div>
                                            @if($zone->estimated_days)
                                                <p class="text-xs text-gray-400">⏱ {{ $zone->estimated_days }}-এর মধ্যে ডেলিভারি</p>
                                            @endif
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('delivery_zone')
                                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                            @enderror

                            <div x-show="selectedZoneDays" x-cloak
                                 class="mt-1.5 text-sm font-semibold text-primary bg-purple-50 border border-purple-200 px-3 py-1.5 rounded-md">
                                🚚 আনুমানিক ডেলিভারি: <span x-text="selectedZoneDays" class="font-bold"></span>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Payment Method -->
                <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-sm">
                    @php
                        $bkashNumber = App\Models\Setting::getValue('bkash_number', '');
                    @endphp
                    <h2 class="font-bold text-gray-900 text-base mb-2">💳 পেমেন্ট মেথড</h2>

                    <div class="space-y-2">
                        <!-- Cash on Delivery -->
                        <label class="relative flex items-center gap-3 p-3 cursor-pointer rounded-lg border-2 transition-all"
                               :class="selectedPayment === 'cod' ? 'border-purple-500 bg-purple-50' : 'border-gray-300 bg-white hover:border-purple-400 hover:bg-gray-50'">
                            <div class="flex items-center h-5">
                                <input type="radio" name="payment_method" value="cod"
                                       x-model="selectedPayment"
                                       class="h-4 w-4 border-gray-400 text-purple-600 focus:ring-purple-500"
                                       {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-purple-100 flex items-center justify-center text-lg">💵</div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">ক্যাশ অন ডেলিভারি</p>
                                <p class="text-gray-500 text-xs">অর্ডার আপনার দরজায় পৌঁছালে পেমেন্ট করুন</p>
                            </div>
                        </label>

                        <!-- bKash Send Money -->
                        <label class="relative flex items-center gap-3 p-3 cursor-pointer rounded-lg border-2 transition-all"
                               :class="selectedPayment === 'bkash' ? 'border-pink-500 bg-pink-50' : 'border-gray-300 bg-white hover:border-pink-400 hover:bg-gray-50'">
                            <div class="flex items-center h-5">
                                <input type="radio" name="payment_method" value="bkash"
                                       x-model="selectedPayment"
                                       class="h-4 w-4 border-gray-400 text-pink-600 focus:ring-pink-500"
                                       {{ old('payment_method') === 'bkash' ? 'checked' : '' }}>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-pink-100 flex items-center justify-center text-lg">💳</div>
                            <div>
                                <p class="font-bold text-gray-900 text-sm">bKash Send Money</p>
                                <p class="text-gray-500 text-xs">bKash থেকে Send Money করে সম্পূর্ণ পেমেন্ট করুন</p>
                            </div>
                        </label>
                    </div>

                    <!-- bKash details (only when selected) -->
                    <div x-show="selectedPayment === 'bkash'" x-cloak x-transition
                         class="mt-3 rounded-lg border-2 border-pink-200 bg-pink-50 p-4 space-y-3"
                         @keydown.escape.window="selectedPayment = 'cod'">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-pink-700 uppercase tracking-wide">💯 Send Money করুন এই নম্বরে</p>
                                <p class="text-lg font-extrabold text-gray-900 mt-0.5 tracking-wide">{{ $bkashNumber }}</p>
                            </div>
                            <button type="button" @click="copyBkashNumber()"
                                    class="text-xs font-bold text-pink-600 border border-pink-300 bg-white px-3 py-1.5 rounded-lg hover:bg-pink-100 focus:outline-none transition">
                                📋 কপি করুন
                            </button>
                        </div>

                        <div>
                            <label for="bkash_sender_last4" class="block text-sm font-bold text-gray-700 mb-1">
                                কোন নম্বর থেকে Send Money করেছেন? (শেষ <span class="text-pink-500">৪ ডিজিট</span>)
                            </label>
                            <input type="text" id="bkash_sender_last4" name="bkash_sender_last4"
                                   inputmode="numeric" maxlength="4" autocomplete="off"
                                   value="{{ old('bkash_sender_last4') }}" x-model="bkashLast4" required
                                   class="w-full bg-white border-2 border-gray-400 rounded-lg px-3 py-2.5 text-base text-center font-bold tracking-[0.5em] focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-pink-500 transition-all"
                                   placeholder="XXXX">
                            <div x-show="bkashLast4.length > 0" x-cloak
                                 class="text-xs text-pink-600 mt-1 font-semibold">
                                ✅ যেই নম্বর থেকে Send Money করেছেন তার শেষ ৪ ডিজিট: <span class="font-extrabold" x-text="bkashLast4"></span>
                            </div>
                            @error('bkash_sender_last4')
                                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right: Order Summary -->
            <aside class="space-y-2 sm:space-y-3">
                <div class="bg-white border border-gray-200 p-3 sm:p-4 rounded-xl shadow-sm space-y-2.5 sm:space-y-3 lg:sticky lg:top-20">
                    <h3 class="font-bold text-gray-900 text-base">🛒 অর্ডার সারসংক্ষেপ</h3>

                    <!-- Cart Items -->
                    <div class="space-y-2">
                        @php $subtotal = 0; @endphp
                        @foreach($cart as $key => $item)
                            @php $subtotal += $item['price'] * $item['quantity']; @endphp
                            <div class="flex justify-between items-start text-sm">
                                <div class="flex-1 flex items-start gap-2.5 pr-2">
                                    <div class="w-11 h-11 bg-gray-50 border border-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                        @if($item['image'])
                                            <img src="{{ Storage::url($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-purple-300">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="font-semibold text-gray-900">{{ $item['name'] }}</span>
                                        @if($item['variation_details'])
                                            <span class="text-xs text-purple-500 font-semibold block">{{ $item['variation_details'] }}</span>
                                        @endif
                                        <span class="text-gray-400 text-xs">× {{ $item['quantity'] }}</span>
                                    </div>
                                </div>
                                <span class="font-semibold text-gray-900 whitespace-nowrap">৳{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="border-t border-gray-200 pt-3 space-y-1.5 text-sm">
                        <div class="flex justify-between text-gray-500">
                            <span>সাবটোটাল</span>
                            <span class="font-semibold text-gray-900">৳{{ number_format($subtotal, 0) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-500">
                            <span>ডেলিভারি চার্জ</span>
                            <span class="font-semibold text-gray-900" x-text="formattedShippingCharge">জোন নির্বাচন করুন</span>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-3 flex justify-between font-bold text-gray-900">
                        <span>সর্বমোট</span>
                        <span class="text-purple-600" x-text="formattedGrandTotal">৳{{ number_format($subtotal, 0) }}</span>
                    </div>

                    <button type="submit"
                            class="block w-full text-center py-4 bg-primary hover:bg-primary text-white rounded-lg font-bold transition shadow-md active:scale-95 text-lg">
                        ✅ অর্ডার কনফার্ম করুন
                    </button>

                    <a href="{{ route('cart') }}"
                       class="block w-full text-center py-1.5 text-xs font-bold text-gray-400 hover:text-gray-700 transition">
                        ← কার্ট এডিট করুন
                    </a>
                </div>
            </aside>
        </div>
    </form>

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
                if (this.shippingCharge === 0) return 'জোন নির্বাচন করুন';
                return '৳' + Math.round(this.shippingCharge);
            },

            get formattedGrandTotal() {
                return '৳' + Math.round(this.subtotal + this.shippingCharge);
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

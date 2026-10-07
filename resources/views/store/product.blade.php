@extends('layouts.store')

@section('title', "{$product->name} - " . site_name())

@section('pixel_events')
<script>
fbq('track', 'ViewContent', {
    content_ids: [{{ $product->id }}],
    content_name: '{{ str_replace("'", "\\'", $product->name) }}',
    content_type: 'product',
    value: {{ $product->getDisplayPrice() ?: 0 }},
    currency: 'BDT'
});
</script>
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-2 md:py-6 pb-24 md:pb-6" x-data="productDetail({{ json_encode($variationsJson) }}, {{ json_encode($attributesJson) }})">
    
    <!-- Breadcrumbs (desktop only) -->
    <nav class="hidden md:flex text-xs font-bold text-gray-400 mb-3 space-x-2">
        <a href="{{ route('home') }}" class="hover:text-purple-600 transition">Home</a>
        <span>/</span>
        <a href="{{ route('shop') }}" class="hover:text-purple-600 transition">Shop</a>
        @if($product->categories->count() > 0)
            @php $mainCat = $product->categories->first(); @endphp
            <span>/</span>
            <a href="{{ route('shop', ['category' => $mainCat->slug]) }}" class="hover:text-purple-600 transition">{{ $mainCat->name }}</a>
        @endif
        <span>/</span>
        <span class="text-gray-600">{{ $product->name }}</span>
    </nav>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 md:gap-6 items-start">
        
        <!-- Left: Image Gallery -->
        @php
            $imagesList = $product->images;
            $featuredImg = $imagesList->where('is_featured', true)->first() ?? $imagesList->first();
        @endphp
        <div class="space-y-1.5 md:space-y-2">
            <div class="bg-gray-100 aspect-square w-full rounded-xl md:rounded-2xl overflow-hidden border border-gray-200 relative cursor-zoom-in select-none group"
                 x-ref="zoomContainer"
                 @mousemove="onZoomMove($event)"
                 @mouseenter="zoomActive = true"
                 @mouseleave="zoomActive = false"
                 @click="openLightbox()">
                <img :src="activeImage" src="{{ $featuredImg ? Storage::url($featuredImg->image) : '/placeholder.png' }}" 
                     alt="{{ $product->name }}" class="w-full h-full object-cover object-center transition duration-300">

                <!-- Zoom box / magnifier lens that follows the cursor -->
                <div x-show="zoomActive" x-cloak
                     class="absolute pointer-events-none rounded-lg md:rounded-xl border-2 border-white shadow-xl ring-1 ring-black/10"
                     :style="zoomLensStyle"></div>

                <!-- Hover hint (desktop only) -->
                <div x-show="!zoomActive" class="hidden md:flex absolute bottom-2 left-1/2 -translate-x-1/2 items-center gap-1 bg-black/50 text-white text-[11px] font-bold px-2.5 py-1 rounded-full pointer-events-none transition-opacity duration-150">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 3h6v6M10 14 21 3M21 14v7H3V3h7"/>
                    </svg>
                    Click to view larger
                </div>
            </div>

            @if($imagesList->count() > 1)
                <div class="flex space-x-2 md:space-x-3 overflow-x-auto pb-1 md:pb-2">
                    @foreach($imagesList as $img)
                        <button type="button" 
                                @click="activeImage = '{{ Storage::url($img->image) }}'; resetAutoSlide()"
                                class="h-16 w-16 md:h-20 md:w-20 bg-white border rounded-lg md:rounded-xl overflow-hidden flex-shrink-0 hover:border-gray-900 focus:outline-none focus:border-gray-900 transition-all"
                                :class="activeImage === '{{ Storage::url($img->image) }}' ? 'border-gray-900 ring-2 ring-gray-200' : 'border-gray-200'">
                            <img src="{{ Storage::url($img->image) }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Info & Form -->
        <div>
            
            <!-- Title -->
            <div>
                <h1 class="text-3xl sm:text-4xl lg:text-4xl font-bold lg:font-extrabold text-gray-900">{{ $product->name }}</h1>
            </div>

            <!-- Price + Stock -->
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 pt-0.5">
                @if($product->sale_price !== null)
                    <span class="font-bold text-primary text-xl md:text-2xl" x-text="formattedPrice">৳{{ number_format($product->sale_price, 0) }}</span>
                    <span class="text-sm text-gray-400 line-through">৳{{ number_format($product->regular_price, 0) }}</span>
                    <span class="inline-flex shrink-0 rounded-md border border-green-200 bg-green-50 px-2 py-0.5 text-xs font-bold text-green-700">Save ৳{{ number_format($product->regular_price - $product->sale_price, 0) }}</span>
                @else
                    <span class="font-bold text-primary text-xl md:text-2xl" x-text="formattedPrice">৳{{ number_format($product->regular_price, 0) }}</span>
                @endif

                <!-- Stock -->
                @if($product->product_type === 'simple')
                    @if($product->stock_quantity > 0)
                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 bg-green-50 text-green-700 rounded-full border border-green-200">✅ In Stock</span>
                    @else
                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 bg-red-50 text-red-700 rounded-full border border-red-200">❌ Out of Stock</span>
                    @endif
                @else
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border"
                          :class="variationStock === null || variationStock > 0 ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                          x-text="stockStatusText">
                    </span>
                @endif
            </div>

            <!-- Short Description -->
            @if($product->short_description)
                <div class="text-base text-gray-600 leading-relaxed [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-purple-600 [&_a]:underline p-2 bg-gray-100 border border-slate-500 rounded-md">{!! $product->short_description !!}</div>
            @endif

            <!-- Form -->
            <form action="{{ route('cart.add') }}" method="POST" id="product-cart-form" class="space-y-1.5 md:space-y-2 border-t border-gray-200 pt-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($product->product_type === 'variable')
                    <input type="hidden" name="variation_id" x-model="selectedVariationId">

                    <div class="space-y-2.5 md:space-y-3 bg-purple-50/30 p-2.5 md:p-3 rounded-xl border border-purple-100/50">
                        @forelse($attributesJson as $attribute)
                            @php $isSwatch = in_array($attribute['type'], ['color', 'image'], true); @endphp

                            <div>
                                <label class="block text-xs text-gray-500 font-bold mb-1.5">{{ $attribute['name'] }}</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($attribute['values'] as $value)
                                        @if($isSwatch)
                                            <button type="button"
                                                    @click="select({{ $attribute['id'] }}, {{ $value['id'] }})"
                                                    class="w-9 h-9 md:w-10 md:h-10 rounded-full border-2 transition-all flex items-center justify-center font-bold text-xs hover:scale-105"
                                                    :class="isSelected({{ $attribute['id'] }}, {{ $value['id'] }}) ? 'border-gray-900 ring-2 ring-gray-300 shadow' : 'border-gray-300 hover:border-gray-600'"
                                                    style="background-color: {{ $value['color'] }}; @if(! empty($value['image'])) background-image: url('{{ $value['image'] }}'); background-size: cover; background-position: center; @endif color: {{ in_array(strtolower($value['name']), ['white', 'yellow', 'lime']) ? '#333' : 'white' }};"
                                                    title="{{ $value['name'] }}">
                                                <span x-show="isSelected({{ $attribute['id'] }}, {{ $value['id'] }})" class="text-sm">✓</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                    @click="select({{ $attribute['id'] }}, {{ $value['id'] }})"
                                                    class="px-4 py-1.5 rounded-lg border-2 font-bold text-sm transition-all active:scale-95 min-w-[3rem] text-center"
                                                    :class="isSelected({{ $attribute['id'] }}, {{ $value['id'] }}) ? 'bg-gray-900 border-gray-900 text-white shadow' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-900 hover:bg-gray-50'">
                                                {{ $value['name'] }}
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500">No options available for this product yet.</p>
                        @endforelse
                    </div>
                @endif

                <!-- Purchase Box -->
                <div class="md:rounded-2xl md:border md:border-gray-200 md:bg-white md:p-4 md:shadow-[0_4px_20px_rgba(15,23,42,0.06)] md:space-y-3">

                    <!-- Quantity + Total -->
                    <div class="flex items-end justify-between gap-3">
                        <div>
                            <label for="quantity" class="block text-[11px] font-bold uppercase tracking-wider text-gray-500 mb-1.5">Quantity</label>
                            <div class="inline-flex flex-shrink-0 items-center overflow-hidden rounded-xl border border-gray-200 bg-gray-50">
                                <button type="button" @click="if(qty > 1) qty--" aria-label="Decrease quantity"
                                        class="flex h-11 w-11 items-center justify-center text-lg font-bold bg-white/70 text-gray-500 hover:bg-white hover:text-gray-900 transition-colors focus:outline-none">−</button>
                                <input type="number" id="quantity" name="quantity" x-model="qty" readonly
                                       class="h-11 w-12 border-x border-gray-200 bg-transparent p-0 text-center text-sm font-bold text-gray-900 focus:ring-0">
                                <button type="button" @click="qty++" aria-label="Increase quantity"
                                        class="flex h-11 w-11 items-center justify-center text-lg font-bold bg-white/70 text-gray-500 hover:bg-white hover:text-gray-900 transition-colors focus:outline-none">+</button>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="block text-[11px] font-bold uppercase tracking-wider text-gray-400">Total</span>
                            <span class="text-xl font-extrabold text-primary md:text-2xl" x-text="totalPrice">৳{{ number_format($product->getDisplayPrice(), 0) }}</span>
                        </div>
                    </div>

                <!-- Desktop Purchase Buttons (md+) -->
                <div class="hidden md:grid grid-cols-[1fr_1.35fr] gap-3">
                    <button type="button" @click="addCart()" :disabled="!canOrder"
                            class="flex h-[54px] w-full items-center justify-center gap-2 rounded-xl border-0 bg-black px-5 text-base font-bold text-white shadow-[0_8px_18px_rgba(0,0,0,0.25)] transition hover:-translate-y-0.5 hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <span>Add to Cart</span>
                    </button>
                    <button type="button" @click="buyNow()" :disabled="!canOrder"
                            class="animate-order-pulse flex h-[54px] w-full items-center justify-center gap-2 rounded-xl bg-rose-700 px-5 text-base font-extrabold tracking-wide text-white shadow-[0_10px_24px_rgba(225,29,72,0.35)] transition hover:-translate-y-0.5 hover:bg-rose-800 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:translate-y-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
                        <span>Order Now</span>
                    </button>
                </div>

                <!-- Desktop Contact Buttons (md+) -->
                @php
                    $whatsappNumber = App\Models\Setting::getValue('whatsapp_number', '');
                    $whatsappDigits = preg_replace('/[^0-9]/', '', $whatsappNumber);
                @endphp
                @if($whatsappNumber)
                    <div class="flex flex-col gap-3">
                        <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer"
                           class="group flex items-center gap-4 bg-lime-50 border-2 border-lime-500/30 p-4 rounded-3xl hover:bg-lime-700 hover:text-white transition-all">
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-md shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="w-8 h-8">
                                    <path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Z" fill="#25D366"/>
                                    <path d="M17.06 14.31c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z" fill="white"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm lg:text-base font-black uppercase leading-tight">WhatsApp এ মেসেজ করুন</p>
                                <p class="text-lg lg:text-xl font-bold opacity-90 group-hover:text-white truncate">{{ $whatsappNumber }}</p>
                            </div>
                        </a>
                        <a href="tel:+{{ $whatsappDigits }}"
                           class="group flex items-center gap-4 bg-sky-50 border-2 border-sky-500/30 p-4 rounded-3xl hover:bg-sky-700 hover:text-white transition-all">
                            <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-md shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6 fill-sky-500 text-sky-500" aria-hidden="true"><path d="M13.832 16.568a1 1 0 0 0 1.213-.303l.355-.465A2 2 0 0 1 17 15h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2A18 18 0 0 1 2 4a2 2 0 0 1 2-2h3a2 2 0 0 1 2 2v3a2 2 0 0 1-.8 1.6l-.468.351a1 1 0 0 0-.292 1.233 14 14 0 0 0 6.392 6.384"/></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm lg:text-base font-black uppercase leading-tight">কল করুন</p>
                                <p class="text-lg lg:text-xl font-bold opacity-90 group-hover:text-white truncate">{{ $whatsappNumber }}</p>
                            </div>
                        </a>
                    </div>
                @endif
                </div>
            </form>

            <!-- Trust Features -->
            <div class="grid grid-cols-2 gap-2 md:gap-3 mt-2 md:mt-3">
                <div class="flex items-start md:items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl md:rounded-2xl p-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold text-gray-900 leading-tight">Fast Delivery</p>
                        <p class="text-[11px] md:text-xs text-gray-500 mt-0.5">Safe delivery within 2-3 days</p>
                    </div>
                </div>
                <div class="flex items-start md:items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl md:rounded-2xl p-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold text-gray-900 leading-tight">Cash on Delivery</p>
                        <p class="text-[11px] md:text-xs text-gray-500 mt-0.5">Pay when you receive the product</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Full Description -->
    @if($product->description)
        <div class="mt-4 md:mt-8 border-t border-gray-200 pt-4 md:pt-6">
            <h3 class="text-lg md:text-2xl font-extrabold text-gray-900 mb-2 md:mb-3">📄 Details</h3>
            <div class="text-base text-gray-600 leading-relaxed [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-purple-600 [&_a]:underline max-w-none">{!! $product->description !!}</div>
        </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <section class="mt-5 md:mt-10 border-t border-gray-200 pt-4 md:pt-8 space-y-3 md:space-y-4">
            <h2 class="text-lg md:text-2xl font-extrabold text-gray-900 text-center">See more</h2>
            {{-- Exactly 4 related products are fetched, so the grid stays at 4
                 columns on desktop. A 5th column would leave a gap. --}}
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 md:gap-4">
                @foreach($relatedProducts as $rel)
                    @include('store.partials.product-card', ['product' => $rel])
                @endforeach
            </div>
        </section>
    @endif

    <!-- Mobile Sticky Purchase Bar -->
    <div class="fixed inset-x-0 bottom-0 z-[1100] md:hidden border-t border-gray-200 bg-white px-3 pt-2.5 pb-[calc(10px+env(safe-area-inset-bottom))] shadow-[0_-8px_24px_rgba(15,23,42,0.12)]">
        <div class="mx-auto flex max-w-lg items-center gap-2">
            @if($whatsappNumber)
                <a href="https://wa.me/{{ $whatsappDigits }}" target="_blank" rel="noopener noreferrer" aria-label="Order Via WhatsApp"
                   class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border-0 bg-[#25D366] text-white transition active:scale-95 hover:bg-[#1ebe5b]">
<div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center shadow-md shrink-0">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="w-8 h-8">
                                    <path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Z" fill="#25D366"></path>
                                    <path d="M17.06 14.31c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z" fill="white"></path>
                                </svg>
                            </div>
                </a>
            @endif
            <button type="button" @click="addCart()" :disabled="!canOrder"
                    class="flex h-12 min-w-0 flex-1 items-center justify-center gap-1.5 rounded-xl border-0 bg-black px-2 text-xs font-bold text-white shadow-[0_4px_12px_rgba(0,0,0,0.3)] transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                <span class="truncate">Add Cart</span>
            </button>
            <button type="button" @click="buyNow()" :disabled="!canOrder"
                    class="animate-order-pulse flex h-12 min-w-0 flex-[1.5] items-center justify-center gap-1.5 rounded-xl bg-rose-700 px-2 text-xs font-extrabold text-white shadow-[0_6px_16px_rgba(225,29,72,0.4)] transition active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-4 w-4 shrink-0"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
                <span class="truncate">Order Now</span>
            </button>
        </div>
    </div>

    <!-- Lightbox -->
    <div x-show="lightboxOpen" x-cloak x-transition.opacity.duration.200ms
         class="fixed inset-0 z-[4000] flex items-center justify-center bg-black/90 p-3 sm:p-6"
         @click.self="closeLightbox()"
         @keydown.escape.window="closeLightbox()"
         @keydown.arrow-right.window.prevent="lightboxNext()"
         @keydown.arrow-left.window.prevent="lightboxPrev()">

        <button type="button" @click="closeLightbox()" aria-label="Close"
                class="absolute top-3 right-3 sm:top-5 sm:right-5 z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25 transition">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="relative max-w-full max-h-full flex items-center justify-center">
            <img :src="lightboxImage" :alt="'{{ $product->name }}'" loading="lazy"
                 class="max-h-[82vh] max-w-full object-contain rounded-lg shadow-2xl">
        </div>

        <template x-if="imageUrls.length > 1">
            <div>
                <button type="button" @click="lightboxPrev()" aria-label="Previous"
                        class="absolute left-2 sm:left-5 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <button type="button" @click="lightboxNext()" aria-label="Next"
                        class="absolute right-2 sm:right-5 top-1/2 -translate-y-1/2 flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-white hover:bg-white/25 transition">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>
        </template>

        <template x-if="imageUrls.length > 1">
            <div class="absolute bottom-3 left-1/2 -translate-x-1/2 max-w-full overflow-x-auto no-scrollbar flex gap-2 p-2">
                <template x-for="(img, i) in imageUrls" :key="i">
                    <button type="button" @click="lightboxIndex = i"
                            class="h-14 w-14 shrink-0 overflow-hidden rounded-lg border-2 transition"
                            :class="lightboxIndex === i ? 'border-white ring-2 ring-white/50' : 'border-white/20 opacity-60 hover:opacity-100'">
                        <img :src="img" :alt="i" class="h-full w-full object-cover" loading="lazy">
                    </button>
                </template>
            </div>
        </template>
    </div>

</div>
@endsection

@section('scripts')
<style>
    .shine {
        position: absolute;
        top: 0;
        left: -75%;
        width: 50%;
        height: 100%;
        background: linear-gradient(100deg, transparent 20%, rgba(255,255,255,0.5) 50%, transparent 80%);
        transform: skewX(-20deg);
        animation: shine-sweep 2.5s ease-in-out infinite;
    }

    @keyframes shine-sweep {
        0% { left: -75%; }
        50% { left: 125%; }
        100% { left: -75%; }
    }

    @keyframes buy-now-pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.03); }
    }

    .animate-buy-now {
        animation: buy-now-pulse 2s ease-in-out infinite;
    }

    .animate-buy-now:hover {
        animation: none;
    }

    @keyframes order-pulse {
        0% {
            box-shadow: 0 8px 22px rgba(225, 29, 72, 0.32), 0 0 0 0 rgba(225, 29, 72, 0.45);
            transform: scale(1);
        }
        70% {
            box-shadow: 0 8px 22px rgba(225, 29, 72, 0.32), 0 0 0 12px rgba(225, 29, 72, 0);
            transform: scale(1.02);
        }
        100% {
            box-shadow: 0 8px 22px rgba(225, 29, 72, 0.32), 0 0 0 0 rgba(225, 29, 72, 0);
            transform: scale(1);
        }
    }

    .animate-order-pulse {
        animation: order-pulse 2s ease-out infinite;
    }

    .animate-order-pulse:hover {
        animation: none;
    }

    .animate-order-pulse:disabled {
        animation: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .animate-order-pulse {
            animation: none;
        }
    }
</style>
<script>
    function productDetail(variations, attributes) {
        return {
            variations: variations || [],
            attributes: attributes || [],
            defaultImage: '{{ $featuredImg ? Storage::url($featuredImg->image) : "/placeholder.png" }}',
            activeImage: '{{ $featuredImg ? Storage::url($featuredImg->image) : "/placeholder.png" }}',
            /** Selected value id per product attribute id. */
            selected: {},
            qty: 1,
            selectedVariationId: '',
            variationStock: 0,
            variationOutOfStock: false,
            variationPrice: 0,

            productType: '{{ $product->product_type }}',
            simpleInStock: {{ $product->product_type === 'simple' ? ($product->manage_stock ? ($product->stock_quantity > 0 ? 'true' : 'false') : 'true') : 'true' }},
            
            formattedPrice: '৳{{ number_format($product->getDisplayPrice(), 0) }}',
            stockStatusText: 'Select an option',
            buttonText: 'Select an option',

            imageUrls: {!! json_encode($imagesList->pluck('image')->map(fn($i) => Storage::url($i))->values()) !!},
            autoSlideInterval: null,
            slideIndex: 0,

            zoomActive: false,
            zoomX: 50,
            zoomY: 50,
            zoomFactorPct: 42,
            zoomLevel: 10,

            init() {
                this.startAutoSlide();
            },

            /** The attributes that actually build variations. */
            get variationAttributeIds() {
                return this.attributes.filter(a => a.is_variation).map(a => a.id);
            },

            select(attributeId, valueId) {
                this.selected[attributeId] = valueId;
                this.matchVariation();
            },

            isSelected(attributeId, valueId) {
                return this.selected[attributeId] == valueId;
            },

            onZoomMove(e) {
                const el = this.$refs.zoomContainer;
                if (!el) return;
                const rect = el.getBoundingClientRect();
                if (!rect.width || !rect.height) return;
                this.zoomX = ((e.clientX - rect.left) / rect.width) * 100;
                this.zoomY = ((e.clientY - rect.top) / rect.height) * 100;
            },

            get zoomLensStyle() {
                const size = this.zoomFactorPct;
                const half = size / 2;
                const cx = Math.min(Math.max(this.zoomX, half), 100 - half);
                const cy = Math.min(Math.max(this.zoomY, half), 100 - half);

                return {
                    width: size + '%',
                    height: size + '%',
                    left: (cx - half) + '%',
                    top: (cy - half) + '%',
                    backgroundImage: 'url("' + this.activeImage + '")',
                    backgroundSize: (this.zoomLevel * size) + '% ' + (this.zoomLevel * size) + '%',
                    backgroundPosition: cx + '% ' + cy + '%',
                    backgroundRepeat: 'no-repeat',
                };
            },

            lightboxOpen: false,
            lightboxIndex: 0,

            get lightboxImage() {
                return this.imageUrls[this.lightboxIndex] || this.activeImage;
            },

            openLightbox() {
                this.zoomActive = false;
                this.lightboxIndex = this.imageUrls.indexOf(this.activeImage);
                if (this.lightboxIndex === -1) this.lightboxIndex = 0;
                this.lightboxOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeLightbox() {
                this.lightboxOpen = false;
                document.body.style.overflow = '';
            },

            lightboxNext() {
                this.lightboxIndex = (this.lightboxIndex + 1) % this.imageUrls.length;
            },

            lightboxPrev() {
                this.lightboxIndex = (this.lightboxIndex - 1 + this.imageUrls.length) % this.imageUrls.length;
            },

            startAutoSlide() {
                if (this.autoSlideInterval) clearInterval(this.autoSlideInterval);
                if (this.imageUrls.length < 2) return;
                this.autoSlideInterval = setInterval(() => {
                    this.slideIndex = (this.slideIndex + 1) % this.imageUrls.length;
                    this.activeImage = this.imageUrls[this.slideIndex];
                }, 4000);
            },

            resetAutoSlide() {
                this.slideIndex = this.imageUrls.indexOf(this.activeImage);
                if (this.slideIndex === -1) this.slideIndex = 0;
                this.startAutoSlide();
            },

            stopAutoSlide() {
                if (this.autoSlideInterval) {
                    clearInterval(this.autoSlideInterval);
                    this.autoSlideInterval = null;
                }
            },

            matchVariation() {
                var basePrice = {{ $product->getDisplayPrice() ?: 0 }};
                var required = this.variationAttributeIds;

                this.selectedVariationId = '';
                this.variationStock = 0;
                this.variationOutOfStock = false;
                this.variationPrice = basePrice;
                this.formattedPrice = '৳' + Number(basePrice).toFixed(0);
                this.stockStatusText = 'Select an option';
                this.buttonText = 'Select an option';

                // Nothing to match until every variation attribute is chosen.
                var chosen = required.filter(id => this.selected[id]);
                if (required.length === 0 || chosen.length < required.length) {
                    return;
                }

                // Spec: prefer an exact match on every attribute, then fall back
                // to a variation that leaves one or more attributes as "Any".
                var matches = function (allowAny) {
                    return function (v) {
                        return required.every(function (id) {
                            var variationValue = v.values[id];
                            if (variationValue === null || variationValue === undefined) {
                                return allowAny;
                            }
                            return String(variationValue) === String(this.selected[id]);
                        }, this);
                    };
                };

                var match = this.variations.find(matches(false), this)
                    || this.variations.find(matches(true), this);

                if (!match) {
                    this.stockStatusText = 'This combination is unavailable';
                    this.formattedPrice = 'N/A';
                    this.buttonText = 'Combination unavailable';
                    return;
                }

                // A published option can still be unbuyable, for instance when it
                // has no price yet. Never show the parent's price for it and
                // never let the shopper submit it.
                if (match.purchasable === false) {
                    this.stockStatusText = match.unavailable_reason || 'This option is unavailable';
                    this.formattedPrice = 'N/A';
                    this.buttonText = '❌ Unavailable';
                    return;
                }

                this.selectedVariationId = match.id;
                // null means stock is not tracked for this option, so the
                // quantity display is left to the in-stock badge below.
                this.variationStock = match.stock;

                if (match.price !== null && match.price !== undefined) {
                    this.variationPrice = parseFloat(match.price);
                    this.formattedPrice = '৳' + this.variationPrice.toFixed(0);
                }

                // A variation with its own image swaps the main image and holds
                // it there; otherwise the gallery keeps auto-rotating.
                if (match.image) {
                    this.activeImage = match.image;
                    this.stopAutoSlide();
                } else if (this.activeImage !== this.defaultImage) {
                    this.activeImage = this.defaultImage;
                    this.resetAutoSlide();
                }

                if (match.in_stock) {
                    this.stockStatusText = '✅ In Stock';
                    this.buttonText = '🛒 Add to Cart';
                } else {
                    this.stockStatusText = '❌ Out of Stock';
                    this.buttonText = '❌ Out of Stock';
                }

                this.variationOutOfStock = !match.in_stock;
            },

            get canOrder() {
                if (this.productType === 'simple') return this.simpleInStock;
                if (!this.selectedVariationId) return false;
                // A null quantity means stock is not tracked for this option,
                // so availability comes from the option's stock badge alone.
                if (this.variationStock === null || this.variationStock === undefined) {
                    return !this.variationOutOfStock;
                }
                return this.variationStock > 0;
            },

            get totalPrice() {
                var unit = parseFloat(String(this.formattedPrice).replace(/[^0-9.]/g, ''));
                if (isNaN(unit)) return this.formattedPrice;
                var total = unit * (parseInt(this.qty, 10) || 1);
                return '৳' + total.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            },

            addCart() {
                if (!this.canOrder) return;
                this.submitForm(false);
            },

            buyNow() {
                if (!this.canOrder) return;
                this.submitForm(true);
            },

            submitForm(buyNow) {
                const form = document.getElementById('product-cart-form');
                if (!form) return;
                let buy = form.querySelector('input[name="buy_now"]');
                if (buyNow) {
                    if (!buy) {
                        buy = document.createElement('input');
                        buy.type = 'hidden';
                        buy.name = 'buy_now';
                        buy.value = '1';
                        form.appendChild(buy);
                    } else {
                        buy.value = '1';
                    }
                } else if (buy) {
                    buy.remove();
                }
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                }
            }
        }
    }
</script>
@endsection

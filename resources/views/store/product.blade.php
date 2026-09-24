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
<div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-3 md:py-10 pb-24 md:pb-10" x-data="productDetail({{ json_encode($variationsJson) }})">
    
    <!-- Breadcrumbs (desktop only) -->
    <nav class="hidden md:flex text-xs font-bold text-gray-400 mb-6 space-x-2">
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
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-10 items-start">
        
        <!-- Left: Image Gallery -->
        @php
            $imagesList = $product->images;
            $featuredImg = $imagesList->where('is_featured', true)->first() ?? $imagesList->first();
        @endphp
        <div class="space-y-2 md:space-y-4">
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
                    ক্লিক করে বড় দেখুন
                </div>
            </div>

            @if($imagesList->count() > 1)
                <div class="flex space-x-2 md:space-x-3 overflow-x-auto pb-1 md:pb-2">
                    @foreach($imagesList as $img)
                        <button type="button" 
                                @click="activeImage = '{{ Storage::url($img->image) }}'; resetAutoSlide()"
                                class="h-16 w-16 md:h-20 md:w-20 bg-white border rounded-lg md:rounded-xl overflow-hidden flex-shrink-0 hover:border-purple-500 focus:outline-none focus:border-purple-500 transition-all"
                                :class="activeImage === '{{ Storage::url($img->image) }}' ? 'border-purple-600 ring-2 ring-purple-100' : 'border-gray-200'">
                            <img src="{{ Storage::url($img->image) }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Right: Info & Form -->
        <div>
            
            <!-- Title + Badge -->
            <div>
                @if($product->is_featured)
                    <span class="text-[10px] font-extrabold bg-purple-50 text-primary border border-purple-100 px-2.5 py-0.5 rounded-full">Featured</span>
                @endif
                <h1 class="text-xl md:text-3xl font-extrabold text-gray-900 {{ $product->is_featured ? 'mt-1' : '' }}">{{ $product->name }}</h1>
            </div>

            <!-- Price -->
            <div class="flex items-baseline gap-3 pt-1 md:pt-2">
                @if($product->sale_price !== null)
                    <span class="font-bold text-primary text-xl md:text-2xl" x-text="formattedPrice">৳{{ number_format($product->sale_price, 2) }}</span>
                    <span class="text-sm text-gray-400 line-through">৳{{ number_format($product->regular_price, 2) }}</span>
                @else
                    <span class="font-bold text-primary text-xl md:text-2xl" x-text="formattedPrice">৳{{ number_format($product->regular_price, 2) }}</span>
                @endif
            </div>

            <!-- Short Description -->
            @if($product->short_description)
                <div class="text-base text-gray-600 leading-relaxed [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-purple-600 [&_a]:underline p-2 bg-gray-100 border border-slate-500 rounded-md">{!! $product->short_description !!}</div>
            @endif

            <!-- Form -->
            <form action="{{ route('cart.add') }}" method="POST" id="product-cart-form" class="space-y-2 md:space-y-3 border-t border-gray-200">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">

                @if($product->product_type === 'variable')
                    <input type="hidden" name="variation_id" x-model="selectedVariationId">

                    <div class="space-y-3 md:space-y-4 bg-purple-50/30 p-3 md:p-4 rounded-xl border border-purple-100/50">
                        @php
                            $sizes = $product->variations->pluck('size')->filter()->unique()->values()->toArray();
                            $colors = $product->variations->pluck('color')->filter()->unique()->values()->toArray();
                        @endphp

                        @if(count($colors) > 0)
                            <div>
                                <label class="block text-xs text-gray-500 font-bold mb-2">রং</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($colors as $color)
                                        <button type="button"
                                                @click="selectedColor = '{{ $color }}'; matchVariation()"
                                                class="w-9 h-9 md:w-10 md:h-10 rounded-full border-2 transition-all flex items-center justify-center font-bold text-xs hover:scale-105"
                                                :class="selectedColor === '{{ $color }}' ? 'border-purple-600 ring-2 ring-purple-200 shadow' : 'border-gray-300 hover:border-purple-400'"
                                                style="background-color: {{ strtolower($color) }}; color: {{ in_array(strtolower($color), ['white', 'yellow', 'lime']) ? '#333' : 'white' }};"
                                                title="{{ $color }}">
                                            <span x-show="selectedColor === '{{ $color }}'" class="text-sm">✓</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if(count($sizes) > 0)
                            <div>
                                <label class="block text-xs text-gray-500 font-bold mb-2">সাইজ</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach($sizes as $size)
                                        <button type="button"
                                                @click="selectedSize = '{{ $size }}'; matchVariation()"
                                                class="px-4 py-2 rounded-lg border-2 font-bold text-sm transition-all active:scale-95 min-w-[3rem] text-center"
                                                :class="selectedSize === '{{ $size }}' ? 'bg-purple-600 border-purple-600 text-white shadow' : 'bg-white border-gray-300 text-gray-700 hover:border-purple-400 hover:bg-gray-50'">
                                            {{ $size }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Quantity + Price -->
                <div class="flex items-center justify-between gap-3">
                    <label for="quantity" class="sr-only">Quantity</label>
                    <div class="inline-flex items-center bg-gray-50 border border-gray-300 rounded-lg overflow-hidden flex-shrink-0">
                        <button type="button" @click="if(qty > 1) qty--" class="px-2.5 py-2.5 text-gray-600 hover:bg-gray-200 focus:outline-none text-base font-bold transition-colors">−</button>
                        <input type="number" id="quantity" name="quantity" x-model="qty" readonly class="w-14 text-center bg-transparent border-0 text-sm focus:ring-0 p-0 font-bold text-gray-900">
                        <button type="button" @click="qty++" class="px-2.5 py-2.5 text-gray-600 hover:bg-gray-200 focus:outline-none text-base font-bold transition-colors">+</button>
                    </div>
                    <div class="flex flex-wrap items-center justify-end gap-x-3 gap-y-2">
                        <span class="font-bold text-primary text-xl md:text-2xl" x-text="formattedPrice">৳{{ number_format($product->getDisplayPrice(), 2) }}</span>
                        @if($product->sale_price !== null)
                            <span class="text-sm text-gray-400 line-through">৳{{ number_format($product->regular_price, 2) }}</span>
                            <span class="inline-flex shrink-0 rounded-[6px] border border-[#1890ff] px-2 py-px font-body text-xs font-bold leading-5 text-[#1890ff]">Save ৳{{ number_format($product->regular_price - $product->sale_price, 0) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Desktop Purchase Buttons (md+) -->
                <div class="hidden md:grid grid-cols-[repeat(2,minmax(0,293px))] gap-6">
                    <button type="button" @click="addCart()" :disabled="!canOrder"
                            class="flex h-[52px] w-full items-center justify-center gap-2 rounded-[999px] border-0 bg-[#ffc107] px-[22px] py-[11px] text-base font-semibold leading-[26px] text-[#212b36] shadow-[0_8px_16px_rgba(255,193,7,0.24)] transition-[filter,transform] duration-150 hover:-translate-y-0.5 hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                        <span>Add to Cart</span>
                    </button>
                    <button type="button" @click="buyNow()" :disabled="!canOrder"
                            class="animate-order-pulse flex h-[52px] w-full items-center justify-center gap-2 rounded-[999px] border-0 bg-[#159758] px-[22px] py-[11px] text-base font-semibold leading-[26px] text-white shadow-[0_8px_16px_rgba(0,171,85,0.24)] transition-[filter,transform] duration-150 hover:-translate-y-0.5 hover:brightness-105 disabled:cursor-not-allowed disabled:opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
                        <span>অর্ডার করুন</span>
                    </button>
                </div>

                <!-- Desktop Contact Buttons (md+) -->
                @php
                    $whatsappNumber = App\Models\Setting::getValue('whatsapp_number', '');
                    $whatsappDigits = preg_replace('/[^0-9]/', '', $whatsappNumber);
                @endphp
                @if($whatsappNumber)
                    <div class="hidden md:grid grid-cols-[repeat(2,minmax(0,293px))] gap-6">
                        <a href="tel:+{{ $whatsappDigits }}"
                           class="flex h-[52px] w-full items-center justify-center gap-2 rounded-[24px] border border-[rgba(145,158,171,0.48)] bg-white px-4 text-[#212b36] transition-[border-color,transform] duration-150 hover:-translate-y-0.5 hover:border-[#159758]">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6 text-[#159758]"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            <span class="flex min-w-0 flex-col text-left">
                                <span class="truncate text-base font-semibold leading-[26px] text-[#212b36]">{{ $whatsappNumber }}</span>
                                <span class="truncate text-xs font-normal leading-[18px] text-[#637381]">Call for Order</span>
                            </span>
                        </a>
                        <a href="https://wa.me/{{ $whatsappDigits }}"
                           target="_blank" rel="noopener noreferrer"
                           class="flex h-[52px] w-full items-center justify-center gap-2 rounded-[24px] border border-[rgba(145,158,171,0.48)] bg-white px-4 text-[#212b36] transition-[border-color,transform] duration-150 hover:-translate-y-0.5 hover:border-[#25D366]">
                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-6 w-6">
                                <path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Z" fill="#25D366"/>
                                <path d="M17.06 14.31c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z" fill="white"/>
                            </svg>
                            <span class="flex min-w-0 flex-col text-left">
                                <span class="truncate text-base font-semibold leading-[26px] text-[#212b36]">Order Via WhatsApp</span>
                                <span class="truncate text-xs font-normal leading-[18px] text-[#637381]">Chat with us</span>
                            </span>
                        </a>
                    </div>
                @endif
            </form>

            <!-- Stock -->
            <div class="pt-3 md:pt-4">
                @if($product->product_type === 'simple')
                    @if($product->stock_quantity > 0)
                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 bg-green-50 text-green-700 rounded-full border border-green-200">✅ স্টকে আছে</span>
                    @else
                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 bg-red-50 text-red-700 rounded-full border border-red-200">❌ স্টক আউট</span>
                    @endif
                @else
                    <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full border"
                          :class="variationStock > 0 ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
                          x-text="stockStatusText">
                    </span>
                @endif
            </div>

            <!-- Trust Features -->
            <div class="grid grid-cols-2 md:grid-cols-[repeat(2,minmax(0,293px))] gap-2 md:gap-6 mt-3 md:mt-4">
                <div class="flex items-start md:items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg md:rounded-[24px] p-3">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold text-gray-900 leading-tight">দ্রুত ডেলিভারি</p>
                        <p class="text-[11px] md:text-xs text-gray-500 mt-0.5">২-৩ দিনের মধ্যে নিরাপদ ডেলিভারি</p>
                    </div>
                </div>
                <div class="flex items-start md:items-center gap-3 bg-gray-50 border border-gray-200 rounded-lg md:rounded-[24px] p-3">
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs md:text-sm font-bold text-gray-900 leading-tight">ক্যাশ অন ডেলিভারি</p>
                        <p class="text-[11px] md:text-xs text-gray-500 mt-0.5">পণ্য হাতে পেয়ে পেমেন্ট করুন</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Full Description -->
    @if($product->description)
        <div class="mt-6 md:mt-12 border-t border-gray-200 pt-5 md:pt-8">
            <h3 class="text-lg md:text-2xl font-extrabold text-gray-900 mb-3 md:mb-5">📄 বিস্তারিত</h3>
            <div class="text-base text-gray-600 leading-relaxed [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_a]:text-purple-600 [&_a]:underline max-w-none">{!! $product->description !!}</div>
        </div>
    @endif

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
        <section class="mt-8 md:mt-16 border-t border-gray-200 pt-6 md:pt-12 space-y-4 md:space-y-6">
            <h2 class="text-lg md:text-2xl font-extrabold text-gray-900 text-center">আরও দেখুন</h2>
            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-2 md:gap-6">
                @foreach($relatedProducts as $rel)
                    @include('store.partials.product-card', ['product' => $rel])
                @endforeach
            </div>
        </section>
    @endif

    <!-- Mobile Sticky Purchase Bar -->
    <div class="fixed inset-x-0 bottom-0 z-[1100] md:hidden border-t border-[#dfe3e8] bg-white px-3 pt-2 pb-[calc(10px+env(safe-area-inset-bottom))] shadow-[0_-8px_24px_rgba(15,23,42,0.12)]">
        <div class="mx-auto grid max-w-[375px] grid-cols-[64px_1fr_1fr] gap-2">
            @if($whatsappNumber)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}"
                   target="_blank" rel="noopener noreferrer" aria-label="Order Via WhatsApp"
                   class="flex min-h-12 flex-col items-center justify-center rounded-[999px] bg-white px-1 text-center text-[#25D366]">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true" class="h-6 w-6">
                        <path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Z" fill="#25D366"/>
                        <path d="M17.06 14.31c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z" fill="white"/>
                    </svg>
                    <span class="mt-0.5 text-[10px] font-bold leading-3 text-[#212b36]">Chat</span>
                </a>
            @endif
            <button type="button" @click="addCart()" :disabled="!canOrder"
                    class="flex min-h-12 items-center justify-center gap-1.5 rounded-[999px] border-0 bg-[#ffc107] px-2 text-xs font-semibold leading-4 text-[#212b36] shadow-[0_4px_10px_rgba(255,193,7,0.3)] transition-all duration-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 shrink-0"><circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/></svg>
                <span>Add Cart</span>
            </button>
            <button type="button" @click="buyNow()" :disabled="!canOrder"
                    class="animate-order-pulse flex min-h-12 items-center justify-center gap-1.5 rounded-[999px] border-0 bg-[#159758] px-2 text-xs font-semibold leading-4 text-white shadow-[0_4px_10px_rgba(0,171,85,0.3)] transition-all duration-200 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 shrink-0"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="M3.3 7 12 12l8.7-5"/><path d="M12 22V12"/></svg>
                <span>অর্ডার করুন</span>
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
            box-shadow: 0 0 0 0 rgba(21, 151, 88, 0.65);
            transform: scale(1);
        }
        30% {
            box-shadow: 0 0 0 8px rgba(21, 151, 88, 0);
            transform: scale(1.02);
        }
        60% {
            box-shadow: 0 0 0 8px rgba(21, 151, 88, 0);
            transform: scale(1);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(21, 151, 88, 0);
            transform: scale(1);
        }
    }

    .animate-order-pulse {
        animation: order-pulse 0.9s ease-out infinite;
    }

    .animate-order-pulse:disabled {
        animation: none;
    }
</style>
<script>
    function productDetail(variations) {
        return {
            variations: variations || [],
            activeImage: '{{ $featuredImg ? Storage::url($featuredImg->image) : "/placeholder.png" }}',
            selectedSize: '',
            selectedColor: '',
            qty: 1,
            selectedVariationId: '',
            variationStock: 0,
            variationPrice: 0,

            productType: '{{ $product->product_type }}',
            simpleInStock: {{ $product->product_type === 'simple' ? ($product->stock_quantity > 0 ? 'true' : 'false') : 'true' }},
            
            formattedPrice: '৳{{ number_format($product->getDisplayPrice(), 2) }}',
            stockStatusText: 'অপশন নির্বাচন করুন',
            buttonText: 'অপশন নির্বাচন করুন',

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

            matchVariation() {
                if (!this.selectedSize && !this.selectedColor) {
                    this.selectedVariationId = '';
                    this.variationStock = 0;
                    this.formattedPrice = '৳{{ number_format($product->getDisplayPrice(), 2) }}';
                    this.stockStatusText = 'অপশন নির্বাচন করুন';
                    this.buttonText = 'অপশন নির্বাচন করুন';
                    return;
                }

                var basePrice = {{ $product->getDisplayPrice() ?: 0 }};
                let match = this.variations.find(v => {
                    let sizeMatch = !v.size || v.size === this.selectedSize;
                    let colorMatch = !v.color || v.color === this.selectedColor;
                    return sizeMatch && colorMatch;
                });

                if (match) {
                    this.selectedVariationId = match.id;
                    this.variationStock = match.stock;
                    this.variationPrice = match.price ? parseFloat(match.price) : basePrice;
                    this.formattedPrice = '৳' + this.variationPrice.toFixed(2);
                    
                    if (match.stock > 0) {
                        this.stockStatusText = '✅ স্টকে আছে';
                        this.buttonText = '🛒 কার্টে যোগ করুন';
                    } else {
                        this.stockStatusText = '❌ স্টক আউট';
                        this.buttonText = '❌ স্টক নেই';
                    }
                } else {
                    this.selectedVariationId = '';
                    this.variationStock = 0;
                    this.stockStatusText = 'এই কম্বিনেশন নেই';
                    this.formattedPrice = 'N/A';
                    this.buttonText = 'কম্বিনেশন নেই';
                }
            },

            get canOrder() {
                if (this.productType === 'simple') return this.simpleInStock;
                return !!this.selectedVariationId && this.variationStock > 0;
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

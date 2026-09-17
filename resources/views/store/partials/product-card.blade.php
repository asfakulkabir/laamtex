@php
    $featuredImg = $product->images->where('is_featured', true)->first() ?? $product->images->first();
    $imageUrl = $featuredImg ? Storage::url($featuredImg->image) : null;
@endphp

<a href="{{ $product->slug ? route('product.detail', $product->slug) : '#' }}" class="group relative block rounded-2xl bg-white ring-1 ring-brand-300 hover:ring-brand-300 hover:shadow-md transition overflow-hidden">
    <div class="relative aspect-square bg-slate-100 overflow-hidden">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="" aria-hidden="true" loading="lazy" decoding="async"
                 class="absolute inset-0 h-full w-full object-cover blur-2xl scale-110">
            <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" decoding="async"
                 class="absolute inset-0 h-full w-full object-contain transition-transform duration-500 group-hover:scale-[1.03]">
        @else
            <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-50 text-slate-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-[10px] uppercase font-bold mt-2 tracking-wider">Image Coming Soon</span>
            </div>
        @endif
    </div>

    <div class="absolute top-3 left-3 z-10 flex flex-col space-y-1">
        @if($product->sale_price !== null)
            <span class="bg-gradient-to-r from-pink-500 to-rose-500 text-white font-extrabold text-[9px] uppercase tracking-wider px-2.5 py-1 rounded-full border border-pink-400/20">Sale</span>
        @endif
    </div>

    <div class="p-3 sm:p-4">
        <h3 class="text-sm font-medium text-slate-900 line-clamp-2 mb-1.5 group-hover:text-brand-600 transition">
            {{ $product->name }}
        </h3>
        <div class="flex items-baseline gap-2">
            @if($product->sale_price !== null)
                <span class="text-base font-bold text-slate-900">৳{{ number_format($product->sale_price, 0) }}</span>
                <span class="text-xs text-slate-400 line-through">৳{{ number_format($product->regular_price, 0) }}</span>
            @elseif($product->regular_price !== null)
                <span class="text-base font-bold text-slate-900">৳{{ number_format($product->regular_price, 0) }}</span>
            @else
                <span class="text-xs text-slate-400 italic">Options Available</span>
            @endif
        </div>
    </div>
</a>
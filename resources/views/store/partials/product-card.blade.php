<div class="group relative bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition duration-300 flex flex-col h-full">
    
    <!-- Badges / Overlays -->
    <div class="absolute top-4 left-4 z-10 flex flex-col space-y-1">
        @if($product->sale_price !== null)
            <span class="bg-gradient-to-r from-pink-500 to-rose-500 text-white font-extrabold text-[9px] uppercase tracking-wider px-2.5 py-1 rounded-full border border-pink-400/20">Sale</span>
        @endif
        @if($product->is_featured)
            <span class="bg-purple-600 text-white font-extrabold text-[9px] uppercase tracking-wider px-2.5 py-1 rounded-full border border-purple-500/20">Featured</span>
        @endif
    </div>

    <!-- Product Thumbnail -->
    <div class="relative bg-gray-50 aspect-square w-full overflow-hidden">
        @php
            $featuredImg = $product->images->where('is_featured', true)->first() ?? $product->images->first();
        @endphp
        @if($featuredImg)
            <img src="{{ Storage::url($featuredImg->image) }}" alt="{{ $product->name }}" 
                 class="w-full h-full object-cover object-center group-hover:scale-105 transition duration-500">
        @else
            <!-- Placeholder -->
            <div class="w-full h-full flex flex-col items-center justify-center bg-purple-50/50 text-purple-300">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="text-[10px] uppercase font-bold mt-2 tracking-wider">Image Coming Soon</span>
            </div>
        @endif
    </div>

    <!-- Details -->
    <div class="p-6 flex flex-col flex-grow space-y-3">
        <!-- Category name -->
        <p class="text-[10px] text-gray-400 font-extrabold uppercase tracking-widest truncate">
            @if($product->categories->count() > 0)
                {{ $product->categories->first()->name }}
            @else
                Uncategorized
            @endif
        </p>

        <!-- Product Name -->
        <h3 class="font-bold text-gray-900 group-hover:text-purple-600 transition truncate text-base">
            @if($product->slug)
                <a href="{{ route('product.detail', $product->slug) }}">
                    <span class="absolute inset-0 z-0"></span>
                    {{ $product->name }}
                </a>
            @else
                <span class="text-gray-400">{{ $product->name }}</span>
            @endif
        </h3>

        <!-- Price -->
        <div class="flex-grow flex items-end">
            @if($product->sale_price !== null)
                <div class="flex items-baseline space-x-2">
                    <span class="font-bold text-purple-600 text-lg">৳{{ number_format($product->sale_price, 2) }}</span>
                    <span class="text-xs text-gray-400 line-through">৳{{ number_format($product->regular_price, 2) }}</span>
                </div>
            @elseif($product->regular_price !== null)
                <span class="font-bold text-purple-600 text-lg">৳{{ number_format($product->regular_price, 2) }}</span>
            @else
                <span class="text-xs text-gray-400 italic">Options Available</span>
            @endif
        </div>
    </div>
</div>

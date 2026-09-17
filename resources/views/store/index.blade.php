@extends('layouts.store')

@section('title', site_name() . ' - Premium E-commerce Store')

@section('content')
<div class="pb-16">
    
    <!-- Hero Slider -->
    @if($sliders->count() > 0)
        <section class="relative w-full overflow-hidden bg-gray-900" x-data="heroSlider({{ $sliders->count() }})" x-init="init()">
            <div class="relative w-full h-[200px] sm:h-[280px] md:h-[55vh] lg:h-[85vh] xl:max-h-[600px]">
                @foreach($sliders as $i => $slider)
                    <div x-show="activeSlide === {{ $i }}" x-transition:enter="transition-opacity duration-700" x-cloak
                         class="absolute inset-0 w-full">
                        @if($slider->link)
                            <a href="{{ $slider->link }}">
                        @endif
                        <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?? 'Slider ' . ($i + 1) }}"
                             class="w-full h-full object-cover">
                        @if($slider->link)
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($sliders->count() > 1)
                <button @click="prev()" class="absolute left-3 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 transition z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button @click="next()" class="absolute right-3 top-1/2 -translate-y-1/2 bg-black/30 hover:bg-black/50 text-white rounded-full p-2 transition z-10">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2 z-10">
                    @foreach($sliders as $i => $slider)
                        <button @click="activeSlide = {{ $i }}; resetTimer()"
                                class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                                :class="activeSlide === {{ $i }} ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/60'">
                        </button>
                    @endforeach
                </div>
            @endif
        </section>
    @else
        <!-- Fallback solid background when no sliders -->
        <section class="relative bg-gradient-to-tr from-indigo-950 via-purple-900 to-pink-700 h-[200px] sm:h-[280px] md:h-[55vh] lg:h-[85vh]"></section>
    @endif

    <!-- Featured Categories -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2 md:py-6 lg:py-8">
        <div class="flex flex-wrap gap-1 lg:gap-4 justify-center items-center">
            @foreach($featuredCategories as $cat)
                <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="group flex flex-col  border bg-white border-gray-200 rounded-md overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 w-1/4 md:w-1/6 lg:w-24">
                    <div class="relative aspect-square bg-gray-100 overflow-hidden w-full">
                        @if($cat->image)
                            <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-purple-100 to-pink-100">
                                <span class="text-4xl font-extrabold text-purple-300/60">{{ substr($cat->name, 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="py-2 text-center">
                        <h4 class="text-gray-900 text-xs sm:text-sm truncate">{{ $cat->name }}</h4>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Featured Products -->
    @if($featuredProducts->count() > 0)
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-4 md:pb-6 2xl:pb-8">
            <div class="flex justify-between items-end">
                <div class="space-y-1">
                    <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900">Featured Additions</h2>
                    <p class="text-sm text-gray-500 hidden md:block">Handpicked premium pieces from the current season.</p>
                </div>
                <a href="{{ route('shop') }}" class="text-sm font-bold text-accent hover:text-accent transition">See All Products →</a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-6 lg:gap-8">
                @foreach($featuredProducts as $product)
                    @include('store.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif

    <!-- Latest Arrivals -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="text-start space-y-2">
            <h2 class="text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900">Latest Arrivals</h2>
            <p class="text-sm text-gray-500">Be the first to secure our fresh collections.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-2 sm:gap-6 lg:gap-8">
            @foreach($latestProducts as $product)
                @include('store.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <!-- Info / Help Cards -->
    <section class="mx-auto max-w-[1280px] px-4 sm:px-6 mt-12">
        <div class="grid md:grid-cols-3 gap-4">
            <div class="group bg-amber-100 rounded-2xl p-6 relative overflow-hidden hover:shadow-md transition">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-200 text-amber-800 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-help h-7 w-7"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><path d="M12 17h.01"></path></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base sm:text-lg leading-snug">Wondering Something? Read This First!</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">Updates on safe shipping in our store</p>
            </div>
            <div class="group bg-brand-100 rounded-2xl p-6 relative overflow-hidden hover:shadow-md transition">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-brand-200 text-brand-800 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card h-7 w-7"><rect width="20" height="14" x="2" y="5" rx="2"></rect><line x1="2" x2="22" y1="10" y2="10"></line></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base sm:text-lg leading-snug">Easy and Secure Online Payment Steps</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">Cash on Delivery means pay only on receipt</p>
            </div>
            <div class="group bg-rose-100 rounded-2xl p-6 relative overflow-hidden hover:shadow-md transition">
                <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-rose-200 text-rose-800 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck h-7 w-7"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                </div>
                <h3 class="font-bold text-slate-900 text-base sm:text-lg leading-snug">Smooth, Simple Home Delivery Options</h3>
                <p class="text-xs sm:text-sm text-slate-600 mt-1">Two zones, transparent fees, fast turnaround</p>
            </div>
        </div>
    </section>

    <!-- Trust Badges -->
    <section class="mx-auto max-w-[1280px] px-4 sm:px-6 mt-12">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-brand-200 hover:ring-brand-200 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck h-5 w-5 text-brand-600"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">Fast and Free Delivery</p><p class="text-xs text-slate-500 line-clamp-2">Free delivery on all orders</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-brand-200 hover:ring-brand-200 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-headphones h-5 w-5 text-brand-600"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"></path></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">24/7 Customer Service</p><p class="text-xs text-slate-500 line-clamp-2">Friendly support 7 days a week</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-brand-200 hover:ring-brand-200 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-5 w-5 text-brand-600"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">Easy &amp; Free Returns</p><p class="text-xs text-slate-500 line-clamp-2">Money-back if you change your mind</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-brand-200 hover:ring-brand-200 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check h-5 w-5 text-brand-600"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"></path><path d="m9 12 2 2 4-4"></path></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">100% Safe &amp; Secure</p><p class="text-xs text-slate-500 line-clamp-2">Cash on Delivery — pay on receipt</p></div>
            </div>
        </div>
    </section>

</div>
@endsection

@section('scripts')
@if($sliders->count() > 0)
<script>
    function heroSlider(count) {
        return {
            activeSlide: 0,
            timer: null,
            init() {
                if (count > 1) {
                    this.timer = setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % count;
                    }, 5000);
                }
            },
            prev() {
                this.activeSlide = (this.activeSlide - 1 + count) % count;
                this.resetTimer();
            },
            next() {
                this.activeSlide = (this.activeSlide + 1) % count;
                this.resetTimer();
            },
            resetTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = setInterval(() => {
                        this.activeSlide = (this.activeSlide + 1) % count;
                    }, 5000);
                }
            }
        }
    }
</script>
@endif
@endsection

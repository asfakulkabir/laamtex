@extends('layouts.store')

@section('title', site_name() . ' - Premium E-commerce Store')

@section('content')
<div class="pb-16">

    <!-- Hero Slider -->
    <style>
        .yt-cover-iframe {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #000;
            border: 0;
            /* YouTube draws its player inside a cross-origin frame, so page CSS
               can never reach the controls. Letting the pointer pass through is
               what actually keeps them hidden: the frame can no longer be
               hovered or clicked, so it has nothing to reveal. Clicks land on the
               slide link underneath instead. */
            pointer-events: none;
        }
        @media (max-width: 639.98px) {
            .yt-cover-iframe { width: 111.112%; height: 100%; }
        }
        @media (min-width: 640px) and (max-width: 767.98px) {
            .yt-cover-iframe { width: 118.52%; height: 100%; }
        }
        @media (min-width: 768px) {
            .yt-cover-iframe {
                width: max(100%, calc(100% * 1.7777778 / var(--hero-ratio, 2.4)));
                height: max(100%, calc(100% * var(--hero-ratio, 2.4) * 0.5625));
            }
        }
    </style>
    @if($sliders->count() > 0)
        <section class="w-full">
            <div class="relative overflow-hidden bg-gray-900" x-data="heroSlider({{ $sliders->count() }}, @json($sliders->map(fn($s) => $s->isVideo())->values()->all()))" x-init="init()">
                <div class="relative w-full [aspect-ratio:16/10] sm:[aspect-ratio:3/2] md:[aspect-ratio:var(--hero-ratio)] 2xl:max-h-[min(68vh,600px)]" :style="'--hero-ratio:' + ratio + ';'">
                    @foreach($sliders as $i => $slider)
                        <div x-show="activeSlide === {{ $i }}" x-transition:enter="transition-opacity duration-700" @if($i > 0) x-cloak @endif
                             class="absolute inset-0 w-full">
                            @if($slider->isAudio())
                                <div class="relative w-full h-full">
                                    @if($slider->image)
                                        @if($slider->link)<a href="{{ $slider->link }}" class="absolute inset-0 z-0" aria-label="{{ $slider->title ?? 'Shop now' }}"></a>@endif
                                        <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?? 'Audio cover' }}"
                                             class="w-full h-full object-cover" @load="imgLoaded({{ $i }}, $event)" loading="eager">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-tr from-indigo-950 via-purple-900 to-pink-700"></div>
                                    @endif
                                    <audio x-ref="audio-{{ $i }}" src="{{ asset('storage/' . $slider->audio) }}" preload="none" loop
                                           @timeupdate="syncAudio({{ $i }}, $event)"
                                           @play="markAudioPlaying({{ $i }})"
                                           @pause="markAudioPaused({{ $i }})"></audio>
                                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-3 sm:gap-4 px-6 text-center z-10 pointer-events-none">
                                        <button type="button" @click="toggleAudio({{ $i }})" class="pointer-events-auto h-16 w-16 sm:h-20 sm:w-20 rounded-full bg-white/95 hover:bg-white text-gray-900 shadow-2xl ring-1 ring-black/10 flex items-center justify-center transition active:scale-95"
                                                :aria-label="audioPlaying === {{ $i }} ? 'Pause audio' : 'Play audio'">
                                            <svg x-show="audioPlaying !== {{ $i }}" class="ml-1 h-7 w-7 sm:h-9 sm:w-9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5.14v13.72a1 1 0 0 0 1.54.84l10.3-6.86a1 1 0 0 0 0-1.68L9.54 4.3A1 1 0 0 0 8 5.14Z"/></svg>
                                            <svg x-show="audioPlaying === {{ $i }}" x-cloak class="h-7 w-7 sm:h-9 sm:w-9" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/></svg>
                                        </button>
                                        <div class="pointer-events-auto w-40 sm:w-56">
                                            <div class="h-1 w-full rounded-full bg-white/35 overflow-hidden">
                                                <div class="h-full rounded-full bg-white transition-[width] duration-150" :style="'width:' + audioPercent({{ $i }}) + '%'"></div>
                                            </div>
                                            <div class="mt-1 flex justify-between text-[10px] sm:text-xs font-medium text-white/80">
                                                <span x-text="audioTime({{ $i }}, 'current')">0:00</span>
                                                <span x-text="audioTime({{ $i }}, 'duration')">0:00</span>
                                            </div>
                                        </div>
                                        @if($slider->title)
                                            <span class="text-sm sm:text-lg font-bold text-white drop-shadow">{{ $slider->title }}</span>
                                        @endif
                                    </div>
                                </div>
                            @elseif($slider->isVideo())
                                @if($slider->link)
                                    <a href="{{ $slider->link }}" class="block w-full h-full">
                                @endif
                                <template x-if="activeSlide === {{ $i }}">
                                    @if($slider->isYoutubeVideo())
                                        <iframe :src="'https://www.youtube.com/embed/{{ $slider->youtube_id }}?autoplay=1&mute=1&loop=1&playlist={{ $slider->youtube_id }}&controls=0&modestbranding=1&rel=0&playsinline=1&iv_load_policy=3&disablekb=1&fs=0&enablejsapi=1'"
                                                class="yt-cover-iframe" frameborder="0" tabindex="-1"
                                                allow="autoplay; encrypted-media; picture-in-picture" title="{{ $slider->title ?? 'Hero video' }}"></iframe>
                                    @else
                                        <video src="{{ asset('storage/' . $slider->video) }}"
                                               @if($slider->image) poster="{{ asset('storage/' . $slider->image) }}" @endif
                                               autoplay muted loop playsinline
                                               class="w-full h-full object-cover bg-black" @loadedmetadata="videoLoaded({{ $i }}, $event)"></video>
                                    @endif
                                </template>
                                @if($slider->link)
                                    </a>
                                @endif
                            @else
                                @if($slider->link)
                                    <a href="{{ $slider->link }}">
                                @endif
                                <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?? 'Slider ' . ($i + 1) }}"
                                     class="w-full h-full object-cover" @load="imgLoaded({{ $i }}, $event)" loading="eager">
                                @if($slider->link)
                                    </a>
                                @endif
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
                            <button @click="go({{ $i }})"
                                    class="w-2.5 h-2.5 rounded-full transition-all duration-300"
                                    :class="activeSlide === {{ $i }} ? 'bg-white w-6' : 'bg-white/40 hover:bg-white/60'">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @else
        <!-- Fallback solid background when no sliders -->
        <div class="w-full">
            <div class="bg-gradient-to-tr from-indigo-950 via-purple-900 to-pink-700 aspect-[4/5] sm:aspect-[9/10] md:aspect-[16/10] lg:aspect-[12/5] xl:aspect-[12/5]"></div>
        </div>
    @endif

    <!-- Featured Categories -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base sm:text-lg md:text-2xl font-bold md:font-extrabold tracking-tight text-gray-900">Shop by Category</h2>
            <a href="{{ route('categories') }}" class="text-xs sm:text-sm font-bold text-purple-600 hover:text-purple-800 transition">View All &rarr;</a>
        </div>

        @php
            $categoryPalettes = [
                'bg-gradient-to-br from-rose-100 to-rose-200 text-rose-700',
                'bg-gradient-to-br from-violet-100 to-violet-200 text-violet-700',
                'bg-gradient-to-br from-amber-100 to-amber-200 text-amber-700',
                'bg-gradient-to-br from-emerald-100 to-emerald-200 text-emerald-700',
                'bg-gradient-to-br from-sky-100 to-sky-200 text-sky-700',
                'bg-gradient-to-br from-pink-100 to-pink-200 text-pink-700',
            ];
        @endphp

        <div class="flex gap-2.5 sm:gap-3 overflow-x-auto pb-2 snap-x snap-mandatory md:snap-none md:flex-wrap md:justify-center md:overflow-visible md:pb-0 no-scrollbar">
            @foreach($featuredCategories as $i => $cat)
                <a href="{{ route('shop', ['category' => $cat->slug]) }}"
                   class="group flex flex-col items-center justify-center gap-2 rounded-2xl aspect-square p-3 w-[28%] sm:w-[21%] md:w-[13.5%] lg:w-24 shrink-0 snap-start {{ $categoryPalettes[$i % count($categoryPalettes)] }} hover:shadow-md hover:-translate-y-0.5 transition">
                    @if($cat->image)
                        <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" loading="lazy"
                             class="h-9 w-9 sm:h-11 sm:w-11 object-contain group-hover:scale-110 transition duration-300">
                    @else
                        <span class="h-9 w-9 sm:h-11 sm:w-11 flex items-center justify-center text-xl sm:text-2xl font-extrabold opacity-70">{{ substr($cat->name, 0, 1) }}</span>
                    @endif
                    <span class="text-xs sm:text-sm font-semibold text-slate-900 line-clamp-1 text-center">{{ $cat->name }}</span>
                </a>
            @endforeach
        </div>
    </section>

    <!-- Featured Products -->
    @if($featuredProducts->count() > 0)
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6 pb-4 md:pb-6 2xl:pb-8" x-data="featuredSlider()" x-init="init()" @mouseenter="pause()" @mouseleave="play()">
            <div class="flex justify-between items-end">
                <div class="space-y-1">
                    <h2 class="text-lg sm:text-xl md:text-3xl font-bold md:font-extrabold tracking-tight text-gray-900">Featured Additions</h2>
                    <p class="text-sm text-gray-500 hidden md:block">Handpicked premium pieces from the current season.</p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <div class="hidden md:flex items-center gap-2">
                        <button type="button" @click="prevSlide()" class="w-9 h-9 rounded-full ring-1 ring-gray-300 text-gray-600 hover:bg-gray-900 hover:text-white hover:ring-gray-900 flex items-center justify-center transition" aria-label="Previous products">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" @click="nextSlide()" class="w-9 h-9 rounded-full ring-1 ring-gray-300 text-gray-600 hover:bg-gray-900 hover:text-white hover:ring-gray-900 flex items-center justify-center transition" aria-label="Next products">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <a href="{{ route('shop') }}" class="text-sm font-bold text-accent hover:text-accent transition">See All Products →</a>
                </div>
            </div>

            <div x-ref="track" class="flex gap-2 sm:gap-6 lg:gap-8 overflow-x-auto no-scrollbar pb-2">
                @foreach($featuredProducts as $product)
                    <div class="slide-item snap-start shrink-0 w-[calc(50%_-_4px)] sm:w-[calc(50%_-_12px)] md:w-[calc(33.33%_-_16px)] lg:w-[calc(33.33%_-_21.33px)] xl:w-[calc(33.33%_-_21.33px)] 2xl:w-[calc(33.33%_-_21.33px)]">
                        @include('store.partials.product-card', ['product' => $product])
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Latest Arrivals -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="text-start space-y-2">
            <h2 class="text-lg sm:text-xl md:text-3xl font-bold md:font-extrabold tracking-tight text-gray-900">Latest Arrivals</h2>
            <p class="text-sm text-gray-500">Be the first to secure our fresh collections.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 gap-2 sm:gap-6 lg:gap-8">
            @foreach($latestProducts as $product)
                @include('store.partials.product-card', ['product' => $product])
            @endforeach
        </div>
    </section>

    <!-- Customer Testimonials -->
    @if($testimonials->count() > 0)
        <section class="mt-12 md:mt-16"
                 x-data="testimonialSlider()"
                 x-init="init()"
                 @mouseenter="pause()" @mouseleave="play()"
                 tabindex="0"
                 @keydown.left.prevent="prevSlide()" @keydown.right.prevent="nextSlide()"
                 aria-label="Customer testimonials">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <div class="space-y-1.5">
                        <h2 class="text-lg sm:text-xl md:text-3xl font-bold md:font-extrabold tracking-tight text-gray-900">
                            What Our Customers Say
                        </h2>
                        <p class="text-sm text-gray-500">Real messages and photos from happy customers.</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="hidden sm:flex items-center gap-2">
                            <button type="button" @click="prevSlide()" :disabled="!canPrev"
                                    class="w-9 h-9 rounded-full ring-1 ring-gray-300 text-gray-600 hover:bg-gray-900 hover:text-white hover:ring-gray-900 flex items-center justify-center transition disabled:opacity-40 disabled:pointer-events-none"
                                    aria-label="Previous testimonials">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <button type="button" @click="nextSlide()" :disabled="!canNext"
                                    class="w-9 h-9 rounded-full ring-1 ring-gray-300 text-gray-600 hover:bg-gray-900 hover:text-white hover:ring-gray-900 flex items-center justify-center transition disabled:opacity-40 disabled:pointer-events-none"
                                    aria-label="Next testimonials">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div x-ref="track" @scroll.passive="sync()"
                 class="flex gap-3 sm:gap-4 lg:gap-6 overflow-x-auto no-scrollbar scroll-smooth px-4 sm:px-6 lg:px-[max(1.5rem,calc((100vw-80rem)/2+2rem))] pb-2 snap-x snap-mandatory">
                @foreach($testimonials as $testimonial)
                    <figure class="slide-item snap-start shrink-0 w-[85vw] sm:w-[62vw] md:w-[55vw] lg:w-[calc(50%_-_0.75rem)]">
                        <div class="aspect-video rounded-2xl bg-white ring-1 ring-gray-200 hover:ring-gray-300 hover:shadow-lg transition overflow-hidden">
                            <img src="{{ $testimonial->image_url }}"
                                 alt="Testimonial from a {{ site_name() }} customer"
                                 loading="lazy" decoding="async"
                                 @if($testimonial->width) width="{{ $testimonial->width }}" height="{{ $testimonial->height }}" @endif
                                 class="w-full h-full object-contain bg-white p-1.5">
                        </div>
                    </figure>
                @endforeach
            </div>

            @if($testimonials->count() > 1)
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-5">
                    <div class="flex sm:hidden items-center justify-center gap-2">
                        <button type="button" @click="prevSlide()" :disabled="!canPrev"
                                class="w-10 h-10 rounded-full ring-1 ring-gray-300 text-gray-600 flex items-center justify-center transition disabled:opacity-40"
                                aria-label="Previous testimonials">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button type="button" @click="nextSlide()" :disabled="!canNext"
                                class="w-10 h-10 rounded-full ring-1 ring-gray-300 text-gray-600 flex items-center justify-center transition disabled:opacity-40"
                                aria-label="Next testimonials">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div class="hidden sm:flex items-center justify-center gap-2 mt-4">
                        <template x-for="(dot, i) in pageCount" :key="i">
                            <button type="button" @click="goToPage(i)"
                                    class="h-2 rounded-full transition-all"
                                    :class="i === page ? 'w-6 bg-gray-900' : 'w-2 bg-gray-300 hover:bg-gray-400'"
                                    :aria-label="'Go to testimonials page ' + (i + 1)"></button>
                        </template>
                    </div>
                </div>
            @endif
        </section>
    @endif

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
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-gray-300 hover:ring-gray-300 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck h-5 w-5 text-brand-600"><path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"></path><path d="M15 18H9"></path><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"></path><circle cx="17" cy="18" r="2"></circle><circle cx="7" cy="18" r="2"></circle></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">Fast and Free Delivery</p><p class="text-xs text-slate-500 line-clamp-2">Free delivery on all orders</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-gray-300 hover:ring-gray-300 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-headphones h-5 w-5 text-brand-600"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"></path></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">24/7 Customer Service</p><p class="text-xs text-slate-500 line-clamp-2">Friendly support 7 days a week</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-gray-300 hover:ring-gray-300 hover:shadow-sm transition">
                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw h-5 w-5 text-brand-600"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path><path d="M21 3v5h-5"></path><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path><path d="M8 16H3v5"></path></svg>
                </div>
                <div class="min-w-0"><p class="text-sm font-semibold text-slate-900">Easy &amp; Free Returns</p><p class="text-xs text-slate-500 line-clamp-2">Money-back if you change your mind</p></div>
            </div>
            <div class="flex items-start gap-3 p-4 rounded-xl bg-white ring-1 ring-gray-300 hover:ring-gray-300 hover:shadow-sm transition">
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
@if($sliders->contains(fn ($slider) => $slider->isYoutubeVideo()))
<script src="https://www.youtube.com/iframe_api"></script>
<script>
    /**
     * The hero slide is background decoration, so it must never show a play
     * button, a pause icon or a control bar. `controls=0` already hides the
     * bar; the centre play button is the remaining piece, and YouTube only
     * draws that while the player is paused. Holding the player muted and
     * playing removes the moment it would ever appear. Every browser permits
     * muted playback, so this survives the autoplay policies that block the
     * plain `autoplay=1` parameter.
     */
    (function () {
        var bind = function () {
            var frames = document.querySelectorAll('iframe.yt-cover-iframe:not([data-yt-bound])');

            for (var i = 0; i < frames.length; i++) {
                frames[i].setAttribute('data-yt-bound', '1');

                /* eslint-disable no-new */
                new YT.Player(frames[i], {
                    events: {
                        onReady: function (event) {
                            event.target.mute();
                            event.target.playVideo();
                        },
                        onStateChange: function (event) {
                            if (event.data === YT.PlayerState.PAUSED) {
                                event.target.playVideo();
                            }
                        }
                    }
                });
            }
        };

        // Bind straight away when the API is already loaded, otherwise wait for
        // its callback. The API overwrites this handler on load, so it has to
        // be assigned before and re-checked after the tag has run.
        window.onYouTubeIframeAPIReady = bind;

        if (window.YT && window.YT.Player) {
            bind();
        }

        // Alpine only inserts a slide's frame once that slide becomes active,
        // so the frames this has to bind mostly do not exist yet.
        var queued = false;
        var schedule = function () {
            if (queued) return;
            queued = true;

            requestAnimationFrame(function () {
                queued = false;
                bind();
            });
        };

        new MutationObserver(schedule).observe(document.body, { childList: true, subtree: true });
    })();
</script>
@endif
@if($sliders->count() > 0)
<script>
    function heroSlider(count, videoSlides) {
        return {
            activeSlide: 0,
            timer: null,
            ratios: [],
            videoSlides: videoSlides || [],
            audioPlaying: -1,
            audioProgress: {},
            ratio: '2.4',
            init() {
                if (count > 1) {
                    this.timer = setInterval(() => {
                        this.step(1);
                    }, 5000);
                }
                this.videoSlides.forEach((isVideo, i) => {
                    if (isVideo) this.ratios[i] = '2.2';
                });
                this.applyRatio();
            },
            step(dir) {
                this.activeSlide = (this.activeSlide + dir + count) % count;
                this.pauseAllAudio();
                this.resetTimer();
                this.applyRatio();
            },
            prev() {
                this.step(-1);
            },
            next() {
                this.step(1);
            },
            go(i) {
                this.activeSlide = i;
                this.pauseAllAudio();
                this.resetTimer();
                this.applyRatio();
            },
            audioEl(i) {
                return this.$refs['audio-' + i] || null;
            },
            toggleAudio(i) {
                const el = this.audioEl(i);
                if (!el) return;

                if (this.audioPlaying === i && !el.paused) {
                    el.pause();
                    return;
                }

                this.pauseAllAudio();
                const started = el.play();
                if (started && typeof started.catch === 'function') {
                    started.catch(() => {
                        this.audioPlaying = -1;
                        this.resetTimer();
                    });
                }
                this.audioPlaying = i;
                this.stopTimer();
            },
            pauseAllAudio() {
                Object.keys(this.$refs).forEach((key) => {
                    if (key.indexOf('audio-') !== 0) return;
                    const el = this.$refs[key];
                    if (el && !el.paused) el.pause();
                });
                this.audioPlaying = -1;
            },
            markAudioPlaying(i) {
                this.audioPlaying = i;
                this.stopTimer();
            },
            markAudioPaused(i) {
                if (this.audioPlaying === i) this.audioPlaying = -1;
                if (this.activeSlide === i) this.resetTimer();
            },
            syncAudio(i, event) {
                const el = event && event.currentTarget;
                if (!el) return;
                this.audioProgress[i] = { current: el.currentTime || 0, duration: el.duration || 0 };
            },
            audioPercent(i) {
                const p = this.audioProgress[i];
                if (!p || !p.duration) return 0;
                return Math.min(100, Math.max(0, (p.current / p.duration) * 100));
            },
            audioTime(i, key) {
                const p = this.audioProgress[i];
                if (!p) return '0:00';
                return this.formatTime(p[key]);
            },
            formatTime(seconds) {
                const total = Math.max(0, Math.floor(seconds || 0));
                const m = Math.floor(total / 60);
                const s = total % 60;
                return m + ':' + (s < 10 ? '0' : '') + s;
            },
            imgLoaded(i, event) {
                const img = event && event.currentTarget;
                if (!img || !img.naturalWidth) return;
                if (this.videoSlides[i]) return;
                const r = img.naturalWidth / img.naturalHeight;
                this.ratios[i] = Math.max(1.25, Math.min(3.4, r)).toFixed(3);
                if (i === this.activeSlide) this.applyRatio();
            },
            videoLoaded(i, event) {
                const video = event && event.currentTarget;
                if (!video || !video.videoWidth) return;
                const r = video.videoWidth / video.videoHeight;
                this.ratios[i] = Math.max(1.25, Math.min(3.4, r)).toFixed(3);
                if (i === this.activeSlide) this.applyRatio();
            },
            applyRatio() {
                const r = this.ratios[this.activeSlide];
                this.ratio = r ? String(r) : '2.4';
            },
            stopTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            resetTimer() {
                this.stopTimer();
                if (count > 1) {
                    this.timer = setInterval(() => {
                        this.step(1);
                    }, 5000);
                }
            }
        }
    }
</script>
@endif

@if($featuredProducts->count() > 0)
<script>
    function testimonialSlider() {
        return {
            timer: null,
            page: 0,
            pageCount: 1,
            canPrev: false,
            canNext: false,

            init() {
                this.measure();
                window.addEventListener('resize', () => this.measure());
                this.play();
            },

            measure() {
                const track = this.$refs.track;
                if (!track) return;

                this.pageCount = Math.max(1, Math.round(track.scrollWidth / track.clientWidth));
                this.sync();
            },

            sync() {
                const track = this.$refs.track;
                if (!track) return;

                const max = track.scrollWidth - track.clientWidth;
                this.canPrev = track.scrollLeft > 4;
                this.canNext = track.scrollLeft < max - 4;

                if (this.pageCount > 1) {
                    const perPage = track.clientWidth / this.pageCount;
                    this.page = Math.min(this.pageCount - 1, Math.round(track.scrollLeft / perPage));
                } else {
                    this.page = 0;
                }
            },

            step() {
                const track = this.$refs.track;
                if (!track) return 0;

                const item = track.querySelector('.slide-item');
                if (!item) return track.clientWidth * 0.8;

                const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
                return item.getBoundingClientRect().width + gap;
            },

            nudge(dir) {
                const track = this.$refs.track;
                if (!track || !track.scrollWidth) return;

                const max = track.scrollWidth - track.clientWidth;

                if (dir > 0) {
                    const target = track.scrollLeft >= max - 4 ? 0 : Math.min(track.scrollLeft + this.step(), max);
                    track.scrollTo({ left: target, behavior: 'smooth' });
                } else {
                    const target = track.scrollLeft <= 4 ? max : Math.max(track.scrollLeft - this.step(), 0);
                    track.scrollTo({ left: target, behavior: 'smooth' });
                }
            },

            goToPage(index) {
                const track = this.$refs.track;
                if (!track) return;

                const perPage = track.clientWidth / this.pageCount;
                track.scrollTo({ left: index * perPage, behavior: 'smooth' });
            },

            nextSlide() {
                this.nudge(1);
                this.play();
            },

            prevSlide() {
                this.nudge(-1);
                this.play();
            },

            play() {
                if (this.timer || this.pageCount < 2) return;
                this.timer = setInterval(() => this.nudge(1), 5000);
            },

            pause() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            }
        }
    }

    function featuredSlider() {
        return {
            timer: null,
            init() {
                this.play();
            },
            play() {
                if (this.timer) return;
                this.timer = setInterval(() => this.nudge(1), 3500);
            },
            pause() {
                if (this.timer) {
                    clearInterval(this.timer);
                    this.timer = null;
                }
            },
            step() {
                const track = this.$refs.track;
                if (!track) return 0;
                const item = track.querySelector('.slide-item');
                if (!item) return 0;
                const gap = parseFloat(getComputedStyle(track).columnGap) || 0;
                return item.getBoundingClientRect().width + gap;
            },
            nudge(dir) {
                const track = this.$refs.track;
                if (!track || !track.scrollWidth) return;
                const step = this.step();
                if (!step) return;
                const max = track.scrollWidth - track.clientWidth;
                if (dir > 0) {
                    if (track.scrollLeft >= max - 2) {
                        track.scrollTo({ left: 0, behavior: 'smooth' });
                    } else {
                        track.scrollTo({ left: Math.min(track.scrollLeft + step, max), behavior: 'smooth' });
                    }
                } else {
                    if (track.scrollLeft <= 2) {
                        track.scrollTo({ left: max, behavior: 'smooth' });
                    } else {
                        track.scrollTo({ left: Math.max(track.scrollLeft - step, 0), behavior: 'smooth' });
                    }
                }
            },
            nextSlide() {
                this.nudge(1);
                this.play();
            },
            prevSlide() {
                this.nudge(-1);
                this.play();
            }
        }
    }
</script>
@endif
@endsection

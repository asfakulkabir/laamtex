<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', site_name() . ' - Premium E-commerce Store')</title>
    @if(site_description())
        <meta name="description" content="{{ site_description() }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    <link rel="icon" type="image/webp" href="{{ asset('favicon.webp') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        $pixelId = \App\Models\Setting::getValue('meta_pixel_id', '');
        $testCode = \App\Models\Setting::getValue('meta_test_event_code', '');
    @endphp
    @if($pixelId)
    <script>
    !function(f,b,e,v,n,t,s)
    {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};
    if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
    n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];
    s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $pixelId }}'@if($testCode), '{{ $testCode }}'@endif);
    fbq('track', 'PageView');
    </script>
    <noscript><img height="1" width="1" style="display:none"
    src="https://www.facebook.com/tr?id={{ $pixelId }}&ev=PageView&noscript=1"
    /></noscript>
    @endif
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        [x-cloak] {
            display: none !important;
        }
    </style>

    <style>
        :root {
            --color-primary: {{ primary_color() }};
            --color-primary-dark: {{ primary_color_dark() }};
            --color-accent: {{ accent_color() }};
            --color-accent-dark: {{ accent_color_dark() }};
        }
        .text-primary { color: var(--color-primary); }
        .hover\:text-primary:hover { color: var(--color-primary-dark); }
        .bg-primary { background-color: var(--color-primary); }
        .hover\:bg-primary:hover { background-color: var(--color-primary-dark); }
        .border-primary { border-color: var(--color-primary); }
        .hover\:border-primary:hover { border-color: var(--color-primary-dark); }
        .ring-primary { --tw-ring-color: var(--color-primary); }
        .from-primary { --tw-gradient-from: var(--color-primary) var(--tw-gradient-from-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .to-primary { --tw-gradient-to: var(--color-primary) var(--tw-gradient-to-position); }
        .hover\:from-primary:hover { --tw-gradient-from: var(--color-primary-dark) var(--tw-gradient-from-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .hover\:to-primary:hover { --tw-gradient-to: var(--color-primary-dark) var(--tw-gradient-to-position); }

        .text-accent { color: var(--color-accent); }
        .hover\:text-accent:hover { color: var(--color-accent-dark); }
        .bg-accent { background-color: var(--color-accent); }
        .hover\:bg-accent:hover { background-color: var(--color-accent-dark); }
        .border-accent { border-color: var(--color-accent); }
        .ring-accent { --tw-ring-color: var(--color-accent); }
        .hover\:ring-accent:hover { --tw-ring-color: var(--color-accent); }

        .bg-purple-50 { background-color: color-mix(in srgb, var(--color-primary) 6%, white) !important; }
        .bg-purple-100 { background-color: color-mix(in srgb, var(--color-primary) 12%, white) !important; }
        .bg-purple-200 { background-color: color-mix(in srgb, var(--color-primary) 25%, white) !important; }
        .bg-purple-500 { background-color: color-mix(in srgb, var(--color-primary) 80%, white) !important; }
        .bg-purple-600 { background-color: var(--color-primary) !important; }
        .bg-purple-900 { background-color: color-mix(in srgb, var(--color-primary) 55%, black) !important; }

        .text-purple-50 { color: color-mix(in srgb, var(--color-primary) 6%, white) !important; }
        .text-purple-200 { color: color-mix(in srgb, var(--color-primary) 25%, white) !important; }
        .text-purple-300 { color: color-mix(in srgb, var(--color-primary) 40%, white) !important; }
        .text-purple-400 { color: color-mix(in srgb, var(--color-primary) 60%, white) !important; }
        .text-purple-500 { color: color-mix(in srgb, var(--color-primary) 80%, white) !important; }
        .text-purple-600 { color: var(--color-primary) !important; }

        .border-purple-100 { border-color: color-mix(in srgb, var(--color-primary) 12%, white) !important; }
        .border-purple-200 { border-color: color-mix(in srgb, var(--color-primary) 25%, white) !important; }
        .border-purple-400 { border-color: color-mix(in srgb, var(--color-primary) 60%, white) !important; }
        .border-purple-500 { border-color: color-mix(in srgb, var(--color-primary) 80%, white) !important; }
        .border-purple-600 { border-color: var(--color-primary) !important; }

        .ring-purple-100 { --tw-ring-color: color-mix(in srgb, var(--color-primary) 12%, white) !important; }
        .ring-purple-200 { --tw-ring-color: color-mix(in srgb, var(--color-primary) 25%, white) !important; }
        .ring-purple-400 { --tw-ring-color: color-mix(in srgb, var(--color-primary) 60%, white) !important; }
        .ring-purple-500 { --tw-ring-color: color-mix(in srgb, var(--color-primary) 80%, white) !important; }

        .from-purple-100 { --tw-gradient-from: color-mix(in srgb, var(--color-primary) 12%, white) var(--tw-gradient-from-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .from-purple-600 { --tw-gradient-from: var(--color-primary) var(--tw-gradient-from-position); --tw-gradient-to: rgb(147 51 234 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .from-purple-900 { --tw-gradient-from: color-mix(in srgb, var(--color-primary) 55%, black) var(--tw-gradient-from-position); --tw-gradient-to: rgb(88 28 135 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), var(--tw-gradient-to); }
        .via-purple-900 { --tw-gradient-to: rgb(88 28 135 / 0) var(--tw-gradient-to-position); --tw-gradient-stops: var(--tw-gradient-from), color-mix(in srgb, var(--color-primary) 55%, black) var(--tw-gradient-via-position), var(--tw-gradient-to); }
        .to-purple-600 { --tw-gradient-to: var(--color-primary) var(--tw-gradient-to-position); }

        .bg-purple-50\/50 { background-color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 6%, white) 50%, transparent) !important; }
        .bg-purple-50\/30 { background-color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 6%, white) 30%, transparent) !important; }
        .bg-purple-50\/20 { background-color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 6%, white) 20%, transparent) !important; }
        .text-purple-300\/60 { color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 40%, white) 60%, transparent) !important; }
        .text-purple-200\/70 { color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 25%, white) 70%, transparent) !important; }
        .border-purple-100\/60 { border-color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 12%, white) 60%, transparent) !important; }
        .border-purple-100\/50 { border-color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 12%, white) 50%, transparent) !important; }
        .border-purple-500\/20 { border-color: color-mix(in srgb, color-mix(in srgb, var(--color-primary) 80%, white) 20%, transparent) !important; }

        .hover\:bg-purple-50:hover { background-color: color-mix(in srgb, var(--color-primary) 10%, white) !important; }
        .hover\:bg-purple-100:hover { background-color: color-mix(in srgb, var(--color-primary) 18%, white) !important; }
        .hover\:text-purple-300:hover { color: color-mix(in srgb, var(--color-primary) 45%, white) !important; }
        .hover\:text-purple-400:hover { color: color-mix(in srgb, var(--color-primary) 65%, white) !important; }
        .hover\:text-purple-600:hover { color: var(--color-primary) !important; }
        .hover\:border-purple-400:hover { border-color: color-mix(in srgb, var(--color-primary) 60%, white) !important; }
        .hover\:border-purple-500:hover { border-color: var(--color-primary) !important; }

        .focus\:border-purple-500:focus { border-color: color-mix(in srgb, var(--color-primary) 80%, white) !important; }
        .focus\:ring-purple-500:focus { --tw-ring-color: color-mix(in srgb, var(--color-primary) 80%, white) !important; }

        .group-hover\:text-purple-600:is(:where(.group):hover *) { color: var(--color-primary) !important; }
        .group-hover\:text-purple-50:is(:where(.group):hover *) { color: color-mix(in srgb, var(--color-primary) 6%, white) !important; }

        /* brand-* accent (links and hover accents) */
        .bg-brand-50 { background-color: color-mix(in srgb, var(--color-accent) 6%, white) !important; }
        .hover\:bg-brand-50:hover { background-color: color-mix(in srgb, var(--color-accent) 12%, white) !important; }
        .text-brand-600 { color: var(--color-accent) !important; }
        .hover\:text-brand-600:hover { color: var(--color-accent-dark) !important; }
        .group-hover\:text-brand-600:is(:where(.group):hover *) { color: var(--color-accent) !important; }
        .ring-brand-200 { --tw-ring-color: var(--color-accent) !important; }
        .ring-brand-300 { --tw-ring-color: var(--color-accent) !important; }
        .hover\:ring-brand-200:hover { --tw-ring-color: var(--color-accent) !important; }
        .hover\:ring-brand-300:hover { --tw-ring-color: var(--color-accent) !important; }
        .focus-within\:ring-brand-500:focus-within { --tw-ring-color: var(--color-primary) !important; }

        /* brand-* primary (buttons, badges, tints) */
        .bg-brand-600 { background-color: var(--color-primary) !important; }
        .hover\:bg-brand-700:hover { background-color: var(--color-primary-dark) !important; }
        .bg-brand-100 { background-color: color-mix(in srgb, var(--color-primary) 12%, white) !important; }
        .bg-brand-200 { background-color: color-mix(in srgb, var(--color-primary) 25%, white) !important; }
        .text-brand-800 { color: color-mix(in srgb, var(--color-primary) 60%, black) !important; }
        .focus-within\:border-brand-500:focus-within { border-color: var(--color-primary) !important; }

        /* violet (WhatsApp pill) follows accent */
        .bg-violet-100 { background-color: color-mix(in srgb, var(--color-accent) 12%, white) !important; }
        .hover\:bg-violet-200:hover { background-color: color-mix(in srgb, var(--color-accent) 22%, white) !important; }
        .text-violet-700 { color: color-mix(in srgb, var(--color-accent) 72%, black) !important; }
    </style>
</head>
<body class="storefront bg-white text-gray-900 flex flex-col min-h-screen">

    <!-- Header / Navbar -->
    @php
        $parentCats = \App\Models\Category::whereNull('parent_id')->with('children')->orderBy('name')->get();
        $cartCount = count(session('cart', []));
        $whatsapp = App\Models\Setting::getValue('whatsapp_number', '');
        $navCatProducts = [];
        foreach ($parentCats as $pcat) {
            $ids = collect([$pcat->id])->merge($pcat->children->pluck('id'));
            $navCatProducts[$pcat->id] = \App\Models\Product::with(['images'])
                ->where('is_active', true)
                ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $ids))
                ->orderBy('sort_order')->latest()->limit(6)->get();
        }
    @endphp

    @include('store.partials.navbar')

    <!-- Notification Toasts / Banners -->
    @if(session('success'))
        <div class="bg-gradient-to-r from-purple-600 to-pink-500 text-white text-center py-3 px-4 font-medium relative shadow-md">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-500 text-white text-center py-3 px-4 font-medium relative shadow-md">
            {{ session('error') }}
        </div>
    @endif

    <!-- Main Content Grid -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    @include('store.partials.footer', ['parentCats' => $parentCats])

    <!-- Side Cart Drawer -->
    <div x-data="sideCart()" 
         @open-cart.window="open()" 
         class="relative z-50" 
         x-show="isOpen" 
         x-cloak
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity duration-300"
             x-show="isOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="close()"></div>

        <div class="fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 overflow-hidden">
                <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                    
                    <!-- Slide-over panel -->
                    <div class="pointer-events-auto w-screen max-w-md"
                         x-show="isOpen"
                         @click.away="close()"
                         x-transition:enter="transform transition ease-in-out duration-300"
                         x-transition:enter-start="translate-x-full"
                         x-transition:enter-end="translate-x-0"
                         x-transition:leave="transform transition ease-in-out duration-300"
                         x-transition:leave-start="translate-x-0"
                         x-transition:leave-end="translate-x-full">
                        
                        <div class="flex h-full flex-col bg-white shadow-2xl border-l border-purple-100">
                            
                            <!-- Header -->
                            <div class="flex items-center justify-between border-b border-gray-150 px-6 py-5 bg-purple-50/20">
                                <h2 class="text-base font-bold text-gray-900 flex items-center space-x-2">
                                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                    <span>Shopping Cart</span>
                                </h2>
                                <button type="button" @click="close()" class="text-gray-400 hover:text-purple-600 transition">
                                    <span class="sr-only">Close panel</span>
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Content / Item List -->
                            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-4">
                                <div class="space-y-4" x-show="count > 0">
                                    <template x-for="item in items" :key="item.key">
                                        <div class="flex items-start bg-white border border-gray-100 rounded-xl p-3 gap-3 shadow-sm">
                                            <!-- Image -->
                                            <div class="w-16 h-16 bg-gray-50 border border-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                                                <img x-show="item.image" :src="item.image" :alt="item.name" class="w-full h-full object-cover">
                                                <div x-show="!item.image" class="w-full h-full flex items-center justify-center text-purple-300 bg-purple-50">
                                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            </div>

                                            <!-- Details -->
                                            <div class="flex-grow space-y-1">
                                                <h4 class="font-bold text-gray-900 text-xs truncate max-w-[180px]" x-text="item.name"></h4>
                                                <p class="text-[10px] font-semibold text-purple-600" x-show="item.variation_details" x-text="item.variation_details"></p>
                                                <div class="flex items-center justify-between mt-2">
                                                    <!-- Quantity controls -->
                                                    <div class="flex items-center border border-gray-200 rounded overflow-hidden bg-gray-50">
                                                        <button type="button" @click="updateQty(item.key, item.quantity - 1)" class="px-1.5 py-0.5 text-gray-500 hover:bg-gray-100 text-xs">-</button>
                                                        <span class="px-2 text-xs font-bold text-gray-950" x-text="item.quantity"></span>
                                                        <button type="button" @click="updateQty(item.key, item.quantity + 1)" class="px-1.5 py-0.5 text-gray-500 hover:bg-gray-100 text-xs">+</button>
                                                    </div>
                                                    <!-- Price -->
                                                    <span class="text-xs font-bold text-gray-950" x-text="money(item.price * item.quantity)"></span>
                                                </div>
                                            </div>

                                            <!-- Remove button -->
                                            <button type="button" @click="removeItem(item.key)" class="text-pink-600 hover:text-pink-700 transition flex-shrink-0">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                                <div class="flex flex-col items-center justify-center py-20 space-y-4 text-center" x-show="count === 0">
                                    <div class="w-16 h-16 bg-purple-50 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                        </svg>
                                    </div>
                                    <div class="text-sm font-semibold text-gray-400">Your cart is empty</div>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div x-show="count > 0" class="border-t border-gray-150 px-6 py-6 bg-gray-50 space-y-4">
                                <div class="flex justify-between items-baseline font-bold text-gray-900">
                                    <span>Subtotal</span>
                                    <span class="text-purple-600 text-lg" x-text="money(subtotal)"></span>
                                </div>
                                <p class="text-[10px] text-gray-400">Shipping and taxes calculated at checkout.</p>

                                <div class="space-y-2">
                                    <a href="{{ route('checkout') }}" class="block w-full text-center py-3 bg-primary hover:bg-primary text-white rounded-lg font-bold tracking-wide transition shadow-md">
                                        Checkout Now
                                    </a>
                                    <a href="{{ route('cart') }}" class="block w-full text-center py-2 text-xs font-bold text-purple-600 hover:text-primary uppercase tracking-wider">
                                        View Full Cart
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script>
        @php
            $sideCartPrepared = [];
            foreach (session('cart', []) as $cartKey => $cartItem) {
                $sideCartPrepared[] = [
                    'key' => $cartKey,
                    'name' => $cartItem['name'],
                    'image' => $cartItem['image'] ? Storage::url($cartItem['image']) : null,
                    'variation_details' => $cartItem['variation_details'] ?? null,
                    'price' => (float) $cartItem['price'],
                    'quantity' => (int) $cartItem['quantity'],
                ];
            }
        @endphp

        document.addEventListener('alpine:init', () => {
            Alpine.data('sideCart', () => ({
                isOpen: false,
                items: @json($sideCartPrepared),

                open() {
                    this.isOpen = true;
                },
                close() {
                    this.isOpen = false;
                },
                get count() {
                    return this.items.length;
                },
                get subtotal() {
                    return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                money(n) {
                    return '\u09F3' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                },
                refreshBadge() {
                    document.querySelectorAll('[data-cart-badge]').forEach((badge) => {
                        badge.textContent = this.count;
                        badge.classList.toggle('hidden', this.count === 0);
                        badge.style.display = this.count === 0 ? 'none' : '';
                    });
                },
                async updateQty(key, qty) {
                    if (qty < 1) return;
                    const item = this.items.find(i => i.key === key);
                    if (!item) return;
                    const previous = item.quantity;
                    item.quantity = qty;

                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    try {
                        const response = await fetch('{{ route("cart.update") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ key, quantity: qty }),
                        });
                        const data = await response.json();
                        if (!data.success) {
                            item.quantity = previous;
                            this.refreshBadge();
                            alert(data.message || 'Cannot update quantity.');
                        }
                    } catch (e) {
                        item.quantity = previous;
                        this.refreshBadge();
                        alert('Cannot update quantity.');
                    }
                },
                async removeItem(key) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
                    try {
                        const response = await fetch('{{ route("cart.remove") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: JSON.stringify({ key }),
                        });
                        const data = await response.json();
                        if (data.success) {
                            this.items = this.items.filter(i => i.key !== key);
                            this.refreshBadge();
                        } else {
                            alert(data.message || 'Cannot remove item.');
                        }
                    } catch (e) {
                        alert('Cannot remove item.');
                    }
                }
            }));
        });

        // Price range slider (used on the shop page)
        document.addEventListener('alpine:init', () => {
            Alpine.data('priceSlider', (options) => ({
                min: 0,
                max: 10000,
                selectedMin: 0,
                selectedMax: 10000,
                drag: null,
                moved: false,
                step: 10,

                init() {
                    this.min = Number(options.min);
                    this.max = Number(options.max);
                    if (!isFinite(this.min) || this.min < 0) this.min = 0;
                    if (!isFinite(this.max) || this.max <= this.min) this.max = this.min + 100;
                    this.selectedMin = Math.max(Number(options.selectedMin), this.min);
                    this.selectedMax = Math.min(Number(options.selectedMax), this.max);
                    if (!isFinite(this.selectedMin)) this.selectedMin = this.min;
                    if (!isFinite(this.selectedMax)) this.selectedMax = this.max;
                    if (this.selectedMin > this.selectedMax) this.selectedMin = this.selectedMax;
                    this.step = Math.max(10, Math.round(this.range / 200));
                },

                get range() {
                    return this.max - this.min;
                },
                get pctMin() {
                    return ((this.selectedMin - this.min) / this.range) * 100;
                },
                get pctMax() {
                    return ((this.selectedMax - this.min) / this.range) * 100;
                },

                displayPrice(n) {
                    return Number(n).toLocaleString('en-US');
                },

                startDrag(event) {
                    if (event.button !== 0) return;
                    const slider = this.$refs.slider;
                    if (!slider) return;
                    const rect = slider.getBoundingClientRect();
                    const pct = ((event.clientX - rect.left) / rect.width) * 100;
                    this.drag = Math.abs(pct - this.pctMin) <= Math.abs(pct - this.pctMax) ? 'min' : 'max';
                    this.moved = false;
                    try {
                        slider.setPointerCapture(event.pointerId);
                    } catch (e) {}
                    event.preventDefault();
                    this.updateFromX(event.clientX - rect.left, rect.width);
                },

                onDrag(event) {
                    if (!this.drag) return;
                    const slider = this.$refs.slider;
                    if (!slider) return;
                    const rect = slider.getBoundingClientRect();
                    this.updateFromX(event.clientX - rect.left, rect.width);
                },

                updateFromX(x, width) {
                    if (width <= 0 || this.range <= 0) return;
                    let value = this.min + (x / width) * this.range;
                    value = Math.round(value / this.step) * this.step;

                    if (this.drag === 'min') {
                        const next = Math.min(Math.max(value, this.min), this.selectedMax);
                        if (next !== this.selectedMin) {
                            this.selectedMin = next;
                            this.moved = true;
                        }
                    } else {
                        const next = Math.max(Math.min(value, this.max), this.selectedMin);
                        if (next !== this.selectedMax) {
                            this.selectedMax = next;
                            this.moved = true;
                        }
                    }
                },

                endDrag(event) {
                    if (!this.drag) return;
                    const slider = this.$refs.slider;
                    if (slider && slider.hasPointerCapture && slider.hasPointerCapture(event.pointerId)) {
                        try {
                            slider.releasePointerCapture(event.pointerId);
                        } catch (e) {}
                    }
                    this.drag = null;
                    if (this.moved) {
                        this.moved = false;
                        const form = document.getElementById('price-filter-form');
                        if (form && typeof form.requestSubmit === 'function') {
                            form.requestSubmit();
                        }
                    }
                }
            }));
        });

        // Ensure cart opens on page load if needed
        document.addEventListener('DOMContentLoaded', () => {
            // Add click handler to cart button
            const cartBtn = document.getElementById('cart-nav-btn');
            if (cartBtn) {
                cartBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    console.log('Cart button clicked');
                    window.dispatchEvent(new Event('open-cart'));
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('open_cart') === '1') {
                setTimeout(() => {
                    window.dispatchEvent(new Event('open-cart'));
                }, 500);
            }
        });
    </script>

    @if($pixelId)
    {{-- Flash-based pixel events (AddToCart, Purchase, etc.) --}}
    @if(session()->has('pixel_events'))
        @foreach(session('pixel_events') as $event)
        <script>fbq('track', '{{ $event['event'] }}', @json($event['data'] ?? []));</script>
        @endforeach
        @php session()->forget('pixel_events') @endphp
    @endif
    {{-- Page-specific pixel events (ViewContent, Search, InitiateCheckout, etc.) --}}
    @yield('pixel_events')
    @endif

    @yield('scripts')
</body>
</html>

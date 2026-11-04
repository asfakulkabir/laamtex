@php
    $fbUrl = App\Models\Setting::getValue('facebook_url', '');
    $igUrl = App\Models\Setting::getValue('instagram_url', '');
    $ytUrl = App\Models\Setting::getValue('youtube_url', '');
    $twUrl = App\Models\Setting::getValue('twitter_url', '');
    $contactEmail = App\Models\Setting::getValue('contact_email', 'laamtexoffice@gmail.com');
    $storeAddress = office_address();
    $factoryAddress = factory_address();
    $storePhone = contact_phone();
    $storePhoneDigits = contact_phone_digits();
    $waDigits = whatsapp_number_digits();
    $waLink = whatsapp_link();
    $socials = array_filter([
        ['url' => $fbUrl, 'label' => 'Facebook', 'path' => 'M13.58 22V12.88H16.64L17.1 9.32H13.58V7.04C13.58 6.01 13.87 5.3 15.34 5.3H17.22V2.12C16.89 2.08 15.76 2 14.45 2C11.72 2 9.86 3.67 9.86 6.73V9.32H6.8V12.88H9.86V22H13.58Z'],
        ['url' => $igUrl, 'label' => 'Instagram', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
        ['url' => $ytUrl, 'label' => 'YouTube', 'path' => 'M21.58 7.19C21.35 6.33 20.67 5.66 19.81 5.42C18.25 5 12 5 12 5C12 5 5.75 5 4.19 5.42C3.33 5.66 2.65 6.33 2.42 7.19C2 8.75 2 12 2 12C2 12 2 15.25 2.42 16.81C2.65 17.67 3.33 18.34 4.19 18.58C5.75 19 12 19 12 19C12 19 18.25 19 19.81 18.58C20.67 18.34 21.35 17.67 21.58 16.81C22 15.25 22 12 22 12C22 12 22 8.75 21.58 7.19ZM10 15.01V8.99L15.2 12L10 15.01Z'],
        ['url' => $twUrl, 'label' => 'X (Twitter)', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    ]);
@endphp

<footer class="mt-auto border-t border-gray-200 bg-gray-50 text-gray-600">

    <!-- Footer links -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-10 pb-8 sm:pt-12 lg:pt-16 lg:pb-12">
        <div class="grid grid-cols-2 gap-x-6 gap-y-9 md:grid-cols-3 lg:grid-cols-12 lg:gap-x-8">

            <!-- Brand -->
            <div class="col-span-2 md:col-span-3 lg:col-span-3">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ site_logo() }}" alt="{{ site_name() }}" class="h-11 w-auto max-w-[200px] object-contain sm:h-12 sm:max-w-[220px]">
                </a>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-gray-600">
                    {{ site_description() ?: site_name() . ' - Premium products curated for modern lifestyles.' }}
                </p>

                @if($socials)
                    <div class="mt-5 flex flex-wrap items-center gap-2.5">
                        @foreach($socials as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                               aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}"
                               class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white text-gray-600 ring-1 ring-gray-200 transition hover:bg-gray-900 hover:text-white hover:ring-gray-900">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-[18px] w-[18px]"><path d="{{ $social['path'] }}"></path></svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Shop -->
            <nav class="min-w-0 lg:col-span-2" aria-label="Shop">
                <h3 class="text-xs font-bold uppercase tracking-[0.14em] text-gray-900">Shop<span class="mt-2 block h-0.5 w-6 rounded-full bg-rose-500"></span></h3>
                <ul class="mt-4 space-y-1.5 text-sm">
                    <li><a href="{{ route('shop') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">Shop All</a></li>
                    <li><a href="{{ route('categories') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">All Categories</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'latest']) }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">New Arrivals</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'price_asc']) }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">Price: Low to High</a></li>
                    <li><a href="{{ route('cart') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">My Cart</a></li>
                </ul>
            </nav>

            <!-- Categories -->
            <nav class="min-w-0 lg:col-span-2" aria-label="Categories">
                <h3 class="text-xs font-bold uppercase tracking-[0.14em] text-gray-900">Categories<span class="mt-2 block h-0.5 w-6 rounded-full bg-rose-500"></span></h3>
                <ul class="mt-4 space-y-1.5 text-sm">
                    @foreach(($parentCats ?? collect())->take(8) as $cat)
                        <li><a href="{{ route('shop', ['category' => $cat->slug]) }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <!-- Customer care -->
            <nav class="min-w-0 lg:col-span-2" aria-label="Customer care">
                <h3 class="text-xs font-bold uppercase tracking-[0.14em] text-gray-900">Customer Care<span class="mt-2 block h-0.5 w-6 rounded-full bg-rose-500"></span></h3>
                <ul class="mt-4 space-y-1.5 text-sm">
                    @if(auth()->check() && auth()->user()->isCustomer())
                        <li><a href="{{ route('customer.dashboard') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">My Orders</a></li>
                    @else
                        {{-- Guests get the public phone lookup instead of a login wall --}}
                        <li><a href="{{ route('order.tracking') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">My Orders</a></li>
                    @endif
                    <li><a href="{{ route('checkout') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">Checkout</a></li>
                    <li><a href="{{ route('contact.page') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">Contact Us</a></li>
                    <li><a href="{{ route('order.tracking') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">Track Your Order</a></li>
                    <li><a href="{{ route('privacy.policy') }}" class="inline-block py-0.5 text-gray-600 transition-colors hover:text-gray-900">Privacy Policy</a></li>
                </ul>
            </nav>

            <!-- Contact -->
            <div class="min-w-0 lg:col-span-3">
                <h3 class="text-xs font-bold uppercase tracking-[0.14em] text-gray-900">Contact<span class="mt-2 block h-0.5 w-6 rounded-full bg-rose-500"></span></h3>
                <ul class="mt-4 space-y-3 text-sm">
                    @if($contactEmail)
                        <li class="flex items-start gap-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"></rect><path d="m3 7 9 6 9-6"></path></svg>
                            <a href="mailto:{{ $contactEmail }}" class="min-w-0 break-words text-gray-600 transition-colors hover:text-gray-900">{{ $contactEmail }}</a>
                        </li>
                    @endif
                    @if($storePhone)
                        <li class="flex items-start gap-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                            <a href="tel:{{ $storePhoneDigits }}" class="text-gray-600 transition-colors hover:text-gray-900">{{ $storePhone }}</a>
                        </li>
                    @endif
                    @if($storeAddress)
                        <li class="flex items-start gap-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span class="min-w-0 leading-relaxed text-gray-600"><span class="font-semibold text-gray-800">Office:</span> {{ $storeAddress }}</span>
                        </li>
                    @endif
                    @if($factoryAddress)
                        <li class="flex items-start gap-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <span class="min-w-0 leading-relaxed text-gray-600"><span class="font-semibold text-gray-800">Factory:</span> {{ $factoryAddress }}</span>
                        </li>
                    @endif
                    @if($waLink !== '#')
                        <li class="flex items-start gap-3">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" aria-hidden="true"><path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 20.5l1.6-5.4A8.4 8.4 0 1 1 21 11.5z"></path></svg>
                            <a href="{{ $waLink }}" target="_blank" rel="noopener" class="text-gray-600 transition-colors hover:text-gray-900">WhatsApp</a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t border-gray-200 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col-reverse items-center justify-between gap-3 px-4 py-4 text-center sm:flex-row sm:px-6 sm:py-5 sm:text-left lg:px-8">
            <p class="text-xs text-gray-500">
                &copy; {{ date('Y') }} {{ site_name() }}. All rights reserved.
            </p>

            <div class="flex flex-wrap items-center justify-center gap-2 sm:justify-end">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">We accept</span>
                <span class="inline-flex items-center gap-1.5 rounded-md bg-gray-50 px-3 py-1.5 text-xs font-bold text-pink-600 ring-1 ring-gray-200">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 2l10 5.5v9L12 22 2 16.5v-9L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path></svg>
                    bKash
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-md bg-gray-50 px-3 py-1.5 text-xs font-bold text-gray-700 ring-1 ring-gray-200">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.5"></rect><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.5"></circle></svg>
                    Cash on Delivery
                </span>
            </div>
        </div>
    </div>
</footer>

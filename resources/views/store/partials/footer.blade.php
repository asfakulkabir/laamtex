@php
    $fbUrl = App\Models\Setting::getValue('facebook_url', '');
    $igUrl = App\Models\Setting::getValue('instagram_url', '');
    $ytUrl = App\Models\Setting::getValue('youtube_url', '');
    $twUrl = App\Models\Setting::getValue('twitter_url', '');
    $contactEmail = App\Models\Setting::getValue('contact_email', 'laamtexoffice@gmail.com');
    $storeAddress = App\Models\Setting::getValue('office_address', 'House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh');
    $storePhone = App\Models\Setting::getValue('whatsapp_number', '');
    $storePhoneDigits = $storePhone ? preg_replace('/[^0-9]/', '', $storePhone) : '';
    $waDigits = $storePhoneDigits;
    $waLink = $waDigits ? 'https://wa.me/' . $waDigits : '#';
    $footerCats = ($parentCats ?? collect())->take(6);
    $socials = array_filter([
        ['url' => $fbUrl, 'label' => 'Facebook', 'path' => 'M13.58 22V12.88H16.64L17.1 9.32H13.58V7.04C13.58 6.01 13.87 5.3 15.34 5.3H17.22V2.12C16.89 2.08 15.76 2 14.45 2C11.72 2 9.86 3.67 9.86 6.73V9.32H6.8V12.88H9.86V22H13.58Z'],
        ['url' => $igUrl, 'label' => 'Instagram', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
        ['url' => $ytUrl, 'label' => 'YouTube', 'path' => 'M21.58 7.19C21.35 6.33 20.67 5.66 19.81 5.42C18.25 5 12 5 12 5C12 5 5.75 5 4.19 5.42C3.33 5.66 2.65 6.33 2.42 7.19C2 8.75 2 12 2 12C2 12 2 15.25 2.42 16.81C2.65 17.67 3.33 18.34 4.19 18.58C5.75 19 12 19 12 19C12 19 18.25 19 19.81 18.58C20.67 18.34 21.35 17.67 21.58 16.81C22 15.25 22 12 22 12C22 12 22 8.75 21.58 7.19ZM10 15.01V8.99L15.2 12L10 15.01Z'],
        ['url' => $twUrl, 'label' => 'X (Twitter)', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    ]);
@endphp

<footer class="mt-auto relative border-t border-gray-200 bg-white text-gray-600">

    <!-- Contact strip -->
    <div class="bg-brand-50 border-b border-gray-100">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 lg:py-10">
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">

                @if($contactEmail)
                    <a href="mailto:{{ $contactEmail }}" class="group flex items-start gap-4">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-gray-200 shadow-sm group-hover:shadow transition">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M17 20.5H7C4 20.5 2 19 2 15.5V8.5C2 5 4 3.5 7 3.5H17C20 3.5 22 5 22 8.5V15.5C22 19 20 20.5 17 20.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17 9L13.87 11.5C12.84 12.32 11.15 12.32 10.12 11.5L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Email Us</span>
                            <span class="block text-sm font-semibold text-gray-900 truncate">{{ $contactEmail }}</span>
                        </span>
                    </a>
                @endif

                @if($storePhone)
                    <a href="tel:{{ $storePhoneDigits }}" class="group flex items-start gap-4">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-gray-200 shadow-sm group-hover:shadow transition">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Hotline</span>
                            <span class="block text-sm font-semibold text-gray-900">{{ $storePhone }}</span>
                        </span>
                    </a>
                @endif

                @if($storeAddress)
                    <div class="group flex items-start gap-4">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-brand-600 ring-1 ring-gray-200 shadow-sm">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M12 13.43C13.7231 13.43 15.12 12.0331 15.12 10.31C15.12 8.58687 13.7231 7.19 12 7.19C10.2769 7.19 8.88 8.58687 8.88 10.31C8.88 12.0331 10.2769 13.43 12 13.43Z" stroke="currentColor" stroke-width="1.5"></path><path d="M3.62 8.49C5.59 -0.17 18.42 -0.16 20.38 8.5C21.53 13.58 18.37 17.88 15.6 20.54C13.59 22.48 10.41 22.48 8.39 20.54C5.63 17.88 2.47 13.57 3.62 8.49Z" stroke="currentColor" stroke-width="1.5"></path></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Showroom</span>
                            <span class="block text-sm font-semibold text-gray-900 leading-snug">{{ $storeAddress }}</span>
                        </span>
                    </div>
                @endif

                @if($waLink !== '#')
                    <a href="{{ $waLink }}" target="_blank" rel="noopener" class="group flex items-start gap-4">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-green-50 text-green-600 ring-1 ring-green-100 shadow-sm group-hover:shadow transition">
                            <svg viewBox="0 0 24 24" fill="none" class="h-5 w-5"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2v10z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-gray-400">Chat on WhatsApp</span>
                            <span class="block text-sm font-semibold text-gray-900">We reply within minutes</span>
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Footer links -->
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-12 lg:gap-8">

            <!-- Brand -->
            <div class="lg:col-span-4">
                <a href="{{ route('home') }}" class="inline-block">
                    <img src="{{ site_logo() }}" alt="{{ site_name() }}" class="h-12 w-auto max-w-[220px] object-contain">
                </a>
                <p class="mt-5 max-w-sm text-sm leading-relaxed text-gray-500">
                    {{ site_description() ?: site_name() . ' - Premium products curated for modern lifestyles.' }}
                </p>

                @if($socials)
                    <div class="mt-6 flex flex-wrap items-center gap-2.5">
                        @foreach($socials as $social)
                            <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                               aria-label="{{ $social['label'] }}" title="{{ $social['label'] }}"
                               class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-gray-50 text-gray-500 ring-1 ring-gray-200 hover:bg-gray-900 hover:text-white hover:ring-gray-900 transition">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="h-[18px] w-[18px]"><path d="{{ $social['path'] }}"></path></svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Shop -->
            <div class="lg:col-span-2">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">Shop</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('shop') }}" class="text-gray-500 hover:text-gray-900 transition">Shop All</a></li>
                    <li><a href="{{ route('categories') }}" class="text-gray-500 hover:text-gray-900 transition">All Categories</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'latest']) }}" class="text-gray-500 hover:text-gray-900 transition">New Arrivals</a></li>
                    <li><a href="{{ route('shop', ['sort' => 'price_asc']) }}" class="text-gray-500 hover:text-gray-900 transition">Price: Low to High</a></li>
                    <li><a href="{{ route('cart') }}" class="text-gray-500 hover:text-gray-900 transition">My Cart</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">Categories</h3>
                <ul class="mt-4 grid grid-cols-2 gap-x-4 gap-y-2.5 text-sm">
                    @forelse($footerCats as $cat)
                        <li>
                            <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="text-gray-500 hover:text-gray-900 transition">{{ $cat->name }}</a>
                        </li>
                    @empty
                        <li class="text-gray-400">Coming soon</li>
                    @endforelse
                </ul>
            </div>

            <!-- Customer care -->
            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">Customer Care</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('customer.login') }}" class="text-gray-500 hover:text-gray-900 transition">My Account</a></li>
                    <li><a href="{{ route('customer.dashboard') }}" class="text-gray-500 hover:text-gray-900 transition">My Orders</a></li>
                    <li><a href="{{ route('cart') }}" class="text-gray-500 hover:text-gray-900 transition">Track My Order</a></li>
                    <li><a href="{{ route('customer.login') }}?checkout_redirect=1" class="text-gray-500 hover:text-gray-900 transition">Checkout</a></li>
                    <li>
                        <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                                class="text-gray-500 hover:text-gray-900 transition">Back to Top ↑</button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Bottom bar -->
    <div class="border-t border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs text-gray-500 order-2 sm:order-1">
                &copy; {{ date('Y') }} {{ site_name() }}. All rights reserved.
            </p>

            <div class="order-1 sm:order-2 flex items-center gap-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400">We accept</span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-pink-600 ring-1 ring-gray-200">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="M12 2l10 5.5v9L12 22 2 16.5v-9L12 2z" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round"></path></svg>
                    bKash
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-gray-700 ring-1 ring-gray-200">
                    <svg viewBox="0 0 24 24" fill="none" class="h-4 w-4"><rect x="2" y="6" width="20" height="12" rx="2" stroke="currentColor" stroke-width="1.5"></rect><circle cx="12" cy="12" r="2.5" stroke="currentColor" stroke-width="1.5"></circle></svg>
                    Cash on Delivery
                </span>
            </div>
        </div>
    </div>
</footer>

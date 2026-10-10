@php
    $fbUrl = App\Models\Setting::getValue('facebook_url', '');
    $igUrl = App\Models\Setting::getValue('instagram_url', '');
    $ytUrl = App\Models\Setting::getValue('youtube_url', '');
    $twUrl = App\Models\Setting::getValue('twitter_url', '');
    $contactEmail = App\Models\Setting::getValue('contact_email', 'laamtexoffice@gmail.com');
    $storeAddress = App\Models\Setting::getValue('office_address', 'House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh');
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

<footer class="mt-auto relative border-t border-gray-200 bg-white text-gray-600">

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

            <!-- Customer care -->
            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">Customer Care</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('customer.login') }}" class="text-gray-500 hover:text-gray-900 transition">My Account</a></li>
                    <li><a href="{{ route('customer.dashboard') }}" class="text-gray-500 hover:text-gray-900 transition">My Orders</a></li>
                    <li><a href="{{ route('customer.login') }}?checkout_redirect=1" class="text-gray-500 hover:text-gray-900 transition">Checkout</a></li>
                    <li><a href="{{ route('privacy.policy') }}" class="text-gray-500 hover:text-gray-900 transition">Privacy Policy</a></li>
                    <li>
                        <button type="button" onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
                                class="text-gray-500 hover:text-gray-900 transition">Back to Top ↑</button>
                    </li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="lg:col-span-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900">Contact</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    @if($contactEmail)
                        <li><a href="mailto:{{ $contactEmail }}" class="text-gray-500 hover:text-gray-900 transition">{{ $contactEmail }}</a></li>
                    @endif
                    @if($storePhone)
                        <li><a href="tel:{{ $storePhoneDigits }}" class="text-gray-500 hover:text-gray-900 transition">{{ $storePhone }}</a></li>
                    @endif
                    @if($storeAddress)
                        <li class="text-gray-500 leading-relaxed">{{ $storeAddress }}</li>
                    @endif
                    @if($waLink !== '#')
                        <li><a href="{{ $waLink }}" target="_blank" rel="noopener" class="text-gray-500 hover:text-gray-900 transition">WhatsApp</a></li>
                    @endif
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

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
    @php
        $fbUrl = App\Models\Setting::getValue('facebook_url', '');
        $igUrl = App\Models\Setting::getValue('instagram_url', '');
        $ytUrl = App\Models\Setting::getValue('youtube_url', '');
        $twUrl = App\Models\Setting::getValue('twitter_url', '');
        $contactEmail = App\Models\Setting::getValue('contact_email', 'laamtexoffice@gmail.com');
        $storeAddress = App\Models\Setting::getValue('office_address', 'House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh');
        $storePhone = App\Models\Setting::getValue('whatsapp_number', '');
        $storePhoneDigits = $storePhone ? preg_replace('/[^0-9]/', '', $storePhone) : '';
    @endphp
    <footer class="relative overflow-hidden border-t border-[rgba(145,158,171,0.24)] bg-[#f1f8f5] text-[#212b36] lg:min-h-[400px] lg:bg-white">
        <div class="px-4 pt-[30px] pb-[calc(45px+var(--mobile-bottom-nav-height,56px))] lg:max-w-[1280px] lg:mx-auto lg:px-0 lg:pt-12 lg:pb-[137px]">
            <div class="lg:flex lg:justify-between">
                <div class="w-full max-w-[285px]">
                    <a href="{{ route('home') }}">
                        <img src="{{ site_logo() }}" alt="{{ site_name() }}" width="280" height="64" class="block h-12 w-[210px] object-contain lg:h-16 lg:w-[280px]">
                    </a>
                    <p class="mt-6 max-w-[285px] text-sm leading-relaxed text-[#637381]">{{ site_description() ?: site_name() . ' - Premium products curated for modern lifestyles.' }}</p>
                    @if($fbUrl || $igUrl || $ytUrl || $twUrl)
                        <div class="mt-6 flex items-center gap-5 lg:mt-12">
                            @if($fbUrl)
                                <a href="{{ $fbUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex transition-opacity duration-150 hover:opacity-80" aria-label="Facebook">
                                    <span class="inline-flex h-[35px] w-[35px] items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 transition-all duration-200 hover:scale-105">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-[#1877f2]"><path d="M13.58 22V12.88H16.64L17.1 9.32H13.58V7.04C13.58 6.01 13.87 5.3 15.34 5.3H17.22V2.12C16.89 2.08 15.76 2 14.45 2C11.72 2 9.86 3.67 9.86 6.73V9.32H6.8V12.88H9.86V22H13.58Z"></path></svg>
                                    </span>
                                </a>
                            @endif
                            @if($igUrl)
                                <a href="{{ $igUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex transition-opacity duration-150 hover:opacity-80" aria-label="Instagram">
                                    <span class="inline-flex h-[35px] w-[35px] items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 transition-all duration-200 hover:scale-105">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    </span>
                                </a>
                            @endif
                            @if($ytUrl)
                                <a href="{{ $ytUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex transition-opacity duration-150 hover:opacity-80" aria-label="YouTube">
                                    <span class="inline-flex h-[35px] w-[35px] items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 transition-all duration-200 hover:scale-105">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5 text-[#ff0000]"><path d="M21.58 7.19C21.35 6.33 20.67 5.66 19.81 5.42C18.25 5 12 5 12 5C12 5 5.75 5 4.19 5.42C3.33 5.66 2.65 6.33 2.42 7.19C2 8.75 2 12 2 12C2 12 2 15.25 2.42 16.81C2.65 17.67 3.33 18.34 4.19 18.58C5.75 19 12 19 12 19C12 19 18.25 19 19.81 18.58C20.67 18.34 21.35 17.67 21.58 16.81C22 15.25 22 12 22 12C22 12 22 8.75 21.58 7.19ZM10 15.01V8.99L15.2 12L10 15.01Z"/></svg>
                                    </span>
                                </a>
                            @endif
                            @if($twUrl)
                                <a href="{{ $twUrl }}" target="_blank" rel="noopener noreferrer" class="inline-flex transition-opacity duration-150 hover:opacity-80" aria-label="Twitter / X">
                                    <span class="inline-flex h-[35px] w-[35px] items-center justify-center rounded-full bg-gray-50 hover:bg-gray-100 transition-all duration-200 hover:scale-105">
                                        <svg viewBox="0 0 24 24" fill="currentColor" class="h-5 w-5"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    </span>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="mt-12 flex flex-col gap-12 lg:mt-0 lg:ml-auto lg:flex-row lg:items-start lg:gap-[65px]">
                    @if($contactEmail || $storePhone || $storeAddress)
                        <div class="max-w-[297px] lg:order-3">
                            <h3 class="text-lg font-semibold text-[#212b36]">Contact</h3>
                            <div class="mt-[13px] flex flex-col gap-4">
                                @if($contactEmail)
                                    <div class="flex items-start gap-[14px]">
                                        <span class="mt-0.5 shrink-0 text-gray-700">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M17 20.5H7C4 20.5 2 19 2 15.5V8.5C2 5 4 3.5 7 3.5H17C20 3.5 22 5 22 8.5V15.5C22 19 20 20.5 17 20.5Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17 9L13.87 11.5C12.84 12.32 11.15 12.32 10.12 11.5L7 9" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        </span>
                                        <div class="flex flex-col text-sm text-[#212b36]">
                                            <span class="font-semibold">Email Us</span>
                                            <span class="flex flex-wrap items-center gap-x-1.5">
                                                <a class="hover:text-gray-900" href="mailto:{{ $contactEmail }}">{{ $contactEmail }}</a>
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                @if($storePhone)
                                    <div class="flex items-start gap-[14px]">
                                        <span class="mt-0.5 shrink-0 text-gray-700">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M21.97 18.33C21.97 18.69 21.89 19.06 21.72 19.42C21.55 19.78 21.33 20.12 21.04 20.44C20.55 20.98 20.01 21.37 19.4 21.62C18.8 21.87 18.15 22 17.45 22C16.43 22 15.34 21.76 14.19 21.27C13.04 20.78 11.89 20.12 10.75 19.29C9.6 18.45 8.51 17.52 7.47 16.49C6.44 15.45 5.51 14.36 4.68 13.22C3.86 12.08 3.2 10.94 2.72 9.81C2.24 8.67 2 7.58 2 6.54C2 5.86 2.12 5.21 2.36 4.61C2.6 4 2.98 3.44 3.51 2.94C4.15 2.31 4.85 2 5.59 2C5.87 2 6.15 2.06 6.4 2.18C6.66 2.3 6.89 2.48 7.07 2.74L9.39 6.01C9.57 6.26 9.7 6.49 9.79 6.71C9.88 6.92 9.93 7.13 9.93 7.32C9.93 7.56 9.86 7.8 9.72 8.03C9.59 8.26 9.4 8.5 9.16 8.74L8.4 9.53C8.29 9.64 8.24 9.77 8.24 9.93C8.24 10.01 8.25 10.08 8.27 10.16C8.3 10.24 8.33 10.3 8.35 10.36C8.53 10.69 8.84 11.12 9.28 11.64C9.73 12.16 10.21 12.69 10.73 13.22C11.27 13.75 11.79 14.24 12.32 14.69C12.84 15.13 13.27 15.43 13.61 15.61C13.66 15.63 13.72 15.66 13.79 15.69C13.87 15.72 13.95 15.73 14.04 15.73C14.21 15.73 14.34 15.67 14.45 15.56L15.21 14.81C15.46 14.56 15.7 14.37 15.93 14.25C16.16 14.11 16.39 14.04 16.64 14.04C16.83 14.04 17.03 14.08 17.25 14.17C17.47 14.26 17.7 14.39 17.95 14.56L21.26 16.91C21.52 17.09 21.7 17.3 21.81 17.55C21.91 17.8 21.97 18.05 21.97 18.33Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10"></path><path d="M18.5 9C18.5 8.4 18.03 7.48 17.33 6.73C16.69 6.04 15.84 5.5 15 5.5" opacity="0.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M22 9C22 5.13 18.87 2 15 2" opacity="0.4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                        </span>
                                        <div class="flex flex-col text-sm text-[#212b36]">
                                            <span class="font-semibold">Customer Support / Hotline</span>
                                            <span class="flex flex-wrap items-center gap-x-1.5">
                                                <a class="hover:text-gray-900" href="tel:{{ $storePhoneDigits }}">{{ $storePhone }}</a>
                                            </span>
                                        </div>
                                    </div>
                                @endif

                                @if($storeAddress)
                                    <div class="flex items-start gap-[14px]">
                                        <span class="mt-0.5 shrink-0 text-gray-700">
                                            <svg viewBox="0 0 24 24" fill="none" class="h-6 w-6"><path d="M12 13.43C13.7231 13.43 15.12 12.0331 15.12 10.31C15.12 8.58687 13.7231 7.19 12 7.19C10.2769 7.19 8.88 8.58687 8.88 10.31C8.88 12.0331 10.2769 13.43 12 13.43Z" stroke="currentColor" stroke-width="1.5"></path><path d="M3.62 8.49C5.59 -0.17 18.42 -0.16 20.38 8.5C21.53 13.58 18.37 17.88 15.6 20.54C13.59 22.48 10.41 22.48 8.39 20.54C5.63 17.88 2.47 13.57 3.62 8.49Z" stroke="currentColor" stroke-width="1.5"></path></svg>
                                        </span>
                                        <div class="flex flex-col text-sm text-[#212b36]">
                                            <span class="font-semibold">Office / Showroom</span>
                                            <span>{{ $storeAddress }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="flex items-start justify-between gap-8 lg:contents">
                        <div class="min-w-0 lg:order-1">
                            <h3 class="text-lg font-semibold text-[#212b36]">Quick Links</h3>
                            <div class="mt-5 flex flex-col gap-4">
                                <a class="inline-flex items-center gap-1.5 text-sm text-[#212b36] hover:text-gray-900" href="{{ route('home') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-[13px] w-[13px] text-gray-700"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <span>Home</span>
                                </a>
                                <a class="inline-flex items-center gap-1.5 text-sm text-[#212b36] hover:text-gray-900" href="{{ route('shop') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-[13px] w-[13px] text-gray-700"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <span>Shop All</span>
                                </a>
                                <a class="inline-flex items-center gap-1.5 text-sm text-[#212b36] hover:text-gray-900" href="{{ route('categories') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-[13px] w-[13px] text-gray-700"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <span>Categories</span>
                                </a>
                            </div>
                        </div>
                        <div class="min-w-0 lg:order-2">
                            <h3 class="text-lg font-semibold text-[#212b36]">Support</h3>
                            <div class="mt-5 flex flex-col gap-4">
                                <a class="inline-flex items-center gap-1.5 text-sm text-[#212b36] hover:text-gray-900" href="{{ route('cart') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-[13px] w-[13px] text-gray-700"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <span>My Cart</span>
                                </a>
                                <a class="inline-flex items-center gap-1.5 text-sm text-[#212b36] hover:text-gray-900" href="{{ route('checkout') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-[13px] w-[13px] text-gray-700"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <span>Shipping &amp; Returns</span>
                                </a>
                                <a class="inline-flex items-center gap-1.5 text-sm text-[#212b36] hover:text-gray-900" href="{{ route('admin.login') }}">
                                    <svg viewBox="0 0 24 24" fill="none" class="h-[13px] w-[13px] text-gray-700"><path d="M9 6L15 12L9 18" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                                    <span>Admin</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pointer-events-none absolute inset-x-0 bottom-[var(--mobile-bottom-nav-height,56px)] lg:bottom-0">
            <svg viewBox="0 0 1740 82.8842" preserveAspectRatio="none" fill="none" class="block h-[43px] w-full lg:h-[83px]">
                <path fill="#0f0f0f" fill-opacity="0.08" d="M0 16.8842V82.8842H174V16.8842C160 17.3842 150 18.032 133 10.8604C97.5 -4.11578 75.5 -3.11583 41 10.8603C25.4071 17.1771 7 16.7175 0 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M174 16.8842V82.8842H348V16.8842C334 17.3842 324 18.032 307 10.8604C271.5 -4.11578 249.5 -3.11583 215 10.8603C199.4071 17.1771 181 16.7175 174 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M348 16.8842V82.8842H522V16.8842C508 17.3842 498 18.032 481 10.8604C445.5 -4.11578 423.5 -3.11583 389 10.8603C373.4071 17.1771 355 16.7175 348 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M522 16.8842V82.8842H696V16.8842C682 17.3842 672 18.032 655 10.8604C619.5 -4.11578 597.5 -3.11583 563 10.8603C547.4071 17.1771 529 16.7175 522 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M696 16.8842V82.8842H870V16.8842C856 17.3842 846 18.032 829 10.8604C793.5 -4.11578 771.5 -3.11583 737 10.8603C721.4071 17.1771 703 16.7175 696 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M870 16.8842V82.8842H1044V16.8842C1030 17.3842 1020 18.032 1003 10.8604C967.5 -4.11578 945.5 -3.11583 911 10.8603C895.4071 17.1771 877 16.7175 870 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M1044 16.8842V82.8842H1218V16.8842C1204 17.3842 1194 18.032 1177 10.8604C1141.5 -4.11578 1119.5 -3.11583 1085 10.8603C1069.4071 17.1771 1051 16.7175 1044 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M1218 16.8842V82.8842H1392V16.8842C1378 17.3842 1368 18.032 1351 10.8604C1315.5 -4.11578 1293.5 -3.11583 1259 10.8603C1243.4071 17.1771 1225 16.7175 1218 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M1392 16.8842V82.8842H1566V16.8842C1552 17.3842 1542 18.032 1525 10.8604C1489.5 -4.11578 1467.5 -3.11583 1433 10.8603C1417.4071 17.1771 1399 16.7175 1392 16.8842Z"></path>
                <path fill="#0f0f0f" fill-opacity="0.08" d="M1566 16.8842V82.8842H1740V16.8842C1726 17.3842 1716 18.032 1699 10.8604C1663.5 -4.11578 1641.5 -3.11583 1607 10.8603C1591.4071 17.1771 1573 16.7175 1566 16.8842Z"></path>
            </svg>
        </div>
        <p class="absolute inset-x-0 bottom-[calc(7px+var(--mobile-bottom-nav-height,56px))] px-4 text-center text-[11px] text-[#212b36] lg:bottom-[21px]">
            Copyright &copy; {{ site_name() }} {{ date('Y') }} &ndash; All Rights Reserved
        </p>
    </footer>

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

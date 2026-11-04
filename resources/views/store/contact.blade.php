@extends('layouts.store')

@section('title', 'Contact Us - ' . site_name())

@php
    $cEmail = contact_email();
    $cAddress = office_address();
    $cFactoryAddress = factory_address();
    $cPhone = contact_phone();
    $cPhoneDigits = contact_phone_digits();
    $cWaDigits = whatsapp_bottom_number_digits();
    $cWaBase = $cWaDigits !== '' ? 'https://wa.me/' . $cWaDigits : '';
    $cSocials = array_filter([
        ['url' => App\Models\Setting::getValue('facebook_url', ''), 'label' => 'Facebook', 'path' => 'M13.58 22V12.88H16.64L17.1 9.32H13.58V7.04C13.58 6.01 13.87 5.3 15.34 5.3H17.22V2.12C16.89 2.08 15.76 2 14.45 2C11.72 2 9.86 3.67 9.86 6.73V9.32H6.8V12.88H9.86V22H13.58Z'],
        ['url' => App\Models\Setting::getValue('instagram_url', ''), 'label' => 'Instagram', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
        ['url' => App\Models\Setting::getValue('youtube_url', ''), 'label' => 'YouTube', 'path' => 'M21.58 7.19C21.35 6.33 20.67 5.66 19.81 5.42C18.25 5 12 5 12 5C12 5 5.75 5 4.19 5.42C3.33 5.66 2.65 6.33 2.42 7.19C2 8.75 2 12 2 12C2 12 2 15.25 2.42 16.81C2.65 17.67 3.33 18.34 4.19 18.58C5.75 19 12 19 12 19C12 19 18.25 19 19.81 18.58C20.67 18.34 21.35 17.67 21.58 16.81C22 15.25 22 12 22 12C22 12 22 8.75 21.58 7.19ZM10 15.01V8.99L15.2 12L10 15.01Z'],
        ['url' => App\Models\Setting::getValue('twitter_url', ''), 'label' => 'X (Twitter)', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    ]);

@endphp

@section('content')
<div class="bg-gray-50 min-h-[60vh]">

    <!-- Hero -->
    <div class="bg-gray-950 text-white pt-8 pb-20 md:pt-10 md:pb-24 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(225,29,72,0.18),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <nav aria-label="Breadcrumb" class="text-xs font-semibold text-gray-400">
                <a href="{{ route('home') }}" class="hover:text-white transition-colors">Home</a>
                <span class="mx-1.5">/</span>
                <span class="text-gray-200">Contact Us</span>
            </nav>
            <h1 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight leading-none">
                Contact <span class="text-rose-500">Us</span>
            </h1>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12 md:pb-16 -mt-10 md:-mt-12 relative z-10 space-y-6 lg:space-y-8">

        <!-- Contact channels -->
        @if($cPhone || $cWaBase || $cEmail)
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-5">

                @if($cPhone)
                    <a href="tel:{{ $cPhoneDigits }}" class="group flex items-start gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all hover:border-rose-300">
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Call us / কল করুন</span>
                            <span class="block text-lg font-extrabold text-gray-900 mt-0.5 break-words">{{ $cPhone }}</span>
                        </span>
                    </a>
                @endif

                @if($cWaBase)
                    <a href="{{ $cWaBase }}" target="_blank" rel="noopener noreferrer" class="group flex items-start gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all hover:border-emerald-300">
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Zm5.04 11.06c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">WhatsApp</span>
                            <span class="block text-lg font-extrabold text-gray-900 mt-0.5 break-words">{{ whatsapp_bottom_number() }}</span>
                        </span>
                    </a>
                @endif

                @if($cEmail)
                    <a href="mailto:{{ $cEmail }}" class="group flex items-start gap-4 bg-white p-5 rounded-2xl border border-gray-200 shadow-sm hover:shadow-md hover:-translate-y-0.5 transition-all hover:border-purple-300">
                        <span class="w-11 h-11 shrink-0 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Email / ইমেইল</span>
                            <span class="block text-base font-extrabold text-gray-900 mt-0.5 break-all leading-snug">{{ $cEmail }}</span>
                        </span>
                    </a>
                @endif
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

            <!-- Message form -->
            <div class="lg:col-span-7 space-y-4">

                @if(session('success'))
                    <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl font-semibold text-sm">{{ session('success') }}</div>
                @endif

                <div class="bg-white p-5 sm:p-7 md:p-8 rounded-2xl border border-gray-200 shadow-sm">
                    <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 tracking-tight">Send us a message</h2>
                    <p class="text-sm text-gray-500 font-medium mt-1">Fill out the form and we will get back to you shortly.</p>

                    <form action="{{ route('contact.submit') }}" method="POST" class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5">
                        @csrf
                        <div>
                            <label for="contact_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Name</label>
                            <input type="text" id="contact_name" name="name" value="{{ old('name') }}" required autocomplete="name" class="w-full bg-white border-2 border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-900 placeholder:text-gray-400 outline-none transition-colors hover:border-gray-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                            @error('name')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="contact_email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email</label>
                            <input type="email" id="contact_email" name="email" value="{{ old('email') }}" autocomplete="email" class="w-full bg-white border-2 border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-900 placeholder:text-gray-400 outline-none transition-colors hover:border-gray-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                            @error('email')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="contact_phone" class="block text-sm font-semibold text-gray-700 mb-1.5">Phone</label>
                            <input type="tel" id="contact_phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" class="w-full bg-white border-2 border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-900 placeholder:text-gray-400 outline-none transition-colors hover:border-gray-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                            @error('phone')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label for="contact_subject" class="block text-sm font-semibold text-gray-700 mb-1.5">Subject</label>
                            <input type="text" id="contact_subject" name="subject" value="{{ old('subject') }}" class="w-full bg-white border-2 border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium text-gray-900 placeholder:text-gray-400 outline-none transition-colors hover:border-gray-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-100">
                            @error('subject')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label for="contact_message" class="block text-sm font-semibold text-gray-700 mb-1.5">Message</label>
                            <textarea id="contact_message" name="message" rows="5" required class="w-full bg-white border-2 border-gray-300 rounded-lg px-4 py-3 text-sm font-medium text-gray-900 placeholder:text-gray-400 outline-none transition-colors hover:border-gray-400 focus:border-rose-500 focus:ring-2 focus:ring-rose-100">{{ old('message') }}</textarea>
                            @error('message')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full sm:w-auto px-8 py-3 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-sm shadow-sm transition-colors active:scale-[0.98]">Send Message</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Side info -->
            <aside class="lg:col-span-5 space-y-4 lg:space-y-6">

                @if($cAddress || $cFactoryAddress)
                    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <h3 class="text-lg font-extrabold text-gray-900 tracking-tight">Visit us</h3>
                        <ul class="mt-4 divide-y divide-gray-100">
                            @if($cAddress)
                                <li class="flex items-start gap-4 pb-4">
                                    <span class="w-11 h-11 shrink-0 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Office / অফিস</span>
                                        <span class="block text-sm font-semibold text-gray-900 mt-0.5 leading-relaxed">{{ $cAddress }}</span>
                                    </span>
                                </li>
                            @endif
                            @if($cFactoryAddress)
                                <li class="flex items-start gap-4 {{ $cAddress ? 'pt-4' : '' }}">
                                    <span class="w-11 h-11 shrink-0 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M2 20V8l6 4V8l6 4V4h4a2 2 0 0 1 2 2v14z"/><path d="M6 16h.01M10 16h.01M14 16h.01M18 16h.01"/></svg></span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-bold uppercase tracking-wider text-gray-400">Factory / ফ্যাক্টরি</span>
                                        <span class="block text-sm font-semibold text-gray-900 mt-0.5 leading-relaxed">{{ $cFactoryAddress }}</span>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </div>
                @endif

                @if($cSocials)
                    <div class="bg-white p-5 sm:p-6 rounded-2xl border border-gray-200 shadow-sm">
                        <h3 class="text-lg font-extrabold text-gray-900 tracking-tight">Follow {{ site_name() }}</h3>
                        <p class="text-sm text-gray-500 font-medium mt-1">New arrivals, restocks and offers are posted here first.</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            @foreach($cSocials as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-2.5 px-4 py-3 rounded-lg bg-gray-50 border border-gray-200 hover:bg-gray-900 hover:border-gray-900 hover:text-white text-gray-700 font-bold text-sm transition-colors">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 shrink-0"><path d="{{ $social['path'] }}"></path></svg>
                                    <span class="truncate">{{ $social['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</div>
@endsection

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

    $faqs = [
        [
            'q' => 'ডেলিভারিতে কত সময় লাগে?',
            'a' => 'ঢাকার ভেতরে সাধারণত ১–২ কর্মদিবসের মধ্যেই পণ্য পৌঁছে যায়। ঢাকার বাইরে ২–৪ কর্মদিবস লাগে। পার্সেল পাঠানোর আগে আমরা আপনাকে ফোন করে নিশ্চিত করে নিই।',
        ],
        [
            'q' => 'অর্ডারটি কোথায় আছে, কীভাবে জানব?',
            'a' => '“অর্ডার ট্র্যাকিং” পাতায় গিয়ে অর্ডার নম্বর ও ফোন নম্বর দিলেই বর্তমান অবস্থা দেখতে পাবেন। অথবা আপনার অ্যাকাউন্টের “আমার অর্ডার” অংশেও সব অর্ডার দেখা যায়।',
        ],
        [
            'q' => 'কোন কোন উপায়ে পেমেন্ট করা যায়?',
            'a' => 'সারা দেশেই ক্যাশ অন ডেলিভারি সুবিধা আছে। চাইলে অগ্রিম পেমেন্টে bKash-ও ব্যবহার করতে পারেন — বিস্তারিত চেকআউট পাতায় পাবেন।',
        ],
        [
            'q' => 'রিটার্ন বা সাইজ এক্সচেঞ্জের নিয়ম কী?',
            'a' => 'ডেলিভারির 1 দিনের মধ্যে অব্যবহৃত ও লেবেলসহ থাকা পণ্য এক্সচেঞ্জ করা যায়। সঠিক সাইজ না পেলে আমাদের জানান — পরবর্তী অর্ডারে বিনামূল্যে সাইজ এক্সচেঞ্জের ব্যবস্থা করে দেব।',
        ],
        [
            'q' => 'কোন সাইজ নেব, কীভাবে বুঝব?',
            'a' => 'প্রতিটি প্রোডাক্টের পাতায় সাইজ চার্ট দেওয়া আছে, যেখানে ইঞ্চি ও সেন্টিমিটারে মাপ লেখা আছে। দুই সাইজের মাঝামাঝি হলে বড় সাইজটি নেওয়াই ভালো।',
        ],
    ];
@endphp

@section('content')
<div class="bg-gray-50">

    <!-- Hero -->
    <div class="bg-gray-950 text-white pt-8 pb-14 md:pb-20 px-4 sm:px-6 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top,rgba(225,29,72,0.18),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto relative z-10">
            <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-none">
                Contact <span class="text-rose-500">Us</span>
            </h1>
            <p class="text-gray-400 font-semibold mt-3 text-sm md:text-base max-w-2xl">
                Pick the channel you prefer — call, WhatsApp or email. We usually reply within a few hours. / আপনার যেকোনো প্রশ্নে আমরা আছি।
            </p>

            @if($cPhone || $cWaBase || $cEmail)
                <div class="flex flex-col sm:flex-row flex-wrap gap-3 mt-6">
                    @if($cPhone)
                        <a href="tel:{{ $cPhoneDigits }}"
                           class="inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-2xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-sm shadow-[0_18px_36px_-16px_rgba(225,29,72,0.7)] transition-all hover:-translate-y-0.5 active:scale-[0.98]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                            Call {{ $cPhone }}
                        </a>
                    @endif
                    @if($cWaBase)
                        <a href="{{ $cWaBase }}" target="_blank" rel="noopener noreferrer"
                           class="inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-sm shadow-[0_18px_36px_-16px_rgba(16,185,129,0.7)] transition-all hover:-translate-y-0.5 active:scale-[0.98]">
                            <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Zm5.04 11.06c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z"/></svg>
                            Chat on WhatsApp
                        </a>
                    @endif
                    @if($cEmail)
                        <a href="mailto:{{ $cEmail }}"
                           class="inline-flex items-center justify-center gap-2.5 px-5 py-3 rounded-2xl border-2 border-white/15 hover:bg-white/10 text-white font-extrabold text-sm transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                            {{ $cEmail }}
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 md:py-14 space-y-6 lg:space-y-8">

        <!-- Contact channels -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5">

            @if($cPhone)
                <a href="tel:{{ $cPhoneDigits }}" class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_18px_40px_-28px_rgba(0,0,0,0.35)] hover:border-rose-300 hover:-translate-y-0.5 transition-all">
                    <span class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:bg-rose-700 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-4">Call us / কল করুন</h3>
                    <p class="text-xl font-extrabold text-gray-900 mt-1 break-words">{{ $cPhone }}</p>
                </a>
            @endif

            @if($cWaBase)
                <a href="{{ $cWaBase }}" target="_blank" rel="noopener noreferrer" class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_18px_40px_-28px_rgba(0,0,0,0.35)] hover:border-emerald-300 hover:-translate-y-0.5 transition-all">
                    <span class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-colors">
                        <svg viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Zm5.04 11.06c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z"/></svg>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-4">WhatsApp</h3>
                    <p class="text-xl font-extrabold text-gray-900 mt-1 break-words">{{ whatsapp_bottom_number() }}</p>
                </a>
            @endif

            @if($cEmail)
                <a href="mailto:{{ $cEmail }}" class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_18px_40px_-28px_rgba(0,0,0,0.35)] hover:border-purple-300 hover:-translate-y-0.5 transition-all">
                    <span class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-4">Email / ইমেইল</h3>
                    <p class="text-base font-extrabold text-gray-900 mt-1 break-all leading-snug">{{ $cEmail }}</p>
                </a>
            @endif

            @if($cAddress)
                <div class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_18px_40px_-28px_rgba(0,0,0,0.35)]">
                    <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-4">Office / অফিস</h3>
                    <p class="text-base font-bold text-gray-900 mt-1 leading-snug">{{ $cAddress }}</p>
                </div>
            @endif
            @if($cFactoryAddress)
                <div class="group bg-white p-5 rounded-2xl border border-gray-200 shadow-[0_18px_40px_-28px_rgba(0,0,0,0.35)]">
                    <span class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </span>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400 mt-4">Factory / ফ্যাক্টরি</h3>
                    <p class="text-base font-bold text-gray-900 mt-1 leading-snug">{{ $cFactoryAddress }}</p>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            <div class="lg:col-span-7 space-y-6">

            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-2xl font-semibold mb-6">{{ session('success') }}</div>
            @endif

            <div class="bg-white p-5 sm:p-7 md:p-8 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)] mb-6">
                <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 tracking-tight">Send us a message</h2>
                <p class="text-sm md:text-base text-gray-500 font-semibold mt-1">Fill out the form and we will get back to you shortly.</p>
                <form action="{{ route('contact.submit') }}" method="POST" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('name')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('email')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('phone')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Subject</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">
                        @error('subject')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">Message</label>
                        <textarea name="message" rows="5" required class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-rose-500">{{ old('message') }}</textarea>
                        @error('message')<span class="text-red-500 text-xs mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit" class="px-6 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-sm transition-colors">Send Message</button>
                    </div>
                </form>
            </div>

            </div>

            <!-- Side info -->            <!-- Side info -->
            <aside class="lg:col-span-5 space-y-6">

                @if($cSocials)
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)]">
                        <h3 class="text-lg font-extrabold text-gray-900 tracking-tight border-b-2 border-gray-100 pb-3">Follow {{ site_name() }}</h3>
                        <p class="text-sm text-gray-500 font-semibold mt-3">New arrivals, restocks and offers are posted here first.</p>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            @foreach($cSocials as $social)
                                <a href="{{ $social['url'] }}" target="_blank" rel="noopener noreferrer"
                                   class="flex items-center gap-2.5 px-4 py-3 rounded-xl bg-gray-50 hover:bg-gray-900 hover:text-white text-gray-700 font-bold text-sm transition-colors">
                                    <svg viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 shrink-0"><path d="{{ $social['path'] }}"></path></svg>
                                    <span class="truncate">{{ $social['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="bg-gray-950 text-white p-6 rounded-2xl shadow-[0_30px_60px_-20px_rgba(0,0,0,0.3)]">
                    <h3 class="text-lg font-extrabold tracking-tight">Order related help?</h3>
                    <p class="text-sm text-gray-400 font-semibold mt-2 leading-relaxed">
                        Have your order number ready and our team can check the delivery status for you right away.
                    </p>
                    <div class="flex flex-col gap-2.5 mt-4">
                        <a href="{{ route('order.tracking') }}" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-center text-sm font-bold tracking-wide transition">Track my order</a>
                        <a href="{{ route('customer.dashboard') }}" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-center text-sm font-bold tracking-wide transition">My account</a>
                        <a href="{{ route('shop') }}" class="px-4 py-3 rounded-xl text-center text-sm font-bold text-gray-400 hover:text-white transition">Continue shopping →</a>
                    </div>
                </div>
            </aside>
        </div>

        <!-- FAQ -->
                <div class="bg-white p-5 sm:p-7 md:p-8 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)]">
                    <div class="flex items-start gap-4">
                        <span class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                        </span>
                        <div>
                            <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 tracking-tight">সাধারণ জিজ্ঞাসা</h2>
                            <p class="text-sm md:text-base text-gray-500 font-semibold mt-1">
                                আমাদের সবচেয়ে বেশি জিজ্ঞাসিত প্রশ্নগুলো — যেকোনো প্রশ্নের উত্তর দেখতে ট্যাপ করুন।
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 space-y-2.5">
                        @foreach($faqs as $faq)
                            <details class="group/faq bg-gray-50 hover:bg-gray-100/80 border border-gray-200 rounded-2xl overflow-hidden transition-colors open:bg-white open:border-rose-200 open:shadow-[0_18px_40px_-30px_rgba(0,0,0,0.4)]">
                                <summary class="flex items-center gap-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden px-4 sm:px-5 py-4">
                                    <span class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-rose-600 flex items-center justify-center font-extrabold text-xs shrink-0 group-open/faq:bg-rose-600 group-open/faq:text-white group-open/faq:border-rose-600 transition-colors">
                                        ?
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="block font-extrabold text-gray-900 text-sm md:text-base leading-snug">{{ $faq['q'] }}</span>
                                    </span>
                                    <svg class="w-5 h-5 text-gray-400 shrink-0 group-open/faq:rotate-180 group-open/faq:text-rose-600 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg>
                                </summary>
                                <div class="px-4 sm:px-5 pb-4 pl-[4.25rem] sm:pl-[4.5rem]">
                                    <p class="text-sm md:text-[0.95rem] text-gray-600 font-semibold leading-relaxed">{{ $faq['a'] }}</p>
                                </div>
                            </details>
                        @endforeach
                    </div>

                    <div class="mt-6 pt-5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center gap-3">
                        <p class="text-sm font-bold text-gray-500 sm:mr-auto">আপনার প্রশ্নের উত্তর এখানে নেই?</p>
                        @if($cWaBase)
                            <a href="{{ $cWaBase }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-extrabold text-sm transition-colors">
                                হোয়াটসঅ্যাপে জিজ্ঞাসা করুন
                            </a>
                        @endif
                        <a href="{{ route('order.tracking') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border-2 border-gray-200 hover:bg-gray-50 text-gray-700 font-extrabold text-sm transition-colors">
                            অর্ডার ট্র্যাক করুন
                        </a>
                    </div>
                </div>
    </div>
</div>
@endsection
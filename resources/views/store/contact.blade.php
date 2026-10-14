@extends('layouts.store')

@section('title', 'Contact Us - ' . site_name())

@php
    $cEmail = contact_email();
    $cAddress = office_address();
    $cPhone = contact_phone();
    $cPhoneDigits = contact_phone_digits();
    $cWaDigits = whatsapp_number_digits();
    $cWaBase = $cWaDigits !== '' ? 'https://wa.me/' . $cWaDigits : '';
    $cSocials = array_filter([
        ['url' => App\Models\Setting::getValue('facebook_url', ''), 'label' => 'Facebook', 'path' => 'M13.58 22V12.88H16.64L17.1 9.32H13.58V7.04C13.58 6.01 13.87 5.3 15.34 5.3H17.22V2.12C16.89 2.08 15.76 2 14.45 2C11.72 2 9.86 3.67 9.86 6.73V9.32H6.8V12.88H9.86V22H13.58Z'],
        ['url' => App\Models\Setting::getValue('instagram_url', ''), 'label' => 'Instagram', 'path' => 'M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z'],
        ['url' => App\Models\Setting::getValue('youtube_url', ''), 'label' => 'YouTube', 'path' => 'M21.58 7.19C21.35 6.33 20.67 5.66 19.81 5.42C18.25 5 12 5 12 5C12 5 5.75 5 4.19 5.42C3.33 5.66 2.65 6.33 2.42 7.19C2 8.75 2 12 2 12C2 12 2 15.25 2.42 16.81C2.65 17.67 3.33 18.34 4.19 18.58C5.75 19 12 19 12 19C12 19 18.25 19 19.81 18.58C20.67 18.34 21.35 17.67 21.58 16.81C22 15.25 22 12 22 12C22 12 22 8.75 21.58 7.19ZM10 15.01V8.99L15.2 12L10 15.01Z'],
        ['url' => App\Models\Setting::getValue('twitter_url', ''), 'label' => 'X (Twitter)', 'path' => 'M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z'],
    ]);
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
                Questions about an order, a product or delivery? Reach us on the channel you prefer — we usually reply within a few hours. / আপনার যেকোনো প্রশ্নে আমরা আছি।
            </p>
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
                    <p class="text-xl font-extrabold text-gray-900 mt-1 break-words">{{ whatsapp_number() }}</p>
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
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8">

            <!-- Message composer -->
            <div class="lg:col-span-7">
                <div class="bg-white p-5 sm:p-7 md:p-8 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)]">
                    <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 tracking-tight">Send us a message</h2>
                    <p class="text-sm md:text-base text-gray-500 font-semibold mt-1">
                        Write your message below and we will open WhatsApp with it ready to send.
                    </p>

                    <form id="contact-form" class="mt-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="contact-name" class="block text-sm font-bold text-gray-700 mb-1.5">Your name</label>
                                <input type="text" id="contact-name" autocomplete="name" placeholder="e.g. Rahim Uddin"
                                       class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-base font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 placeholder:font-semibold hover:border-gray-300 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                            </div>
                            <div>
                                <label for="contact-phone" class="block text-sm font-bold text-gray-700 mb-1.5">Phone number</label>
                                <input type="tel" id="contact-phone" inputmode="tel" autocomplete="tel" placeholder="e.g. 01700000000"
                                       class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-base font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 placeholder:font-semibold hover:border-gray-300 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-100">
                            </div>
                        </div>

                        <div>
                            <label for="contact-message" class="block text-sm font-bold text-gray-700 mb-1.5">Your message</label>
                            <textarea id="contact-message" rows="5" maxlength="1000" placeholder="Tell us what you need help with..."
                                      class="w-full bg-gray-50 border-2 border-gray-200 rounded-xl px-4 py-3 text-base font-semibold text-gray-900 outline-none transition-all placeholder:text-gray-400 placeholder:font-semibold hover:border-gray-300 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-100 resize-none"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-3 pt-1">
                            <button type="submit" id="contact-submit"
                                    class="inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-extrabold text-base shadow-[0_20px_40px_-15px_rgba(16,185,129,0.5)] transition-all hover:-translate-y-0.5 active:scale-[0.98]">
                                <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5"><path d="M12.02 3.25a8.73 8.73 0 0 0-7.45 13.28l.19.3-.98 3.59 3.68-.96.29.17a8.73 8.73 0 1 0 4.27-16.38Zm5.04 11.06c-.28-.14-1.64-.81-1.89-.9-.25-.09-.44-.14-.62.14-.18.27-.71.9-.87 1.08-.16.18-.32.2-.6.07-.28-.14-1.16-.43-2.21-1.36-.82-.73-1.37-1.63-1.53-1.91-.16-.27-.02-.42.12-.56.13-.13.28-.32.42-.48.14-.16.18-.27.27-.46.09-.18.05-.34-.02-.48-.07-.14-.62-1.49-.85-2.04-.22-.53-.45-.46-.62-.47h-.53c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.98 2.65 1.12 2.83.14.18 1.93 2.94 4.67 4.12.65.28 1.16.45 1.56.58.66.21 1.25.18 1.72.11.53-.08 1.64-.67 1.87-1.31.23-.64.23-1.2.16-1.31-.07-.12-.25-.19-.53-.33Z"/></svg>
                                Send on WhatsApp
                            </button>
                            @if($cEmail)
                                <button type="button" id="contact-email-btn"
                                        class="inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl border-2 border-gray-200 hover:border-gray-300 hover:bg-gray-50 text-gray-700 font-extrabold text-base transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg>
                                    Send as Email
                                </button>
                            @endif
                        </div>

                        <p id="contact-hint" class="text-sm font-bold text-gray-400 hidden">
                            Please write a message first — or just call us on {{ $cPhone ?: 'the number above' }}.
                        </p>
                    </form>
                </div>
            </div>

            <!-- Side info -->
            <aside class="lg:col-span-5 space-y-6">

                @if($cSocials)
                    <div class="bg-white p-6 rounded-2xl border border-gray-200 shadow-[0_30px_60px_-20px_rgba(0,0,0,0.15)]">
                        <h3 class="text-lg font-extrabold text-gray-900 tracking-tight border-b-2 border-gray-100 pb-3">Follow {{ site_name() }}</h3>
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
                        <a href="{{ route('customer.dashboard') }}" class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/20 border border-white/15 text-center text-sm font-bold tracking-wide transition">Track my order</a>
                        <a href="{{ route('shop') }}" class="px-4 py-3 rounded-xl text-center text-sm font-bold text-gray-400 hover:text-white transition">Continue shopping →</a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    (function () {
        var siteName = @json(site_name());
        var waBase = @json($cWaBase);
        var email = @json($cEmail);
        var contactUrl = @json(route('contact.page'));

        var form = document.getElementById('contact-form');
        if (!form) return;

        var nameInput = document.getElementById('contact-name');
        var phoneInput = document.getElementById('contact-phone');
        var messageInput = document.getElementById('contact-message');
        var hint = document.getElementById('contact-hint');
        var emailBtn = document.getElementById('contact-email-btn');

        function showHint() {
            hint.classList.remove('hidden');
        }

        function buildMessage() {
            var lines = ['Hello ' + siteName + '!'];

            if (nameInput.value.trim()) {
                lines.push('My name is ' + nameInput.value.trim() + '.');
            }
            if (phoneInput.value.trim()) {
                lines.push('My phone number: ' + phoneInput.value.trim() + '.');
            }
            if (messageInput.value.trim()) {
                lines.push('', messageInput.value.trim());
            }

            return lines.join('\n');
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!messageInput.value.trim()) {
                showHint();
                messageInput.focus();
                return;
            }

            if (waBase) {
                window.open(waBase + '?text=' + encodeURIComponent(buildMessage()), '_blank', 'noopener');
            } else {
                window.location.href = 'mailto:' + email + '?subject=' + encodeURIComponent('Contact from ' + siteName) + '&body=' + encodeURIComponent(buildMessage());
            }
        });

        if (emailBtn) {
            emailBtn.addEventListener('click', function () {
                if (!messageInput.value.trim()) {
                    showHint();
                    messageInput.focus();
                    return;
                }

                window.location.href = 'mailto:' + email + '?subject=' + encodeURIComponent('Contact from ' + siteName) + '&body=' + encodeURIComponent(buildMessage());
            });
        }

        [nameInput, phoneInput, messageInput].forEach(function (el) {
            el.addEventListener('input', function () {
                hint.classList.add('hidden');
            });
        });
    })();
</script>
@endsection
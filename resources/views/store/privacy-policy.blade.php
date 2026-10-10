@extends('layouts.store')

@section('title', 'Privacy Policy - ' . site_name())

@php
    $ppEmail = App\Models\Setting::getValue('contact_email', 'laamtexoffice@gmail.com');
    $ppAddress = App\Models\Setting::getValue('office_address', 'House 12, Road 5, Dhanmondi, Dhaka 1205, Bangladesh');
    $ppPhone = contact_phone();
@endphp

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10">

    <div class="mb-6 md:mb-8">
        <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900">Privacy Policy <span class="text-gray-400 font-bold">/</span> গোপনীয়তা নীতি</h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Last updated / সর্বশেষ আপডেট: {{ date('F j, Y') }}</p>
    </div>

    <!-- ==================== English ==================== -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-7 shadow-[0_4px_20px_rgba(15,23,42,0.05)] mb-6">
        <div class="mb-5 inline-flex items-center gap-2 rounded-lg bg-gray-900 px-3 py-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-white">English</span>
        </div>

        <div class="space-y-5 text-sm leading-relaxed text-gray-600">
            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">1. Introduction</h2>
                <p>{{ site_name() }} (“we”, “our”, “us”) respects your privacy and is committed to protecting your personal information. This Privacy Policy explains how we collect, use, store and share your information when you visit our website or place an order with us.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">2. Information We Collect</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Personal information:</strong> name, delivery address, phone number, email address and billing details that you provide when placing an order or creating an account.</li>
                    <li><strong>Order information:</strong> products ordered, price, payment method, delivery status and order history.</li>
                    <li><strong>Technical information:</strong> IP address, browser type, device information and pages visited, collected automatically through cookies and similar technologies.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">3. How We Use Your Information</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li>To process and deliver your orders.</li>
                    <li>To communicate with you about order status, delivery and support.</li>
                    <li>To process payments and prevent fraudulent transactions.</li>
                    <li>To improve our website, products and customer experience.</li>
                    <li>To send promotional offers or newsletters, only if you have opted in. You may unsubscribe at any time.</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">4. Payment Information</h2>
                <p>We do not store your card or mobile financial account credentials on our servers. Payments are processed by trusted third-party payment providers (such as bKash, Nagad, Rocket or card gateways) under their own security and privacy standards.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">5. Cookies</h2>
                <p>We use cookies to keep you signed in, remember your cart and understand how you use our site. You can disable cookies in your browser settings, though some features may not work properly afterwards.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">6. Information Sharing</h2>
                <p>We only share your information with:</p>
                <ul class="list-disc pl-5 space-y-1 mt-1">
                    <li>Courier and delivery partners, to deliver your order.</li>
                    <li>Payment providers, to complete your transaction.</li>
                    <li>Authorities, when required by law or to protect our legal rights.</li>
                </ul>
                <p class="mt-2">We never sell or rent your personal information to third parties.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">7. Data Security &amp; Retention</h2>
                <p>We use reasonable technical and organisational measures to protect your data. We keep your information only as long as necessary for order history, customer support and legal obligations, then delete or anonymise it.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">8. Your Rights</h2>
                <p>You may request access to, correction of, or deletion of your personal information at any time by contacting us. You may also opt out of marketing messages whenever you wish.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">9. Children’s Privacy</h2>
                <p>Our website is not intended for children under 13, and we do not knowingly collect information from them.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">10. Changes to This Policy</h2>
                <p>We may update this Privacy Policy from time to time. Any changes will be posted on this page with an updated “Last updated” date.</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">11. Contact Us</h2>
                <p>For any questions about this Privacy Policy, contact us at:</p>
                <ul class="list-none space-y-1 mt-2">
                    <li><strong>Email:</strong> <a href="mailto:{{ $ppEmail }}" class="text-brand-600 hover:underline">{{ $ppEmail }}</a></li>
                    <li><strong>Phone:</strong> {{ $ppPhone }}</li>
                    <li><strong>WhatsApp:</strong> {{ whatsapp_number() }}</li>
                    <li><strong>Address:</strong> {{ $ppAddress }}</li>
                </ul>
            </section>
        </div>
    </div>

    <!-- ==================== বাংলা ==================== -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-7 shadow-[0_4px_20px_rgba(15,23,42,0.05)]">
        <div class="mb-5 inline-flex items-center gap-2 rounded-lg bg-brand-600 px-3 py-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-white">বাংলা</span>
        </div>

        <div class="space-y-5 text-sm leading-relaxed text-gray-600">
            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">১. ভূমিকা</h2>
                <p>{{ site_name() }} (“আমরা”) আপনার গোপনীয়তাকে সম্মান করি এবং আপনার ব্যক্তিগত তথ্য সুরক্ষিত রাখায় প্রতিশ্রুতিবদ্ধ। আপনি যখন আমাদের ওয়েবসাইট দেখেন বা অর্ডার করেন, তখন আপনার তথ্য কীভাবে সংগ্রহ, ব্যবহার, সংরক্ষণ ও শেয়ার করা হয় — তা এই গোপনীয়তা নীতিতে ব্যাখ্যা করা হয়েছে।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">২. আমরা কী তথ্য সংগ্রহ করি</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>ব্যক্তিগত তথ্য:</strong> নাম, ডেলিভারি ঠিকানা, ফোন নম্বর, ইমেইল এবং অর্ডার বা অ্যাকাউন্ট তৈরির সময় প্রদত্ত বিলিং তথ্য।</li>
                    <li><strong>অর্ডারের তথ্য:</strong> কেনা পণ্য, মূল্য, পেমেন্ট পদ্ধতি, ডেলিভারি অবস্থা এবং অর্ডার ইতিহাস।</li>
                    <li><strong>কারিগরি তথ্য:</strong> IP ঠিকানা, ব্রাউজারের ধরন, ডিভাইসের তথ্য ও দেখা পাতা — এগুলো কুকি ও অনুরূপ প্রযুক্তির মাধ্যমে স্বয়ংক্রিয়ভাবে সংগ্রহ হয়।</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৩. তথ্যের ব্যবহার</h2>
                <ul class="list-disc pl-5 space-y-1">
                    <li>আপনার অর্ডার প্রসেস ও ডেলিভারি করতে।</li>
                    <li>অর্ডারের অবস্থা, ডেলিভারি ও সাপোর্ট নিয়ে আপনার সাথে যোগাযোগ করতে।</li>
                    <li>পেমেন্ট প্রসেস ও প্রতারণামূলক লেনদেন ঠেকাতে।</li>
                    <li>আমাদের ওয়েবসাইট, পণ্য ও কাস্টমার অভিজ্ঞতা উন্নত করতে।</li>
                    <li>শুধুমাত্র আপনি সম্মতি দিলে প্রোমোশনাল অফার বা নিউজলেটার পাঠাতে — যেকোনো সময় আপনি আনসবস্ক্রাইব করতে পারেন।</li>
                </ul>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৪. পেমেন্ট তথ্য</h2>
                <p>আমাদের সার্ভারে আপনার কার্ড বা মোবাইল ফিন্যান্সিয়াল অ্যাকাউন্টের তথ্য আমরা সংরক্ষণ করি না। পেমেন্ট বিশ্বস্ত তৃতীয় পক্ষের পেমেন্ট প্রোভাইডার (যেমন বিকাশ, নগদ, রকেট বা কার্ড গেটওয়ে) এর নিজস্ব নিরাপত্তা ও গোপনীয়তা মানদণ্ড অনুসারে প্রসেস হয়।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৫. কুকি</h2>
                <p>আপনাকে লগইন রাখতে, কার্ট মনে রাখতে এবং সাইট কীভাবে ব্যবহার করছেন তা বোঝার জন্য আমরা কুকি ব্যবহার করি। ব্রাউজার সেটিংসে আপনি কুকি বন্ধ করতে পারেন, তবে এরপর কিছু ফিচার ঠিকমতো কাজ নাও করতে পারে।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৬. তথ্য শেয়ার</h2>
                <p>আমরা শুধু নিম্নলিখিত ক্ষেত্রে আপনার তথ্য শেয়ার করি:</p>
                <ul class="list-disc pl-5 space-y-1 mt-1">
                    <li>কুরিয়ার ও ডেলিভারি পার্টনার — অর্ডার পৌঁছে দিতে।</li>
                    <li>পেমেন্ট প্রোভাইডার — লেনদেন সম্পন্ন করতে।</li>
                    <li>কর্তৃপক্ষ — আইনানুগ প্রয়োজনে বা আমাদের আইনি অধিকার রক্ষায়।</li>
                </ul>
                <p class="mt-2">আপনার ব্যক্তিগত তথ্য আমরা কোনো তৃতীয় পক্ষকে কখনো বিক্রি বা ভাড়া দিই না।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৭. ডেটা নিরাপত্তা ও সংরক্ষণকাল</h2>
                <p>আপনার ডেটা রক্ষায় আমরা যুক্তিসঙ্গত কারিগরি ও সাংগঠনিক ব্যবস্থা গ্রহণ করি। অর্ডার ইতিহাস, কাস্টমার সাপোর্ট ও আইনগত দায়িত্ব পালনের জরুরি সময় পর্যন্ত তথ্য সংরক্ষণ করি, এরপর তা মুছে ফেলা বা বেনামী করা হয়।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৮. আপনার অধিকার</h2>
                <p>আপনি যেকোনো সময় আমাদের সাথে যোগাযোগ করে আপনার ব্যক্তিগত তথ্যের প্রবেশাধিকার, সংশোধন বা মুছে ফেলার অনুরোধ করতে পারেন। মার্কেটিং বার্তা বন্ধ করার সুযোগও আপনার আছে।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">৯. শিশুদের গোপনীয়তা</h2>
                <p>আমাদের ওয়েবসাইট ১৩ বছরের কম বয়সী শিশুদের জন্য প্রস্তুত নয়, এবং আমরা তাদের কাছ থেকে ইচ্ছাকৃতভাবে কোনো তথ্য সংগ্রহ করি না।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">১০. নীতিতে পরিবর্তন</h2>
                <p>সময়ে সময়ে এই গোপনীয়তা নীতি আপডেট করা হতে পারে। যেকোনো পরিবর্তন এই পাতায় নতুন “সর্বশেষ আপডেট” তারিখসহ প্রকাশিত হবে।</p>
            </section>

            <section>
                <h2 class="text-base font-bold text-gray-900 mb-2">১১. যোগাযোগ</h2>
                <p>এই গোপনীয়তা নীতি নিয়ে কোনো প্রশ্ন থাকলে আমাদের সাথে যোগাযোগ করুন:</p>
                <ul class="list-none space-y-1 mt-2">
                    <li><strong>ইমেইল:</strong> <a href="mailto:{{ $ppEmail }}" class="text-brand-600 hover:underline">{{ $ppEmail }}</a></li>
                    <li><strong>ফোন:</strong> {{ $ppPhone }}</li>
                    <li><strong>হোয়াটসঅ্যাপ:</strong> {{ whatsapp_number() }}</li>
                    <li><strong>ঠিকানা:</strong> {{ $ppAddress }}</li>
                </ul>
            </section>
        </div>
    </div>

</div>
@endsection

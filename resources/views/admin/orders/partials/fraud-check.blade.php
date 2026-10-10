{{--
    Steadfast fraud check. Sits under the customer's phone number in the order
    dropdown, and beside the send button on the single order page.

    The result panel is positioned rather than in flow: the dropdown row is
    already tall, and pushing the tiles into the layout made the whole expanded
    row grow by several hundred pixels.

    The lookup is lazy because the endpoint is rate limited per merchant. The
    delegated handler lives in layouts/admin.blade.php.
--}}
@php
    $fraudCheckId = 'fraud-check-' . $order->id;
@endphp

<div class="js-fraud-check relative"
     data-url="{{ route('admin.orders.fraud-check', $order->id) }}"
     data-phone="{{ $order->customer_phone }}">
    <button type="button"
            class="js-fraud-check-toggle inline-flex items-center gap-1 mt-1 px-2 py-1 bg-cyan-500/10 hover:bg-cyan-500/20 text-cyan-300 border border-cyan-500/30 rounded text-[10px] font-bold uppercase tracking-wider transition"
            aria-expanded="false"
            aria-controls="{{ $fraudCheckId }}">
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Fraud Check
    </button>

    <div id="{{ $fraudCheckId }}"
         class="js-fraud-check-panel hidden absolute left-0 top-full mt-2 z-50 w-72 rounded-xl border border-cyan-500/30 bg-slate-950 p-3 shadow-2xl text-left"
         role="region"
         aria-live="polite">

        <p class="js-fraud-check-loading text-xs text-slate-400">Checking Steadfast…</p>

        <p class="js-fraud-check-error hidden text-xs text-rose-400"></p>

        <div class="js-fraud-check-body hidden">
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500 mb-2">
                Steadfast history for
                {{-- The number exactly as it is stored on the order, so the admin
                     matches what the customer gave, not the API form. --}}
                <span class="js-fraud-check-phone font-mono text-cyan-300">{{ $order->customer_phone }}</span>
            </p>

            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="rounded-lg bg-emerald-500/10 border border-emerald-500/20 px-1 py-1.5">
                    <p class="js-fraud-check-delivered text-base font-black text-emerald-400"></p>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-emerald-300/70">Delivered</p>
                </div>
                <div class="rounded-lg bg-amber-500/10 border border-amber-500/20 px-1 py-1.5">
                    <p class="js-fraud-check-cancelled text-base font-black text-amber-400"></p>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-amber-300/70">Cancelled</p>
                </div>
                <div class="rounded-lg bg-rose-500/10 border border-rose-500/20 px-1 py-1.5">
                    <p class="js-fraud-check-reports text-base font-black text-rose-400"></p>
                    <p class="text-[9px] font-bold uppercase tracking-wider text-rose-300/70">Reports</p>
                </div>
            </div>

            {{-- A counter Steadfast did not send must read as "no record", never
                 as a clean zero, or an empty history looks like a good customer. --}}
            <p class="js-fraud-check-derived mt-2 hidden text-[10px] leading-snug text-slate-500"></p>
            <p class="js-fraud-check-unavailable mt-2 hidden text-[10px] leading-snug text-amber-300/80"></p>
            <p class="js-fraud-check-extra mt-2 hidden text-[10px] leading-snug text-slate-400"></p>
        </div>
    </div>
</div>
@extends('layouts.admin')
@section('title', 'Abandoned Orders - laamtex')
@section('page_title', 'Abandoned Orders')
@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Phone</th>
                        <th class="px-6 py-4">Address</th>
                        <th class="px-6 py-4">Items</th>
                        <th class="px-6 py-4 text-right">Subtotal</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($orders as $order)
                        <tr class="hover:bg-slate-800/30 transition-all">
                            <td class="px-6 py-4 font-semibold text-slate-200">{{ $order->customer_name ?: '—' }}</td>
                            <td class="px-6 py-4 font-mono text-slate-300">{{ $order->customer_phone ?: '—' }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $order->customer_address ?: '—' }}</td>
                            <td class="px-6 py-4 text-slate-300">
                                @foreach($order->cart as $item)
                                    <div>{{ $item['name'] ?? '—' }} × {{ $item['quantity'] ?? 1 }}</div>
                                @endforeach
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-white">৳{{ number_format($order->subtotal, 0) }}</td>
                            <td class="px-6 py-4 text-slate-300">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-6 py-12 text-center text-slate-300">No abandoned orders.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="px-6 py-4 border-t border-slate-800/50">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection

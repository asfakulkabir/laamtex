@extends('layouts.admin')

@section('title', 'Shipping Delivery Charges - Outfitt')
@section('page_title', 'Shipping Zones & Rates')

@section('content')
<div class="max-w-4xl space-y-6">
    
    <div class="flex justify-between items-center">
        <p class="text-sm text-slate-300">Configure regional shipping zones, delivery prices, and estimated delivery times.</p>
        <a href="{{ route('admin.delivery-charges.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all active:scale-[0.98]">
            + Add Shipping Zone
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                    <th class="px-8 py-4">Delivery Zone / Region</th>
                    <th class="px-8 py-4">Shipping Cost</th>
                    <th class="px-8 py-4">Estimated Timeframe</th>
                    <th class="px-8 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/30 text-slate-400">
                @forelse($charges as $charge)
                    <tr class="hover:bg-slate-800/30 transition-all duration-150">
                        <td class="px-8 py-4 font-bold text-slate-200">{{ $charge->zone }}</td>
                        <td class="px-8 py-4 font-semibold text-purple-400">৳{{ number_format($charge->charge, 2) }}</td>
                        <td class="px-8 py-4 text-sm font-semibold text-slate-300">{{ $charge->estimated_days ?: 'Not specified' }}</td>
                        <td class="px-8 py-4 text-right">
                            <div class="flex justify-end items-center space-x-2">
                                <a href="{{ route('admin.delivery-charges.edit', $charge->id) }}" class="p-2 text-purple-400 hover:text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.delivery-charges.destroy', $charge->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this shipping zone?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded-lg transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-8 py-12 text-center text-slate-300">No delivery charges/zones defined.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

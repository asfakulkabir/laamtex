@extends('layouts.admin')

@section('title', 'Edit Shipping Zone - Outfitt')
@section('page_title', 'Edit Shipping Zone')

@section('content')
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">
    
    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Modify Shipping Zone</h3>
        <p class="text-sm text-slate-300 mt-1">Adjust regional names, costs, or shipping durations.</p>
    </div>

    <form action="{{ route('admin.delivery-charges.update', $deliveryCharge->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Zone Name -->
        <div>
            <label for="zone" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Zone Name / Region <span class="text-pink-500">*</span></label>
            <input type="text" id="zone" name="zone" value="{{ old('zone', $deliveryCharge->zone) }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="e.g. Inside Dhaka, West Coast, International">
            @error('zone')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Charge -->
        <div>
            <label for="charge" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Shipping Cost ($) <span class="text-pink-500">*</span></label>
            <input type="number" step="0.01" min="0" id="charge" name="charge" value="{{ old('charge', $deliveryCharge->charge) }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="e.g. 5.00">
            @error('charge')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Estimated Days -->
        <div>
            <label for="estimated_days" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Estimated Duration</label>
            <input type="text" id="estimated_days" name="estimated_days" value="{{ old('estimated_days', $deliveryCharge->estimated_days) }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="e.g. 2-3 Days, 1 week">
            @error('estimated_days')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- CTAs -->
        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Update Zone
            </button>
            <a href="{{ route('admin.delivery-charges.index') }}"
               class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

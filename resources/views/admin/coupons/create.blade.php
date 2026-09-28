@extends('layouts.admin')

@section('title', 'Add Coupon - laamtex')
@section('page_title', 'Add Coupon')

@section('content')
<div class="max-w-2xl space-y-6">
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">
        <div class="mb-6">
            <h3 class="font-bold text-white text-lg">Coupon Details</h3>
            <p class="text-sm text-slate-300 mt-1">The customer types this code on the checkout page.</p>
        </div>

        <form action="{{ route('admin.coupons.store') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="code" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Coupon Code <span class="text-pink-500">*</span></label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 uppercase placeholder-slate-600"
                           placeholder="e.g. EID2026">
                    @error('code')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Description</label>
                    <input type="text" id="description" name="description" value="{{ old('description') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="e.g. Eid sale 10% off">
                    @error('description')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="type" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Discount Type <span class="text-pink-500">*</span></label>
                    <select id="type" name="type" required onchange="toggleTypeFields(this.value)"
                            class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200">
                        <option value="percent" {{ old('type', 'percent') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
                        <option value="fixed" {{ old('type') === 'fixed' ? 'selected' : '' }}>Fixed Amount (৳)</option>
                    </select>
                    @error('type')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="value" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                        Discount Value <span class="text-pink-500">*</span>
                        <span class="normal-case font-normal text-slate-400" id="valueHint">(percent, max 100)</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="value" name="value" value="{{ old('value') }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="e.g. 10">
                    @error('value')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="min_order_amount" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Minimum Order (৳)</label>
                    <input type="number" step="0.01" min="0" id="min_order_amount" name="min_order_amount" value="{{ old('min_order_amount') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="e.g. 1000">
                    @error('min_order_amount')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div id="maxDiscountField">
                    <label for="max_discount_amount" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Maximum Discount (৳)</label>
                    <input type="number" step="0.01" min="0" id="max_discount_amount" name="max_discount_amount" value="{{ old('max_discount_amount') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="e.g. 300 (blank = no cap)">
                    <p class="text-xs text-slate-400 mt-1">Only for percentage coupons.</p>
                    @error('max_discount_amount')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="usage_limit" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Total Usage Limit</label>
                    <input type="number" min="1" id="usage_limit" name="usage_limit" value="{{ old('usage_limit') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="blank = unlimited">
                    @error('usage_limit')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="per_user_limit" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Usage Limit Per Customer</label>
                    <input type="number" min="1" id="per_user_limit" name="per_user_limit" value="{{ old('per_user_limit') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="e.g. 1 (blank = unlimited)">
                    @error('per_user_limit')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="starts_at" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Starts On</label>
                    <input type="datetime-local" id="starts_at" name="starts_at" value="{{ old('starts_at') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 [color-scheme:dark]">
                    @error('starts_at')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="expires_at" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Expires On</label>
                    <input type="datetime-local" id="expires_at" name="expires_at" value="{{ old('expires_at') }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 transition-all text-slate-200 [color-scheme:dark]">
                    @error('expires_at')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))
                       class="w-4 h-4 rounded bg-slate-800 border-slate-600 text-purple-600 focus:ring-purple-500">
                <span class="text-sm text-slate-200">Coupon is active and can be used at checkout</span>
            </label>

            <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                    Create Coupon
                </button>
                <a href="{{ route('admin.coupons.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleTypeFields(value) {
        var isPercent = value === 'percent';
        document.getElementById('maxDiscountField').style.display = isPercent ? '' : 'none';
        document.getElementById('valueHint').textContent = isPercent ? '(percent, max 100)' : '(amount in ৳)';
    }
    document.addEventListener('DOMContentLoaded', function () {
        toggleTypeFields(document.getElementById('type').value);
    });
</script>
@endsection

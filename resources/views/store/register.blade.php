@extends('layouts.store')

@section('title', 'Create Account - ' . site_name())

@section('content')
<div class="max-w-md mx-auto px-4 py-8">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900">Create Account</h1>
            <p class="text-sm text-gray-500 mt-1">Sign up with {{ site_name() }} to track your orders</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-semibold">
                ✅ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-semibold">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('customer.register.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Full Name <span class="text-pink-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="Your full name">
                @error('name')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Email Address <span class="text-pink-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="you@example.com">
                @error('email')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-bold text-gray-700 mb-1">Phone Number <span class="text-pink-500">*</span></label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-base font-semibold select-none">🇧🇩</span>
                    <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" required
                           pattern="^(\+?88)?01[3-9]\d{8}$"
                           class="w-full bg-white border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                           placeholder="01XXXXXXXXX">
                </div>
                @error('phone')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
                <p class="text-xs text-gray-400 mt-0.5">Format: 01XXXXXXXXX (Bangladeshi number)</p>
            </div>

            <div>
                <label for="address" class="block text-sm font-bold text-gray-700 mb-1">Address</label>
                <textarea id="address" name="address" rows="2"
                          class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                          placeholder="House / Road / Area / City / District (optional)">{{ old('address') }}</textarea>
                @error('address')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-pink-500">*</span></label>
                <input type="password" id="password" name="password" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="Minimum 6 characters">
                @error('password')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">Confirm Password <span class="text-pink-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="Re-enter password">
            </div>

            <button type="submit"
                    class="w-full block text-center py-3.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-bold transition shadow-md active:scale-95 text-base">
                🚀 Create Account
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
            Already have an account?
            <a href="{{ route('customer.login') }}" class="font-bold text-purple-600 hover:underline">Login</a>
        </p>
    </div>
</div>
@endsection
@extends('layouts.store')

@section('title', 'Edit Profile - ' . site_name())

@section('content')
<div class="max-w-xl mx-auto px-3 sm:px-4 py-6 space-y-4">

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm font-semibold">
            ✅ {{ session('success') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900">✏️ Edit Profile</h1>
            <p class="text-sm text-gray-500">Update your details — checkout auto-fills from this</p>
        </div>
        <a href="{{ route('customer.dashboard') }}" class="text-sm font-bold text-purple-600 hover:underline">← My Account</a>
    </div>

    <form action="{{ route('customer.profile.update') }}" method="POST" class="bg-white border border-gray-200 rounded-2xl shadow-sm p-5 sm:p-6 space-y-4">
        @csrf
        @method('PUT')

        <div>
            <label for="name" class="block text-sm font-bold text-gray-700 mb-1">Full Name <span class="text-pink-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
            @error('name')
                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-bold text-gray-700 mb-1">Email Address <span class="text-pink-500">*</span></label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
            @error('email')
                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="phone" class="block text-sm font-bold text-gray-700 mb-1">Phone Number <span class="text-pink-500">*</span></label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 text-base font-semibold select-none">🇧🇩</span>
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required
                       pattern="^(\+?88)?01[3-9]\d{8}$"
                       class="w-full bg-white border border-gray-300 rounded-lg pl-9 pr-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
            </div>
            @error('phone')
                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="address" class="block text-sm font-bold text-gray-700 mb-1">Address</label>
            <textarea id="address" name="address" rows="2"
                      class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                      placeholder="House / Road / Area / City / District">{{ old('address', $user->address) }}</textarea>
            @error('address')
                <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-bold text-gray-700 mb-3">Change Password <span class="text-gray-400 font-semibold">(optional)</span></p>

            <div class="space-y-3">
                <div>
                    <label for="current_password" class="block text-sm font-bold text-gray-600 mb-1">Current Password</label>
                    <input type="password" id="current_password" name="current_password"
                           class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                    @error('current_password')
                        <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="new_password" class="block text-sm font-bold text-gray-600 mb-1">New Password</label>
                    <input type="password" id="new_password" name="new_password"
                           class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                    @error('new_password')
                        <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="new_password_confirmation" class="block text-sm font-bold text-gray-600 mb-1">Confirm New Password</label>
                    <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                           class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all">
                </div>
            </div>
        </div>

        <button type="submit"
                class="w-full block text-center py-3.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-bold transition shadow-md active:scale-95 text-base">
            💾 Save Changes
        </button>
    </form>
</div>
@endsection
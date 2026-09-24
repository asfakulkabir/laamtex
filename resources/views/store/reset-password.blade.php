@extends('layouts.store')

@section('title', 'Reset Password - ' . site_name())

@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900">Set New Password</h1>
            <p class="text-sm text-gray-500 mt-1">Choose a new password for your account</p>
        </div>

        @if(session('error'))
            <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-sm font-semibold">
                ⚠️ {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('customer.reset.submit') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="login" class="block text-sm font-bold text-gray-700 mb-1">Email or Phone <span class="text-pink-500">*</span></label>
                <input type="text" id="login" name="login" value="{{ old('login', $login ?? '') }}" required
                       @if($login) readonly @endif
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all @if($login) bg-gray-50 @endif"
                       placeholder="you@example.com or 01XXXXXXXXX">
                @error('login')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">New Password <span class="text-pink-500">*</span></label>
                <input type="password" id="password" name="password" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="Minimum 6 characters">
                @error('password')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-1">Confirm New Password <span class="text-pink-500">*</span></label>
                <input type="password" id="password_confirmation" name="password_confirmation" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="Re-enter new password">
            </div>

            <button type="submit"
                    class="w-full block text-center py-3.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-bold transition shadow-md active:scale-95 text-base">
                🔑 Reset Password
            </button>
        </form>
    </div>
</div>
@endsection
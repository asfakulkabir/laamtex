@extends('layouts.store')

@section('title', 'Forgot Password - ' . site_name())

@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900">Forgot Password?</h1>
            <p class="text-sm text-gray-500 mt-1">Enter your email or phone and we'll send you a reset link</p>
        </div>

        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-3 py-2 rounded-lg text-sm font-semibold">
                ✅ {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('customer.forgot.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-sm font-bold text-gray-700 mb-1">Email or Phone <span class="text-pink-500">*</span></label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required autofocus
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="you@example.com or 01XXXXXXXXX">
                @error('login')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <button type="submit"
                    class="w-full block text-center py-3.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-bold transition shadow-md active:scale-95 text-base">
                📧 Send Reset Link
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
            Remembered your password?
            <a href="{{ route('customer.login') }}" class="font-bold text-purple-600 hover:underline">Back to Login</a>
        </p>
    </div>
</div>
@endsection
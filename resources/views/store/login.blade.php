@extends('layouts.store')

@section('title', 'Login - ' . site_name())

@section('content')
<div class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm p-6 sm:p-8">

        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-gray-900">Welcome Back</h1>
            <p class="text-sm text-gray-500 mt-1">Login with your email or phone number</p>
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

        <form action="{{ route('customer.login.submit') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="login" class="block text-sm font-bold text-gray-700 mb-1">Email or Phone <span class="text-pink-500">*</span></label>
                <input type="text" id="login" name="login" value="{{ old('login') }}" required
                       autofocus
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="you@example.com or 01XXXXXXXXX">
                @error('login')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-bold text-gray-700 mb-1">Password <span class="text-pink-500">*</span></label>
                <input type="password" id="password" name="password" required
                       class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2.5 text-base focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all"
                       placeholder="Your password">
                @error('password')
                    <span class="text-sm text-red-500 mt-0.5 block">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center space-x-2 text-sm text-gray-600 font-semibold cursor-pointer">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-gray-400 text-purple-600 focus:ring-purple-500">
                    <span>Remember me</span>
                </label>
                <a href="{{ route('customer.forgot') }}" class="text-sm font-bold text-purple-600 hover:underline">Forgot password?</a>
            </div>

            @if(request()->has('checkout_redirect'))
                <input type="hidden" name="checkout_redirect" value="1">
            @endif

            <button type="submit"
                    class="w-full block text-center py-3.5 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-bold transition shadow-md active:scale-95 text-base">
                🔓 Login
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-5">
            Don't have an account?
            <a href="{{ route('customer.register') }}" class="font-bold text-purple-600 hover:underline">Create Account</a>
        </p>
    </div>
</div>
@endsection
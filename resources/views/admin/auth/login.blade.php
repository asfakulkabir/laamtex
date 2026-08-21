<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - {{ site_name() }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-950 min-h-screen flex items-center justify-center relative overflow-hidden px-4">

    <!-- Decorative Blurry Gradient Circles -->
    <div class="absolute -top-40 -left-40 w-96 h-96 bg-purple-600 rounded-full filter blur-[120px] opacity-30 animate-pulse"></div>
    <div class="absolute -bottom-40 -right-40 w-96 h-96 bg-pink-600 rounded-full filter blur-[120px] opacity-30 animate-pulse"></div>

    <div class="w-full max-w-md bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-2xl relative z-10">
        
        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="{{ site_logo() }}" alt="{{ site_name() }}" class="h-14 mx-auto rounded shadow-lg">
            <h2 class="text-2xl font-bold tracking-tight text-white mt-4">Admin Portal Access</h2>
            <p class="text-sm text-purple-300/60 mt-1">Please enter your credentials below</p>
        </div>

        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 p-3 rounded-lg text-sm mb-6">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('admin.login.submit') }}" method="POST" class="space-y-6">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-semibold uppercase tracking-wider text-purple-200/80 mb-2">Email Address</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all placeholder-slate-700"
                       placeholder="admin@outfitt.com">
                @error('email')
                    <span class="text-sm text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex justify-between items-center mb-2">
                    <label for="password" class="block text-sm font-semibold uppercase tracking-wider text-purple-200/80">Password</label>
                </div>
                <input type="password" id="password" name="password" required
                       class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-3 text-white text-sm focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent transition-all placeholder-slate-700"
                       placeholder="••••••••••••">
                @error('password')
                    <span class="text-sm text-red-400 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center">
                <input id="remember" name="remember" type="checkbox"
                       class="h-4 w-4 bg-slate-950 border-slate-800 rounded text-pink-500 focus:ring-pink-500 focus:ring-offset-slate-900 focus:ring-offset-2">
                <label for="remember" class="ml-2 block text-sm text-purple-200/70 select-none">Remember my session</label>
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold tracking-wide transition-all shadow-lg hover:shadow-pink-500/20 active:scale-[0.98]">
                Log In
            </button>
        </form>
        
        <div class="text-center mt-6">
            <a href="{{ route('home') }}" class="text-sm text-purple-400 hover:text-purple-300 transition-colors uppercase tracking-wider font-semibold">← Return to Store</a>
        </div>
    </div>
</body>
</html>

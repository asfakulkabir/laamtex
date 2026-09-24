<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', site_name() . ' Admin Panel')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Styles / Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Quill Rich Text Editor -->
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
        }
        .ql-container {
            font-size: 14px;
            font-family: 'Outfit', sans-serif;
        }
        .ql-editor {
            min-height: 120px;
        }
        .ql-toolbar {
            border-radius: 0.5rem 0.5rem 0 0;
            background: #1e293b;
            border-color: #334155;
        }
        .ql-container {
            border-radius: 0 0 0.5rem 0.5rem;
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }
        .ql-snow .ql-stroke { stroke: #94a3b8; }
        .ql-snow .ql-fill { fill: #94a3b8; }
        .ql-snow .ql-picker { color: #94a3b8; }
        .ql-snow .ql-picker-options { background: #1e293b; border-color: #334155; }
        .ql-snow .ql-picker.ql-expanded .ql-picker-label { color: #c084fc; border-color: #334155; }
        .ql-editor.ql-blank::before { color: #64748b; }
        .ql-snow .ql-picker-label { border-color: #334155; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>
<body class="bg-slate-950 text-slate-300 flex min-h-screen scroll-smooth" x-data="{ sidebarOpen: false }">

    <!-- Sidebar Navigation (Fixed) -->
    <aside class="w-64 bg-slate-900/80 backdrop-blur-xl text-white flex-shrink-0 flex flex-col z-40 transition-all duration-300 fixed h-screen left-0 top-0 border-r border-slate-800/50"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        
        <!-- Sidebar Header -->
        <div class="h-16 md:h-20 flex items-center justify-between px-4 md:px-6 border-b border-slate-800/50 bg-slate-900/50">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 flex-1 min-w-0">
                <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center shadow-lg shadow-purple-500/20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <span class="font-extrabold text-sm uppercase tracking-widest bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent hidden sm:inline truncate">{{ site_name() }}</span>
            </a>
            <button @click="sidebarOpen = false" class="md:hidden text-gray-500 hover:text-white flex-shrink-0 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex-grow py-4 md:py-6 px-3 md:px-4 space-y-1 overflow-y-auto">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">🏠</span>
                <span>Overview</span>
            </a>

            <a href="{{ route('admin.categories.index') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">📂</span>
                <span>Categories</span>
            </a>

            <a href="{{ route('admin.products.index') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.products.*') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">📦</span>
                <span>Products</span>
            </a>

            <a href="{{ route('admin.delivery-charges.index') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.delivery-charges.*') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">🚚</span>
                <span>Shipping</span>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.orders.*') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">📋</span>
                <span>Orders</span>
            </a>

            <a href="{{ route('admin.customers.index') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.customers.*') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">👥</span>
                <span>Customers</span>
            </a>

            <a href="{{ route('admin.sliders.index') }}" class="flex items-center space-x-3 px-3 md:px-4 py-3 md:py-3.5 rounded-xl text-base md:text-base font-semibold transition-all duration-200 {{ request()->routeIs('admin.sliders.*') ? 'bg-gradient-to-r from-purple-500/20 to-pink-500/10 border border-purple-500/30 text-purple-300 shadow-sm shadow-purple-500/5' : 'text-slate-400 hover:bg-gradient-to-r hover:from-purple-500/20 hover:to-pink-500/10 hover:border hover:border-purple-500/30 hover:text-purple-300 border border-transparent' }}">
                <span class="text-lg">🎠</span>
                <span>Home Slider</span>
            </a>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-3 md:p-4 border-t border-slate-800/50 bg-slate-900/30 flex flex-col space-y-2">
            <div class="px-3 md:px-4 py-2 flex items-center space-x-2">
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm flex-shrink-0 shadow-lg shadow-purple-500/20">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="truncate hidden sm:block">
                    <p class="text-sm font-bold text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-300 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
            
            <a href="{{ route('home') }}" target="_blank" class="w-full text-center px-3 py-2 bg-slate-800/50 border border-slate-700/50 text-slate-400 rounded-lg text-sm hover:bg-slate-700/50 hover:text-white transition-all duration-200">
                Visit Site
            </a>
            
            <form action="{{ route('admin.logout') }}" method="POST" class="w-full">
                @csrf
                <button type="submit" class="w-full text-center px-3 py-2 bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-500 hover:to-rose-500 text-white rounded-lg text-sm font-bold transition-all duration-200 shadow-lg shadow-pink-600/20">
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Mobile Backdrop -->
    <div class="fixed inset-0 bg-black/70 z-30 md:hidden transition-opacity duration-300 backdrop-blur-sm"
         @click="sidebarOpen = false"
         :class="sidebarOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'">
    </div>

    <!-- Main Section -->
    <div class="flex-1 flex flex-col min-w-0 md:ml-64">
        <!-- Top bar -->
        <header class="h-14 md:h-16 lg:h-20 bg-slate-900/60 backdrop-blur-xl border-b border-slate-800/50 flex items-center justify-between px-3 md:px-6 lg:px-8">
            <div class="flex items-center space-x-2 md:space-x-4 min-w-0">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-400 hover:text-white flex-shrink-0 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg md:text-xl lg:text-2xl font-bold tracking-tight text-white truncate">@yield('page_title', 'Control Panel')</h1>
            </div>
            <div class="flex items-center space-x-2 md:space-x-4 flex-shrink-0">
                <a href="{{ route('admin.settings.edit') }}" class="text-slate-400 hover:text-white transition-all p-1.5 rounded-lg hover:bg-slate-800/50" title="Settings">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </a>
                <div class="flex items-center space-x-2 px-3 py-1.5 bg-emerald-500/10 border border-emerald-500/20 rounded-full">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                    <span class="text-[10px] md:text-sm font-semibold text-emerald-400 hidden sm:inline">Live</span>
                </div>
            </div>
        </header>

        <!-- Toasts -->
        @if(session('success'))
            <div class="mx-2 md:mx-4 lg:mx-8 mt-3 md:mt-4 lg:mt-6 bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 p-3 md:p-4 rounded-xl shadow-sm text-sm md:text-sm backdrop-blur-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mx-2 md:mx-4 lg:mx-8 mt-3 md:mt-4 lg:mt-6 bg-red-500/10 border border-red-500/20 text-red-400 p-3 md:p-4 rounded-xl shadow-sm text-sm md:text-sm backdrop-blur-sm">
                {{ session('error') }}
            </div>
        @endif

        <!-- Panel Body -->
        <main class="flex-grow p-3 md:p-4 lg:p-8 overflow-y-auto">
            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.js"></script>
    @yield('scripts')
</body>
</html>
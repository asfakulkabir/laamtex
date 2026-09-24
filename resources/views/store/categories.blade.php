@extends('layouts.store')

@section('title', 'All Categories - ' . site_name())

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 md:py-10">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl md:text-3xl font-extrabold tracking-tight text-gray-900">All Categories</h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Browse every category in our store</p>
        </div>
        <a href="{{ route('shop') }}" class="hidden sm:inline-flex items-center gap-1.5 text-xs sm:text-sm font-bold text-purple-600 hover:text-purple-800 transition">
            View All Products &rarr;
        </a>
    </div>

    @php
        $categoryPalettes = [
            'from-rose-100 to-rose-200 text-rose-700',
            'from-violet-100 to-violet-200 text-violet-700',
            'from-amber-100 to-amber-200 text-amber-700',
            'from-emerald-100 to-emerald-200 text-emerald-700',
            'from-sky-100 to-sky-200 text-sky-700',
            'from-pink-100 to-pink-200 text-pink-700',
            'from-teal-100 to-teal-200 text-teal-700',
            'from-indigo-100 to-indigo-200 text-indigo-700',
        ];
    @endphp

    @forelse($categories as $i => $cat)
        <div class="bg-white border border-gray-200 rounded-2xl p-4 sm:p-5 mb-4">
            <a href="{{ route('shop', ['category' => $cat->slug]) }}" class="group flex items-center gap-3 hover:-translate-y-0.5 transition">
                @if($cat->image)
                    <img src="{{ asset('storage/' . $cat->image) }}" alt="{{ $cat->name }}" loading="lazy"
                         class="h-12 w-12 sm:h-14 sm:w-14 object-contain rounded-xl bg-gradient-to-br {{ $categoryPalettes[$i % count($categoryPalettes)] }} p-2">
                @else
                    <span class="h-12 w-12 sm:h-14 sm:w-14 flex items-center justify-center rounded-xl bg-gradient-to-br {{ $categoryPalettes[$i % count($categoryPalettes)] }} font-extrabold text-xl">{{ substr($cat->name, 0, 1) }}</span>
                @endif
                <div class="min-w-0">
                    <h2 class="text-base sm:text-lg font-bold text-gray-900 group-hover:text-purple-600 transition">{{ $cat->name }}</h2>
                    @if($cat->children->count() > 0)
                        <p class="text-xs text-gray-400">{{ $cat->children->count() }} subcategories</p>
                    @endif
                </div>
            </a>

            @if($cat->children->count() > 0)
                <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
                    @foreach($cat->children as $child)
                        <a href="{{ route('shop', ['category' => $child->slug]) }}"
                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-900 hover:border-gray-900 hover:text-white transition-all duration-200">
                            @if($child->image)
                                <img src="{{ asset('storage/' . $child->image) }}" alt="" loading="lazy" class="h-4 w-4 object-contain">
                            @endif
                            {{ $child->name }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="col-span-full py-16 text-center text-gray-400 bg-white border border-gray-200 rounded-2xl">
            <svg class="w-16 h-16 text-purple-200 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
            </svg>
            <h4 class="font-bold text-gray-600 mt-4">No Categories</h4>
            <p class="text-xs text-gray-400 mt-1">Categories will appear here once added.</p>
        </div>
    @endforelse
</div>
@endsection
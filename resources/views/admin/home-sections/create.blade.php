@extends('layouts.admin')

@section('title', 'Add Home Section - laamtex')
@section('page_title', 'Add Home Section')

@section('content')
<div class="max-w-2xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">

    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">New Home Section</h3>
        <p class="text-sm text-slate-300 mt-1">Show a category's products on the home page, as a slider or a grid.</p>
    </div>

    <form action="{{ route('admin.home-sections.store') }}" method="POST" class="space-y-6" x-data="{ layout: '{{ old('layout', 'grid') }}' }">
        @csrf

        <div>
            <label for="title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                Heading <span class="text-pink-500">*</span>
            </label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                   placeholder="e.g. New Arrivals in Shirts"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
            <p class="text-xs text-slate-500 mt-1">Shown as the section heading on the home page.</p>
            @error('title')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="subtitle" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Subheading</label>
            <input type="text" id="subtitle" name="subtitle" value="{{ old('subtitle') }}"
                   placeholder="Optional short line under the heading"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
            @error('subtitle')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="category_id" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Category</label>
            <select id="category_id" name="category_id"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200">
                <option value="">All categories (newest first)</option>
                @foreach($categories as $id => $label)
                    <option value="{{ $id }}" @selected(old('category_id') == $id)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="text-xs text-slate-500 mt-1">Products in the child categories are included too.</p>
            @error('category_id')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="layout" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Layout <span class="text-pink-500">*</span></label>
                <select id="layout" name="layout" x-model="layout"
                        class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200">
                    <option value="grid">Grid (simple rows)</option>
                    <option value="slider">Slider (scrolls sideways)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1" x-text="layout === 'slider'
                    ? 'A horizontal carousel with arrows and auto-play.'
                    : 'A plain responsive grid, best for small counts.'"></p>
                @error('layout')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label for="product_limit" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                    Products to show <span class="text-pink-500">*</span>
                </label>
                <input type="number" id="product_limit" name="product_limit" min="1" max="{{ \App\Models\HomeSection::MAX_PRODUCT_LIMIT }}" required
                       value="{{ old('product_limit', 8) }}"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200">
                <p class="text-xs text-slate-500 mt-1">Between 1 and {{ \App\Models\HomeSection::MAX_PRODUCT_LIMIT }}.</p>
                @error('product_limit')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="flex items-center">
            <input id="is_active" name="is_active" type="checkbox" value="1" @checked(old('is_active', true))
                   class="h-4 w-4 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
            <label for="is_active" class="ml-2 block text-sm font-semibold text-slate-300">Show this section on the home page</label>
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Add Section
            </button>
            <a href="{{ route('admin.home-sections.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection


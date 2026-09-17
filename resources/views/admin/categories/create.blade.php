@extends('layouts.admin')

@section('title', 'Create Category - laamtex')
@section('page_title', 'Create Category')

@section('content')
<div class="max-w-2xl bg-slate-900 border border-slate-800/50 p-8 rounded-2xl shadow-lg shadow-black/10">
    
    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">New Category Details</h3>
        <p class="text-sm text-slate-300 mt-1">Define the name, grouping, hierarchy, and banner image.</p>
    </div>

    <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Category Name <span class="text-pink-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/50 text-slate-200 placeholder:text-slate-600 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800 transition-all"
                   placeholder="e.g. Summer Outfits">
            @error('name')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Parent Category -->
        <div>
            <label for="parent_id" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Parent Category</label>
            <select id="parent_id" name="parent_id"
                    class="w-full bg-slate-800/50 border border-slate-700/50 text-slate-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800 transition-all">
                <option value="">-- None (Root Category) --</option>
                @foreach($parentCategories as $parent)
                    <option value="{{ $parent->id }}" {{ old('parent_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                @endforeach
            </select>
            @error('parent_id')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Group Name -->
        <div>
            <label for="group_name" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Group Name</label>
            <input type="text" id="group_name" name="group_name" value="{{ old('group_name') }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 text-slate-200 placeholder:text-slate-600 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800 transition-all"
                   placeholder="e.g. Gender, Brand, Clothing Type">
            <span class="text-sm text-slate-300 mt-1 block">Allows grouping categories in secondary menus (e.g. 'Gender' for Men/Women).</span>
            @error('group_name')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- Image Upload -->
        <div>
            <label for="image" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Category Banner Image</label>
            <input type="file" id="image" name="image" accept="image/*"
                   class="w-full text-sm text-slate-300 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-purple-500/10 file:text-purple-400 hover:file:bg-purple-500/20">
            <span class="text-sm text-slate-300 mt-1 block">PNG, JPG, JPEG or WEBP formats. Recommended size 600x400.</span>
            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <!-- CTAs -->
        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Save Category
            </button>
            <a href="{{ route('admin.categories.index') }}"
               class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

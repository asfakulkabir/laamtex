@extends('layouts.admin')

@section('title', 'Edit Slider Image - laamtex')
@section('page_title', 'Edit Slider Image')

@section('content')
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">
    
    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Edit Slider Image</h3>
        <p class="text-sm text-slate-300 mt-1">Update slider image or details.</p>
    </div>

    <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Current Image</label>
            <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?? 'Slider' }}"
                 class="w-full max-h-40 object-cover rounded-lg border border-slate-700/50 mb-3">
        </div>

        <div>
            <label for="image" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Replace Image (optional)</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30">
            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Title (optional)</label>
            <input type="text" id="title" name="title" value="{{ old('title', $slider->title) }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="e.g. Summer Collection">
            @error('title')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="link" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Link URL (optional)</label>
            <input type="url" id="link" name="link" value="{{ old('link', $slider->link) }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="https://example.com/shop">
            @error('link')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="sort_order" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Sort Order</label>
                <input type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', $slider->sort_order) }}"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200">
                @error('sort_order')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Active</label>
                <label class="relative inline-flex items-center cursor-pointer mt-1.5">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $slider->is_active) ? 'checked' : '' }} class="sr-only peer">
                    <div class="w-10 h-5 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                    <span class="ml-3 text-sm text-slate-400">Show on home page</span>
                </label>
            </div>
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Update Slider
            </button>
            <a href="{{ route('admin.sliders.index') }}"
               class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

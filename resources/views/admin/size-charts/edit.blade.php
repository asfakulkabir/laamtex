@extends('layouts.admin')

@section('title', 'Edit Size Chart - laamtex')
@section('page_title', 'Edit Size Chart')

@section('content')
@php
    $recommendedWidth = \App\Models\SizeChart::RECOMMENDED_WIDTH;
    $recommendedHeight = \App\Models\SizeChart::RECOMMENDED_HEIGHT;
@endphp
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">

    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Current Chart</h3>
        <p class="text-sm text-slate-300 mt-1">Change the title, replace the image, or leave the image field empty to keep it.</p>
    </div>

    <div class="mb-6">
        <img src="{{ $sizeChart->image_url }}" alt="{{ $sizeChart->title }}" loading="lazy"
             @if($sizeChart->width) width="{{ $sizeChart->width }}" height="{{ $sizeChart->height }}" @endif
             class="w-full max-h-[420px] object-contain bg-white rounded-xl border border-slate-700/50">

        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-mono font-semibold text-slate-200">{{ $sizeChart->dimensions }}</span>
            <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-semibold text-slate-300">{{ $sizeChart->size_label }}</span>
        </div>

        <p class="mt-2 text-xs text-slate-400">
            Recommended: <strong class="text-purple-400">{{ $recommendedWidth }} x {{ $recommendedHeight }} px</strong>.
            Used by <strong class="text-purple-400">{{ $sizeChart->products()->count() }} {{ Str::plural('product', $sizeChart->products()->count()) }}</strong>.
        </p>
    </div>

    <form action="{{ route('admin.size-charts.update', $sizeChart->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                Title <span class="text-pink-500">*</span>
            </label>
            <input type="text" id="title" name="title" value="{{ old('title', $sizeChart->title) }}" required
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
            @error('title')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Replace Image</label>

            @include('admin.partials.media-picker', [
                'field' => 'image',
                'kind' => 'image',
                'label' => 'Size Chart Image',
                'value' => $sizeChart->image,
                'ratio' => '4/5',
            ])

            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Save
            </button>
            <a href="{{ route('admin.size-charts.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

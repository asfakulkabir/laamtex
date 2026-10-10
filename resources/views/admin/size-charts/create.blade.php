@extends('layouts.admin')

@section('title', 'Add Size Chart - laamtex')
@section('page_title', 'Add Size Chart')

@section('content')
@php
    $recommendedWidth = \App\Models\SizeChart::RECOMMENDED_WIDTH;
    $recommendedHeight = \App\Models\SizeChart::RECOMMENDED_HEIGHT;
@endphp
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">

    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Size Chart</h3>
        <p class="text-sm text-slate-300 mt-1">
            The title is shown as the link text on the product page and as the heading above the chart image.
            JPG, PNG or WEBP up to 5 MB, ideally
            <strong class="text-purple-400">{{ $recommendedWidth }} x {{ $recommendedHeight }} px</strong>.
        </p>
    </div>

    <form action="{{ route('admin.size-charts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label for="title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                Title <span class="text-pink-500">*</span>
            </label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                   placeholder="e.g. Unisex T-Shirt Measurements"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
            @error('title')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                Chart Image <span class="text-pink-500">*</span>
            </label>

            @include('admin.partials.media-picker', [
                'field' => 'image',
                'kind' => 'image',
                'label' => 'Size Chart Image',
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

@extends('layouts.admin')

@section('title', 'Replace Testimonial - laamtex')
@section('page_title', 'Replace Testimonial Image')

@section('content')
@php
    $recommendedWidth = \App\Models\Testimonial::RECOMMENDED_WIDTH;
    $recommendedHeight = \App\Models\Testimonial::RECOMMENDED_HEIGHT;
@endphp
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">

    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Current Image</h3>
        <p class="text-sm text-slate-300 mt-1">Upload a new image to replace the current one, or leave the field empty to keep it.</p>
    </div>

    <div class="mb-6">
        <img src="{{ $testimonial->image_url }}" alt="Current testimonial"
             @if($testimonial->width) width="{{ $testimonial->width }}" height="{{ $testimonial->height }}" @endif
             class="w-full aspect-video object-contain bg-white rounded-xl border border-slate-700/50">

        <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
            <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-mono font-semibold text-slate-200">{{ $testimonial->dimensions }}</span>
            <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-semibold text-slate-300">{{ $testimonial->size_label }}</span>
            <span class="px-2 py-1 rounded-lg font-bold uppercase tracking-wider
                         {{ $testimonial->isLandscape() ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400' }}">
                {{ $testimonial->orientation }}
            </span>
        </div>

        <p class="mt-2 text-xs text-slate-400">
            Recommended: <strong class="text-purple-400">{{ $recommendedWidth }} x {{ $recommendedHeight }} px</strong> landscape.
            @unless($testimonial->isLandscape())
                <span class="text-amber-400">This one is not landscape, so it is letterboxed in the slider.</span>
            @endunless
        </p>
    </div>

    <form action="{{ route('admin.testimonials.update', $testimonial->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Replace With</label>

            @include('admin.partials.media-picker', [
                'field' => 'image',
                'kind' => 'image',
                'label' => 'Testimonial Image',
                'value' => $testimonial->image,
                'ratio' => '16/9',
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
            <a href="{{ route('admin.testimonials.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

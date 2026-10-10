@extends('layouts.admin')

@section('title', 'Upload Testimonial - laamtex')
@section('page_title', 'Upload Testimonial')

@section('content')
@php
    $recommendedWidth = \App\Models\Testimonial::RECOMMENDED_WIDTH;
    $recommendedHeight = \App\Models\Testimonial::RECOMMENDED_HEIGHT;
@endphp
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">

    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Testimonial Image</h3>
        <p class="text-sm text-slate-300 mt-1">
            JPG, PNG or WEBP up to 5 MB. Landscape images
            (<strong class="text-purple-400">{{ $recommendedWidth }} x {{ $recommendedHeight }} px</strong>, width bigger than height)
            look best: the home page slider shows two side by side on desktop and one on mobile.
        </p>
    </div>

    <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                Image <span class="text-pink-500">*</span>
            </label>

            @include('admin.partials.media-picker', [
                'field' => 'image',
                'kind' => 'image',
                'label' => 'Testimonial Image',
                'ratio' => '16/9',
            ])

            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Upload
            </button>
            <a href="{{ route('admin.testimonials.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

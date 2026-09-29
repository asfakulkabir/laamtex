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

    <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" x-data="testimonialUpload()">
        @csrf

        <div>
            <label for="image" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">
                Image <span class="text-pink-500">*</span>
            </label>

            <label for="image"
                   class="flex flex-col items-center justify-center gap-3 w-full px-4 py-10 rounded-xl border-2 border-dashed cursor-pointer transition"
                   :class="preview ? 'border-emerald-500/50 bg-emerald-500/5' : 'border-slate-700/60 bg-slate-800/30 hover:border-purple-500/50'">
                <template x-if="!preview">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <svg class="w-10 h-10 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 16.5V9.75m0 0l3 3m-3-3l-3 3M6.75 19.5a4.5 4.5 0 01-.41-8.98 4.5 4.5 0 018.08-3.05 5.25 5.25 0 011.58 4.61 4.5 4.5 0 01-.41 8.98H6.75z"/>
                        </svg>
                        <span class="text-sm font-bold text-slate-300">Click to choose an image</span>
                        <span class="text-xs text-slate-500">or drag and drop it here</span>
                    </div>
                </template>
                <template x-if="preview">
                    <img :src="preview" alt="Selected testimonial"
                         class="w-full aspect-video object-contain bg-white rounded-lg border border-slate-700/50">
                </template>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" required
                       class="sr-only" @change="handleFile($event)">
            </label>

            <div x-show="dimensions" x-cloak class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-mono font-semibold text-slate-200" x-text="dimensions"></span>
                <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-semibold text-slate-300" x-text="sizeLabel"></span>
                <span class="px-2 py-1 rounded-lg font-bold uppercase tracking-wider"
                      :class="isLandscape ? 'bg-emerald-500/10 text-emerald-400' : 'bg-amber-500/10 text-amber-400'"
                      x-text="orientation"></span>
            </div>
            <p x-show="dimensions && ! isLandscape" x-cloak class="mt-2 text-xs text-amber-400">
                This image is taller than it is wide, so it will be letterboxed in the slider. Use a
                {{ $recommendedWidth }} x {{ $recommendedHeight }} px landscape image instead.
            </p>

            <p x-show="fileName" x-cloak class="mt-2 text-xs font-semibold text-emerald-400">Selected: <span x-text="fileName"></span></p>
            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit" :disabled="!preview"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                Upload
            </button>
            <a href="{{ route('admin.testimonials.index') }}" class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function testimonialUpload() {
        return {
            preview: null,
            fileName: '',
            width: null,
            height: null,
            sizeLabel: '',

            get dimensions() {
                return this.width ? this.width + ' x ' + this.height + ' px' : '';
            },

            get orientation() {
                if (!this.width || !this.height) return '';
                if (this.width > this.height) return 'landscape';
                return this.width === this.height ? 'square' : 'portrait';
            },

            get isLandscape() {
                return this.orientation === 'landscape';
            },

            setPreview(url) {
                if (this.preview) URL.revokeObjectURL(this.preview);
                this.preview = url;
            },

            handleFile(event) {
                const file = event.target.files[0];
                if (!file) return;

                this.fileName = file.name;
                this.sizeLabel = file.size >= 1048576
                    ? (file.size / 1048576).toFixed(1) + ' MB'
                    : Math.max(1, Math.round(file.size / 1024)) + ' KB';

                // Read the real pixel size so the admin sees what the slider will get.
                const url = URL.createObjectURL(file);
                const probe = new Image();

                probe.onload = () => {
                    this.width = probe.naturalWidth;
                    this.height = probe.naturalHeight;
                    this.setPreview(url);
                };
                probe.onerror = () => {
                    URL.revokeObjectURL(url);
                    this.width = this.height = null;
                    this.setPreview(null);
                };
                probe.src = url;
            }
        };
    }
</script>
@endsection

@extends('layouts.admin')

@section('title', 'Edit Slider - laamtex')
@section('page_title', 'Edit Slider')

@section('content')
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">
    
    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">Edit Slider Item</h3>
        <p class="text-sm text-slate-300 mt-1">Update image, video or details.</p>
    </div>

    <form action="{{ route('admin.sliders.update', $slider->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Current Preview</label>
            @if($slider->isYoutubeVideo())
                <div class="aspect-video w-full rounded-lg border border-slate-700/50 overflow-hidden bg-black mb-3">
                    <iframe src="https://www.youtube.com/embed/{{ $slider->youtube_id }}" class="w-full h-full" frameborder="0"
                            allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>
                </div>
            @elseif($slider->isUploadedVideo())
                <video src="{{ asset('storage/' . $slider->video) }}" controls
                       class="w-full max-h-40 object-cover rounded-lg border border-slate-700/50 mb-3"></video>
            @elseif($slider->image)
                <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?? 'Slider' }}"
                     class="w-full max-h-40 object-cover rounded-lg border border-slate-700/50 mb-3">
            @else
                <p class="text-sm text-slate-400 bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-4 mb-3">No media currently.</p>
            @endif
        </div>

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Media Type</label>
            <select id="media_type" name="media_type"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200">
                <option value="image" {{ old('media_type', $slider->isVideo() ? '' : 'image') === 'image' ? 'selected' : '' }}>Image</option>
                <option value="video_upload" {{ old('media_type', $slider->isUploadedVideo() ? 'video_upload' : '') === 'video_upload' ? 'selected' : '' }}>Video Upload</option>
                <option value="youtube" {{ old('media_type', $slider->isYoutubeVideo() ? 'youtube' : '') === 'youtube' ? 'selected' : '' }}>YouTube Video</option>
            </select>
        </div>

        <div id="image_field">
            <label for="image" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">{{ $slider->isVideo() ? 'Poster Image (optional)' : 'Replace Image (optional)' }}</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30">
            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div id="video_upload_field" class="{{ $slider->isUploadedVideo() ? '' : 'hidden' }}">
            <label for="video" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Upload Video</label>
            <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/quicktime,video/ogg,video/x-m4v"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30">
            <span class="text-xs text-slate-500 mt-1 block">MP4, WebM, MOV or OGV. Max 50 MB. Leave empty to keep the current video.</span>
            @error('video')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div id="youtube_field" class="{{ $slider->isYoutubeVideo() ? '' : 'hidden' }}">
            <label for="youtube_url" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">YouTube Video URL</label>
            <input type="url" id="youtube_url" name="youtube_url" value="{{ old('youtube_url', $slider->isYoutubeVideo() ? $slider->video : '') }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
            <span class="text-xs text-slate-500 mt-1 block">Leave empty to keep the current video.</span>
            @error('youtube_url')
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

@section('scripts')
<script>
    function toggleSliderFields() {
        const type = document.getElementById('media_type').value;
        const imageField = document.getElementById('image_field');
        const videoField = document.getElementById('video_upload_field');
        const youtubeField = document.getElementById('youtube_field');

        imageField.classList.toggle('hidden', type === 'video_upload' || type === 'youtube');
        videoField.classList.toggle('hidden', type !== 'video_upload');
        youtubeField.classList.toggle('hidden', type !== 'youtube');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('media_type').addEventListener('change', toggleSliderFields);
        toggleSliderFields();
    });
</script>
@endsection
@endsection
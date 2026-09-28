@extends('layouts.admin')

@section('title', 'Add Slider - laamtex')
@section('page_title', 'Add Slider')

@section('content')
<div class="max-w-xl bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg">
    
    <div class="mb-6">
        <h3 class="font-bold text-white text-lg">New Slider Item</h3>
        <p class="text-sm text-slate-300 mt-1">Add an image, uploaded video, YouTube video or audio track for the home page hero. Recommended image size: <strong class="text-purple-400">1920 x 800 px</strong>.</p>
    </div>

    <form action="{{ route('admin.sliders.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <div>
            <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Media Type</label>
            <select id="media_type" name="media_type"
                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200">
                <option value="image" {{ in_array(old('media_type'), ['video_upload', 'youtube', 'audio'], true) ? '' : 'selected' }}>Image</option>
                <option value="video_upload" {{ old('media_type') === 'video_upload' ? 'selected' : '' }}>Video Upload</option>
                <option value="youtube" {{ old('media_type') === 'youtube' ? 'selected' : '' }}>YouTube Video</option>
                <option value="audio" {{ old('media_type') === 'audio' ? 'selected' : '' }}>Audio</option>
            </select>
        </div>

        <div id="image_field">
            <label for="image" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Image</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30">
            <span class="text-xs text-slate-500 mt-1 block">Required when Media Type is <strong>Image</strong>. Optional as a video poster or audio cover.</span>
            @error('image')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div id="audio_field" class="hidden">
            <label for="audio" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Upload Audio <span class="text-pink-500">*</span></label>
            <input type="file" id="audio" name="audio" accept="audio/mpeg,audio/wav,audio/ogg,audio/mp4,audio/aac,audio/x-m4a,audio/flac"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30">
            <span class="text-xs text-slate-500 mt-1 block">MP3, WAV, OGG, M4A, AAC or FLAC. Max 20 MB. Audio slides play only after the visitor taps play.</span>
            @error('audio')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div id="video_upload_field" class="hidden">
            <label for="video" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Upload Video <span class="text-pink-500">*</span></label>
            <input type="file" id="video" name="video" accept="video/mp4,video/webm,video/quicktime,video/ogg,video/x-m4v"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-purple-500/20 file:text-purple-400 hover:file:bg-purple-500/30">
            <span class="text-xs text-slate-500 mt-1 block">MP4, WebM, MOV or OGV. Max 50 MB.</span>
            @error('video')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div id="youtube_field" class="hidden">
            <label for="youtube_url" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">YouTube Video URL <span class="text-pink-500">*</span></label>
            <input type="url" id="youtube_url" name="youtube_url" value="{{ old('youtube_url') }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="https://www.youtube.com/watch?v=VIDEO_ID">
            @error('youtube_url')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Title (optional)</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="e.g. Summer Collection">
            @error('title')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div>
            <label for="link" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Link URL (optional)</label>
            <input type="url" id="link" name="link" value="{{ old('link') }}"
                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200 placeholder-slate-600"
                   placeholder="https://example.com/shop">
            @error('link')
                <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
            @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="sort_order" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Sort Order</label>
                <input type="number" min="0" id="sort_order" name="sort_order" value="{{ old('sort_order', 0) }}"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-800/50 transition-all text-slate-200">
                @error('sort_order')
                    <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Active</label>
                <label class="relative inline-flex items-center cursor-pointer mt-1.5">
                    <input type="checkbox" name="is_active" value="1" checked class="sr-only peer">
                    <div class="w-10 h-5 bg-slate-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-purple-500 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-purple-600"></div>
                    <span class="ml-3 text-sm text-slate-400">Show on home page</span>
                </label>
            </div>
        </div>

        <div class="flex items-center space-x-4 pt-4 border-t border-slate-800/50">
            <button type="submit"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Add Slider
            </button>
            <a href="{{ route('admin.sliders.index') }}"
               class="px-6 py-2.5 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function toggleSliderFields() {
        const type = document.getElementById('media_type').value;
        const imageField = document.getElementById('image_field');
        const videoField = document.getElementById('video_upload_field');
        const youtubeField = document.getElementById('youtube_field');
        const audioField = document.getElementById('audio_field');

        imageField.classList.toggle('hidden', type === 'video_upload' || type === 'youtube');
        videoField.classList.toggle('hidden', type !== 'video_upload');
        youtubeField.classList.toggle('hidden', type !== 'youtube');
        audioField.classList.toggle('hidden', type !== 'audio');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('media_type').addEventListener('change', toggleSliderFields);
        toggleSliderFields();
    });
</script>
@endsection
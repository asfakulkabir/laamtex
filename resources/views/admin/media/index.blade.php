@extends('layouts.admin')

@section('title', 'Media Library - laamtex')
@section('page_title', 'Media Library')

@section('content')
<div class="max-w-7xl space-y-6"
     x-data="mediaLibrary({{ json_encode([
         'storeUrl' => route('admin.media.store'),
         'limits' => \App\Http\Controllers\Admin\MediaController::limits(),
     ]) }})">

    <div class="flex flex-wrap justify-between items-center gap-3">
        <p class="text-sm text-slate-300">
            Every image, video and audio file in one place. Pick from here in the slider, product and variation forms instead of uploading the same file again.
        </p>
        <a href="{{ route('admin.sliders.create') }}" class="px-4 py-2 bg-slate-800/50 hover:bg-slate-700/50 text-slate-200 rounded-lg text-sm font-bold transition">
            Back to sliders
        </a>
    </div>

    {{-- Uploader --}}
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl p-5 shadow-lg">
        <label class="flex flex-col items-center justify-center gap-2 px-4 py-8 rounded-xl border-2 border-dashed cursor-pointer transition"
               :class="busy ? 'border-purple-500/60 bg-purple-500/5' : 'border-slate-700/60 bg-slate-800/30 hover:border-purple-500/50'">
            <svg class="h-8 w-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            <span class="text-sm font-bold text-slate-200">Click to upload images, videos or audio</span>
            <span class="text-xs text-slate-500" x-text="'Up to ' + formatSize(limits.maxBytes) + ' per file'"></span>
            <input type="file" class="sr-only" :accept="limits.accept" :disabled="busy" @change="upload($event)">
        </label>

        <div x-show="busy" x-cloak class="mt-4 rounded-xl border border-purple-500/30 bg-purple-500/5 p-4">
            <div class="flex items-center gap-3">
                <svg class="h-5 w-5 shrink-0 animate-spin text-purple-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-bold text-purple-200" x-text="fileName"></p>
                    <p class="mt-0.5 truncate text-xs text-slate-400" x-text="phase === 'processing' ? 'Upload finished. Saving into the library…' : detailText"></p>
                </div>
                <span class="shrink-0 text-sm font-extrabold tabular-nums text-purple-300" x-text="progress + '%'">0%</span>
            </div>
            <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-800">
                <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-500 transition-[width] duration-200" :style="'width:' + progress + '%'"></div>
            </div>
        </div>

        <div x-show="message" x-cloak class="mt-3 rounded-xl border px-4 py-3 text-sm"
             :class="messageOk ? 'border-emerald-500/30 bg-emerald-500/10 text-emerald-300' : 'border-red-500/30 bg-red-500/10 text-red-300'">
            <p x-text="message"></p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3">
        <div class="flex rounded-lg overflow-hidden border border-slate-700/60 text-xs font-bold">
            @foreach(['' => 'All', 'image' => 'Images', 'video' => 'Videos', 'audio' => 'Audio'] as $value => $label)
                <a href="{{ route('admin.media.index', array_filter(['kind' => $value, 'q' => $query])) }}"
                   class="px-4 py-2 transition {{ ($kind ?? '') === $value ? 'bg-purple-600 text-white' : 'bg-slate-800/50 text-slate-400 hover:text-slate-200' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.media.index') }}" class="flex-1 min-w-[12rem]">
            @if($kind)
                <input type="hidden" name="kind" value="{{ $kind }}">
            @endif
            <input type="search" name="q" value="{{ $query }}" placeholder="Search by file name…"
                   class="w-full bg-slate-800/50 border border-slate-700/60 rounded-lg px-4 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500">
        </form>

        <span class="text-xs text-slate-400">{{ $media->total() }} file{{ $media->total() === 1 ? '' : 's' }}</span>
    </div>

    {{-- Grid --}}
    @if($media->isEmpty())
        <div class="py-16 text-center text-slate-400 bg-slate-900/50 border border-slate-800/50 rounded-2xl">
            <p class="font-bold">Nothing here yet</p>
            <p class="text-sm mt-1">Upload a file above and it will show up here, ready to use anywhere in the admin.</p>
        </div>
    @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
            @foreach($media as $item)
                <div class="group relative rounded-xl overflow-hidden border border-slate-800/60 bg-slate-900 aspect-video">
                    @if($item->isImage())
                        <img src="{{ $item->url }}" alt="{{ $item->name }}" loading="lazy" class="w-full h-full object-cover object-center">
                    @elseif($item->isVideo())
                        <video src="{{ $item->url }}" muted playsinline preload="metadata" class="w-full h-full object-cover object-center bg-black"></video>
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-slate-800">
                            <span class="text-3xl">&#9835;</span>
                        </div>
                    @endif

                    <span class="absolute top-1.5 left-1.5 px-1.5 py-0.5 rounded bg-black/70 text-[9px] font-bold text-white uppercase">{{ $item->kind }}</span>

                    <div class="absolute inset-x-0 bottom-0 p-2 bg-gradient-to-t from-black/95 via-black/70 to-transparent">
                        <p class="text-[11px] font-semibold text-white truncate">{{ $item->name }}</p>
                        <p class="text-[10px] text-slate-300">
                            {{ $item->sizeForHumans() }}@if($item->width) · {{ $item->width }}×{{ $item->height }}@endif
                        </p>
                    </div>

                    <div class="absolute top-1.5 right-1.5 flex gap-1 opacity-0 group-hover:opacity-100 transition">
                        <button type="button" @click="copyUrl('{{ $item->url }}')" title="Copy URL"
                                class="p-1.5 bg-black/70 hover:bg-purple-600 rounded-lg text-white text-[10px] font-bold transition">URL</button>
                        <button type="button" @click="remove({{ $item->id }}, '{{ $item->name }}')" title="Delete"
                                class="p-1.5 bg-black/70 hover:bg-red-600 rounded-lg text-white text-[10px] font-bold transition">&#10005;</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2">{{ $media->links() }}</div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function mediaLibrary(config) {
        return {
            storeUrl: config.storeUrl,
            limits: config.limits || {},

            busy: false,
            phase: 'idle',
            progress: 0,
            progressVisible: false,
            sent: 0,
            total: 0,
            fileName: '',
            message: '',
            messageOk: true,

            get detailText() {
                if (!this.progressVisible || !this.total) return 'Starting upload…';
                return this.formatSize(this.sent) + ' of ' + this.formatSize(this.total);
            },

            upload(event) {
                const input = event.target;
                const file = input.files && input.files[0];
                if (!file) return;

                if (this.limits.maxBytes && file.size > this.limits.maxBytes) {
                    this.notify('That file is ' + this.formatSize(file.size) + '. The limit is ' + this.formatSize(this.limits.maxBytes) + '.', false);
                    input.value = '';
                    return;
                }

                this.busy = true;
                this.phase = 'uploading';
                this.progress = 0;
                this.progressVisible = false;
                this.sent = 0;
                this.total = 0;
                this.fileName = file.name;
                this.message = '';

                const form = new FormData();
                form.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', this.storeUrl, true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]').content);

                xhr.upload.addEventListener('progress', (e) => {
                    if (!e.lengthComputable) return;
                    this.progressVisible = true;
                    this.sent = e.loaded;
                    this.total = e.total;
                    this.progress = Math.min(99, Math.round((e.loaded / e.total) * 100));
                    if (e.loaded >= e.total) {
                        this.phase = 'processing';
                        this.progress = 99;
                    }
                });

                xhr.addEventListener('load', () => {
                    let data = null;
                    try { data = JSON.parse(xhr.responseText); } catch (e) { data = null; }

                    input.value = '';

                    if (!data) {
                        this.notify('The server did not answer as expected. Check that you are still logged in.', false);
                        return;
                    }

                    if (xhr.status >= 200 && xhr.status < 300 && data.ok) {
                        this.notify(data.message || 'Uploaded.', true);
                        // Show the new file straight away.
                        setTimeout(() => window.location.reload(), 700);
                        return;
                    }

                    this.notify(this.firstError(data), false);
                });

                xhr.addEventListener('error', () => this.notify('The upload was interrupted. Check your connection.', false));

                xhr.send(form);
            },

            firstError(data) {
                if (data && data.message) return data.message;
                if (data && data.errors) {
                    const first = Object.values(data.errors)[0];
                    if (Array.isArray(first) && first.length) return first[0];
                }
                return 'The upload failed.';
            },

            notify(message, ok) {
                this.busy = false;
                this.phase = 'idle';
                this.progress = 0;
                this.progressVisible = false;
                this.message = message;
                this.messageOk = ok;
            },

            copyUrl(url) {
                const done = () => this.notify('URL copied.', true);

                if (navigator.clipboard) {
                    navigator.clipboard.writeText(url).then(done);
                    return;
                }
                this.notify(url, true);
            },

            remove(id, name) {
                if (!confirm('Delete "' + name + '" from the library?')) return;

                fetch('{{ route('admin.media.index') }}/' + id, {
                    method: 'DELETE',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                })
                    .then((r) => r.json())
                    .then((data) => {
                        this.notify(data.message || 'Done.', data.ok !== false);
                        if (data.ok !== false) setTimeout(() => window.location.reload(), 500);
                    })
                    .catch(() => this.notify('The file could not be deleted.', false));
            },

            formatSize(bytes) {
                if (!bytes) return '0 B';
                if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
                return Math.max(1, Math.round(bytes / 1024)) + ' KB';
            },
        };
    }
</script>
@endsection
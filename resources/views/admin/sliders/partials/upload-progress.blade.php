{{--
    Upload progress for the slider form. Uploaded over XHR so the real byte
    progress can be reported; a plain form post cannot show anything until it
    finishes, which looks like a hang on a large video.
--}}
<div x-show="uploading" x-cloak class="rounded-xl border border-purple-500/30 bg-purple-500/5 p-4">
    <div class="flex items-center gap-3">
        <svg class="h-5 w-5 shrink-0 animate-spin text-purple-400" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
        </svg>

        <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-bold text-purple-200" x-text="statusText"></p>
            <p class="mt-0.5 truncate text-xs text-slate-400" x-text="detailText"></p>
        </div>

        <span class="shrink-0 text-sm font-extrabold tabular-nums text-purple-300"
              x-text="progress + '%'" x-show="progressVisible">100%</span>
    </div>

    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-800">
        <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-500 transition-[width] duration-200"
             :style="'width:' + (progressVisible ? progress : 100) + '%'"
             :class="phase === 'processing' ? 'animate-pulse' : ''"></div>
    </div>

    <p class="mt-2 text-xs text-amber-400/90">
        Keep this page open until the upload finishes.
    </p>
</div>

{{-- Shown when the browser or the server refuses the file. --}}
<div x-show="uploadError" x-cloak
     class="rounded-xl border border-red-500/30 bg-red-500/10 p-4 text-sm text-red-300">
    <p class="font-bold">Upload failed</p>
    <p class="mt-1" x-text="uploadError"></p>
</div>
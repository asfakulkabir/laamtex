{{--
    Reusable media picker.

    Drop this next to any existing file input and the admin gains two ways to
    fill the field: pick something already in the library, or upload from the
    PC. Both write to a hidden "<field>_media_id" input, which
    MediaService::resolve() understands alongside the normal file input, so the
    controller keeps working exactly as before.

    @include('admin.partials.media-picker', [
        'field'  => 'image',        // the file input's name
        'kind'   => 'image',        // image | video | audio, or null for any
        'label'  => 'Image',
        'value'  => $currentPath ?? null,  // path already stored, if any
        'ratio'  => '16/9',         // preview frame hint
        // Inside an Alpine loop (e.g. a product variation row) pass the field
        // name as a raw JS expression, since json_encode would stringify it:
        //   'fieldExpr' => '`variations[${index}][image]`',
        // If the media id lives on a different key than the auto-derived
        // "<field>_media_id", add that too:
        //   'mediaIdFieldExpr' => '`images[${index}][media_id]`',
    ])
--}}
@php
    $pickerConfig = [
        'field' => $field ?? 'image',
        'fieldExpr' => null,
        'mediaIdFieldExpr' => null,
        'kind' => $kind ?? null,
        'label' => $label ?? 'Media',
        'value' => $value ?? null,
        'listUrl' => route('admin.media.list'),
        'storeUrl' => route('admin.media.store'),
        'indexUrl' => route('admin.media.index'),
        'limits' => \App\Http\Controllers\Admin\MediaController::limits(),
    ];

    $pickerJson = json_encode($pickerConfig, JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP);

    if (! empty($fieldExpr)) {
        $pickerJson = str_replace('"fieldExpr":null', '"fieldExpr":' . $fieldExpr, $pickerJson);
    }

    if (! empty($mediaIdFieldExpr)) {
        $pickerJson = str_replace('"mediaIdFieldExpr":null', '"mediaIdFieldExpr":' . $mediaIdFieldExpr, $pickerJson);
    }
@endphp
<div class="media-picker" x-data='mediaPicker({!! $pickerJson !!})' x-on:keydown.escape.window="if (open) close()">

    {{-- The picked library file. The file input stays for direct uploads.
         Both names are bound, because a picker inside an Alpine loop (a product
         variation row) needs the field name to change with the row index. --}}
    <input type="hidden" :name="mediaIdInputName" x-model="mediaId">
    <input type="file" :id="fieldInputName + '_file'" :name="fieldInputName" :accept="acceptForKind" class="sr-only" @change="uploadLocal($event)">

    <div class="flex items-start gap-3">
        <div class="w-28 sm:w-36 shrink-0">
            <div class="relative w-full rounded-lg overflow-hidden bg-slate-800/60 border border-slate-700/50"
                 :style="'aspect-ratio:' + (@js($ratio ?? '16/9') ?? '16/9')">
                <template x-if="! selectedUrl">
                    <div class="absolute inset-0 flex flex-col items-center justify-center gap-1 text-slate-500 text-[10px]">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0021.75 18.75V5.25A2.25 2.25 0 0019.5 3h-15a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 005.25 21z"/></svg>
                        <span>No {{ strtolower($label ?? 'file') }}</span>
                    </div>
                </template>

                <template x-if="selectedUrl && kind !== 'video'">
                    <img :src="selectedUrl" class="absolute inset-0 w-full h-full object-cover object-center" alt="">
                </template>

                <template x-if="selectedUrl && kind === 'video'">
                    <video :src="selectedUrl" class="absolute inset-0 w-full h-full object-cover object-center bg-black" muted playsinline preload="metadata"></video>
                </template>

                <div x-show="busy" x-cloak class="absolute inset-0 bg-slate-900/80 flex flex-col items-center justify-center gap-2 p-2">
                    <svg class="h-6 w-6 animate-spin text-purple-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                    <span class="text-[10px] font-bold text-purple-300" x-text="progress + '%'">0%</span>
                </div>
            </div>
            <div class="mt-1.5 h-1.5 w-full rounded-full bg-slate-800 overflow-hidden" x-show="busy" x-cloak>
                <div class="h-full bg-gradient-to-r from-purple-500 to-pink-500 transition-[width] duration-200" :style="'width:' + progress + '%'"></div>
            </div>
        </div>

        <div class="min-w-0 flex-1 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="openPicker()"
                        class="px-3 py-1.5 bg-purple-500/15 hover:bg-purple-500/25 text-purple-300 border border-purple-500/30 rounded-lg text-xs font-bold transition">
                    Choose from library
                </button>
                <label :for="fieldInputName + '_file'"
                    class="px-3 py-1.5 bg-slate-700/40 hover:bg-slate-700/60 text-slate-200 border border-slate-600/50 rounded-lg text-xs font-bold transition cursor-pointer">
                    Upload from PC
                </label>
                <button type="button" x-show="mediaId || selectedUrl" x-cloak @click="clearPick()"
                        class="px-3 py-1.5 bg-red-500/10 hover:bg-red-500/20 text-red-300 border border-red-500/30 rounded-lg text-xs font-bold transition">
                    Clear
                </button>
            </div>

            <p class="text-[11px] text-slate-500 truncate" x-text="selectedName">@js($value ?? '')</p>

            <div x-show="error" x-cloak class="rounded-lg border border-red-500/30 bg-red-500/10 px-3 py-2 text-xs text-red-300">
                <p x-text="error"></p>
            </div>
        </div>
    </div>

    {{-- Library modal --}}
    <div x-show="open" x-cloak class="fixed inset-0 z-[900] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="close()"></div>

        <div class="relative w-full max-w-5xl max-h-[85vh] bg-slate-900 border border-slate-700/60 rounded-2xl shadow-2xl flex flex-col overflow-hidden">
            <div class="flex items-center gap-3 px-5 py-3 border-b border-slate-700/50">
                <h3 class="text-sm font-bold text-white flex-1">Choose {{ strtolower($label ?? 'media') }}</h3>

                <div class="flex rounded-lg overflow-hidden border border-slate-700/60 text-xs font-bold">
                    <button type="button" @click="tab = 'library'"
                            :class="tab === 'library' ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-slate-200'">Library</button>
                    <button type="button" @click="tab = 'upload'"
                            :class="tab === 'upload' ? 'bg-purple-600 text-white' : 'bg-slate-800 text-slate-400 hover:text-slate-200'">Upload from PC</button>
                </div>

                <button type="button" @click="close()" class="text-slate-400 hover:text-white text-xl leading-none">&times;</button>
            </div>

            {{-- Library tab --}}
            <div x-show="tab === 'library'" class="flex-1 overflow-y-auto p-5">
                <input type="search" x-model="search" @input.debounce.300ms="load()"
                       placeholder="Search by file name…"
                       class="w-full mb-4 bg-slate-800/60 border border-slate-700/60 rounded-lg px-4 py-2 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-purple-500">

                <div x-show="loading" class="py-10 text-center text-slate-400 text-sm">Loading…</div>

                <div x-show="! loading && items.length === 0" class="py-10 text-center text-slate-400 text-sm">
                    Nothing in the library yet. Switch to <strong class="text-slate-200">Upload from PC</strong>.
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                    <template x-for="item in items" :key="item.id">
                        <button type="button" @click="choose(item)"
                                class="group relative rounded-lg overflow-hidden border-2 bg-slate-800 aspect-video"
                                :class="String(item.id) === String(mediaId) ? 'border-purple-500' : 'border-transparent hover:border-purple-400'">
                            <template x-if="item.kind === 'image'">
                                <img :src="item.url" :alt="item.name" loading="lazy" class="w-full h-full object-cover object-center">
                            </template>
                            <template x-if="item.kind !== 'image'">
                                <video :src="item.url" muted playsinline preload="metadata" class="w-full h-full object-cover object-center bg-black"></video>
                            </template>

                            <span class="absolute top-1 left-1 px-1.5 py-0.5 rounded bg-black/70 text-[9px] font-bold text-white uppercase"
                                  x-text="item.kind"></span>

                            <span class="absolute inset-x-0 bottom-0 px-2 py-1 bg-gradient-to-t from-black/90 to-transparent text-[10px] font-semibold text-white truncate"
                                  x-text="item.name"></span>
                        </button>
                    </template>
                </div>
            </div>

            {{-- Upload tab --}}
            <div x-show="tab === 'upload'" class="flex-1 overflow-y-auto p-5">
                <label class="flex flex-col items-center justify-center gap-3 w-full px-4 py-10 rounded-xl border-2 border-dashed cursor-pointer transition"
                       :class="busy ? 'border-purple-500/60 bg-purple-500/5' : 'border-slate-700/60 bg-slate-800/30 hover:border-purple-500/50'">
                    <svg class="h-9 w-9 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    <span class="text-sm font-bold text-slate-200">Click to choose a file</span>
                    <span class="text-xs text-slate-500" x-text="'Max ' + formatSize(limits.maxBytes)"></span>
                    <input type="file" class="sr-only" :accept="acceptForKind" :disabled="busy" @change="uploadLocal($event)">
                </label>

                <div x-show="busy" x-cloak class="mt-4 rounded-xl border border-purple-500/30 bg-purple-500/5 p-4">
                    <div class="flex items-center gap-3">
                        <svg class="h-5 w-5 shrink-0 animate-spin text-purple-400" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-bold text-purple-200" x-text="uploadingName"></p>
                            <p class="mt-0.5 truncate text-xs text-slate-400" x-text="phase === 'processing' ? 'Upload finished. Saving into the library…' : detailText"></p>
                        </div>
                        <span class="shrink-0 text-sm font-extrabold tabular-nums text-purple-300" x-text="progress + '%'">0%</span>
                    </div>
                    <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-800">
                        <div class="h-full rounded-full bg-gradient-to-r from-purple-500 to-pink-500 transition-[width] duration-200" :style="'width:' + progress + '%'"></div>
                    </div>
                </div>

                <div x-show="error" x-cloak class="mt-4 rounded-xl border border-red-500/30 bg-red-500/10 p-3 text-sm text-red-300">
                    <p x-text="error"></p>
                </div>
            </div>

            <div class="px-5 py-3 border-t border-slate-700/50 flex items-center justify-between">
                <a :href="indexUrl" target="_blank" rel="noopener" class="text-xs text-slate-400 hover:text-slate-200 underline decoration-dotted">
                    Open the full media library
                </a>
                <button type="button" @click="close()" class="px-4 py-1.5 bg-slate-700/50 hover:bg-slate-700/70 text-slate-200 rounded-lg text-xs font-bold transition">Close</button>
            </div>
        </div>
    </div>
</div>
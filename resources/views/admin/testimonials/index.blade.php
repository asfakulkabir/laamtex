@extends('layouts.admin')

@section('title', 'Testimonials - laamtex')
@section('page_title', 'Testimonials')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <p class="text-sm text-slate-300">
            Upload customer testimonial images. They appear in the "What Our Customers Say" slider on the home page,
            just below Latest Arrivals. Recommended size:
            <strong class="text-purple-400">{{ \App\Models\Testimonial::RECOMMENDED_WIDTH }} x {{ \App\Models\Testimonial::RECOMMENDED_HEIGHT }} px (landscape, 16:9)</strong>.
            The home page shows two side by side on desktop and one on mobile, so the image must be wider than it is tall.
        </p>
        <a href="{{ route('admin.testimonials.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
            + Upload Testimonial
        </a>
    </div>

    @if($testimonials->contains(fn ($t) => ! $t->isLandscape()))
        <div class="flex items-start gap-3 px-4 py-3 rounded-xl border border-amber-500/30 bg-amber-500/5">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <p class="text-sm text-amber-200/90">
                Some images are not landscape, so they will be letterboxed in the slider. Replace them with a
                <strong>{{ \App\Models\Testimonial::RECOMMENDED_WIDTH }} x {{ \App\Models\Testimonial::RECOMMENDED_HEIGHT }} px</strong> image.
            </p>
        </div>
    @endif

    @if($testimonials->count() > 1)
        <div class="flex justify-end">
            <button type="button" id="saveOrderBtn"
                    class="hidden px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow transition-all">
                Save Order
            </button>
        </div>
    @endif

    @if($testimonials->isEmpty())
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg">
            <div class="px-8 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-white text-lg mt-4">No testimonials yet</h3>
                <p class="text-sm text-slate-300 mt-1">Upload your first customer testimonial image to get started.</p>
                <a href="{{ route('admin.testimonials.create') }}" class="inline-block mt-5 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg text-sm font-bold">
                    + Upload Testimonial
                </a>
            </div>
        </div>
    @else
        <div id="sortable-testimonials" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($testimonials as $testimonial)
                <div data-id="{{ $testimonial->id }}"
                     class="group bg-gradient-to-br from-slate-900 to-slate-950 border {{ $testimonial->isLandscape() ? 'border-slate-800/50' : 'border-amber-500/40' }} rounded-2xl shadow-lg overflow-hidden relative">
                    <div class="relative aspect-video bg-slate-800/50">
                        <img src="{{ $testimonial->image_url }}" alt="Customer testimonial"
                             @if($testimonial->width) width="{{ $testimonial->width }}" height="{{ $testimonial->height }}" @endif
                             class="w-full h-full object-contain">
                        <div class="absolute inset-0 bg-slate-950/0 group-hover:bg-slate-950/40 transition-colors"></div>
                        <span class="drag-handle absolute top-2 left-2 cursor-grab active:cursor-grabbing p-1.5 rounded-lg bg-slate-900/80 text-slate-300 opacity-0 group-hover:opacity-100 transition-opacity"
                              title="Drag to reorder">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M9 4h2v2H9V4zm4 0h2v2h-2V4zM9 8h2v2H9V8zm4 0h2v2h-2V8zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2z"/>
                            </svg>
                        </span>
                        @if(!$testimonial->is_active)
                            <span class="absolute top-2 right-2 px-2 py-1 rounded-lg bg-slate-900/85 text-slate-300 text-[10px] font-bold uppercase tracking-wider">Hidden</span>
                        @endif
                        <span class="absolute bottom-2 left-2 px-2 py-1 rounded-lg bg-slate-900/85 text-[10px] font-bold uppercase tracking-wider
                                     {{ $testimonial->isLandscape() ? 'text-emerald-400' : 'text-amber-400' }}">
                            {{ $testimonial->orientation }}
                        </span>
                    </div>

                    <div class="p-3 space-y-2">
                        <div class="flex items-center justify-between gap-2 text-[11px]">
                            <span class="font-mono font-semibold text-slate-300">{{ $testimonial->dimensions }}</span>
                            <span class="font-semibold text-slate-500">{{ $testimonial->size_label }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}"
                               class="flex-1 text-center px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/20 rounded-lg text-xs font-bold transition">
                                Replace
                            </a>
                            <form action="{{ route('admin.testimonials.toggle', $testimonial->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="{{ $testimonial->is_active ? 'Hide on site' : 'Show on site' }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold border transition {{ $testimonial->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20 hover:bg-slate-500/20' }}">
                                    {{ $testimonial->is_active ? 'Shown' : 'Hidden' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this testimonial image?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold border transition bg-pink-500/10 text-pink-400 border-pink-500/20 hover:bg-pink-500/20">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var grid = document.getElementById('sortable-testimonials');
        if (!grid) return;

        var saveBtn = document.getElementById('saveOrderBtn');

        new Sortable(grid, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'opacity-40',
            onEnd: function () {
                if (saveBtn) saveBtn.classList.remove('hidden');
            }
        });

        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                var order = [];
                grid.querySelectorAll('[data-id]').forEach(function (card) {
                    order.push(card.getAttribute('data-id'));
                });

                var btn = this;
                btn.disabled = true;
                btn.textContent = 'Saving...';

                fetch('{{ route('admin.testimonials.reorder') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ order: order })
                })
                .then(function (r) { return r.json(); })
                .then(function () {
                    btn.textContent = 'Saved!';
                    setTimeout(function () {
                        btn.classList.add('hidden');
                        btn.textContent = 'Save Order';
                        btn.disabled = false;
                    }, 1200);
                })
                .catch(function () {
                    btn.textContent = 'Error!';
                    btn.disabled = false;
                    setTimeout(function () { btn.textContent = 'Save Order'; }, 2000);
                });
            });
        }
    });
</script>
@endsection

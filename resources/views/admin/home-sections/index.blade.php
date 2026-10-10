@extends('layouts.admin')

@section('title', 'Home Content - laamtex')
@section('page_title', 'Home Content Sections')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <p class="text-sm text-slate-300">
            Build the extra product blocks on the home page. Each section picks a category and shows that category's
            products, either as a scrolling slider or a simple grid. Child categories are included automatically,
            and you choose how many products to show. Drag the handle to reorder, then press Save Order.
        </p>
        <a href="{{ route('admin.home-sections.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
            + Add Section
        </a>
    </div>

    @if($sections->count() > 1)
        <div class="flex justify-end">
            <button type="button" id="saveOrderBtn"
                    class="hidden px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow transition-all">
                Save Order
            </button>
        </div>
    @endif

    @if($sections->isEmpty())
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg">
            <div class="px-8 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-white text-lg mt-4">No home sections yet</h3>
                <p class="text-sm text-slate-300 mt-1">Add a section to show a category's products on the home page.</p>
                <a href="{{ route('admin.home-sections.create') }}" class="inline-block mt-5 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg text-sm font-bold">
                    + Add Section
                </a>
            </div>
        </div>
    @else
        <div id="sortable-sections" class="space-y-4">
            @foreach($sections as $section)
                <div data-id="{{ $section->id }}"
                     class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg p-5 flex flex-col gap-4 sm:flex-row sm:items-center">
                    <span class="drag-handle cursor-grab active:cursor-grabbing text-slate-500 hover:text-purple-400 transition shrink-0 self-start sm:self-auto"
                          title="Drag to reorder">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M9 4h2v2H9V4zm4 0h2v2h-2V4zM9 8h2v2H9V8zm4 0h2v2h-2V8zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2zm-4 4h2v2H9v-2zm4 0h2v2h-2v-2z"/>
                        </svg>
                    </span>

                    <div class="flex-1 min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h3 class="font-bold text-white text-base">{{ $section->title }}</h3>
                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider
                                         {{ $section->isSlider() ? 'bg-purple-500/10 text-purple-400' : 'bg-sky-500/10 text-sky-400' }}">
                                {{ $section->isSlider() ? 'Slider' : 'Grid' }}
                            </span>
                            @unless($section->is_active)
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider bg-slate-500/15 text-slate-400">Hidden</span>
                            @endunless
                        </div>

                        @if($section->subtitle)
                            <p class="text-xs text-slate-400">{{ $section->subtitle }}</p>
                        @endif

                        <p class="text-xs text-slate-500">
                            Category:
                            <span class="font-semibold {{ $section->category ? 'text-slate-300' : 'text-slate-500' }}">
                                {{ $section->category?->name ?? 'All categories' }}
                            </span>
                            &middot; Shows up to
                            <span class="font-semibold text-slate-300">{{ $section->effective_limit }}</span>
                            {{ Str::plural('product', $section->effective_limit) }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <a href="{{ route('admin.home-sections.edit', $section->id) }}"
                           class="flex-1 sm:flex-none text-center px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/20 rounded-lg text-xs font-bold transition">
                            Edit
                        </a>
                        <form action="{{ route('admin.home-sections.toggle', $section->id) }}" method="POST">
                            @csrf
                            <button type="submit" title="{{ $section->is_active ? 'Hide on home page' : 'Show on home page' }}"
                                    class="px-3 py-1.5 rounded-lg text-xs font-bold border transition {{ $section->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20 hover:bg-slate-500/20' }}">
                                {{ $section->is_active ? 'Shown' : 'Hidden' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.home-sections.destroy', $section->id) }}" method="POST"
                              onsubmit="return confirm('Delete this home section?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold border transition bg-pink-500/10 text-pink-400 border-pink-500/20 hover:bg-pink-500/20">
                                Delete
                            </button>
                        </form>
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
        var list = document.getElementById('sortable-sections');
        if (!list) return;

        var saveBtn = document.getElementById('saveOrderBtn');

        new Sortable(list, {
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
                list.querySelectorAll('[data-id]').forEach(function (row) {
                    order.push(row.getAttribute('data-id'));
                });

                var btn = this;
                btn.disabled = true;
                btn.textContent = 'Saving...';

                fetch('{{ route('admin.home-sections.reorder') }}', {
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

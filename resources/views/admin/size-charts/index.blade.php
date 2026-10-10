@extends('layouts.admin')

@section('title', 'Size Charts - laamtex')
@section('page_title', 'Size Charts')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <p class="text-sm text-slate-300">
            Upload the measurement charts you reuse across products. Each chart has a title and an image, and you can
            attach one chart (or none) to any product from the product add / edit screen. Customers open it from a small
            link above the Buy Now button. Recommended size:
            <strong class="text-purple-400">{{ \App\Models\SizeChart::RECOMMENDED_WIDTH }} x {{ \App\Models\SizeChart::RECOMMENDED_HEIGHT }} px</strong>.
        </p>
        <a href="{{ route('admin.size-charts.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
            + Add Size Chart
        </a>
    </div>

    @if($sizeCharts->isEmpty())
        <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg">
            <div class="px-8 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-purple-500/10 flex items-center justify-center">
                    <svg class="w-8 h-8 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 6v12m6-12v12M6 9h12M6 15h12"/>
                    </svg>
                </div>
                <h3 class="font-bold text-white text-lg mt-4">No size charts yet</h3>
                <p class="text-sm text-slate-300 mt-1">Add your first size chart image to get started.</p>
                <a href="{{ route('admin.size-charts.create') }}" class="inline-block mt-5 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 text-white rounded-lg text-sm font-bold">
                    + Add Size Chart
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($sizeCharts as $sizeChart)
                <div class="group bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden relative">
                    <div class="relative aspect-[4/5] bg-slate-800/50">
                        <img src="{{ $sizeChart->image_url }}" alt="{{ $sizeChart->title }}" loading="lazy"
                             @if($sizeChart->width) width="{{ $sizeChart->width }}" height="{{ $sizeChart->height }}" @endif
                             class="w-full h-full object-contain">
                        @if(!$sizeChart->is_active)
                            <span class="absolute top-2 right-2 px-2 py-1 rounded-lg bg-slate-900/85 text-slate-300 text-[10px] font-bold uppercase tracking-wider">Hidden</span>
                        @endif
                    </div>

                    <div class="p-4 space-y-3">
                        <div>
                            <h3 class="font-bold text-white text-base leading-tight">{{ $sizeChart->title }}</h3>
                            <div class="mt-1.5 flex flex-wrap items-center gap-2 text-[11px]">
                                <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-mono font-semibold text-slate-200">{{ $sizeChart->dimensions }}</span>
                                <span class="px-2 py-1 rounded-lg bg-slate-800/60 font-semibold text-slate-300">{{ $sizeChart->size_label }}</span>
                            </div>
                        </div>

                        <p class="text-xs text-slate-400">
                            Used by
                            <span class="font-bold {{ $sizeChart->products_count > 0 ? 'text-purple-400' : 'text-slate-500' }}">
                                {{ $sizeChart->products_count }} {{ Str::plural('product', $sizeChart->products_count) }}
                            </span>
                        </p>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.size-charts.edit', $sizeChart->id) }}"
                               class="flex-1 text-center px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 border border-purple-500/20 rounded-lg text-xs font-bold transition">
                                Edit
                            </a>
                            <form action="{{ route('admin.size-charts.toggle', $sizeChart->id) }}" method="POST">
                                @csrf
                                <button type="submit" title="{{ $sizeChart->is_active ? 'Hide on site' : 'Show on site' }}"
                                        class="px-3 py-1.5 rounded-lg text-xs font-bold border transition {{ $sizeChart->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20 hover:bg-emerald-500/20' : 'bg-slate-500/10 text-slate-400 border-slate-500/20 hover:bg-slate-500/20' }}">
                                    {{ $sizeChart->is_active ? 'Shown' : 'Hidden' }}
                                </button>
                            </form>
                            <form action="{{ route('admin.size-charts.destroy', $sizeChart->id) }}" method="POST"
                                  onsubmit="return confirm('Delete this size chart?{{ $sizeChart->products_count > 0 ? ' It is used by ' . $sizeChart->products_count . ' ' . Str::plural('product', $sizeChart->products_count) . ', which will no longer show a size chart link.' : '' }}');">
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

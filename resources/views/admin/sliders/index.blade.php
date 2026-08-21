@extends('layouts.admin')

@section('title', 'Home Slider - Outfitt')
@section('page_title', 'Home Page Slider')

@section('content')
<div class="max-w-5xl space-y-6">
    
    <div class="flex justify-between items-center">
        <p class="text-sm text-slate-300">Manage the hero slider images on the home page. Recommended image size: <strong class="text-purple-400">1920 x 800 px</strong>.</p>
        <a href="{{ route('admin.sliders.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all active:scale-[0.98]">
            + Add Slider Image
        </a>
    </div>

    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <table class="w-full text-left border-collapse text-sm">
            <thead>
                <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                    <th class="px-8 py-4">Preview</th>
                    <th class="px-8 py-4">Title</th>
                    <th class="px-8 py-4">Order</th>
                    <th class="px-8 py-4">Status</th>
                    <th class="px-8 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/30 text-slate-400">
                @forelse($sliders as $slider)
                    <tr class="hover:bg-slate-800/30 transition-all duration-150">
                        <td class="px-8 py-4">
                            <img src="{{ asset('storage/' . $slider->image) }}" alt="{{ $slider->title ?? 'Slider' }}"
                                 class="w-32 h-16 object-cover rounded-lg border border-slate-700/50">
                        </td>
                        <td class="px-8 py-4 font-bold text-slate-200">{{ $slider->title ?: '—' }}</td>
                        <td class="px-8 py-4 text-slate-300">{{ $slider->sort_order }}</td>
                        <td class="px-8 py-4">
                            @if($slider->is_active)
                                <span class="text-emerald-400 bg-emerald-500/10 px-2.5 py-1 rounded-full text-xs font-bold">Active</span>
                            @else
                                <span class="text-red-400 bg-red-500/10 px-2.5 py-1 rounded-full text-xs font-bold">Inactive</span>
                            @endif
                        </td>
                        <td class="px-8 py-4 text-right">
                            <div class="flex justify-end items-center space-x-2">
                                <a href="{{ route('admin.sliders.edit', $slider->id) }}" class="p-2 text-purple-400 hover:text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition">
                                    Edit
                                </a>
                                <form action="{{ route('admin.sliders.destroy', $slider->id) }}" method="POST" onsubmit="return confirm('Delete this slider image?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded-lg transition">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-8 py-12 text-center text-slate-300">No slider images yet. Click "Add Slider Image" to get started.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

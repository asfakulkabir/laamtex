@extends('layouts.admin')

@section('title', 'Manage Attributes - laamtex')
@section('page_title', 'Attributes Management')

@section('content')
<div class="space-y-6">

    <div class="flex justify-between items-center">
        <p class="text-sm text-slate-300">Global attributes are shared by every product. Their values build product variations.</p>
    </div>

    <div class="space-y-6">
        @forelse($attributes as $attribute)
            <div class="bg-slate-900 border border-slate-800/50 rounded-2xl shadow-lg shadow-black/10 overflow-hidden">
                <div class="px-8 py-4 bg-slate-800/30 border-b border-slate-800/50 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="text-lg">🏷️</span>
                        <div>
                            <h3 class="font-bold text-white">{{ $attribute->name }}</h3>
                            <p class="text-xs text-slate-400 font-mono">{{ $attribute->slug }}</p>
                        </div>
                        <span class="px-2 py-1 bg-purple-500/10 text-purple-400 border border-purple-500/30 text-xs rounded font-semibold">{{ $attribute->display_type }}</span>
                        <span class="px-2 py-1 bg-slate-800/50 text-slate-400 text-xs rounded font-semibold">
                            {{ $attribute->product_attributes_count }} product(s)
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" onclick="document.getElementById('edit-attr-{{ $attribute->id }}').classList.toggle('hidden')"
                                class="px-4 py-2 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 rounded-lg text-sm font-bold transition">
                            Edit
                        </button>
                        <form action="{{ route('admin.attributes.destroy', $attribute->id) }}" method="POST"
                              onsubmit="return confirm('Delete this attribute and all of its values?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 rounded-lg text-sm font-bold transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>

                <div id="edit-attr-{{ $attribute->id }}" class="hidden px-8 py-4 border-b border-slate-800/50">
                    <form action="{{ route('admin.attributes.update', $attribute->id) }}" method="POST" class="flex flex-wrap items-end gap-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Name</label>
                            <input type="text" name="name" value="{{ old('name', $attribute->name) }}" required
                                   class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Type</label>
                            <select name="type" class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                                @foreach(['select' => 'Select', 'color' => 'Color swatch', 'image' => 'Image swatch', 'button' => 'Button'] as $value => $label)
                                    <option value="{{ $value }}" @selected($attribute->type === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Order by</label>
                            <select name="order_by" class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                                @foreach(['custom' => 'Custom', 'name' => 'Name', 'id' => 'Term order'] as $value => $label)
                                    <option value="{{ $value }}" @selected($attribute->order_by === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold transition">
                            Update
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="bg-slate-800/50 border-b border-slate-800/50 text-xs font-bold uppercase text-slate-300">
                                <th class="px-8 py-3">Name</th>
                                <th class="px-8 py-3">Slug</th>
                                @if($attribute->type === 'color')
                                    <th class="px-8 py-3">Swatch</th>
                                @endif
                                <th class="px-8 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/50 text-slate-400">
                            @forelse($attribute->values as $value)
                                <tr class="hover:bg-slate-800/30 transition">
                                    <td class="px-8 py-3 font-semibold text-white">{{ $value->name }}</td>
                                    <td class="px-8 py-3 text-sm font-mono text-slate-300">{{ $value->slug }}</td>
                                    @if($attribute->type === 'color')
                                        <td class="px-8 py-3">
                                            <span class="inline-block h-6 w-6 rounded-full border border-slate-700"
                                                  style="background-color: {{ $value->swatch_color }}"></span>
                                        </td>
                                    @endif
                                    <td class="px-8 py-3">
                                        <div class="flex justify-end items-center gap-3">
                                            <form action="{{ route('admin.attributes.values.update', [$attribute->id, $value->id]) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-center gap-2">
                                                @csrf
                                                @method('PUT')
                                                <input type="text" name="name" value="{{ $value->name }}" required
                                                       class="px-3 py-1.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                                                @if($attribute->type === 'color')
                                                    @include('admin.partials.color-picker', ['name' => 'color_code', 'value' => $value->color_code, 'inputClass' => 'w-24'])
                                                @endif
                                                @if($attribute->type === 'image')
                                                    @if($value->image)
                                                        <img src="{{ $value->image_url }}" alt="{{ $value->name }}" class="h-8 w-8 rounded-full object-cover border border-slate-700">
                                                        <label class="flex items-center gap-1 text-[11px] text-slate-300 cursor-pointer">
                                                            <input type="checkbox" name="remove_image" value="1" class="rounded bg-slate-800 border-slate-700">
                                                            Remove
                                                        </label>
                                                    @endif
                                                    <input type="file" name="image" accept="image/*"
                                                           class="text-[11px] text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white text-xs">
                                                @endif
                                                <button type="submit" class="px-3 py-1.5 bg-purple-500/10 hover:bg-purple-500/20 text-purple-400 rounded-lg text-xs font-bold transition">Save</button>
                                            </form>
                                            <form action="{{ route('admin.attributes.values.destroy', [$attribute->id, $value->id]) }}" method="POST"
                                                  onsubmit="return confirm('Delete this value?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="px-3 py-1.5 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 rounded-lg text-xs font-bold transition">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-8 py-8 text-center text-slate-300">No values yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-4 border-t border-slate-800/50">
                    <form action="{{ route('admin.attributes.values.store', $attribute->id) }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Add value</label>
                            <input type="text" name="name" placeholder="e.g. Navy" required
                                   class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                        </div>
                        @if($attribute->type === 'color')
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Color code</label>
                                @include('admin.partials.color-picker', ['name' => 'color_code', 'value' => '', 'inputClass' => 'w-32'])
                            </div>
                        @endif
                        @if($attribute->type === 'image')
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Image</label>
                                <input type="file" name="image" accept="image/*"
                                       class="text-[11px] text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white text-xs">
                            </div>
                        @endif
                        <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold transition">
                            Add Value
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-slate-900 border border-slate-800/50 rounded-2xl p-12 text-center text-slate-300">
                No attributes yet. Create one below.
            </div>
        @endforelse
    </div>

    <div class="bg-slate-900 border border-slate-800/50 rounded-2xl shadow-lg shadow-black/10 p-8">
        <h3 class="font-bold text-white mb-4">Create Attribute</h3>
        <form action="{{ route('admin.attributes.store') }}" method="POST" class="flex flex-wrap items-end gap-4">
            @csrf

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Name</label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Material" required
                       class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Type</label>
                <select name="type" class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                    <option value="select">Select</option>
                    <option value="color">Color swatch</option>
                    <option value="image">Image swatch</option>
                    <option value="button">Button</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Order by</label>
                <select name="order_by" class="px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-lg text-white text-sm focus:border-purple-500 focus:outline-none">
                    <option value="name">Name</option>
                    <option value="custom">Custom</option>
                    <option value="id">Term order</option>
                </select>
            </div>

            <button type="submit" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold transition">
                Create Attribute
            </button>
        </form>
    </div>
</div>
@endsection

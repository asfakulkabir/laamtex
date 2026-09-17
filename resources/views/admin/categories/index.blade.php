@extends('layouts.admin')

@section('title', 'Manage Categories - laamtex')
@section('page_title', 'Categories Management')

@section('content')
<div class="space-y-6">
    
    <!-- Header Actions -->
    <div class="flex justify-between items-center">
        <p class="text-sm text-slate-300">Add, edit, or delete categories in your catalog catalog.</p>
        <a href="{{ route('admin.categories.create') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all active:scale-[0.98]">
            + Add New Category
        </a>
    </div>

    <!-- Table Card -->
    <div class="bg-slate-900 border border-slate-800/50 rounded-2xl shadow-lg shadow-black/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/50 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-8 py-4">Image</th>
                        <th class="px-8 py-4">Name</th>
                        <th class="px-8 py-4">Slug</th>
                        <th class="px-8 py-4">Parent Category</th>
                        <th class="px-8 py-4">Group Name</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/50 text-slate-400">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-8 py-4">
                                @if($category->image)
                                    <img src="{{ Storage::url($category->image) }}" alt="{{ $category->name }}" class="h-10 w-10 object-cover rounded-lg border border-slate-800/50">
                                @else
                                    <div class="h-10 w-10 bg-purple-500/10 text-purple-400 rounded-lg flex items-center justify-center text-sm font-bold">No Img</div>
                                @endif
                            </td>
                            <td class="px-8 py-4 font-bold text-white">{{ $category->name }}</td>
                            <td class="px-8 py-4 text-sm font-mono text-slate-300">{{ $category->slug }}</td>
                            <td class="px-8 py-4">
                                @if($category->parent)
                                    <span class="px-2 py-1 bg-slate-800/50 text-slate-300 text-sm rounded font-semibold">{{ $category->parent->name }}</span>
                                @else
                                    <span class="text-slate-600 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-sm font-semibold text-purple-400">{{ $category->group_name ?: '-' }}</td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.categories.edit', $category->id) }}" class="p-2 text-purple-400 hover:text-purple-400 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this category? All child subcategories parent will be set to NULL.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 text-pink-400 hover:text-pink-400 bg-pink-500/10 hover:bg-pink-500/20 rounded-lg transition">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-8 py-12 text-center text-slate-300">No categories created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        @if($categories->hasPages())
            <div class="px-8 py-4 border-t border-slate-800/50">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

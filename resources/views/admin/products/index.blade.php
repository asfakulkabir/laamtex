@extends('layouts.admin')

@section('title', 'Manage Products - laamtex')
@section('page_title', 'Products Management')

@section('content')
<div class="space-y-6">

    @if(session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 px-4 py-3 rounded-lg text-sm font-semibold">{{ session('success') }}</div>
    @endif

    <!-- Header Actions & Search Filters -->
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <!-- Search and Filters Form -->
        <form action="{{ route('admin.products.index') }}" method="GET" class="flex flex-wrap items-center gap-3 flex-grow">
            <div class="relative w-64">
                <input type="text" name="search" placeholder="Search by name..." value="{{ request('search') }}"
                       class="w-full bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 pl-9 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all text-slate-200 placeholder-slate-600">
                <svg class="w-4 h-4 text-slate-300 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>

            <select name="type" class="bg-slate-900/80 border border-slate-700/50 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 text-slate-200">
                <option value="">-- All Product Types --</option>
                <option value="simple" {{ request('type') === 'simple' ? 'selected' : '' }}>Simple Products</option>
                <option value="variable" {{ request('type') === 'variable' ? 'selected' : '' }}>Variable Products</option>
            </select>

            <button type="submit" class="px-4 py-2 bg-slate-700/50 hover:bg-slate-700/50 text-white rounded-lg text-sm font-semibold transition">
                Filter
            </button>
            @if(request()->filled('search') || request()->filled('type'))
                <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-pink-400 hover:underline">Clear Filters</a>
            @endif
        </form>

        <div class="flex items-center gap-3 flex-wrap">
            <button id="saveOrderBtn"
                    class="hidden px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow transition-all active:scale-[0.98]">
                Save Order
            </button>
            <a href="{{ route('admin.products.export-csv') }}"
               class="px-5 py-2.5 bg-blue-600/20 hover:bg-blue-600/30 text-blue-400 border border-blue-500/30 rounded-lg text-sm font-bold shadow transition-all whitespace-nowrap">
                ⬇ Export CSV
            </a>
            <label class="px-5 py-2.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-400 border border-emerald-500/30 rounded-lg text-sm font-bold shadow transition-all cursor-pointer whitespace-nowrap">
                ⬆ Import CSV
                <input type="file" accept=".csv,.txt" class="hidden" id="csvFileInput">
            </label>
            <a href="{{ route('admin.products.create') }}" class="flex-shrink-0 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold shadow transition-all active:scale-[0.98] text-center">
                + Add New Product
            </a>
        </div>

        <!-- Hidden Import Form -->
        <form id="importForm" action="{{ route('admin.products.import-csv') }}" method="POST" enctype="multipart/form-data" class="hidden">
            @csrf
            <input type="file" name="csv_file" id="csvFileInputHidden" accept=".csv,.txt">
        </form>
    </div>

    <!-- Table Card -->
    <div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-slate-800/30 border-b border-slate-800/50 text-sm font-bold uppercase text-slate-300">
                        <th class="px-8 py-4 w-10">
                            <svg class="w-4 h-4 text-slate-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                        </th>
                        <th class="px-8 py-4">Image</th>
                        <th class="px-8 py-4">Name</th>
                        <th class="px-8 py-4">Categories</th>
                        <th class="px-8 py-4">Price</th>
                        <th class="px-8 py-4">Stock</th>
                        <th class="px-8 py-4">Status</th>
                        <th class="px-8 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody id="sortable-products" class="divide-y divide-slate-800/30 text-slate-400">
                    @forelse($products as $product)
                        <tr data-id="{{ $product->id }}" class="hover:bg-slate-800/30 transition-all duration-150 cursor-grab active:cursor-grabbing">
                            <td class="px-8 py-4">
                                <span class="drag-handle inline-flex items-center justify-center text-slate-500 hover:text-slate-300 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 6h2v2H8V6zm6 0h2v2h-2V6zM8 11h2v2H8v-2zm6 0h2v2h-2v-2zm-6 5h2v2H8v-2zm6 0h2v2h-2v-2z"/></svg>
                                </span>
                            </td>
                            <td class="px-8 py-4">
                                @php
                                    $featuredImg = $product->images->where('is_featured', true)->first() ?? $product->images->first();
                                @endphp
                                @if($featuredImg)
                                    <img src="{{ Storage::url($featuredImg->image) }}" alt="{{ $product->name }}" class="h-10 w-10 object-cover rounded-lg border border-slate-800/50">
                                @else
                                    <div class="h-10 w-10 bg-purple-500/10 text-purple-400 rounded-lg flex items-center justify-center text-sm font-bold">No Img</div>
                                @endif
                            </td>
                            <td class="px-8 py-4">
                                <div class="font-bold text-slate-200 leading-tight">{{ $product->name }}</div>
                                <div class="flex items-center space-x-2 mt-1.5">
                                    <span class="px-2 py-0.5 rounded text-sm uppercase font-bold tracking-wider {{ $product->product_type === 'variable' ? 'bg-purple-500/20 text-purple-400' : 'bg-slate-800/50 text-slate-300' }}">
                                        {{ $product->product_type }}
                                    </span>
                                    @if($product->is_featured)
                                        <span class="px-2 py-0.5 rounded text-sm uppercase font-bold tracking-wider bg-pink-500/20 text-pink-400">Featured</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                <div class="flex flex-wrap gap-1 max-w-[200px]">
                                    @forelse($product->categories as $cat)
                                        <span class="px-2 py-0.5 bg-slate-800/50 text-slate-300 rounded text-sm font-semibold">{{ $cat->name }}</span>
                                    @empty
                                        <span class="text-slate-600 text-sm">-</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-8 py-4">
                                @if($product->product_type === 'simple')
                                    @if($product->sale_price !== null)
                                        <div>
                                            <span class="font-bold text-slate-200">৳{{ number_format($product->sale_price, 2) }}</span>
                                            <span class="text-sm line-through text-slate-600 ml-1">৳{{ number_format($product->regular_price, 2) }}</span>
                                        </div>
                                    @else
                                        <span class="font-bold text-slate-200">৳{{ number_format($product->regular_price, 2) }}</span>
                                    @endif
                                @else
                                    @php
                                        $minPrice = $product->variations->min('price');
                                        $maxPrice = $product->variations->max('price');
                                    @endphp
                                    @if($minPrice !== null && $maxPrice !== null)
                                        <span class="font-bold text-purple-400">
                                            @if($minPrice == $maxPrice)
                                                ৳{{ number_format($minPrice, 2) }}
                                            @else
                                                ৳{{ number_format($minPrice, 2) }} - ৳{{ number_format($maxPrice, 2) }}
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-slate-600 text-sm italic">No variations price</span>
                                    @endif
                                @endif
                            </td>
                            <td class="px-8 py-4 font-semibold">
                                @if($product->stock_quantity > 0)
                                    <span class="text-emerald-400">{{ $product->stock_quantity }} units</span>
                                @else
                                    <span class="text-pink-400 font-bold bg-pink-500/10 border border-pink-500/20 px-2 py-0.5 rounded text-sm">Out of Stock</span>
                                @endif
                            </td>
                            <td class="px-8 py-4">
                                <span class="px-2.5 py-1 rounded-full text-sm font-semibold border uppercase tracking-wider {{ $product->is_active ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : 'bg-slate-800/50 text-slate-300 border-slate-700/50' }}">
                                    {{ $product->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-right">
                                <div class="flex justify-end items-center space-x-2">
                                    <a href="{{ route('admin.products.edit', $product->id) }}" class="p-2 text-purple-400 hover:text-purple-300 bg-purple-500/10 hover:bg-purple-500/20 rounded-lg transition">
                                        Edit
                                    </a>
                                    <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this product? All image files and variations will be permanently deleted.');">
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
                            <td colspan="8" class="px-8 py-12 text-center text-slate-300">No products created yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        @if($products->hasPages())
            <div class="px-8 py-4 border-t border-slate-800/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tbody = document.getElementById('sortable-products');
        if (!tbody) return;

        var sortable = new Sortable(tbody, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'bg-slate-700/50',
            onEnd: function () {
                document.getElementById('saveOrderBtn').classList.remove('hidden');
            }
        });

        document.getElementById('csvFileInput').addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                var hiddenInput = document.getElementById('csvFileInputHidden');
                var dataTransfer = new DataTransfer();
                dataTransfer.items.add(this.files[0]);
                hiddenInput.files = dataTransfer.files;
                document.getElementById('importForm').submit();
            }
        });

        document.getElementById('saveOrderBtn').addEventListener('click', function () {
            var order = [];
            tbody.querySelectorAll('tr[data-id]').forEach(function (row) {
                order.push(row.getAttribute('data-id'));
            });

            var btn = this;
            btn.disabled = true;
            btn.textContent = 'Saving...';

            fetch('{{ route('admin.products.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ order: order })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                btn.textContent = 'Saved!';
                btn.classList.remove('bg-emerald-600', 'hover:bg-emerald-700');
                btn.classList.add('bg-emerald-700');
                setTimeout(function () {
                    btn.classList.add('hidden');
                    btn.classList.remove('bg-emerald-700');
                    btn.classList.add('bg-emerald-600', 'hover:bg-emerald-700');
                    btn.textContent = 'Save Order';
                    btn.disabled = false;
                }, 1500);
            })
            .catch(function () {
                btn.textContent = 'Error!';
                btn.disabled = false;
                setTimeout(function () { btn.textContent = 'Save Order'; }, 2000);
            });
        });
    });
</script>
@endsection

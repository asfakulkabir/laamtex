@extends('layouts.admin')

@section('title', 'Edit Product - laamtex')
@section('page_title', 'Edit Product')

@section('content')
<div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg" x-data="productForm()">
    
    <div class="mb-8 pb-4 border-b border-slate-800/50 flex justify-between items-center">
        <div>
            <h3 class="font-bold text-white text-lg">Modify Product</h3>
            <p class="text-sm text-slate-300 mt-1">Edit core properties, adjust stock/prices, manage variations, or upload catalog images.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button type="submit" form="product-form"
                    class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Update Product
            </button>
            <div class="text-sm font-semibold px-3 py-1 bg-purple-500/10 text-purple-400 rounded border border-purple-500/20 uppercase">
                {{ $product->product_type }}
            </div>
        </div>
    </div>

    <form id="product-form" action="{{ route('admin.products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        {{-- Every validation error is listed here. Without this a rejected
             field (an oversized variation image, a bad SKU, ...) silently
             discards the submission and the admin has no idea why. --}}
        @php
            $detailedVariationErrors = $errors->get('variations');
            $salePriceErrors = collect($errors->getMessages())
                ->filter(fn ($messages, $key) => str_starts_with($key, 'variations.') && str_ends_with($key, '.sale_price'));

            // "variations.3.image" is meaningless to an admin, so the row and
            // the field are named instead.
            $variationFieldLabels = [
                'image' => 'image', 'sku' => 'SKU', 'regular_price' => 'regular price',
                'stock_quantity' => 'stock quantity', 'weight_value' => 'weight',
                'stock_status' => 'stock status', 'status' => 'status',
            ];

            $otherErrors = [];

            foreach ($errors->getMessages() as $key => $messages) {
                if ($key === 'variations' || $salePriceErrors->has($key)) {
                    continue;
                }

                foreach ($messages as $message) {
                    if (preg_match('/^variations\.(\d+)\.([a-z_]+)$/', $key, $m)) {
                        $label = $variationFieldLabels[$m[2]] ?? str_replace('_', ' ', $m[2]);
                        // Strip the raw key from the tail of the message.
                        $message = preg_replace('/\s*field\s/mi', ' ' . $label . ' ', $message);
                        $otherErrors[] = 'Row ' . ((int) $m[1] + 1) . ' — ' . $message;
                    } else {
                        $otherErrors[] = $message;
                    }
                }
            }
        @endphp
        @if($otherErrors || $detailedVariationErrors)
            <div class="px-4 py-3 rounded-xl border border-red-500/30 bg-red-500/5 space-y-1">
                <p class="text-sm font-bold text-red-300">Please fix the following to save this product</p>
                <ul class="list-disc list-inside space-y-0.5 text-xs text-red-200/90">
                    @foreach($otherErrors as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Columns -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Product Title <span class="text-pink-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required x-model="name"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
                    {{-- The slug follows the title, so the resulting URL is shown before saving. --}}
                    <p class="mt-1.5 text-xs text-slate-500 font-mono truncate">
                        /product/<span x-text="slugPreview || '…'"></span>
                    </p>
                    @error('name')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Product Type (Kept as select, toggle binds Alpine variable) -->
                <div>
                    <label for="product_type" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Product Type <span class="text-pink-500">*</span></label>
                    <select id="product_type" name="product_type" x-model="productType"
                            class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200">
                        <option value="simple">Simple Product</option>
                        <option value="variable">Variable Product (Sizes, Colors, etc.)</option>
                    </select>
                    @error('product_type')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Pricing (visible for both product types) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 bg-purple-500/5 p-6 rounded-xl border border-purple-500/20">
                    <div>
                        <label for="regular_price" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Regular Price (৳) <span class="text-pink-500">*</span></label>
                        <input type="number" step="0.01" min="0" id="regular_price" name="regular_price" value="{{ old('regular_price', $product->regular_price) }}" required
                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
                        @error('regular_price')
                            <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="sale_price" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Sale Price (৳)</label>
                        <input type="number" step="0.01" min="0" id="sale_price" name="sale_price" value="{{ old('sale_price', $product->sale_price) }}"
                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
                        @error('sale_price')
                            <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Internal costing: never shown on the storefront -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-700/50">
                    <label for="cost_price" class="flex items-center gap-2 text-sm font-bold uppercase tracking-wider text-slate-400 mb-2">
                        Costing Price (৳)
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-amber-500/15 text-amber-400 border border-amber-500/30">Admin only</span>
                    </label>
                    <input type="number" step="0.01" min="0" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="Leave blank if unknown">
                    <p class="text-xs text-slate-500 mt-1.5">Your purchase or production cost. Used for internal margin reporting, never shown to customers.</p>
                    @error('cost_price')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror

                    @if ($product->cost_price !== null)
                        @php
                            $marginBase = (float) ($product->sale_price ?? $product->regular_price ?? 0);
                            $cost = (float) $product->cost_price;
                            $margin = $marginBase - $cost;
                            $marginPct = $marginBase > 0 ? round($margin / $marginBase * 100, 1) : null;
                        @endphp
                        <div class="grid grid-cols-3 gap-3 mt-4 pt-4 border-t border-slate-700/50">
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Cost</p>
                                <p class="text-sm font-bold text-slate-300">৳{{ number_format($cost, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Margin</p>
                                <p class="text-sm font-bold {{ $margin >= 0 ? 'text-emerald-400' : 'text-red-400' }}">৳{{ number_format($margin, 2) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">Margin %</p>
                                <p class="text-sm font-bold {{ ($marginPct ?? 0) >= 0 ? 'text-emerald-400' : 'text-red-400' }}">
                                    {{ $marginPct !== null ? $marginPct . '%' : '—' }}
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Simple Product Stock -->
                <div x-show="productType === 'simple'">
                    <label for="stock_quantity" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Stock Quantity <span class="text-pink-500">*</span></label>
                    <input type="number" min="0" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}"
                           x-bind:required="productType === 'simple'"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
                    @error('stock_quantity')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Variable Product: Attributes -->
                <div x-show="productType === 'variable'" class="space-y-4 bg-purple-500/5 p-6 rounded-xl border border-purple-500/20">
                    <div class="flex justify-between items-center pb-2 border-b border-purple-500/20">
                        <h4 class="font-bold text-slate-200 text-sm">Product Attributes</h4>
                        <button type="button" @click="addAttribute()" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded text-sm font-bold transition">
                            + Add Attribute
                        </button>
                    </div>

                    <p class="text-xs text-slate-400">
                        Choose a global attribute and tick the values this product offers. Attributes marked
                        <span class="text-purple-300 font-semibold">Used for variations</span> build the variation combinations.
                    </p>

                    <div class="space-y-3">
                        <template x-for="(a, index) in attributes" :key="index">
                            <div class="bg-slate-900/60 border border-slate-800/50 rounded-lg p-4 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Attribute</label>
                                        <select :name="`attributes[${index}][attribute_id]`" x-model="a.attribute_id"
                                                @change="onAttributeChange(index)"
                                                class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                            <option value="">— Select attribute —</option>
                                            <template x-for="g in allAttributes" :key="g.id">
                                                <option :value="g.id" x-text="g.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div x-show="!a.attribute_id" class="md:col-span-2">
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Custom name</label>
                                        <input type="text" :name="`attributes[${index}][custom_name]`" x-model="a.custom_name" placeholder="e.g. Material"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>

                                    <div x-show="!a.attribute_id">
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Options (comma separated)</label>
                                        <input type="text" :name="`attributes[${index}][options]`" x-model="a.options" placeholder="Cotton, Silk"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>

                                    <div class="flex items-center justify-end gap-4">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="checkbox" :name="`attributes[${index}][is_visible]`" value="1" x-model="a.is_visible"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Visible
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="checkbox" :name="`attributes[${index}][is_variation]`" value="1" x-model="a.is_variation"
                                                   @change="syncVariationRows()"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Used for variations
                                        </label>
                                        <button type="button" @click="removeAttribute(index)" class="p-1 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded text-sm transition">Remove</button>
                                    </div>
                                </div>

                                <!-- Value picker for global attributes -->
                                <div x-show="a.attribute_id" class="flex flex-wrap gap-2">
                                    <template x-if="optionsFor(index).length === 0">
                                        <span class="text-xs text-slate-500">This attribute has no values yet. Add them under Attributes in the admin menu.</span>
                                    </template>
                                    <template x-for="v in optionsFor(index)" :key="v.id">
                                        <label class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs border cursor-pointer transition"
                                               :class="(a.value_ids || []).map(String).includes(String(v.id)) ? 'bg-purple-500/20 border-purple-500/50 text-purple-200' : 'bg-slate-800/50 border-slate-700/50 text-slate-400 hover:border-purple-500/30'">
                                            <input type="checkbox" :name="`attributes[${index}][value_ids][]`" :value="v.id" x-model="a.value_ids"
                                                   @change="syncVariationRows()"
                                                   class="h-3 w-3 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            <span x-show="v.color_code" class="inline-block h-3 w-3 rounded-full border border-slate-600" :style="`background-color:${v.color_code}`"></span>
                                            <span x-text="v.name"></span>
                                        </label>
                                    </template>
                                </div>

                                <!-- Custom option preview -->
                                <div x-show="!a.attribute_id && a.options" class="text-xs text-slate-400">
                                    Will create values: <span x-text="a.options"></span>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Variable Product: Variations -->
                @php
                    // Built here so the sale price error below can name the
                    // option that failed instead of the raw field key.
                    $variationRowsPayload = $product->variations->map(fn ($variation) => [
                        'id' => $variation->id,
                        'label' => $variation->attribute_label,
                    ])->values();
                @endphp
                <div x-show="productType === 'variable'" class="space-y-4 bg-purple-500/5 p-6 rounded-xl border border-purple-500/20">
                    <div class="flex flex-wrap justify-between items-center gap-3 pb-2 border-b border-purple-500/20">
                        <button type="button" @click="variationsOpen = !variationsOpen"
                                class="flex items-center gap-2 text-left group" :aria-expanded="variationsOpen ? 'true' : 'false'"
                                aria-controls="variations-panel">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"
                                 class="w-4 h-4 text-purple-300 transition-transform" :class="variationsOpen && 'rotate-90'">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                            <h4 class="font-bold text-slate-200 text-sm group-hover:text-purple-200 transition">Variations</h4>
                            <span class="text-xs text-slate-400" x-text="variationsOpen ? 'Hide' : `Show ${variations.length} row(s)`"></span>
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="variationsOpen = true; addVariation()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded text-sm font-bold transition">
                                + Add Variation
                            </button>
                            {{-- This posts to a different route, which accepts
                                 both verbs because the form's hidden
                                 _method=PUT would otherwise spoof the request. --}}
                            <button type="submit"
                                    formaction="{{ route('admin.products.generate-variations', $product) }}"
                                    formmethod="POST"
                                    formnovalidate
                                    class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded text-sm font-bold transition">
                                Generate from Attributes
                            </button>
                        </div>
                    </div>

                    {{-- Collapsed by default: a variable product with many combinations is a wall
                         of rows, and most edits are to the fields above. A failed save forces it
                         open so the rejected rows are never hidden. --}}
                    <div id="variations-panel" x-show="variationsOpen" x-cloak x-transition class="space-y-4">

                    <p class="text-xs text-slate-400">
                        Each row is one combination of attribute values. The name above each row is the combination
                        that was created for this product. Untick a row and save to delete it.
                    </p>
                    @php $perImage = ini_get('upload_max_filesize') ?: '2M'; $perRequest = ini_get('post_max_size') ?: '8M'; @endphp
                    <p class="mt-1 text-xs text-amber-300/80">
                        Images: up to {{ $perImage }} each, and {{ $perRequest }} for all images in one save.
                        If a save comes back with nothing stored, the images together were too large.
                    </p>

                    {{-- Fills the price boxes of the rows below so a price can be
                         typed once instead of once per combination. Still saved by
                         the normal Save button below. --}}
                    <div class="flex flex-col md:flex-row md:items-end gap-3 p-4 rounded-xl border border-purple-500/20 bg-purple-500/5">
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-1" for="bulkRegularPrice">Regular price (৳)</label>
                            <input type="number" step="0.01" min="0" id="bulkRegularPrice" x-model="bulkPrice.regular"
                                   placeholder="e.g. 1200"
                                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                        <div class="flex-1">
                            <label class="block text-xs font-bold uppercase text-slate-300 mb-1" for="bulkSalePrice">Sale price (৳)</label>
                            <input type="number" step="0.01" min="0" id="bulkSalePrice" x-model="bulkPrice.sale"
                                   placeholder="leave blank for none"
                                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-3 py-2 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" @click="applyPriceToAll()"
                                    class="px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold transition">
                                Fill All Rows
                            </button>
                            <button type="button" @click="applyPriceToSelected()"
                                    class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-bold transition">
                                Fill Selected
                            </button>
                        </div>
                    </div>

                    <p x-show="priceMessage" x-cloak class="text-xs font-semibold"
                       :class="priceMessageIsError ? 'text-red-400' : 'text-emerald-400'"
                       x-text="priceMessage"></p>

                    {{-- The whole variation set was rejected, e.g. duplicate
                         combinations. Nothing was saved, so say so plainly. --}}
                    @foreach($detailedVariationErrors as $message)
                        <div class="px-4 py-3 rounded-xl border border-red-500/30 bg-red-500/5">
                            <p class="text-sm font-bold text-red-300">Variations were not saved</p>
                            <p class="mt-1 text-xs text-red-200/90">{{ $message }}</p>
                        </div>
                    @endforeach

                    {{-- A sale price that is not below the regular price is
                         rejected, so the row is named instead of the raw
                         "variations.1.sale_price" key. --}}
                    @if($salePriceErrors->isNotEmpty())
                        <div class="px-4 py-3 rounded-xl border border-red-500/30 bg-red-500/5">
                            <p class="text-sm font-bold text-red-300">Some sale prices were not saved</p>
                            <ul class="mt-1 space-y-0.5 text-xs text-red-200/90">
                                @foreach($salePriceErrors as $key => $messages)
                                    @php $rowIndex = (int) str_replace(['variations.', '.sale_price'], '', $key); @endphp
                                    <li>
                                        <strong>{{ $variationRowsPayload[$rowIndex]['label'] ?? 'Row '.($rowIndex + 1) }}</strong>
                                        — the sale price must be lower than the regular price.
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @php
                        $unbuyableVariations = $product->variations()->get()
                            ->reject(fn ($variation) => $variation->isPurchasable())
                            ->values();
                    @endphp

                    @if($unbuyableVariations->isNotEmpty())
                        <div class="flex items-start gap-3 px-4 py-3 rounded-xl border border-amber-500/30 bg-amber-500/5">
                            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                            <div class="text-sm">
                                <p class="font-bold text-amber-300">
                                    {{ $unbuyableVariations->count() }} option(s) cannot be bought by customers yet:
                                </p>
                                <ul class="mt-1 space-y-0.5 text-amber-200/90">
                                    @foreach($unbuyableVariations as $variation)
                                        <li>
                                            <strong>{{ $variation->attribute_label ?: 'Variation #'.$variation->id }}</strong>
                                            — {{ $variation->unavailabilityReason() }}
                                            @if($variation->status === \App\Models\ProductVariation::STATUS_PUBLISH)
                                                <span class="text-amber-300/70">(published)</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-1.5 text-xs text-amber-200/70">
                                    Give each one a regular price, or untick it to hide it from customers.
                                </p>
                            </div>
                        </div>
                    @endif

                    <div class="space-y-3">
                        <template x-if="variationAttributes().length === 0">
                            <p class="text-sm text-slate-400 py-4 text-center">
                                No attributes are marked “Used for variations” yet. Add one above, then generate.
                            </p>
                        </template>

                        <template x-for="(v, index) in variations" :key="index">
                            <div class="bg-slate-900/60 border border-slate-800/50 rounded-lg p-4 space-y-3">
                                <input type="hidden" :name="`variations[${index}][id]`" x-model="v.id">

                                <div class="flex items-center gap-2 pb-2 border-b border-slate-800/50">
                                    <span class="text-sm font-bold text-purple-300 truncate" x-text="variationLabel(v)"></span>
                                    <span class="text-[10px] uppercase tracking-wider text-slate-500" x-show="v.status === 'private'">Disabled</span>
                                    <span class="text-[10px] uppercase tracking-wider text-amber-400" x-show="v.manage_stock && Number(v.stock_quantity) === 0">Out of stock</span>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <template x-for="pa in variationAttributes()" :key="pa.key">
                                        <div>
                                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1" x-text="pa.name"></label>
                                            <select :name="`variations[${index}][values][${pa.key}]`"
                                                    x-model="v.values[pa.key]"
                                                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                                <option value="">— Any —</option>
                                                <template x-for="o in pa.values" :key="o.id">
                                                    <option :value="String(o.id)"
                                                            :selected="String(o.id) === String(v.values[pa.key] ?? '')"
                                                            x-text="o.name"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </template>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Regular (৳)</label>
                                        <input type="number" step="0.01" min="0" :name="`variations[${index}][regular_price]`" x-model="v.regular_price"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Sale (৳)</label>
                                        <input type="number" step="0.01" min="0" :name="`variations[${index}][sale_price]`" x-model="v.sale_price"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Stock</label>
                                        <input type="number" min="0" :name="`variations[${index}][stock_quantity]`" x-model="v.stock_quantity"
                                               :disabled="!v.manage_stock"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 disabled:opacity-50 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Status</label>
                                        <select :name="`variations[${index}][stock_status]`" x-model="v.stock_status"
                                                class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                            <option value="instock">In stock</option>
                                            <option value="outofstock">Out of stock</option>
                                            <option value="onbackorder">On backorder</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">SKU</label>
                                        <input type="text" :name="`variations[${index}][sku]`" x-model="v.sku" placeholder="auto"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Enabled</label>
                                        <select :name="`variations[${index}][status]`" x-model="v.status"
                                                class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                            <option value="publish">Enabled</option>
                                            <option value="private">Disabled</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Image</label>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <div class="min-w-0 flex-1 sm:max-w-xl">
                                            @include('admin.partials.media-picker', [
                                                'fieldExpr' => '`variations[${index}][image]`',
                                                'kind' => 'image',
                                                'label' => 'Image',
                                            ])
                                        </div>
                                        <div x-show="v.image_url" class="flex items-center gap-2">
                                            <img :src="v.image_url" class="h-12 w-12 object-cover rounded-lg border border-slate-700" alt="">
                                            <label class="flex items-center gap-1.5 text-xs text-pink-400">
                                                <input type="checkbox" :name="`variations[${index}][remove_image]`" value="1" x-model="v.remove_image"
                                                       class="h-3.5 w-3.5 rounded border-slate-600 text-pink-400 focus:ring-pink-500">
                                                Remove saved
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Weight</label>
                                        <input type="number" step="0.001" min="0" :name="`variations[${index}][weight_value]`" x-model="v.weight_value"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div class="flex items-end">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="checkbox" :name="`variations[${index}][manage_stock]`" value="1" x-model="v.manage_stock"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Manage stock here
                                        </label>
                                    </div>
                                    <div class="flex items-end justify-start">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-400">
                                            <input type="checkbox" form="bulkVariationForm" :name="`variation_ids[]`" :value="v.id"
                                                   x-model="selectedVariationIds"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Select (bulk edit)
                                        </label>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-slate-500" x-text="v.combo_key ? `Key: ${v.combo_key}` : ''"></div>
                                    <button type="button" @click="removeVariation(index)" class="p-1 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded text-sm transition">Remove row</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    </div>
                </div>

                <!-- Descriptions -->
                <div>
                    <label for="short_description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Short Description</label>
                    <input type="hidden" name="short_description" id="short_description_input">
                    <div id="short_description_editor" class="bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-200">{!! old('short_description', $product->short_description) !!}</div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Detailed Description</label>
                    <input type="hidden" name="description" id="description_input">
                    <div id="description_editor" class="bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-200">{!! old('description', $product->description) !!}</div>
                </div>

                <!-- SEO Fields -->
                <div class="border-t border-slate-800/50 pt-6 space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm">SEO Meta Fields</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="seo_title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">SEO Title</label>
                            <input type="text" id="seo_title" name="seo_title" value="{{ old('seo_title', $product->seo_title) }}"
                                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
                        </div>
                        <div>
                            <label for="meta_description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="2"
                                      class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">{{ old('meta_description', $product->meta_description) }}</textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                
                <!-- Publish Settings -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50 space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm">Publish Settings</h4>
                    
                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" value="1" {{ $product->is_active ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                        <label for="is_active" class="ml-2 block text-sm font-semibold text-slate-300">Active (Visible to customers)</label>
                    </div>

                    <div class="flex items-center">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1" {{ $product->is_featured ? 'checked' : '' }}
                               class="h-4 w-4 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                        <label for="is_featured" class="ml-2 block text-sm font-semibold text-slate-300">Featured Highlight</label>
                    </div>
                </div>

                <!-- Categories -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50">
                    <h4 class="font-bold text-slate-200 text-sm mb-4">Categories Mappings</h4>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        @php
                            $mappedCatIds = $product->categories->pluck('id')->toArray();
                        @endphp
                        @foreach($categories as $category)
                            <div class="flex items-center">
                                <input id="cat_{{ $category->id }}" name="categories[]" type="checkbox" value="{{ $category->id }}"
                                       {{ in_array($category->id, $mappedCatIds) ? 'checked' : '' }}
                                       class="h-4 w-4 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                <label for="cat_{{ $category->id }}" class="ml-2 text-sm text-slate-300">
                                    {{ $category->name }}
                                    @if($category->parent)
                                        <span class="text-sm text-slate-300 font-semibold">({{ $category->parent->name }})</span>
                                    @endif
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('categories')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Size Chart Selector -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50">
                    <h4 class="font-bold text-slate-200 text-sm mb-1">Size Chart</h4>
                    <p class="text-xs text-slate-400 mb-4">Optional. Choose "None" and no size chart link appears on the product page.</p>

                    @if($sizeCharts->isEmpty())
                        <p class="text-sm text-slate-400 mb-3">No size charts uploaded yet.</p>
                        <a href="{{ route('admin.size-charts.create') }}" class="inline-block px-4 py-2 bg-purple-500/10 border border-purple-500/20 text-purple-400 rounded-md text-xs font-bold hover:bg-purple-500/20 transition">
                            + Add Size Chart
                        </a>
                    @else
                        <select name="size_chart_id" id="size_chart_id"
                                class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200">
                            <option value="">None</option>
                            @foreach($sizeCharts as $sizeChart)
                                <option value="{{ $sizeChart->id }}" @selected(old('size_chart_id', $product->size_chart_id) == $sizeChart->id)>
                                    {{ $sizeChart->title }}
                                </option>
                            @endforeach
                        </select>

                        @if($product->sizeChart)
                            <a href="{{ route('admin.size-charts.edit', $product->sizeChart->id) }}" class="mt-3 inline-block text-xs font-bold text-purple-400 hover:text-purple-300 transition">
                                Edit "{{ $product->sizeChart->title }}" &rarr;
                            </a>
                        @endif
                    @endif

                    @error('size_chart_id')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Product Images Gallery Manager -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50 space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm">Product Images Gallery</h4>

                    <!-- Existing Images -->
                    @if($product->images->count() > 0)
                        <div class="space-y-3">
                            <p class="text-sm font-bold text-slate-300 uppercase border-b border-slate-700/50 pb-1">Current Images:</p>
                            @foreach($product->images as $index => $img)
                                <div class="p-3 bg-slate-900/80 border border-slate-700/50 rounded-lg space-y-2 relative">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ Storage::url($img->image) }}" class="h-10 w-10 object-cover rounded border border-slate-700/50 flex-shrink-0">
                                        <div class="text-sm text-slate-300 truncate flex-grow">
                                            <p class="font-mono text-slate-400 truncate">{{ basename($img->image) }}</p>
                                        </div>
                                        <div class="flex items-center space-x-1">
                                            <input type="checkbox" id="del_img_{{ $img->id }}" name="delete_images[]" value="{{ $img->id }}"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-pink-400 focus:ring-pink-500">
                                            <label for="del_img_{{ $img->id }}" class="text-sm font-bold text-pink-400 uppercase">Delete</label>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="existing_images[{{ $index }}][id]" value="{{ $img->id }}">
                                    <div class="grid grid-cols-3 gap-1.5">
                                        <div class="col-span-2">
                                            <label class="block text-sm font-bold text-slate-300 uppercase">Name</label>
                                            <input type="text" name="existing_images[{{ $index }}][name]" value="{{ $img->name }}"
                                                   class="w-full border border-slate-700/50 rounded px-1 py-0.5 text-sm text-slate-200 placeholder-slate-600">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-300 uppercase">Order</label>
                                            <input type="number" name="existing_images[{{ $index }}][order]" value="{{ $img->order }}"
                                                   class="w-full border border-slate-700/50 rounded px-1 py-0.5 text-sm text-slate-200 placeholder-slate-600">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 uppercase">Alt Text</label>
                                        <input type="text" name="existing_images[{{ $index }}][alt_text]" value="{{ $img->alt_text }}"
                                               class="w-full border border-slate-700/50 rounded px-1.5 py-0.5 text-sm text-slate-200 placeholder-slate-600">
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" id="featured_img_old_{{ $img->id }}" name="new_image_featured_temp" value="existing_{{ $img->id }}"
                                                   {{ $img->is_featured ? 'checked' : '' }}
                                                   class="h-3 w-3 text-purple-400 focus:ring-purple-500">
                                            <label for="featured_img_old_{{ $img->id }}" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">Featured Main Image</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" id="secondary_img_old_{{ $img->id }}" name="new_image_secondary_temp" value="existing_{{ $img->id }}"
                                                   {{ $img->is_secondary ? 'checked' : '' }}
                                                   class="h-3 w-3 text-pink-400 focus:ring-pink-500">
                                            <label for="secondary_img_old_{{ $img->id }}" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">2nd Image (Hover)</label>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Upload New Images -->
                    <div class="space-y-3 pt-3 border-t border-slate-700/50">
                        <p class="text-sm font-bold text-slate-300 uppercase">Upload New Images:</p>
                        <button type="button" @click="addImageField()" class="w-full text-center px-4 py-2 bg-purple-500/10 border border-purple-500/20 text-purple-400 rounded-md text-sm font-bold hover:bg-purple-500/20 transition">
                            + Add Image Upload Row
                        </button>

                        <div class="space-y-4">
                            <template x-for="(img, idx) in uploadImages" :key="idx">
                                <div class="p-3 bg-slate-900/80 border border-slate-700/50 rounded-lg space-y-2 relative">
                                    <button type="button" @click="removeImageField(idx)" class="absolute top-2 right-2 text-pink-400 hover:text-pink-300 text-sm font-bold">&#x2715;</button>
                                    
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Image</label>
                                        @include('admin.partials.media-picker', [
                                            'fieldExpr' => '`new_images[${idx}][file]`',
                                            'mediaIdFieldExpr' => '`new_images[${idx}][media_id]`',
                                            'kind' => 'image',
                                            'label' => 'Image',
                                        ])
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Name</label>
                                            <input type="text" :name="`new_images_names[${idx}]`" placeholder="detail_shot"
                                                   class="w-full border border-slate-700/50 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Alt Text</label>
                                            <input type="text" :name="`new_images_alts[${idx}]`" placeholder="alt text"
                                                   class="w-full border border-slate-700/50 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                        </div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" :id="`featured_img_new_${idx}`" name="new_image_featured_temp" :value="`new_${idx}`"
                                                   class="h-3 w-3 text-purple-400 focus:ring-purple-500">
                                            <label :for="`featured_img_new_${idx}`" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">Set Main Featured</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" :id="`secondary_img_new_${idx}`" name="new_image_secondary_temp" :value="`new_${idx}`"
                                                   class="h-3 w-3 text-pink-400 focus:ring-pink-500">
                                            <label :for="`secondary_img_new_${idx}`" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">Set 2nd Image (Hover)</label>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- Submit CTAs -->
        <div class="flex items-center space-x-4 pt-6 border-t border-slate-800/50">
            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Update Product
            </button>
            <a href="{{ route('admin.products.index') }}"
               class="px-8 py-3 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
    </form>

    {{-- Bulk actions operate on the "Select" checkboxes inside the variations form. --}}
    <form id="bulkVariationForm" action="{{ route('admin.products.variations.bulk', $product) }}" method="POST"
          class="mt-6 bg-slate-900 border border-slate-800/50 rounded-2xl p-6">
        @csrf

        <h4 class="font-bold text-slate-200 text-sm mb-4">Bulk Actions</h4>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Action</label>
                <select name="action" required
                        class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                    <option value="set_regular_price">Set regular price</option>
                    <option value="set_sale_price">Set sale price</option>
                    <option value="increase_price">Increase regular price</option>
                    <option value="decrease_price">Decrease regular price</option>
                    <option value="set_stock">Set stock quantity</option>
                    <option value="toggle_manage_stock">Toggle manage stock</option>
                    <option value="set_weight">Set weight</option>
                    <option value="enable">Enable</option>
                    <option value="disable">Disable</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Value</label>
                <input type="text" name="params[value]"
                       class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase text-slate-300 mb-2">Mode</label>
                <select name="params[mode]"
                        class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                    <option value="fixed">Fixed amount</option>
                    <option value="percent">Percentage</option>
                </select>
            </div>

            <div>
                <button type="submit"
                        class="w-full px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded-lg text-sm font-bold transition">
                    Apply to Selected
                </button>
            </div>
        </div>

        @error('variation_ids')
            <span class="text-sm text-red-500 mt-2 block">{{ $message }}</span>
        @enderror
    </form>
</div>
@endsection

@section('scripts')
@php
    $allAttributesPayload = $attributes->map(fn ($attribute) => [
        'id' => $attribute->id,
        'name' => $attribute->name,
        'type' => $attribute->type,
        'values' => $attribute->values->map(fn ($value) => [
            'id' => $value->id,
            'name' => $value->name,
            'color_code' => $value->color_code,
        ])->values(),
    ])->values();

    // For every saved product attribute, the value options a variation row
    // may select. Global attributes use their own values; custom ones use the
    // attribute values created for their options.
    $variationOptionsPayload = $product->productAttributes->map(fn ($productAttribute) => [
        'product_attribute_id' => $productAttribute->id,
        'attribute_id' => $productAttribute->attribute_id,
        'name' => $productAttribute->display_name,
        'is_variation' => (bool) $productAttribute->is_variation,
        'values' => $productAttribute->isCustom()
            ? $productAttribute->values->map(fn ($value) => [
                'id' => $value->id,
                'name' => $value->name,
                'color_code' => $value->color_code,
            ])->values()
            : ($productAttribute->attribute ? $productAttribute->attribute->values->map(fn ($value) => [
                'id' => $value->id,
                'name' => $value->name,
                'color_code' => $value->color_code,
            ])->values() : $productAttribute->values->map(fn ($value) => [
                'id' => $value->id,
                'name' => $value->name,
                'color_code' => $value->color_code,
            ])->values()),
    ])->values();

    $variationRows = $product->variations->map(fn ($variation) => [
        'id' => $variation->id,
        'label' => $variation->attribute_label,
        'values' => $variation->attributeValues
            ->mapWithKeys(fn ($value) => [$value->product_attribute_id => (string) $value->attribute_value_id])
            ->all(),
        'regular_price' => $variation->regular_price,
        'sale_price' => $variation->sale_price,
        'manage_stock' => (bool) $variation->manage_stock,
        'stock_quantity' => $variation->stock_quantity,
        'stock_status' => $variation->stock_status,
        'sku' => $variation->sku,
        'weight_value' => $variation->weight_value,
        'status' => $variation->status,
        'combo_key' => $variation->combo_key,
        'image_url' => $variation->image_url,
        'remove_image' => false,
    ])->values();

    $attributeRowsPayload = $product->productAttributes->map(function ($productAttribute) {
        $valueIds = $productAttribute->values->pluck('id')->map(fn ($id) => (int) $id)->all();
        if (empty($valueIds)) {
            $used = \Illuminate\Support\Facades\DB::table('variation_attribute_values')
                ->where('product_attribute_id', $productAttribute->id)
                ->whereNotNull('attribute_value_id')
                ->pluck('attribute_value_id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
            if (!empty($used)) {
                $valueIds = $used;
                $productAttribute->values()->syncWithoutDetaching($used);
            }
        }
        return [
            'product_attribute_id' => $productAttribute->id,
            'attribute_id' => $productAttribute->attribute_id,
            'custom_name' => $productAttribute->custom_name,
            'options' => implode(', ', (array) $productAttribute->custom_options),
            'value_ids' => $valueIds,
            'is_visible' => (bool) $productAttribute->is_visible,
            'is_variation' => (bool) $productAttribute->is_variation,
        ];
    })->values();
@endphp
    <script>
    // Seeded into a freshly added variation row so it is not left at 0, which
    // reads as "out of stock" until the admin types a real count.
    const DEFAULT_VARIATION_STOCK = 5;

    function productForm() {
        return {
            productType: '{{ $product->product_type }}',
            name: {!! json_encode(old('name', $product->name)) !!},
            allAttributes: {!! json_encode($allAttributesPayload) !!},
            variationOptions: {!! json_encode($variationOptionsPayload) !!},
            attributes: {!! json_encode($attributeRowsPayload) !!},
            variations: {!! json_encode($variationRows) !!},

            // The variation table stays folded away until it is asked for, but a
            // rejected save has to show the rows that failed validation.
            variationsOpen: {{ collect($errors->keys())->contains(fn ($key) => str_starts_with($key, 'variations')) ? 'true' : 'false' }},

            // Prices typed once and copied into the variation rows below.
            bulkPrice: {
                regular: '',
                sale: '',
            },
            priceMessage: '',
            priceMessageIsError: false,

            // Ids of the rows ticked in the bulk actions form, so the price
            // filler can target just those.
            selectedVariationIds: [],

            /**
             * Read the two price boxes and check them the same way the server
             * does: a sale price has to be lower than the regular one, and
             * anything that is not a number is rejected before it can reach the
             * rows.
             */
            resolveBulkPrice() {
                const regular = this.bulkPrice.regular === '' ? null : Number(this.bulkPrice.regular);
                const sale = this.bulkPrice.sale === '' ? null : Number(this.bulkPrice.sale);

                if (regular !== null && (!Number.isFinite(regular) || regular < 0)) {
                    return { error: 'Enter a valid regular price, or clear the box.' };
                }

                if (sale !== null && (!Number.isFinite(sale) || sale < 0)) {
                    return { error: 'Enter a valid sale price, or clear the box.' };
                }

                if (regular === null && sale === null) {
                    return { error: 'Type a regular price, a sale price, or both first.' };
                }

                if (regular === null) {
                    return { error: 'A sale price needs a regular price to be lower than.' };
                }

                if (sale !== null && sale >= regular) {
                    return { error: 'The sale price must be lower than the regular price.' };
                }

                return { regular: regular, sale: sale };
            },

            setPriceMessage(text, isError) {
                this.priceMessage = text;
                this.priceMessageIsError = !!isError;
            },

            applyPriceToAll() {
                const price = this.resolveBulkPrice();
                if (price.error) return this.setPriceMessage(price.error, true);

                this.variations.forEach(variation => {
                    variation.regular_price = price.regular;
                    variation.sale_price = price.sale;
                });

                this.setPriceMessage(
                    `Filled ${this.variations.length} row(s) with regular ${price.regular}` +
                    (price.sale === null ? '.' : ` and sale ${price.sale}.`) +
                    ' Remember to press Save to store it.',
                    false
                );
            },

            applyPriceToSelected() {
                const price = this.resolveBulkPrice();
                if (price.error) return this.setPriceMessage(price.error, true);

                const selected = this.variations.filter(variation => variation.id && this.isRowSelected(variation.id));

                if (selected.length === 0) {
                    return this.setPriceMessage('Tick “Select” on a row first.', true);
                }

                selected.forEach(variation => {
                    variation.regular_price = price.regular;
                    variation.sale_price = price.sale;
                });

                this.setPriceMessage(
                    `Filled ${selected.length} selected row(s)` +
                    (price.sale === null ? '.' : ` with sale ${price.sale}.`) +
                    ' Remember to press Save to store it.',
                    false
                );
            },

            /** Mirror of the "Select" checkboxes inside the bulk actions form. */
            isRowSelected(id) {
                return this.selectedVariationIds.map(String).includes(String(id));
            },

            addAttribute() {
                this.attributes.push({
                    product_attribute_id: null,
                    attribute_id: '',
                    custom_name: '',
                    options: '',
                    value_ids: [],
                    is_visible: true,
                    is_variation: true,
                });
            },

            removeAttribute(index) {
                this.attributes.splice(index, 1);
            },

            /** Value options for an unsaved attribute row. */
            optionsFor(index) {
                const row = this.attributes[index];
                if (!row || !row.attribute_id) return [];

                const attribute = this.allAttributes.find(a => String(a.id) === String(row.attribute_id));
                return attribute ? (attribute.values || []) : [];
            },

            onAttributeChange(index) {
                this.attributes[index].value_ids = [];
                this.syncVariationRows();
            },

            /**
             * Add a variation row for every combination the ticked values imply
             * but the table is missing.
             *
             * Until this existed, ticking a new Size or Color only updated the
             * attribute row; the combination row appeared solely after
             * "Generate from Attributes", which posts the whole form and reloads.
             * Now the row appears straight away and is persisted by the normal
             * Save. Rows already present are left untouched, so prices, stock and
             * images typed into them are never rebuilt.
             */
            syncVariationRows() {
                const slots = this.variationSlots();
                if (!slots.length) return;

                const combinations = slots.map(slot => {
                    const row = this.attributes.find(a => slot.attribute_id
                        ? String(a.attribute_id) === String(slot.attribute_id)
                        : (a.custom_name && a.custom_name === slot.name)
                    );
                    const ticked = row ? (row.value_ids || []).map(String) : [];

                    if (slot.attribute_id) {
                        return slot.values.filter(v => ticked.includes(String(v.id))).map(v => String(v.id));
                    }
                    return (slot.values || []).map(v => String(v.id));
                });

                // Wait until every variation attribute has at least one value
                if (combinations.some(list => !list.length)) return;

                let rows = [[]];

                combinations.forEach(list => {
                    const next = [];
                    rows.forEach(combo => list.forEach(id => next.push([...combo, id])));
                    rows = next;
                });

                rows.forEach(combo => {
                    const alreadyListed = this.variations.some(v =>
                        slots.every((slot, i) => String((v.values && v.values[slot.key]) ?? '') === combo[i])
                    );

                    if (alreadyListed) return;

                    const values = {};
                    slots.forEach((slot, i) => { values[slot.key] = combo[i]; });

                    this.variations.push(this.blankVariation(values));
                });

                this.variationsOpen = true;
            },

            /** Only the attributes flagged for variations, in display order. */
            variationAttributes() {
                return this.variationSlots();
            },

            /**
             * One slot per attribute the admin marked "Used for variations",
             * built from the live attribute rows rather than the saved ones.
             *
             * Reading only variationOptions (the product attributes already in
             * the database) meant that picking Size or Color for the first time
             * produced no combinations at all, because the new attribute had no
             * product_attribute_id yet. That is why the create page appears to
             * work and the edit page did not.
             *
             * A slot already linked to the product is keyed by its
             * product_attribute_id. One added in this session has no id until it
             * is saved, so it is keyed by its position, "p0", "p1" ... and the
             * server resolves that key back to the attribute it creates.
             */
            variationSlots() {
                return this.attributes
                    .map((a, index) => {
                        if (!a.is_variation) return null;

                        const saved = a.attribute_id
                            ? this.variationOptions.find(pa => String(pa.attribute_id) === String(a.attribute_id))
                            : this.variationOptions.find(pa => pa.product_attribute_id && pa.product_attribute_id === a.product_attribute_id);

                        let options = [];
                        if (a.attribute_id) {
                            const globalAttr = this.allAttributes.find(g => String(g.id) === String(a.attribute_id));
                            options = globalAttr ? globalAttr.values : (saved?.values || []);
                        } else {
                            if (saved && saved.values && saved.values.length) {
                                options = saved.values;
                            } else if (a.options) {
                                const raw = Array.isArray(a.options) ? a.options : String(a.options).split(',');
                                options = raw.map(s => s.trim()).filter(Boolean).map(name => ({ id: name, name: name }));
                            }
                        }

                        return {
                            key: saved ? saved.product_attribute_id : 'p' + index,
                            product_attribute_id: saved ? saved.product_attribute_id : null,
                            attribute_id: a.attribute_id,
                            name: a.attribute_id
                                ? (this.allAttributes.find(g => String(g.id) === String(a.attribute_id))?.name || saved?.name || '')
                                : (a.custom_name || saved?.name || 'Custom'),
                            values: options,
                        };
                    })
                    .filter(Boolean);
            },

            optionsForProductAttribute(productAttribute) {
                if (productAttribute.attribute_id) {
                    const attribute = this.allAttributes.find(a => a.id == productAttribute.attribute_id);
                    return attribute ? attribute.values : [];
                }

                return productAttribute.values;
            },

            /**
             * Mirrors Str::slug so the admin can see the URL the title will
             * produce. The real slug is generated server side, so this is only
             * a preview of what the title will become.
             */
            slugPreview() {
                return String(this.name || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[̀-ͯ]/g, '')
                    .replace(/[^a-z0-9\s-]/g, '')
                    .trim()
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            },

            /**
             * The option names a row currently selects, e.g. "S / Red". A slot
             * left on "— Any —" reads as "Any" so it is obvious the row is a
             * wildcard rather than a blank row.
             */
            variationLabel(variation) {
                const slots = this.variationSlots();
                if (!slots.length) return variation.label || 'Variation';

                const parts = slots.map(pa => {
                    const selectedId = variation.values ? variation.values[pa.key] : null;

                    if (selectedId === undefined || selectedId === null || selectedId === '') {
                        return 'Any ' + pa.name;
                    }

                    const option = pa.values.find(o => String(o.id) === String(selectedId));

                    return option ? option.name : (variation.label || String(selectedId) || 'Any');
                });

                return parts.join(' / ');
            },

            addVariation() {
                this.variations.push(this.blankVariation({}));
            },

            blankVariation(values) {
                return {
                    id: '',
                    values: values || {},
                    regular_price: '',
                    sale_price: '',
                    manage_stock: true,
                    stock_quantity: DEFAULT_VARIATION_STOCK,
                    stock_status: 'instock',
                    sku: '',
                    weight_value: '',
                    status: 'publish',
                    combo_key: null,
                    image_url: null,
                    remove_image: false,
                };
            },

            removeVariation(index) {
                this.variations.splice(index, 1);
            },

            uploadImages: [],
            addImageField() {
                this.uploadImages.push({ name: '', alt_text: '' });
            },
            removeImageField(index) {
                this.uploadImages.splice(index, 1);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        var shortEditor = new Quill('#short_description_editor', { theme: 'snow', placeholder: 'Describe the product highlight briefly...' });
        var descEditor = new Quill('#description_editor', { theme: 'snow', placeholder: 'Provide the complete product details, care instructions, material compositions...' });

        function syncHidden() {
            document.getElementById('short_description_input').value = shortEditor.root.innerHTML;
            document.getElementById('description_input').value = descEditor.root.innerHTML;
        }

        shortEditor.on('text-change', syncHidden);
        descEditor.on('text-change', syncHidden);
        syncHidden();

        document.querySelector('form').addEventListener('submit', syncHidden);
    });
</script>
@endsection

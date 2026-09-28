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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Columns -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Product Title <span class="text-pink-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600">
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
                                            <option value="">— Custom attribute —</option>
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
                                               :class="a.value_ids.includes(v.id) ? 'bg-purple-500/20 border-purple-500/50 text-purple-200' : 'bg-slate-800/50 border-slate-700/50 text-slate-400 hover:border-purple-500/30'">
                                            <input type="checkbox" :name="`attributes[${index}][value_ids][]`" :value="v.id" x-model="a.value_ids"
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
                <div x-show="productType === 'variable'" class="space-y-4 bg-purple-500/5 p-6 rounded-xl border border-purple-500/20">
                    <div class="flex flex-wrap justify-between items-center gap-3 pb-2 border-b border-purple-500/20">
                        <h4 class="font-bold text-slate-200 text-sm">Variations</h4>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="addVariation()" class="px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded text-sm font-bold transition">
                                + Add Variation
                            </button>
                            <button type="submit"
                                    formaction="{{ route('admin.products.generate-variations', $product) }}"
                                    formmethod="POST"
                                    class="px-3 py-1.5 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white rounded text-sm font-bold transition">
                                Generate from Attributes
                            </button>
                        </div>
                    </div>

                    <p class="text-xs text-slate-400">
                        Each row is one combination of attribute values. Untick a row and save to delete it.
                    </p>

                    <div class="space-y-3">
                        <template x-if="variationAttributes().length === 0">
                            <p class="text-sm text-slate-400 py-4 text-center">
                                No attributes are marked “Used for variations” yet. Add one above, then generate.
                            </p>
                        </template>

                        <template x-for="(v, index) in variations" :key="index">
                            <div class="bg-slate-900/60 border border-slate-800/50 rounded-lg p-4 space-y-3">
                                <input type="hidden" :name="`variations[${index}][id]`" x-model="v.id">

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                    <template x-for="pa in variationAttributes()" :key="pa.product_attribute_id">
                                        <div>
                                            <label class="block text-xs font-bold uppercase text-slate-400 mb-1" x-text="pa.name"></label>
                                            <select :name="`variations[${index}][values][${pa.product_attribute_id}]`"
                                                    :value="v.values[pa.product_attribute_id] || ''"
                                                    @change="v.values[pa.product_attribute_id] = $event.target.value"
                                                    class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                                <option value="">— Any —</option>
                                                <template x-for="o in optionsForProductAttribute(pa)" :key="o.id">
                                                    <option :value="o.id" x-text="o.name"></option>
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

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Image</label>
                                        <input type="file" :name="`variations[${index}][image]`" accept="image/*"
                                               class="w-full text-xs text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Weight</label>
                                        <input type="number" step="0.001" min="0" :name="`variations[${index}][weight_value]`" x-model="v.weight_value"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <div class="flex items-center gap-1.5">
                                        <input type="checkbox" :name="`variations[${index}][manage_stock]`" value="1" x-model="v.manage_stock"
                                               class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                        <label class="text-xs text-slate-300">Manage stock here</label>
                                    </div>
                                    <div class="flex items-center justify-between gap-2">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-400">
                                            <input type="checkbox" form="bulkVariationForm" :name="`bulk_variation_ids[]`" :value="v.id"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Select
                                        </label>
                                        <div x-show="v.image_url" class="flex items-center gap-2">
                                            <img :src="v.image_url" class="h-8 w-8 object-cover rounded border border-slate-700" alt="">
                                            <label class="flex items-center gap-1 text-xs text-pink-400">
                                                <input type="checkbox" :name="`variations[${index}][remove_image]`" value="1" x-model="v.remove_image"
                                                       class="h-3 w-3 rounded border-slate-600 text-pink-400 focus:ring-pink-500">
                                                Remove
                                            </label>
                                        </div>
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
                                    <div class="flex items-center">
                                        <input type="radio" id="featured_img_old_{{ $img->id }}" name="new_image_featured_temp" value="existing_{{ $img->id }}"
                                               {{ $img->is_featured ? 'checked' : '' }}
                                               class="h-3 w-3 text-purple-400 focus:ring-purple-500">
                                        <label for="featured_img_old_{{ $img->id }}" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">Featured Main Image</label>
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
                                        <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Image File</label>
                                        <input type="file" name="new_images[]" required accept="image/*"
                                               class="w-full text-sm text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-purple-500/10 file:text-purple-400 file:text-sm">
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
                                    <div class="flex items-center">
                                        <input type="radio" :id="`featured_img_new_${idx}`" name="new_image_featured_temp" :value="`new_${idx}`"
                                               class="h-3 w-3 text-purple-400 focus:ring-purple-500">
                                        <label :for="`featured_img_new_${idx}`" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">Set Main Featured</label>
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
        'values' => $productAttribute->values->map(fn ($value) => [
            'id' => $value->id,
            'name' => $value->name,
            'color_code' => $value->color_code,
        ])->values(),
    ])->values();

    $variationRowsPayload = $product->variations->map(fn ($variation) => [
        'id' => $variation->id,
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

    $attributeRowsPayload = $product->productAttributes->map(fn ($productAttribute) => [
        'product_attribute_id' => $productAttribute->id,
        'attribute_id' => $productAttribute->attribute_id,
        'custom_name' => $productAttribute->custom_name,
        'options' => implode(', ', (array) $productAttribute->custom_options),
        'value_ids' => $productAttribute->values->pluck('id')->map(fn ($id) => (int) $id)->all(),
        'is_visible' => (bool) $productAttribute->is_visible,
        'is_variation' => (bool) $productAttribute->is_variation,
    ])->values();
@endphp
<script>
    function productForm() {
        return {
            productType: '{{ $product->product_type }}',
            allAttributes: {!! json_encode($allAttributesPayload) !!},
            variationOptions: {!! json_encode($variationOptionsPayload) !!},
            attributes: {!! json_encode($attributeRowsPayload) !!},
            variations: {!! json_encode($variationRowsPayload) !!},

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

                const attribute = this.allAttributes.find(a => a.id == row.attribute_id);
                return attribute ? attribute.values : [];
            },

            onAttributeChange(index) {
                this.attributes[index].value_ids = [];
            },

            /** Only the attributes flagged for variations, in display order. */
            variationAttributes() {
                return this.variationOptions.filter(a => a.is_variation);
            },

            optionsForProductAttribute(productAttribute) {
                if (productAttribute.attribute_id) {
                    const attribute = this.allAttributes.find(a => a.id == productAttribute.attribute_id);
                    return attribute ? attribute.values : [];
                }

                return productAttribute.values;
            },

            addVariation() {
                this.variations.push({
                    id: '',
                    values: {},
                    regular_price: '',
                    sale_price: '',
                    manage_stock: true,
                    stock_quantity: 0,
                    stock_status: 'instock',
                    sku: '',
                    weight_value: '',
                    status: 'publish',
                    combo_key: null,
                    image_url: null,
                    remove_image: false,
                });
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

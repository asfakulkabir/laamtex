@extends('layouts.admin')

@section('title', 'Create Product - laamtex')
@section('page_title', 'Create Product')

@section('content')
<div class="bg-gradient-to-br from-slate-900 to-slate-950 border border-slate-800/50 p-8 rounded-2xl shadow-lg" x-data="productForm()">
    
    <div class="mb-8 pb-4 border-b border-slate-800/50">
        <h3 class="font-bold text-white text-lg">Product Details</h3>
        <p class="text-sm text-slate-300 mt-1">Fill out the basic information, product options, and upload product catalog images.</p>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Left 2 Columns: Product Info -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Product Name -->
                <div>
                    <label for="name" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Product Title <span class="text-pink-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="e.g. Silk V-Neck Dress">
                    @error('name')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Product Type Selector -->
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
                        <input type="number" step="0.01" min="0" id="regular_price" name="regular_price" value="{{ old('regular_price') }}" required
                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                               placeholder="49.99">
                        @error('regular_price')
                            <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label for="sale_price" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Sale Price (৳)</label>
                        <input type="number" step="0.01" min="0" id="sale_price" name="sale_price" value="{{ old('sale_price') }}"
                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                               placeholder="39.99">
                        @error('sale_price')
                            <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Simple Product Stock -->
                <div x-show="productType === 'simple'">
                    <label for="stock_quantity" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Stock Quantity <span class="text-pink-500">*</span></label>
                    <input type="number" min="0" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', 10) }}"
                           x-bind:required="productType === 'simple'"
                           class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                           placeholder="15">
                    @error('stock_quantity')
                        <span class="text-sm text-red-500 mt-1 block">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Variable Product Attributes (Only visible when productType is 'variable') -->
                <div x-show="productType === 'variable'" class="space-y-4 bg-purple-500/5 p-6 rounded-xl border border-purple-500/20">
                    <div class="flex justify-between items-center pb-2 border-b border-purple-500/20">
                        <h4 class="font-bold text-slate-200 text-sm">Product Attributes</h4>
                        <button type="button" @click="addAttribute()" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded text-sm font-bold transition">
                            + Add Attribute
                        </button>
                    </div>

                    <p class="text-xs text-slate-400">
                        Choose a global attribute and tick the values this product offers. Attributes marked
                        <span class="text-purple-300 font-semibold">Used for variations</span> build the variation combinations
                        automatically. Save the product, then set the price, stock and image for each row on the edit screen.
                    </p>

                    <div class="space-y-3">
                        <template x-for="(a, index) in attributes" :key="a.key || index">
                            <div class="bg-slate-900/60 border border-slate-800/50 rounded-lg p-4 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Attribute</label>
                                        <select :name="`attributes[${index}][attribute_id]`" x-model="a.attribute_id"
                                                @change="a.value_ids = []; syncVariations()"
                                                class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                            <option value="">— Custom attribute —</option>
                                            <template x-for="g in allAttributes" :key="g.id">
                                                <option :value="g.id" x-text="g.name"></option>
                                            </template>
                                        </select>
                                    </div>

                                    <div x-show="!a.attribute_id" class="md:col-span-2">
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Custom name</label>
                                        <input type="text" :name="`attributes[${index}][custom_name]`" x-model="a.custom_name" @input.debounce.400ms="syncVariations()" placeholder="e.g. Material"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>

                                    <div x-show="!a.attribute_id">
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Options (comma separated)</label>
                                        <input type="text" :name="`attributes[${index}][options]`" x-model="a.options" @input.debounce.400ms="syncVariations()" placeholder="Cotton, Silk"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>

                                    <div class="flex items-center justify-end gap-4">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="checkbox" :name="`attributes[${index}][is_visible]`" value="1" x-model="a.is_visible"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Visible
                                        </label>
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="checkbox" :name="`attributes[${index}][is_variation]`" value="1" x-model="a.is_variation" @change="syncVariations()"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Used for variations
                                        </label>
                                        <button type="button" @click="removeAttribute(index); syncVariations()" class="p-1 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded text-sm transition">Remove</button>
                                    </div>
                                </div>

                                <div x-show="a.attribute_id" class="flex flex-wrap gap-2">
                                    <template x-if="optionsFor(index).length === 0">
                                        <span class="text-xs text-slate-500">This attribute has no values yet. Add them under Attributes in the admin menu.</span>
                                    </template>
                                    <template x-for="v in optionsFor(index)" :key="v.id">
                                        <label class="flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs border cursor-pointer transition"
                                               :class="a.value_ids.includes(v.id) ? 'bg-purple-500/20 border-purple-500/50 text-purple-200' : 'bg-slate-800/50 border-slate-700/50 text-slate-400 hover:border-purple-500/30'">
                                            <input type="checkbox" :name="`attributes[${index}][value_ids][]`" :value="v.id" x-model="a.value_ids" @change="syncVariations()"
                                                   class="h-3 w-3 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            <span x-show="v.color_code" class="inline-block h-3 w-3 rounded-full border border-slate-600" :style="`background-color:${v.color_code}`"></span>
                                            <span x-text="v.name"></span>
                                        </label>
                                    </template>
                                </div>

                                <div x-show="!a.attribute_id && a.options" class="text-xs text-slate-400">
                                    Will create values: <span x-text="a.options"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                </div>

                <!-- Variations (variable products only) -->
                <div x-show="productType === 'variable'" class="space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="text-lg font-bold text-white">Variations</h3>
                            <p class="text-xs text-slate-400">
                                Every combination of the ticked values is created automatically.
                                Set the price, stock and image for each row, then save.
                            </p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-xs text-slate-400" x-show="variations.length > 0"
                                  x-text="`${variations.length} variation${variations.length === 1 ? '' : 's'}`"></span>
                            <button type="button" @click="generateVariations()"
                                    class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded text-sm font-bold transition">
                                Generate from Attributes
                            </button>
                        </div>
                    </div>

                    <template x-if="variationAttributes().length > 0 && variations.length === 0">
                        <div class="flex items-center justify-between gap-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-4 py-3">
                            <p class="text-xs text-amber-200">
                                You removed all rows. Tick the values again to bring them back.
                            </p>
                            <button type="button" @click="restoreRemovedVariations()"
                                    class="shrink-0 px-3 py-1.5 bg-amber-500/20 hover:bg-amber-500/30 text-amber-200 rounded text-xs font-bold transition">
                                Restore
                            </button>
                        </div>
                    </template>

                    <div class="space-y-3">
                        <template x-if="variationAttributes().length === 0">
                            <p class="text-sm text-slate-400 py-4 text-center">
                                No attributes are marked “Used for variations” yet. Add one above to build combinations.
                            </p>
                        </template>

                        <template x-for="(v, index) in variations" :key="v.combo_key || index">
                            <div class="bg-slate-900/60 border border-slate-800/50 rounded-lg p-4 space-y-3">
                                <input type="hidden" :name="`variations[${index}][id]`" x-model="v.id">
                                <input type="hidden" :name="`variations[${index}][combo_key]`" x-model="v.combo_key">

                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <template x-for="pa in variationAttributes()" :key="pa.key">
                                            <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-700/60 bg-slate-800/60 px-2.5 py-1 text-xs text-slate-200">
                                                <span class="text-slate-500" x-text="pa.name + ':'"></span>
                                                <span class="font-semibold" x-text="valueLabel(pa, v.values[pa.key])"></span>
                                                <input type="hidden" :name="`variations[${index}][values][${pa.position}]`" :value="v.values[pa.key]">
                                            </span>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <label class="flex items-center gap-1.5 text-xs text-slate-300">
                                            <input type="hidden" :name="`variations[${index}][status]`" x-model="v.status">
                                            <input type="checkbox" value="1"
                                                   :checked="v.status === 'publish'"
                                                   @change="v.status = $event.target.checked ? 'publish' : 'private'"
                                                   class="h-3.5 w-3.5 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                                            Enabled
                                        </label>
                                        <button type="button" @click="removeVariation(index)"
                                                class="px-3 py-1.5 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 rounded text-xs font-bold transition">
                                            Remove row
                                        </button>
                                    </div>
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
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 items-end">
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Image</label>
                                        <input type="file" :name="`variations[${index}][image]`" accept="image/*"
                                               class="text-[11px] text-slate-300 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-slate-700 file:text-white text-xs">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Weight</label>
                                        <input type="number" step="0.001" min="0" :name="`variations[${index}][weight_value]`" x-model="v.weight_value"
                                               class="w-full bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm text-slate-200 focus:outline-none focus:ring-1 focus:ring-purple-500">
                                    </div>
                                    <label class="flex items-center gap-2 text-xs text-slate-300">
                                        <input type="checkbox" :name="`variations[${index}][manage_stock]`" value="1" x-model="v.manage_stock"
                                               class="rounded bg-slate-800 border-slate-700">
                                        Manage stock
                                    </label>
                                    <div class="flex justify-end">
                                        <button type="button" @click="removeVariation(index)"
                                                class="px-3 py-1.5 bg-pink-500/10 hover:bg-pink-500/20 text-pink-400 rounded text-xs font-bold transition">
                                            Remove row
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Descriptions -->
                <div>
                    <label for="short_description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Short Description</label>
                    <input type="hidden" name="short_description" id="short_description_input">
                    <div id="short_description_editor" class="bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-200"></div>
                </div>

                <div>
                    <label for="description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Detailed Description</label>
                    <input type="hidden" name="description" id="description_input">
                    <div id="description_editor" class="bg-slate-800/50 border border-slate-700/50 rounded-lg text-slate-200"></div>
                </div>

                <!-- SEO Fields -->
                <div class="border-t border-slate-800/50 pt-6 space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm">SEO Meta Fields</h4>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label for="seo_title" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">SEO Title</label>
                            <input type="text" id="seo_title" name="seo_title"
                                   class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                                   placeholder="Title tag for search engines">
                        </div>
                        <div>
                            <label for="meta_description" class="block text-sm font-bold uppercase tracking-wider text-slate-300 mb-2">Meta Description</label>
                            <textarea id="meta_description" name="meta_description" rows="2"
                                      class="w-full bg-slate-800/50 border border-slate-700/50 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:bg-slate-900/80 transition-all text-slate-200 placeholder-slate-600"
                                      placeholder="Brief text that displays in search engines results..."></textarea>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sidebar Options -->
            <div class="space-y-6">
                
                <!-- Status Options -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50 space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm">Publish Settings</h4>
                    
                    <div class="flex items-center">
                        <input id="is_active" name="is_active" type="checkbox" checked value="1"
                               class="h-4 w-4 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                        <label for="is_active" class="ml-2 block text-sm font-semibold text-slate-300">Active (Visible to customers)</label>
                    </div>

                    <div class="flex items-center">
                        <input id="is_featured" name="is_featured" type="checkbox" value="1"
                               class="h-4 w-4 rounded border-slate-600 text-purple-400 focus:ring-purple-500">
                        <label for="is_featured" class="ml-2 block text-sm font-semibold text-slate-300">Featured Highlight</label>
                    </div>
                </div>

                <!-- Categories Checkboxes -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50">
                    <h4 class="font-bold text-slate-200 text-sm mb-4">Categories Mappings</h4>
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-2">
                        @foreach($categories as $category)
                            <div class="flex items-center">
                                <input id="cat_{{ $category->id }}" name="categories[]" type="checkbox" value="{{ $category->id }}"
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

                <!-- Product Images Upload -->
                <div class="bg-slate-800/30 p-6 rounded-xl border border-slate-800/50 space-y-4">
                    <h4 class="font-bold text-slate-200 text-sm">Product Images Gallery</h4>
                    
                    <button type="button" @click="addImageField()" class="w-full text-center px-4 py-2 bg-purple-500/10 border border-purple-500/20 text-purple-400 rounded-md text-sm font-bold hover:bg-purple-500/20 transition">
                        + Add Image Upload Row
                    </button>

                    <div class="space-y-4">
                        <template x-for="(img, idx) in uploadImages" :key="idx">
                            <div class="p-3 bg-slate-900/80 border border-slate-700/50 rounded-lg space-y-2 relative">
                                <button type="button" @click="removeImageField(idx)" class="absolute top-2 right-2 text-pink-400 hover:text-pink-300 text-sm font-bold">✕</button>
                                
                                <div>
                                    <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Image File</label>
                                    <input type="file" name="images[]" required accept="image/*"
                                           class="w-full text-sm text-slate-400 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-purple-500/10 file:text-purple-400 file:text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Name</label>
                                        <input type="text" :name="`image_names[${idx}]`" placeholder="front_view"
                                               class="w-full border border-slate-700/50 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-bold text-slate-300 uppercase mb-1">Alt Text</label>
                                        <input type="text" :name="`image_alts[${idx}]`" placeholder="alt text"
                                               class="w-full border border-slate-700/50 rounded px-2 py-1 text-sm focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                    </div>
                                </div>
                                <div class="flex items-center">
                                    <input type="radio" :id="`featured_img_${idx}`" name="image_featured_index" :value="idx" :checked="idx === 0"
                                           class="h-3.5 w-3.5 text-purple-400 focus:ring-purple-500">
                                    <label :for="`featured_img_${idx}`" class="ml-1.5 text-sm font-bold text-slate-400 uppercase">Set Main Featured</label>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

        </div>

        <!-- Submit CTAs -->
        <div class="flex items-center space-x-4 pt-6 border-t border-slate-800/50">
            <button type="submit"
                    class="px-8 py-3 bg-gradient-to-r from-purple-600 to-pink-500 hover:from-purple-700 hover:to-pink-600 text-white font-bold rounded-lg text-sm transition-all shadow-md">
                Create Product
            </button>
            <a href="{{ route('admin.products.index') }}"
               class="px-8 py-3 bg-slate-800/50 hover:bg-slate-700/50 text-slate-300 font-bold rounded-lg text-sm transition-all">
                Cancel
            </a>
        </div>
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
@endphp
<script>
    function productForm() {
        return {
            productType: 'simple',
            allAttributes: {!! json_encode($allAttributesPayload) !!},
            attributes: [],
            variations: [],
            removedCombos: [],
            nextKey: 1,
            uploadImages: [
                { name: 'front_view', alt_text: '' }
            ],

            addAttribute() {
                this.attributes.push({
                    key: 'a' + (this.nextKey++),
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

            optionsFor(index) {
                const row = this.attributes[index];
                if (!row || !row.attribute_id) return [];

                const attribute = this.allAttributes.find(a => a.id == row.attribute_id);
                return attribute ? attribute.values : [];
            },

            /**
             * The attributes flagged "Used for variations", each reduced to the
             * values the shopper ticked. `position` is the index in the submitted
             * attributes array, which the controller maps to a real
             * product attribute id once the product exists.
             */
            variationAttributes() {
                return this.attributes
                    .map((row, position) => ({ row, position }))
                    .filter(({ row }) => row.is_variation)
                    .map(({ row, position }) => {
                        let values = [];

                        if (row.attribute_id) {
                            const attribute = this.allAttributes.find(a => a.id == row.attribute_id);
                            const picked = (attribute ? attribute.values : []).filter(
                                v => (row.value_ids || []).some(id => String(id) === String(v.id))
                            );
                            values = picked;
                        } else if (row.options) {
                            // Custom attributes are stored by name, so the name is
                            // the identifier the controller resolves afterwards.
                            values = String(row.options)
                                .split(',')
                                .map(s => s.trim())
                                .filter(Boolean)
                                .map(name => ({ id: name, name: name }));
                        }

                        return {
                            key: row.key,
                            position: position,
                            name: row.attribute_id
                                ? (this.allAttributes.find(a => a.id == row.attribute_id) || {}).name || ''
                                : row.custom_name,
                            values: values,
                        };
                    })
                    .filter(a => a.values.length > 0);
            },

            /**
             * Build the cartesian product of the selected values. Rows the admin
             * already filled in are kept, matched on their combination, and rows
             * the admin removed stay removed until their combination is
             * un-ticked and ticked again.
             */
            generateVariations() {
                const attributes = this.variationAttributes();

                if (attributes.length === 0) {
                    this.variations = [];
                    this.removedCombos = [];
                    return;
                }

                let combinations = [{}];

                for (const attribute of attributes) {
                    const next = [];
                    for (const combination of combinations) {
                        for (const value of attribute.values) {
                            next.push(Object.assign({}, combination, { [attribute.key]: value.id }));
                        }
                    }
                    combinations = next;
                }

                const existing = {};
                this.variations.forEach(row => {
                    if (row.combo_key) existing[row.combo_key] = row;
                });

                this.forgetRemovedCombosMissingFrom(attributes);

                const removed = this.removedCombos;
                this.variations = combinations
                    .filter(combination => !removed.includes(this.comboKeyFor(attributes, combination)))
                    .map(combination => {
                        const key = this.comboKeyFor(attributes, combination);

                        if (existing[key]) {
                            return Object.assign({}, existing[key], { combo_key: key });
                        }

                        return {
                            id: '',
                            combo_key: key,
                            values: combination,
                            regular_price: '',
                            sale_price: '',
                            manage_stock: true,
                            stock_quantity: 0,
                            stock_status: 'instock',
                            sku: '',
                            weight_value: '',
                            status: 'publish',
                        };
                    });
            },

            comboKeyFor(attributes, combination) {
                return attributes
                    .map(a => a.key + ':' + combination[a.key])
                    .join('|');
            },

            /** The human readable value shown on a generated row. */
            valueLabel(attribute, valueId) {
                if (valueId === null || valueId === undefined || valueId === '') return 'Any';

                for (const value of attribute.values) {
                    if (String(value.id) === String(valueId)) return value.name;
                }

                return String(valueId);
            },

            removeVariation(index) {
                const row = this.variations[index];
                if (row && row.combo_key) {
                    this.removedCombos.push(row.combo_key);
                }
                this.variations.splice(index, 1);
            },

            restoreRemovedVariations() {
                this.removedCombos = [];
                this.generateVariations();
            },

            /**
             * A row the admin removed comes back when its combination stops
             * being offered and is ticked again, which is how WooCommerce
             * behaves after deleting a variation.
             */
            forgetRemovedCombosMissingFrom(attributes) {
                if (this.removedCombos.length === 0) return;

                const live = new Set();

                let combinations = [{}];
                for (const attribute of attributes) {
                    const next = [];
                    for (const combination of combinations) {
                        for (const value of attribute.values) {
                            next.push(Object.assign({}, combination, { [attribute.key]: value.id }));
                        }
                    }
                    combinations = next;
                }
                combinations.forEach(c => live.add(this.comboKeyFor(attributes, c)));

                this.removedCombos = this.removedCombos.filter(key => live.has(key));
            },


            // Regenerate whenever the selected attributes or values change, so
            // the rows always match what is ticked above. Anything the admin has
            // already typed is preserved.
            syncVariations() {
                this.$nextTick(() => this.generateVariations());
            },

            addImageField() {
                this.uploadImages.push({ name: '', alt_text: '' });
            },
            removeImageField(index) {
                this.uploadImages.splice(index, 1);
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const shortEditor = new Quill('#short_description_editor', { theme: 'snow', placeholder: 'Describe the product highlight briefly...' });
        const descEditor = new Quill('#description_editor', { theme: 'snow', placeholder: 'Provide the complete product details, care instructions, material compositions...' });

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

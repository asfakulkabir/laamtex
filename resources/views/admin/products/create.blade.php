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

                <!-- Variable Product Details (Only visible when productType is 'variable') -->
                <div x-show="productType === 'variable'" class="space-y-4 bg-purple-500/5 p-6 rounded-xl border border-purple-500/20">
                    <div class="flex justify-between items-center pb-2 border-b border-purple-500/20">
                        <h4 class="font-bold text-slate-200 text-sm">Product Variations Mappings</h4>
                        <button type="button" @click="addVariation()" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white rounded text-sm font-bold transition">
                            + Add Row
                        </button>
                    </div>

                    <div class="space-y-3">
                        <!-- Headings -->
                        <div class="grid grid-cols-6 gap-2 text-sm font-bold uppercase text-slate-300">
                            <div>Size</div>
                            <div>Color</div>
                            <div>Weight</div>
                            <div>Price (৳)</div>
                            <div>Stock</div>
                            <div class="text-right">Action</div>
                        </div>

                        <!-- Rows -->
                        <template x-for="(v, index) in variations" :key="index">
                            <div class="grid grid-cols-6 gap-2 items-center">
                                <input type="text" :name="`variations[${index}][size]`" x-model="v.size" placeholder="e.g. M"
                                       class="bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                <input type="text" :name="`variations[${index}][color]`" x-model="v.color" placeholder="e.g. Red"
                                       class="bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                <input type="text" :name="`variations[${index}][weight]`" x-model="v.weight" placeholder="e.g. 200g"
                                       class="bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                <input type="number" step="0.01" min="0" :name="`variations[${index}][price]`" x-model="v.price" placeholder="45.99"
                                       class="bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                <input type="number" min="0" :name="`variations[${index}][stock]`" x-model="v.stock" placeholder="10" required
                                       class="bg-slate-800/50 border border-slate-700/50 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-purple-500 text-slate-200 placeholder-slate-600">
                                <div class="text-right">
                                    <button type="button" @click="removeVariation(index)" class="p-1 text-pink-400 hover:text-pink-300 bg-pink-500/10 hover:bg-pink-500/20 rounded text-sm transition">Remove</button>
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
<script>
    function productForm() {
        return {
            productType: 'simple',
            variations: [
                { size: '', color: '', weight: '', price: '', stock: 10 }
            ],
            uploadImages: [
                { name: 'front_view', alt_text: '' }
            ],
            addVariation() {
                this.variations.push({ size: '', color: '', weight: '', price: '', stock: 10 });
            },
            removeVariation(index) {
                this.variations.splice(index, 1);
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

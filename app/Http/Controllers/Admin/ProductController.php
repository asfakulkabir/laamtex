<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with(['categories', 'images']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('product_type', $request->input('type'));
        }

        $products = $query->orderBy('sort_order')->latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|required_if:product_type,simple|integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            
            // Variations validation
            'variations' => 'nullable|array',
            'variations.*.size' => 'nullable|string|max:50',
            'variations.*.weight' => 'nullable|string|max:50',
            'variations.*.color' => 'nullable|string|max:50',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',

            // Images validation
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'image_names' => 'nullable|array',
            'image_alts' => 'nullable|array',
            'image_featured_index' => 'nullable|integer',
        ]);

        $productData = $request->only([
            'name', 'product_type', 'short_description', 'description', 
            'seo_title', 'meta_description'
        ]);
        
        $productData['user_id'] = auth()->id();
        $productData['is_active'] = $request->boolean('is_active', true);
        $productData['is_featured'] = $request->boolean('is_featured', false);

        $productData['regular_price'] = $request->input('regular_price');
        $productData['sale_price'] = $request->input('sale_price');

        if ($request->input('product_type') === 'simple') {
            $productData['stock_quantity'] = $request->input('stock_quantity', 10);
        } else {
            $productData['stock_quantity'] = 0; // Will update from variations
        }

        $product = Product::create($productData);

        // Sync Categories
        if ($request->has('categories')) {
            $product->categories()->sync($request->input('categories'));
        }

        // Create Variations if variable
        if ($product->product_type === 'variable' && $request->has('variations')) {
            foreach ($request->input('variations') as $variationData) {
                if (empty($variationData['size']) && empty($variationData['color']) && empty($variationData['weight'])) {
                    continue; // Skip blank variations
                }
                $product->variations()->create([
                    'size' => $variationData['size'],
                    'weight' => $variationData['weight'],
                    'color' => $variationData['color'],
                    'price' => $variationData['price'] ?: null,
                    'stock' => $variationData['stock'] ?: 0,
                ]);
            }
            $product->updateStockFromVariations();
        }

        // Upload Images
        if ($request->hasFile('images')) {
            $featuredIndex = $request->input('image_featured_index', 0);
            $uploadedFiles = $request->file('images');

            foreach ($uploadedFiles as $index => $file) {
                $path = $file->store('product_images', 'public');
                $name = $request->input("image_names.{$index}") ?: ('image_' . Str::random(5));
                $alt = $request->input("image_alts.{$index}") ?: $product->name;
                $isFeatured = ($index == $featuredIndex);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'name' => $name,
                    'alt_text' => $alt,
                    'is_featured' => $isFeatured,
                    'order' => $index,
                ]);
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $product->load(['categories', 'images', 'variations']);
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|required_if:product_type,simple|integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            // Variations
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|exists:product_variations,id',
            'variations.*.size' => 'nullable|string|max:50',
            'variations.*.weight' => 'nullable|string|max:50',
            'variations.*.color' => 'nullable|string|max:50',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'required_with:variations|integer|min:0',

            // Existing images details updates
            'existing_images' => 'nullable|array',
            'existing_images.*.id' => 'required|exists:product_images,id',
            'existing_images.*.name' => 'nullable|string|max:255',
            'existing_images.*.alt_text' => 'nullable|string|max:255',
            'existing_images.*.order' => 'integer',
            'existing_images_featured_id' => 'nullable|integer',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',

            // New Images upload
            'new_images' => 'nullable|array',
            'new_images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'new_images_names' => 'nullable|array',
            'new_images_alts' => 'nullable|array',
            'new_image_featured_temp' => 'nullable|string', // "new_0", "existing_23"
        ]);

        // Capture original product type
        $originalType = $product->product_type;

        $productData = $request->only([
            'name', 'product_type', 'short_description', 'description', 
            'seo_title', 'meta_description'
        ]);
        
        $productData['is_active'] = $request->boolean('is_active', true);
        $productData['is_featured'] = $request->boolean('is_featured', false);
        $productData['regular_price'] = $request->input('regular_price');
        $productData['sale_price'] = $request->input('sale_price');

        if ($request->input('product_type') === 'simple') {
            $productData['stock_quantity'] = $request->input('stock_quantity', 10);
            
            // Delete all variations if product type switched to simple
            $product->variations()->delete();
        } else {
            // Stock quantity will be computed from variations
        }

        $product->update($productData);

        // Sync Categories
        if ($request->has('categories')) {
            $product->categories()->sync($request->input('categories'));
        } else {
            $product->categories()->detach();
        }

        // Manage Variations if variable
        if ($product->product_type === 'variable') {
            $submittedVariationIds = [];

            if ($request->has('variations')) {
                foreach ($request->input('variations') as $variationData) {
                    if (empty($variationData['size']) && empty($variationData['color']) && empty($variationData['weight'])) {
                        continue; // Skip blanks
                    }

                    if (!empty($variationData['id'])) {
                        // Update existing variation
                        $variation = ProductVariation::findOrFail($variationData['id']);
                        $variation->update([
                            'size' => $variationData['size'],
                            'weight' => $variationData['weight'],
                            'color' => $variationData['color'],
                            'price' => $variationData['price'] ?: null,
                            'stock' => $variationData['stock'] ?: 0,
                        ]);
                        $submittedVariationIds[] = $variation->id;
                    } else {
                        // Create new variation
                        $variation = $product->variations()->create([
                            'size' => $variationData['size'],
                            'weight' => $variationData['weight'],
                            'color' => $variationData['color'],
                            'price' => $variationData['price'] ?: null,
                            'stock' => $variationData['stock'] ?: 0,
                        ]);
                        $submittedVariationIds[] = $variation->id;
                    }
                }
            }

            // Delete variations not submitted
            $product->variations()->whereNotIn('id', $submittedVariationIds)->delete();
            $product->updateStockFromVariations();
        }

        // Handle Image Deletions
        if ($request->has('delete_images')) {
            foreach ($request->input('delete_images') as $imgId) {
                $img = ProductImage::find($imgId);
                if ($img) {
                    $img->delete(); // Model observer automatically deletes file
                }
            }
        }

        // Determine which image is featured
        $featuredId = $request->input('existing_images_featured_id'); // If existing is chosen
        $newFeaturedSelect = $request->input('new_image_featured_temp'); // Format: "existing_12" or "new_0"

        // Update existing images meta
        if ($request->has('existing_images')) {
            foreach ($request->input('existing_images') as $imgData) {
                $img = ProductImage::find($imgData['id']);
                if ($img) {
                    $isFeatured = ($img->id == $featuredId);
                    
                    // Or if it matches new_image_featured_temp
                    if ($newFeaturedSelect && $newFeaturedSelect === "existing_" . $img->id) {
                        $isFeatured = true;
                    } elseif ($newFeaturedSelect && Str::startsWith($newFeaturedSelect, 'new_')) {
                        $isFeatured = false; // A new image will be featured
                    }

                    $img->update([
                        'name' => $imgData['name'],
                        'alt_text' => $imgData['alt_text'] ?: $product->name,
                        'order' => $imgData['order'] ?: 0,
                        'is_featured' => $isFeatured,
                    ]);
                }
            }
        }

        // Upload New Images
        if ($request->hasFile('new_images')) {
            $newImages = $request->file('new_images');
            foreach ($newImages as $index => $file) {
                $path = $file->store('product_images', 'public');
                $name = $request->input("new_images_names.{$index}") ?: ('image_' . Str::random(5));
                $alt = $request->input("new_images_alts.{$index}") ?: $product->name;
                
                $isFeatured = false;
                if ($newFeaturedSelect === "new_" . $index) {
                    $isFeatured = true;
                }

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'name' => $name,
                    'alt_text' => $alt,
                    'is_featured' => $isFeatured,
                    'order' => 100 + $index, // Put new ones at the end
                ]);
            }
        }

        // Ensure exactly one image is featured if images exist
        $images = $product->images()->get();
        if ($images->count() > 0 && $images->where('is_featured', true)->isEmpty()) {
            $firstImg = $images->first();
            $firstImg->is_featured = true;
            $firstImg->saveQuietly();
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer|exists:products,id',
        ]);

        foreach ($request->input('order') as $index => $productId) {
            Product::where('id', $productId)->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true, 'message' => 'Product order updated.']);
    }

    public function exportCsv()
    {
        $products = Product::with('categories')->orderBy('sort_order')->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products-export.csv"',
        ];

        $callback = function () use ($products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['id', 'name', 'slug', 'product_type', 'regular_price', 'sale_price', 'stock_quantity', 'is_active', 'is_featured', 'sort_order', 'categories', 'short_description', 'description', 'seo_title', 'meta_description']);

            foreach ($products as $product) {
                fputcsv($handle, [
                    $product->id,
                    $product->name,
                    $product->slug,
                    $product->product_type,
                    $product->regular_price,
                    $product->sale_price,
                    $product->stock_quantity,
                    $product->is_active ? 1 : 0,
                    $product->is_featured ? 1 : 0,
                    $product->sort_order,
                    $product->categories->pluck('name')->implode('|'),
                    $product->short_description,
                    $product->description,
                    $product->seo_title,
                    $product->meta_description,
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);

        if (!$header) {
            fclose($handle);
            return redirect()->back()->with('error', 'Invalid CSV file.');
        }

        $header = array_map('trim', $header);
        $imported = 0;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            if (empty($data['name'])) {
                $errors[] = 'Row has no name, skipped.';
                continue;
            }

            try {
                $productData = [
                    'name' => $data['name'],
                    'slug' => $data['slug'] ?? Str::slug($data['name']),
                    'product_type' => $data['product_type'] ?? 'simple',
                    'regular_price' => $data['regular_price'] ?? 0,
                    'sale_price' => $data['sale_price'] ?? null,
                    'stock_quantity' => $data['stock_quantity'] ?? 10,
                    'is_active' => filter_var($data['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                    'is_featured' => filter_var($data['is_featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'sort_order' => $data['sort_order'] ?? 0,
                    'short_description' => $data['short_description'] ?? null,
                    'description' => $data['description'] ?? null,
                    'seo_title' => $data['seo_title'] ?? null,
                    'meta_description' => $data['meta_description'] ?? null,
                    'user_id' => auth()->id(),
                ];

                $product = Product::create($productData);

                if (!empty($data['categories'])) {
                    $catNames = array_map('trim', explode('|', $data['categories']));
                    $catIds = [];
                    foreach ($catNames as $catName) {
                        $category = Category::firstOrCreate(['name' => $catName], ['slug' => Str::slug($catName)]);
                        $catIds[] = $category->id;
                    }
                    $product->categories()->sync($catIds);
                }

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error importing '{$data['name']}': " . $e->getMessage();
            }
        }

        fclose($handle);

        $message = "Imported {$imported} products successfully.";
        if (!empty($errors)) {
            $message .= ' ' . implode(' ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.products.index')->with('success', $message);
    }

    public function destroy(Product $product)
    {
        // Image files will be deleted automatically due to ProductImage model observer booting events!
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }
}

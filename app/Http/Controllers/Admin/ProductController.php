<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use App\Models\SizeChart;
use App\Services\VariationService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function __construct(private readonly VariationService $variationService)
    {
    }

    /**
     * The internal costing price. An empty number input posts "", which means
     * "not set" rather than zero, so it is stored as null.
     */
    private function parseCostPrice(Request $request): ?string
    {
        $value = trim((string) $request->input('cost_price', ''));

        if ($value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * The new admin UI posts a `values` map keyed by product attribute id.
     * The legacy UI only posted size/color/weight columns.
     */
    private function submittedVariationsAreAttributeBased(Request $request): bool
    {
        foreach ((array) $request->input('variations', []) as $row) {
            if (array_key_exists('values', $row)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, \Illuminate\Http\UploadedFile|null>
     */
    /**
     * Count the files that actually arrived.
     *
     * PHP silently drops everything past `max_file_uploads`, so a product with
     * more images than that saves fewer images than the admin uploaded without
     * ever saying so.
     */
    private function countUploadedFiles(Request $request): int
    {
        $count = 0;

        $walk = function ($files) use (&$walk, &$count) {
            foreach ((array) $files as $file) {
                if (is_array($file)) {
                    $walk($file);
                } else {
                    $count++;
                }
            }
        };

        $walk($request->allFiles());

        return $count;
    }

    /**
     * Warn when the upload was clipped by the server's file count limit.
     */
    private function warnIfUploadsWereClipped(Request $request): void
    {
        $max = (int) (ini_get('max_file_uploads') ?: 20);

        if ($max > 0 && $this->countUploadedFiles($request) >= $max) {
            session()->flash('error', "Only the first {$max} uploaded files were kept: this server accepts at most {$max} files per submission, so some images were skipped. Raise max_file_uploads in php.ini to upload more at once.");
        }
    }

    /**
     * Keep the gallery marks consistent: exactly one main image, at most one
     * second (hover) image, and the two can never land on the same row.
     *
     * The form posts two independent radio groups, so the same row can be
     * picked for both, and picking a new image as the main one used to leave
     * the previous main image flagged as well.
     */
    private function reconcileGalleryMarks(Product $product): void
    {
        $images = $product->images()->orderBy('order')->orderBy('id')->get();

        if ($images->isEmpty()) {
            return;
        }

        $main = $images->firstWhere('is_featured', true) ?? $images->first();
        $second = $images->firstWhere('is_secondary', true);

        if ($second && $second->id === $main->id) {
            $second = null;
        }

        foreach ($images as $image) {
            $isMain = $image->id === $main->id;
            $isSecond = $second !== null && $image->id === $second->id;

            if ($image->is_featured === $isMain && $image->is_secondary === $isSecond) {
                continue;
            }

            $image->is_featured = $isMain;
            $image->is_secondary = $isSecond;
            $image->saveQuietly();
        }
    }

    private function uploadedVariationImages(Request $request): array
    {
        $files = $request->file('variations');

        if (! is_array($files)) {
            return [];
        }

        $uploaded = [];

        foreach ($files as $index => $fileSet) {
            if (is_array($fileSet) && isset($fileSet['image'])) {
                $uploaded[$index] = $fileSet['image'];
            }
        }

        return $uploaded;
    }

    /**
     * Gather a product gallery from whatever the form sent: fresh uploads,
     * library picks, or both.
     *
     * The picker posts one object per row with a file and/or a media id, e.g.
     * images[0][file] and images[0][media_id], keyed by the row index so it
     * lines up with image_names/image_alts and the featured marks. The legacy
     * flat images[] shape is still accepted so older forms keep working.
     *
     * @return array<int, string> Row index => stored path.
     */
    private function galleryRows(Request $request, string $filesKey, string $directory): array
    {
        $rows = [];

        foreach ((array) $request->file($filesKey) as $index => $fileSet) {
            $file = is_array($fileSet) ? ($fileSet['file'] ?? null) : $fileSet;

            if ($file instanceof UploadedFile) {
                $rows[(int) $index] = Media::storeUpload($file, $directory)->path;
            }
        }

        foreach ((array) $request->input($filesKey) as $index => $row) {
            $mediaId = is_array($row) ? ($row['media_id'] ?? null) : null;

            if (! $mediaId) {
                continue;
            }

            $picked = Media::find($mediaId);

            if ($picked && $picked->isImage() && Storage::disk($picked->disk ?: 'public')->exists($picked->path)) {
                $rows[(int) $index] = $picked->path;
            }
        }

        ksort($rows, SORT_NUMERIC);

        return $rows;
    }

    /**
     * On the create screen the attributes have no database id yet, so the form
     * keys each variation selection by the attribute's position in the submitted
     * array. Turn those keys into the real product attribute ids now that
     * `syncAttributes()` has created them.
     *
     * Custom attribute options are identified by name (their value rows are
     * generated server side), global ones by value id.
     */
    private function remapCreateVariationValues(Product $product, Request $request): array
    {
        $rows = (array) $request->input('variations', []);

        if ($rows === []) {
            return [];
        }

        $productAttributes = $product->productAttributes()->with('values')->get()
            ->keyBy(fn (ProductAttribute $attribute) => (int) $attribute->position);

        $remapped = [];

        foreach ($rows as $index => $row) {
            $row = is_array($row) ? $row : [];
            $values = [];

            foreach ((array) ($row['values'] ?? []) as $position => $value) {
                $productAttribute = $productAttributes[(int) $position] ?? null;

                if ($productAttribute === null) {
                    continue;
                }

                $valueId = $this->resolveCreateVariationValue($productAttribute, $value);

                if ($valueId !== null) {
                    $values[$productAttribute->id] = $valueId;
                }
            }

            $row['values'] = $values;
            $remapped[$index] = $row;
        }

        return $remapped;
    }

    private function resolveCreateVariationValue(ProductAttribute $productAttribute, $submitted): ?int
    {
        if (is_numeric($submitted) && ! $productAttribute->isCustom()) {
            $id = (int) $submitted;

            return $productAttribute->values->contains('id', $id) ? $id : null;
        }

        // Custom attributes send the option name.
        $name = is_string($submitted) ? trim($submitted) : '';

        if ($name === '') {
            return null;
        }

        return optional(
            $productAttribute->values->first(fn ($value) => $value->name === $name)
        )->id;
    }

    /**
     * Backwards compatible update of variations from the old size/color form.
     */
    private function updateLegacyVariations(Product $product, Request $request): void
    {
        $submittedVariationIds = [];

        foreach ((array) $request->input('variations', []) as $variationData) {
            if (empty($variationData['size']) && empty($variationData['color']) && empty($variationData['weight'])) {
                continue; // Skip blanks
            }

            $stock = (int) ($variationData['stock'] ?? 0);
            $fields = [
                'size' => $variationData['size'] ?? null,
                'weight' => $variationData['weight'] ?? null,
                'color' => $variationData['color'] ?? null,
                'price' => $variationData['price'] ?: null,
                'regular_price' => $variationData['price'] ?: null,
                'stock' => $stock,
                'stock_quantity' => $stock,
                'manage_stock' => true,
                'stock_status' => $stock > 0 ? 'instock' : 'outofstock',
            ];

            if (!empty($variationData['id'])) {
                // Only touch variations that actually belong to this product.
                $variation = $product->variations()->where('id', $variationData['id'])->first();

                if ($variation === null) {
                    continue;
                }

                $variation->update($fields);
            } else {
                $variation = $product->variations()->create($fields);
            }

            $submittedVariationIds[] = $variation->id;
        }

        // Nothing usable was submitted. That is a form that sent an empty or
        // blank variation set, not a request to delete every row, so the
        // existing variations are left alone.
        if ($submittedVariationIds === []) {
            return;
        }

        $product->variations()->whereNotIn('id', $submittedVariationIds)->delete();
    }

    /**
     * Create every missing combination of the variation attributes.
     */
    public function generateVariations(Request $request, Product $product)
    {
        if ($product->product_type !== 'variable') {
            return back()->with('error', 'Variations can only be generated for variable products.');
        }

        if ($request->has('attributes')) {
            $this->variationService->syncAttributes($product, $request->input('attributes', []));
        }

        $result = $this->variationService->generateVariations($product, $request->input('defaults', []));
        $product->updateStockFromVariations();

        $message = $result['created'] > 0
            ? "{$result['created']} variation(s) created."
            : 'No new variations to create; all combinations already exist.';

        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} already existed.";
        }

        return back()->with('success', $message);
    }

    /**
     * Apply a bulk action to the selected variations.
     */
    public function bulkVariationAction(Request $request, Product $product)
    {
        $request->validate([
            'action' => 'required|string',
            'variation_ids' => 'required|array',
            'variation_ids.*' => 'integer',
            'params' => 'nullable|array',
        ]);

        $affected = $this->variationService->applyBulkAction(
            $product,
            $request->input('action'),
            $request->input('params', []),
            $request->input('variation_ids', [])
        );

        $product->updateStockFromVariations();

        return back()->with('success', "{$affected} variation(s) updated.");
    }

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
        $attributes = Attribute::with('values')->get();
        $sizeCharts = SizeChart::orderBy('title')->get();
        return view('admin.products.create', compact('categories', 'attributes', 'sizeCharts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'sku' => 'nullable|string|max:255',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|required_if:product_type,simple|integer|min:0',
            'manage_stock' => 'nullable|boolean',
            'stock_status' => 'nullable|in:instock,outofstock,onbackorder',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'size_chart_id' => 'nullable|exists:size_charts,id',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            // Attributes
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'nullable|exists:attributes,id',
            'attributes.*.custom_name' => 'nullable|string|max:255',
            'attributes.*.value_ids' => 'nullable|array',
            'attributes.*.value_ids.*' => 'integer|exists:attribute_values,id',
            'attributes.*.is_visible' => 'nullable|boolean',
            'attributes.*.is_variation' => 'nullable|boolean',

            // Variations validation
            'variations' => 'nullable|array',
            'variations.*.size' => 'nullable|string|max:50',
            'variations.*.weight' => 'nullable|string|max:50',
            'variations.*.color' => 'nullable|string|max:50',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'nullable|integer|min:0',
            'variations.*.regular_price' => 'nullable|numeric|min:0',
            'variations.*.sale_price' => ['nullable', 'numeric', 'min:0', 'lt:variations.*.regular_price'],
            'variations.*.stock_quantity' => 'nullable|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variations.*.image_media_id' => 'nullable|integer',

            // Images validation
            'images' => 'nullable|array',
            'images.*' => 'nullable',
            'images.*.file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images.*.media_id' => 'nullable|integer',
            'image_names' => 'nullable|array',
            'image_alts' => 'nullable|array',
            'image_featured_index' => 'nullable|integer',
            'image_secondary_index' => 'nullable|integer',
        ]);

        $productData = $request->only([
            'name', 'product_type', 'short_description', 'description',
            'seo_title', 'meta_description', 'sku'
        ]);
        
        $productData['user_id'] = auth()->id();
        $productData['is_active'] = $request->boolean('is_active', true);
        $productData['is_featured'] = $request->boolean('is_featured', false);
        $productData['manage_stock'] = $request->boolean('manage_stock', false);
        $productData['stock_status'] = $request->input('stock_status', 'instock');

        $productData['regular_price'] = $request->input('regular_price');
        $productData['sale_price'] = $request->input('sale_price');
        $productData['cost_price'] = $this->parseCostPrice($request);

        // An empty select posts "", which means the admin picked "None".
        $productData['size_chart_id'] = $request->input('size_chart_id') ?: null;

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

        if ($product->product_type === 'variable') {
            $this->variationService->syncAttributes($product, $request->input('attributes', []));

            $createRows = $this->submittedVariationsAreAttributeBased($request)
                ? $this->remapCreateVariationValues($product, $request)
                : [];

            // Two rows for one combination cannot both be stored, so this
            // would silently keep the last one. Report it instead.
            if ($createRows !== []) {
                $duplicates = $this->variationService->duplicateCombinationLabels($product, $createRows);

                if ($duplicates !== []) {
                    $product->delete();

                    return back()
                        ->withInput()
                        ->withErrors(['variations' => 'Duplicate option combinations: ' . implode(', ', $duplicates) . '. Each combination can only be used once.']);
                }
            }

            // Term selection alone is enough to create the variation rows.
            $this->variationService->generateVariations($product);

            if ($createRows !== []) {
                $this->variationService->saveVariations(
                    $product,
                    $createRows,
                    $this->uploadedVariationImages($request)
                );
            } elseif ($request->has('variations')) {
                // Only touch the legacy rows when the old form actually sent
                // them, otherwise the generated rows would be wiped.
                $this->updateLegacyVariations($product, $request);
            }

            $this->variationService->refreshPriceRange($product);
            $product->updateStockFromVariations();
        }

        // Upload Images
        $galleryRows = $this->galleryRows($request, 'images', 'product_images');

        if ($galleryRows !== []) {
            $featuredIndex = $request->input('image_featured_index', 0);
            $secondaryIndex = $request->input('image_secondary_index');

            foreach ($galleryRows as $index => $path) {
                $name = $request->input("image_names.{$index}") ?: ('image_' . Str::random(5));
                $alt = $request->input("image_alts.{$index}") ?: $product->name;
                $isFeatured = ($index == $featuredIndex);
                $isSecondary = ($secondaryIndex !== null && $index == $secondaryIndex);

                ProductImage::create([
                    'product_id' => $product->id,
                    'image' => $path,
                    'name' => $name,
                    'alt_text' => $alt,
                    'is_featured' => $isFeatured,
                    'is_secondary' => $isSecondary,
                    'order' => $index,
                ]);
            }

            $this->reconcileGalleryMarks($product);
        }

        $this->warnIfUploadsWereClipped($request);

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $attributes = Attribute::with('values')->get();
        $sizeCharts = SizeChart::orderBy('title')->get();
        $product->load(['categories', 'images', 'variations.attributeValues', 'productAttributes.values', 'sizeChart']);
        return view('admin.products.edit', compact('product', 'categories', 'attributes', 'sizeCharts'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'product_type' => 'required|in:simple,variable',
            'sku' => 'nullable|string|max:255',
            'regular_price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|required_if:product_type,simple|integer|min:0',
            'manage_stock' => 'nullable|boolean',
            'stock_status' => 'nullable|in:instock,outofstock,onbackorder',
            'categories' => 'nullable|array',
            'categories.*' => 'exists:categories,id',
            'size_chart_id' => 'nullable|exists:size_charts,id',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'seo_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',

            // Attributes
            'attributes' => 'nullable|array',
            'attributes.*.attribute_id' => 'nullable|exists:attributes,id',
            'attributes.*.custom_name' => 'nullable|string|max:255',
            'attributes.*.value_ids' => 'nullable|array',
            'attributes.*.value_ids.*' => 'integer|exists:attribute_values,id',
            'attributes.*.is_visible' => 'nullable|boolean',
            'attributes.*.is_variation' => 'nullable|boolean',

            // Variations
            'variations' => 'nullable|array',
            'variations.*.id' => 'nullable|integer',
            'variations.*.size' => 'nullable|string|max:50',
            'variations.*.weight' => 'nullable|string|max:50',
            'variations.*.color' => 'nullable|string|max:50',
            'variations.*.price' => 'nullable|numeric|min:0',
            'variations.*.stock' => 'nullable|integer|min:0',
            'variations.*.regular_price' => 'nullable|numeric|min:0',
            'variations.*.sale_price' => ['nullable', 'numeric', 'min:0', 'lt:variations.*.regular_price'],
            'variations.*.stock_quantity' => 'nullable|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:255',
            'variations.*.image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'variations.*.image_media_id' => 'nullable|integer',

            // Existing images details updates
            'existing_images' => 'nullable|array',
            'existing_images.*.id' => 'required|exists:product_images,id',
            'existing_images.*.name' => 'nullable|string|max:255',
            'existing_images.*.alt_text' => 'nullable|string|max:255',
            'existing_images.*.order' => 'integer',
            'existing_images_featured_id' => 'nullable|integer',
            'existing_images_secondary_id' => 'nullable|integer',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:product_images,id',

            // New Images upload
            'new_images' => 'nullable|array',
            'new_images.*' => 'nullable',
            'new_images.*.file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'new_images.*.media_id' => 'nullable|integer',
            'new_images_names' => 'nullable|array',
            'new_images_alts' => 'nullable|array',
            'new_image_featured_temp' => 'nullable|string', // "new_0", "existing_23"
            'new_image_secondary_temp' => 'nullable|string', // "new_1", "existing_23"
        ]);

        // Capture original product type
        $originalType = $product->product_type;

        $productData = $request->only([
            'name', 'product_type', 'short_description', 'description',
            'seo_title', 'meta_description', 'sku'
        ]);
        
        $productData['is_active'] = $request->boolean('is_active', true);
        $productData['is_featured'] = $request->boolean('is_featured', false);
        $productData['manage_stock'] = $request->boolean('manage_stock', false);
        $productData['stock_status'] = $request->input('stock_status', 'instock');
        $productData['regular_price'] = $request->input('regular_price');
        $productData['sale_price'] = $request->input('sale_price');
        $productData['cost_price'] = $this->parseCostPrice($request);

        // An empty select posts "", which means the admin picked "None".
        $productData['size_chart_id'] = $request->input('size_chart_id') ?: null;

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
            if ($request->has('attributes')) {
                $this->variationService->syncAttributes($product, $request->input('attributes', []));
            }

            // Two rows for one combination cannot both be stored, so saving would
            // silently keep the last one and discard the other. Report it instead.
            if (is_array($request->input('variations'))) {
                $duplicates = $this->variationService
                    ->duplicateCombinationLabels($product, $request->input('variations', []));

                if ($duplicates !== []) {
                    return back()
                        ->withInput()
                        ->withErrors(['variations' => 'Duplicate option combinations: ' . implode(', ', $duplicates) . '. Each combination can only be used once.']);
                }
            }

            if ($this->submittedVariationsAreAttributeBased($request)) {
                $this->variationService->saveVariations($product, $request->input('variations', []), $this->uploadedVariationImages($request));
            } elseif ($request->has('variations')) {
                $this->updateLegacyVariations($product, $request);
            } else {
                $this->variationService->generateVariations($product);
            }

            $this->variationService->refreshPriceRange($product);
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

        // Determine which image is featured and which is the hover (second) image
        $featuredId = $request->input('existing_images_featured_id'); // If existing is chosen
        $newFeaturedSelect = $request->input('new_image_featured_temp'); // Format: "existing_12" or "new_0"
        $secondaryId = $request->input('existing_images_secondary_id');
        $newSecondarySelect = $request->input('new_image_secondary_temp'); // Format: "existing_12" or "new_0"

        // Update existing images meta
        if ($request->has('existing_images')) {
            foreach ($request->input('existing_images') as $imgData) {
                $img = ProductImage::find($imgData['id']);
                if ($img) {
                    $isFeatured = ($img->id == $featuredId);
                    $isSecondary = ($img->id == $secondaryId);

                    // Or if it matches new_image_featured_temp
                    if ($newFeaturedSelect && $newFeaturedSelect === "existing_" . $img->id) {
                        $isFeatured = true;
                    } elseif ($newFeaturedSelect && Str::startsWith($newFeaturedSelect, 'new_')) {
                        $isFeatured = false; // A new image will be featured
                    }

                    // Or if it matches new_image_secondary_temp
                    if ($newSecondarySelect && $newSecondarySelect === "existing_" . $img->id) {
                        $isSecondary = true;
                    } elseif ($newSecondarySelect && Str::startsWith($newSecondarySelect, 'new_')) {
                        $isSecondary = false; // A new image will be the hover image
                    }

                    $img->update([
                        'name' => $imgData['name'],
                        'alt_text' => $imgData['alt_text'] ?: $product->name,
                        'order' => $imgData['order'] ?: 0,
                        'is_featured' => $isFeatured,
                        'is_secondary' => $isSecondary,
                    ]);
                }
            }
        }

        // Upload New Images
        foreach ($this->galleryRows($request, 'new_images', 'product_images') as $index => $path) {
            $name = $request->input("new_images_names.{$index}") ?: ('image_' . Str::random(5));
            $alt = $request->input("new_images_alts.{$index}") ?: $product->name;

            $isFeatured = $newFeaturedSelect === "new_" . $index;
            $isSecondary = $newSecondarySelect === "new_" . $index;

            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
                'name' => $name,
                'alt_text' => $alt,
                'is_featured' => $isFeatured,
                'is_secondary' => $isSecondary,
                'order' => 100 + $index, // Put new ones at the end
            ]);
        }

        $this->reconcileGalleryMarks($product);

        $this->warnIfUploadsWereClipped($request);

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

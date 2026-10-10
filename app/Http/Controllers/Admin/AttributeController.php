<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AttributeController extends Controller
{
    public function index()
    {
        $attributes = Attribute::with('values')->withCount('productAttributes')->orderBy('name')->get();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAttribute($request);
        $attribute = Attribute::create($data);

        return back()->with('success', "Attribute \"{$attribute->name}\" created.");
    }

    public function update(Request $request, Attribute $attribute)
    {
        $attribute->update($this->validateAttribute($request, $attribute));

        return back()->with('success', "Attribute \"{$attribute->name}\" updated.");
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->productAttributes()->exists()) {
            return back()->with('error', 'This attribute is used by one or more products and cannot be deleted.');
        }

        $attribute->delete();

        return back()->with('success', 'Attribute deleted.');
    }

    public function storeValue(Request $request, Attribute $attribute)
    {
        $data = $this->validateValue($request);

        $attribute->values()->create([
            'name' => $data['name'],
            'color_code' => $attribute->type === Attribute::TYPE_COLOR ? ($data['color_code'] ?? null) : null,
            'image' => $attribute->type === Attribute::TYPE_IMAGE
                ? $this->resolveValueImage($request)
                : null,
            'sort_order' => $data['sort_order'] ?? ((int) $attribute->values()->max('sort_order') + 1),
        ]);

        return back()->with('success', 'Value added.');
    }

    public function updateValue(Request $request, Attribute $attribute, AttributeValue $value)
    {
        abort_unless($value->attribute_id === $attribute->id, 404);

        $data = $this->validateValue($request);

        $payload = [
            'name' => $data['name'],
            'color_code' => $attribute->type === Attribute::TYPE_COLOR ? ($data['color_code'] ?? null) : null,
            'sort_order' => $data['sort_order'] ?? $value->sort_order,
        ];

        if ($attribute->type === Attribute::TYPE_IMAGE) {
            if ($request->boolean('remove_image')) {
                $this->deleteValueImage($value);
            } else {
                $newImage = $this->resolveValueImage($request);

                if ($newImage !== null) {
                    if ($value->image && $value->image !== $newImage && ! Media::isLibraryPath($value->image)) {
                        Storage::disk('public')->delete($value->image);
                    }

                    $payload['image'] = $newImage;
                }
            }
        }

        $value->update($payload);

        return back()->with('success', 'Value updated.');
    }

    /**
     * Resolve the swatch image from a fresh upload or a media library pick.
     */
    private function resolveValueImage(Request $request): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('attributes', 'public');
        }

        $media = Media::find($request->input('image_media_id'));

        if ($media && $media->isImage() && Storage::disk($media->disk ?: 'public')->exists($media->path)) {
            return $media->path;
        }

        return null;
    }

    private function deleteValueImage(AttributeValue $value): void
    {
        if ($value->image && ! Media::isLibraryPath($value->image)) {
            Storage::disk('public')->delete($value->image);
        }

        $value->forceFill(['image' => null])->save();
    }

    public function destroyValue(Attribute $attribute, AttributeValue $value)
    {
        abort_unless($value->attribute_id === $attribute->id, 404);

        if ($value->variationValues()->exists() || $value->productAttributeValues()->exists()) {
            return back()->with('error', 'This value is used by one or more products and cannot be deleted.');
        }

        $value->delete();

        return back()->with('success', 'Value deleted.');
    }

    private function validateAttribute(Request $request, ?Attribute $attribute = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'type' => 'required|in:' . implode(',', [
                Attribute::TYPE_SELECT,
                Attribute::TYPE_COLOR,
                Attribute::TYPE_IMAGE,
                Attribute::TYPE_BUTTON,
            ]),
            'order_by' => 'nullable|in:' . implode(',', Attribute::ORDER_BY),
        ]);

        $data['name'] = trim($data['name']);

        return $data;
    }

    private function validateValue(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'color_code' => 'nullable|string|max:32',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
            'image_media_id' => 'nullable|integer',
            'sort_order' => 'nullable|integer',
        ]);
    }
}

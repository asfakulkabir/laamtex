<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index()
    {
        $testimonials = Testimonial::orderBy('sort_order')->orderBy('id')->get();

        return view('admin.testimonials.index', compact('testimonials'));
    }

    public function create()
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_media_id' => ['nullable', 'integer'],
        ]);

        $resolved = $this->resolveImage($request);

        if ($resolved === null) {
            return back()->withInput()->withErrors([
                'image' => 'Choose an image from the media library or upload one.',
            ]);
        }

        Testimonial::create(array_merge(
            $resolved['meta'],
            [
                'image'      => $resolved['path'],
                'sort_order' => (int) Testimonial::max('sort_order') + 1,
                'is_active'  => true,
            ]
        ));

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial image uploaded successfully.');
    }

    public function edit(Testimonial $testimonial)
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $request->validate([
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_media_id' => ['nullable', 'integer'],
        ]);

        $resolved = $this->resolveImage($request);

        if ($resolved !== null) {
            $this->deleteImage($testimonial);

            $testimonial->update(array_merge(
                $resolved['meta'],
                ['image' => $resolved['path']]
            ));
        }

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function toggle(Testimonial $testimonial)
    {
        $testimonial->update(['is_active' => !$testimonial->is_active]);

        return back()->with('success', 'Testimonial ' . ($testimonial->is_active ? 'is now visible' : 'is now hidden') . ' on the home page.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $this->deleteImage($testimonial);
        $testimonial->delete();

        return redirect()->route('admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);

        if (!is_array($order)) {
            return response()->json(['success' => false], 422);
        }

        foreach (array_values($order) as $position => $id) {
            Testimonial::whereKey($id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    private function deleteImage(Testimonial $testimonial): void
    {
        if ($testimonial->image && ! Media::isLibraryPath($testimonial->image)) {
            Storage::disk('public')->delete($testimonial->image);
        }
    }

    /**
     * Resolve a fresh upload or a library pick into a path and its metadata.
     *
     * @return array{meta: array{width:int|null,height:int|null,file_size:int|null}, path: string}|null
     */
    private function resolveImage(Request $request): ?array
    {
        if ($request->hasFile('image')) {
            $upload = $request->file('image');

            return [
                'meta' => Testimonial::readImageMeta($upload),
                'path' => $upload->store('testimonials', 'public'),
            ];
        }

        $media = Media::find($request->input('image_media_id'));

        if (! $media || ! $media->isImage() || ! Storage::disk($media->disk ?: 'public')->exists($media->path)) {
            return null;
        }

        return [
            'meta' => ['width' => $media->width, 'height' => $media->height, 'file_size' => $media->size],
            'path' => $media->path,
        ];
    }
}

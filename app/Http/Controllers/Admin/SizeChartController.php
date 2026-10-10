<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\SizeChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SizeChartController extends Controller
{
    public function index()
    {
        $sizeCharts = SizeChart::withCount('products')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return view('admin.size-charts.index', compact('sizeCharts'));
    }

    public function create()
    {
        return view('admin.size-charts.create');
    }

    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_media_id' => ['nullable', 'integer'],
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! $request->hasFile('image') && ! $request->filled('image_media_id')) {
                $validator->errors()->add('image', 'Choose an image from the media library or upload one.');
            }
        });

        $validator->validate();

        $resolved = $this->resolveImage($request);

        if ($resolved === null) {
            return back()->withInput()->withErrors([
                'image' => 'The selected media could not be found. Choose an image from the media library or upload one.',
            ]);
        }

        SizeChart::create(array_merge(
            $resolved['meta'],
            [
                'title' => $request->input('title'),
                'image' => $resolved['path'],
                'sort_order' => (int) SizeChart::max('sort_order') + 1,
                'is_active' => true,
            ]
        ));

        return redirect()->route('admin.size-charts.index')
            ->with('success', 'Size chart added successfully.');
    }

    public function edit(SizeChart $sizeChart)
    {
        return view('admin.size-charts.edit', compact('sizeChart'));
    }

    public function update(Request $request, SizeChart $sizeChart)
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'image_media_id' => ['nullable', 'integer'],
        ]);

        $sizeChart->update(['title' => $request->input('title')]);

        $resolved = $this->resolveImage($request);

        if ($resolved !== null) {
            // The model's updating hook deletes the previous file once the
            // new path is saved (unless it is a reusable library asset).
            $sizeChart->update(array_merge(
                $resolved['meta'],
                ['image' => $resolved['path']]
            ));
        }

        return redirect()->route('admin.size-charts.index')
            ->with('success', 'Size chart updated successfully.');
    }

    public function toggle(SizeChart $sizeChart)
    {
        $sizeChart->update(['is_active' => !$sizeChart->is_active]);

        return back()->with('success', 'Size chart ' . ($sizeChart->is_active ? 'is now shown' : 'is now hidden') . ' on product pages.');
    }

    public function destroy(SizeChart $sizeChart)
    {
        // Products referencing it are detached by the nullOnDelete constraint.
        $sizeChart->delete();

        return redirect()->route('admin.size-charts.index')
            ->with('success', 'Size chart deleted successfully.');
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
                'meta' => SizeChart::readImageMeta($upload),
                'path' => $upload->store('size_charts', 'public'),
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

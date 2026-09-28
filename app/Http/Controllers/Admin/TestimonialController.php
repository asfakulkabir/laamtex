<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        Testimonial::create([
            'image'      => $request->file('image')->store('testimonials', 'public'),
            'sort_order' => (int) Testimonial::max('sort_order') + 1,
            'is_active'  => true,
        ]);

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
        ]);

        if ($request->hasFile('image')) {
            $this->deleteImage($testimonial);
            $testimonial->update(['image' => $request->file('image')->store('testimonials', 'public')]);
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
        if ($testimonial->image) {
            Storage::disk('public')->delete($testimonial->image);
        }
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\HomeSection;
use Illuminate\Http\Request;

class HomeSectionController extends Controller
{
    public function index()
    {
        $sections = HomeSection::with('category')->orderBy('sort_order')->orderBy('id')->get();

        return view('admin.home-sections.index', compact('sections'));
    }

    public function create()
    {
        $categories = $this->categoryOptions();

        return view('admin.home-sections.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $data['sort_order'] = (int) HomeSection::max('sort_order') + 1;
        $data['is_active'] = $request->boolean('is_active', true);

        HomeSection::create($data);

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Home section added successfully.');
    }

    public function edit(HomeSection $homeSection)
    {
        $categories = $this->categoryOptions();
        $homeSection->load('category');

        return view('admin.home-sections.edit', compact('homeSection', 'categories'));
    }

    public function update(Request $request, HomeSection $homeSection)
    {
        $data = $this->validated($request);

        $data['is_active'] = $request->boolean('is_active', false);

        $homeSection->update($data);

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Home section updated successfully.');
    }

    public function toggle(HomeSection $homeSection)
    {
        $homeSection->update(['is_active' => !$homeSection->is_active]);

        return back()->with('success', 'Home section ' . ($homeSection->is_active ? 'is now shown' : 'is now hidden') . ' on the home page.');
    }

    public function destroy(HomeSection $homeSection)
    {
        $homeSection->delete();

        return redirect()->route('admin.home-sections.index')
            ->with('success', 'Home section deleted successfully.');
    }

    public function reorder(Request $request)
    {
        $order = $request->input('order', []);

        if (! is_array($order)) {
            return response()->json(['success' => false], 422);
        }

        foreach (array_values($order) as $position => $id) {
            HomeSection::whereKey($id)->update(['sort_order' => $position + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * @return array<string,string>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'layout' => ['required', 'in:' . HomeSection::LAYOUT_SLIDER . ',' . HomeSection::LAYOUT_GRID],
            'product_limit' => ['required', 'integer', 'min:1', 'max:' . HomeSection::MAX_PRODUCT_LIMIT],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // A missing or empty select means the admin picked "All", so the key
        // may be absent entirely and has to be normalised either way.
        $data['category_id'] = ($data['category_id'] ?? null) ?: null;

        return $data;
    }

    private function categoryOptions()
    {
        // Indented by depth so a parent reads above its own children.
        return Category::with('children.children')->orderBy('name')->get()
            ->flatMap(fn (Category $category) => $this->flatten($category))
            ->mapWithKeys(fn (array $row) => [$row[0] => $row[1]])
            ->all();
    }

    /**
     * @return \Illuminate\Support\Collection<int, array{0: int, 1: string}>
     */
    private function flatten(Category $category, int $depth = 0): \Illuminate\Support\Collection
    {
        $prefix = $depth > 0 ? str_repeat('— ', $depth) : '';

        $rows = collect([[$category->id, $prefix . $category->name]]);

        foreach ($category->children as $child) {
            $rows = $rows->merge($this->flatten($child, $depth + 1));
        }

        return $rows;
    }
}

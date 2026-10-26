<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::with('parent')
            ->withCount('books')
            ->when($request->search, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true]), 'parents' => $this->parentOptions()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $category = Category::create($this->validated($request));

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" created.");
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', ['category' => $category, 'parents' => $this->parentOptions($category)]);
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->books()->exists()) {
            return back()->with('warning', "\"{$category->name}\" still has books. Move them to another category first.");
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        $category->delete();

        return redirect()->route('admin.categories.index')->with('status', "Category \"{$category->name}\" deleted.");
    }

    private function validated(Request $request, ?Category $category = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('categories')->ignore($category)],
            'parent_id' => ['nullable', Rule::exists('categories', 'id'), Rule::notIn(array_filter([$category?->id]))],
            'description' => ['nullable', 'string', 'max:2000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
            'remove_image' => ['boolean'],
        ]);

        $data['sort_order'] ??= 0;

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($category?->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')?->store('categories', 'public');
        }
        unset($data['remove_image']);

        return $data;
    }

    /**
     * Top-level categories that can be a parent (a category can't be its own parent).
     */
    private function parentOptions(?Category $except = null): array
    {
        return Category::whereNull('parent_id')
            ->when($except?->exists, fn ($query) => $query->whereKeyNot($except->id))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}

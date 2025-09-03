<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    /**
     * ADMIN: list categories (top-level with children)
     */
    public function index()
    {
        $categoryTree = \App\Models\Category::whereNull('parent_id')
        ->withCount('products')              // direct count on roots
        ->with(['childrenRecursive'])        // children + grandchildren with counts
        ->orderBy('name')
        ->get();

    return view('admin.categories.index', ['categoryTree' => $categoryTree]);
    }

    /**
     * ADMIN: create form
     */
    public function create()
    {
        // tree for the parent dropdown
        $categories = Category::with('children')
                              ->whereNull('parent_id')
                              ->orderBy('name')
                              ->get();

        return view('admin.categories.create', compact('categories'));
    }

    /**
     * ADMIN: store new category (with optional image)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'parent_id' => 'nullable|exists:categories,id',
            'slug'      => 'nullable|string|max:255|unique:categories,slug',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        // slug → fallback + uniqueness
        $slug = $validated['slug'] ?? Str::slug($validated['name']);
        $base = $slug;
        $i = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        // create first (so we can store image under categories/{id}/...)
        $category = Category::create([
            'name'      => $validated['name'],
            'slug'      => $slug,
            'parent_id' => $validated['parent_id'] ?? null,
            'image'     => null,
        ]);

        // handle upload (optional)
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store("categories/{$category->id}", 'public');
            $category->update(['image' => $path]);
        }

        // bust caches that feed nav/home grids
        Cache::forget('nav_categories');
        Cache::forget('home_top_categories');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * ADMIN: edit form
     */
    public function edit(Category $category)
    {
        $categories = Category::with('children')
                              ->whereNull('parent_id')
                              ->orderBy('name')
                              ->get();

        return view('admin.categories.edit', compact('category', 'categories'));
    }

    /**
     * ADMIN: update category (supports replace/remove image)
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'parent_id'     => 'nullable|exists:categories,id|not_in:' . $category->id,
            'slug'          => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'remove_image'  => 'sometimes|boolean',
        ]);

        // slug → fallback + uniqueness (excluding self)
        $slug = $validated['slug'] ?? Str::slug($validated['name']);
        $base = $slug;
        $i = 1;
        while (
            Category::where('slug', $slug)
                ->where('id', '!=', $category->id)
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        // update main fields
        $category->name      = $validated['name'];
        $category->slug      = $slug;
        $category->parent_id = $validated['parent_id'] ?? null;

        // remove old image if requested
        if ($request->boolean('remove_image') && $category->image) {
            Storage::disk('public')->delete($category->image);
            $category->image = null;
            // optional: also remove the directory
            Storage::disk('public')->deleteDirectory("categories/{$category->id}");
        }

        // replace/upload new image
        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $path = $request->file('image')->store("categories/{$category->id}", 'public');
            $category->image = $path;
        }

        $category->save();

        Cache::forget('nav_categories');
        Cache::forget('home_top_categories');

        return redirect()->route('admin.categories.edit', $category)
            ->with('success', 'Category updated successfully.');
    }

    /**
     * ADMIN: delete category (also cleans up image files)
     */
    public function destroy(Category $category)
    {
        // stop if it has children or products
        if ($category->children()->exists()) {
            return back()->withErrors(['error' => 'Please delete or reassign child categories first.']);
        }
        if (method_exists($category, 'products') && $category->products()->exists()) {
            return back()->withErrors(['error' => 'This category has products. Move them before deleting.']);
        }

        // delete image files if any
        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }
        Storage::disk('public')->deleteDirectory("categories/{$category->id}");

        $category->delete();

        Cache::forget('nav_categories');
        Cache::forget('home_top_categories');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * PUBLIC: category landing page (products listing)
     */
    public function show($slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $products = $category->products()
            ->with('images')
            ->paginate(12);

        // sidebar/tree if you need it
        $categories = Category::whereNull('parent_id')
            ->with('childrenRecursive') // ensure this relation exists in Category
            ->get();

        return view('categories.show', compact('category', 'products', 'categories'));
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Middleware\AdminMiddleware;
use App\Imports\ProductsImport;
use App\Imports\ProductsWithImagesImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', AdminMiddleware::class])->only([
            'adminIndex', 'create', 'store', 'edit', 'update',
            'destroy', 'updateStock', 'quickUpdate'
        ]);
    }

    /* ============================================================
     * FRONTEND PRODUCT PAGES
     * ============================================================ */

    // Public product listing with validated search + sort
    public function index(Request $request)
    {
        // Validate query params gracefully (avoid 422 on GET)
        $validator = Validator::make($request->query(), [
            'q'    => 'nullable|string|max:100',
            // accept both "new" (used in your blade) and "latest" (alias)
            'sort' => 'nullable|in:new,latest,price_asc,price_desc,name_asc,name_desc,rating_desc',
        ]);

        $q    = null;
        $sort = 'name_asc'; // default

        if ($validator->fails()) {
            // Sanitize fallback
            $rawQ   = $request->query('q');
            $q      = is_string($rawQ) ? mb_substr($rawQ, 0, 100) : null;
            $rawSort = $request->query('sort', $sort);
            $allowed = ['new','latest','price_asc','price_desc','name_asc','name_desc','rating_desc'];
            if (in_array($rawSort, $allowed, true)) {
                $sort = $rawSort;
            }
        } else {
            $data = $validator->validated();
            $q    = $data['q']    ?? null;
            $sort = $data['sort'] ?? $sort;
        }

        // Normalize "new" -> "latest"
        if ($sort === 'new') {
            $sort = 'latest';
        }

        $query = Product::with(['category', 'images', 'brand'])
            ->where('is_active', true);

        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('sku', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%");
            });
        }

        switch ($sort) {
            case 'latest':
                $query->orderByDesc('created_at')->orderBy('id');
                break;
            case 'price_asc':
                $query->orderBy('price')->orderBy('id');
                break;
            case 'price_desc':
                $query->orderByDesc('price')->orderBy('id');
                break;
            case 'name_desc':
                $query->orderByDesc('name')->orderBy('id');
                break;
            case 'rating_desc':
                $query->orderByRaw('COALESCE(rating, 0) DESC')->orderBy('name');
                break;
            case 'name_asc':
            default:
                $query->orderBy('name')->orderBy('id');
                break;
        }

        $products   = $query->paginate(12)->appends(['q' => $q, 'sort' => $sort]);
        $categories = Category::with('children.children')->orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands', 'q', 'sort'));
    }

    // ✅ Route-model binding by slug: /p/{product:slug}
    public function show(Product $product)
    {
        $product->load(['images', 'category', 'brand']);
        return view('products.show', compact('product'));
    }

    public function productsByCategory($slug)
    {
        $category = Category::with('children.children')->where('slug', $slug)->firstOrFail();

        // collect this category + all children + grandchildren
        $categoryIds = collect([$category->id]);
        foreach ($category->children as $child) {
            $categoryIds->push($child->id);
            foreach ($child->children as $grandchild) {
                $categoryIds->push($grandchild->id);
            }
        }

        $products = Product::with(['category', 'images', 'brand'])
            ->whereIn('category_id', $categoryIds)
            ->where('is_active', true)
            ->paginate(12);

        $categories = Category::with('children.children')->orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands', 'category'));
    }

    public function byBrand(string $brandSlug)
    {
        $brand = Brand::where('slug', $brandSlug)->firstOrFail();

        $products = Product::with(['category', 'images', 'brand'])
            ->where('is_active', true)
            ->where('brand_id', $brand->id)
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::with('children.children')->orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('products.index', [
            'products'   => $products,
            'categories' => $categories,
            'brands'     => $brands,
            'brand'      => $brand, // pass the Brand model (cleaner for the view)
        ]);
    }

    public function productsByVendor($vendorId)
    {
        $vendor = Vendor::findOrFail($vendorId);

        $products = Product::with(['category', 'images', 'brand'])
            ->where('vendor_id', $vendor->id)
            ->where('is_active', true)
            ->paginate(12);

        $categories = Category::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'brands', 'vendor'));
    }

    /* ============================================================
     * ADMIN PRODUCT MANAGEMENT
     * ============================================================ */

    // Admin product list (with optional search/filters)
    public function adminIndex()
    {
        $q          = request('q');
        $brandId    = request('brand_id');
        $categoryId = request('category_id');
        $active     = request('active'); // '1' | '0' | null

        $query = Product::with(['category', 'brand', 'images']);

        if ($q) {
            $query->where(function ($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                   ->orWhere('sku', 'like', "%{$q}%");
            });
        }
        if ($brandId) {
            $query->where('brand_id', $brandId);
        }
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        if ($active !== null && $active !== '') {
            $query->where('is_active', (bool)((int)$active));
        }

        $products   = $query->orderBy('name')->paginate(20)->withQueryString();
        $categories = Category::with('children.children')->whereNull('parent_id')->orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'brands'));
    }

    public function create()
    {
        $categories = Category::with(['children.children'])->whereNull('parent_id')->orderBy('name')->get();
        $vendors    = Vendor::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('admin.products.create', compact('categories', 'vendors', 'brands'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:products,slug',
            'sku'              => 'nullable|string|max:255|unique:products,sku',
            'barcode'          => 'nullable|string|max:255',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0.01',
            'discount_price'   => 'nullable|numeric|min:0|lt:price',
            'stock'            => 'nullable|integer|min:0',
            'brand_id'         => 'nullable|exists:brands,id',
            'category_id'      => 'nullable|exists:categories,id',
            'vendor_id'        => 'nullable|exists:vendors,id',
            'is_active'        => 'sometimes|boolean',
            'featured'         => 'sometimes|boolean',
            'is_new'           => 'sometimes|boolean',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:500',

            // Images arrays
            'images'        => 'nullable|array',
            'images.*'      => 'file|image|mimes:jpeg,jpg,png,webp|max:4096',
            'images_alt'    => 'nullable|array',
            'images_alt.*'  => 'nullable|string|max:255',
            'images_sort'   => 'nullable|array',
            'images_sort.*' => 'nullable|integer|min:0',
            'primary_index' => 'nullable|integer|min:0',
        ]);

        // Flags & defaults
        $validated['is_active'] = $request->has('is_active');
        $validated['featured']  = $request->has('featured');
        $validated['is_new']    = $request->has('is_new');
        $validated['stock']     = $validated['stock'] ?? 0;

        // If a slug is provided, sanitize it; otherwise the Model will generate on creating()
        if (!blank($validated['slug'] ?? null)) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // Optional SKU fallback
        $validated['sku'] = $validated['sku'] ?? ('SKU-' . strtoupper(Str::random(6)));

        // SEO fallbacks
        if (blank($validated['meta_title'] ?? null)) {
            $validated['meta_title'] = method_exists(Product::class, 'makeMetaTitle')
                ? Product::makeMetaTitle($validated['name'])
                : $validated['name'];
        }
        if (blank($validated['meta_description'] ?? null)) {
            $src = $validated['description'] ?? $validated['name'];
            $validated['meta_description'] = method_exists(Product::class, 'makeMetaDescription')
                ? Product::makeMetaDescription($src)
                : mb_strimwidth(strip_tags($src), 0, 155, '…');
        }

        DB::transaction(function () use ($request, $validated) {
            /** @var Product $product */
            $product = Product::create($validated);

            $files   = $request->file('images', []);
            $alts    = $request->input('images_alt', []);
            $orders  = $request->input('images_sort', []);
            $primary = (int) $request->input('primary_index', 0);

            if (!empty($files)) {
                foreach ($files as $i => $file) {
                    $path = $file->store("products/{$product->id}", 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $path,
                        'alt'        => $alts[$i] ?? $product->name,
                        'is_primary' => $i === $primary,
                        'sort_order' => isset($orders[$i]) ? (int)$orders[$i] : $i,
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        $categories = Category::with(['children.children'])->whereNull('parent_id')->orderBy('name')->get();
        $vendors    = Vendor::orderBy('name')->get();
        $brands     = Brand::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories', 'vendors', 'brands'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'slug'             => 'nullable|string|max:255|unique:products,slug,' . $product->id,
            'sku'              => 'nullable|string|max:255|unique:products,sku,' . $product->id,
            'barcode'          => 'nullable|string|max:255',
            'rating'           => 'nullable|numeric|min:0|max:5',
            'description'      => 'nullable|string',
            'price'            => 'required|numeric|min:0.01',
            'discount_price'   => 'nullable|numeric|min:0|lt:price',
            'stock'            => 'nullable|integer|min:0',
            'brand_id'         => 'nullable|exists:brands,id',
            'category_id'      => 'nullable|exists:categories,id',
            'vendor_id'        => 'nullable|exists:vendors,id',
            'is_active'        => 'sometimes|boolean',
            'featured'         => 'sometimes|boolean',
            'is_new'           => 'sometimes|boolean',
            'meta_title'       => 'nullable|string|max:70',
            'meta_description' => 'nullable|string|max:500',

            // New images to append
            'images'        => 'nullable|array',
            'images.*'      => 'file|image|mimes:jpeg,jpg,png,webp|max:4096',
            'images_alt'    => 'nullable|array',
            'images_alt.*'  => 'nullable|string|max:255',
            'images_sort'   => 'nullable|array',
            'images_sort.*' => 'nullable|integer|min:0',
            'primary_index' => 'nullable|integer|min:0',

            // Existing images editable fields
            'existing_images'              => 'nullable|array',
            'existing_images.*.alt'        => 'nullable|string|max:255',
            'existing_images.*.sort_order' => 'nullable|integer|min:0',
            'primary_image_id'             => 'nullable|integer|min:1',
        ]);

        // Flags
        $validated['is_active'] = $request->has('is_active');
        $validated['featured']  = $request->has('featured');
        $validated['is_new']    = $request->has('is_new');
        $validated['stock']     = $validated['stock'] ?? $product->stock ?? 0;

        // If slug provided, sanitize; uniqueness is enforced by validation + model on updating()
        if (!blank($validated['slug'] ?? null)) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // SEO fallbacks
        if (blank($validated['meta_title'] ?? null)) {
            $validated['meta_title'] = method_exists(Product::class, 'makeMetaTitle')
                ? Product::makeMetaTitle($validated['name'])
                : $validated['name'];
        }
        if (blank($validated['meta_description'] ?? null)) {
            $src = $validated['description'] ?? $validated['name'];
            $validated['meta_description'] = method_exists(Product::class, 'makeMetaDescription')
                ? Product::makeMetaDescription($src)
                : mb_strimwidth(strip_tags($src), 0, 155, '…');
        }

        DB::transaction(function () use ($request, $product, $validated) {
            $product->update($validated);

            // 1) Update existing images (alt/sort_order)
            $existing = $request->input('existing_images', []); // [id => ['alt'=>..., 'sort_order'=>...]]
            if (!empty($existing)) {
                foreach ($existing as $imgId => $vals) {
                    ProductImage::where('product_id', $product->id)
                        ->where('id', $imgId)
                        ->update([
                            'alt'        => $vals['alt'] ?? null,
                            'sort_order' => isset($vals['sort_order']) ? (int)$vals['sort_order'] : 0,
                        ]);
                }
            }

            // 2) Set primary among existing (only if no new primary_index will be used)
            if (!$request->filled('primary_index') && $request->filled('primary_image_id')) {
                $pid = (int) $request->input('primary_image_id');
                ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
                ProductImage::where('product_id', $product->id)->where('id', $pid)->update(['is_primary' => true]);
            }

            // 3) Append newly uploaded images (+ optional new primary_index)
            $files   = $request->file('images', []);
            $alts    = $request->input('images_alt', []);
            $orders  = $request->input('images_sort', []);
            $primary = $request->filled('primary_index') ? (int) $request->input('primary_index') : null;

            if (!empty($files)) {
                if (!is_null($primary)) {
                    // If new primary is chosen, clear previous primaries
                    ProductImage::where('product_id', $product->id)->update(['is_primary' => false]);
                }

                $startOrder = (int) ($product->images()->max('sort_order') ?? -1) + 1;
                foreach ($files as $i => $file) {
                    $path = $file->store("products/{$product->id}", 'public');
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image'      => $path,
                        'alt'        => $alts[$i] ?? $product->name,
                        'is_primary' => (!is_null($primary) && $i === $primary),
                        'sort_order' => isset($orders[$i]) ? (int)$orders[$i] : ($startOrder + $i),
                    ]);
                }
            }
        });

        return redirect()->route('admin.products.edit', $product)->with('success', 'Product updated successfully.');
    }

    // Quick inline update (category, brand, price, discount_price, stock, is_active)
    public function quickUpdate(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'    => 'nullable|exists:categories,id',
            'brand_id'       => 'nullable|exists:brands,id',
            'price'          => 'required|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'stock'          => 'required|integer|min:0',
            'is_active'      => 'required|boolean',
        ]);

        try {
            // Normalize: empty discount => null
            if ($request->filled('discount_price') === false) {
                $validated['discount_price'] = null;
            }

            $product->update([
                'category_id'    => $validated['category_id'] ?? null,
                'brand_id'       => $validated['brand_id'] ?? null,
                'price'          => $validated['price'],
                'discount_price' => $validated['discount_price'] ?? null,
                'stock'          => $validated['stock'],
                'is_active'      => (bool) $validated['is_active'],
            ]);

            return back()->with('success', 'Product updated successfully!');
        } catch (\Throwable $e) {
            return back()->withErrors(['error' => 'Failed to update product: '.$e->getMessage()]);
        }
    }

    public function updateStock(Request $request, Product $product)
    {
        $request->validate(['stock' => 'required|integer|min:0']);
        $product->stock = $request->stock;
        $product->save();

        return redirect()->route('admin.products.index')->with('success', 'Stock updated successfully!');
    }

    public function destroy(Product $product)
    {
        if (OrderItem::where('product_id', $product->id)->exists()) {
            return redirect()->route('admin.products.index')
                ->withErrors(['error' => 'This product cannot be deleted because it is linked to existing orders.']);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    /* ============================================================
     * BULK IMPORTS
     * ============================================================ */

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new ProductsImport, $request->file('file'));
        return redirect()->back()->with('success', 'Products imported successfully!');
    }

    public function importProductsWithImages(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new ProductsWithImagesImport('products_import_images'), $request->file('excel_file'));
            return redirect()->back()->with('success', 'Products with images imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Import failed: ' . $e->getMessage()]);
        }
    }
}

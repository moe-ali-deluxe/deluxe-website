<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;
use App\Models\Category;
use App\Models\Vendor;
use App\Models\Brand;

class HomeController extends Controller
{
    /**
     * Home page: hero, sliders, brands/vendors, and curated top categories.
     * - Header categories bar comes from AppServiceProvider (composer).
     * - We DO NOT pass $categories here to avoid overriding the composer data.
     */
    public function index()
    {
        try {
            // Featured picks
            $recommendedProducts = Product::query()
                ->where('is_active', true)
                ->where('featured', true)
                ->with(['images', 'brand'])
                ->latest('id')
                ->take(10)
                ->get();

            // New arrivals
            $newProducts = Product::query()
                ->where('is_active', true)
                ->where('is_new', true)
                ->with(['images', 'brand'])
                ->latest('id')
                ->take(10)
                ->get();

            // Brand/Vendor sliders
            $brands  = Brand::query()->orderBy('name')->get();
            $vendors = Vendor::query()->orderBy('name')->get();

            /**
             * Curated "Shop by Category"
             * - Parents only
             * - Load direct product counts on parents
             * - Load full tree so children have their own products_count via childrenRecursive
             * - Sort by total_products_count (accessor) so parents with items in subcats rank correctly
             */
            $topCategories = Cache::remember('home_top_categories', now()->addHours(6), function () {
                // Columns (defensive inclusion of image)
                $cols = ['categories.id', 'categories.name', 'categories.slug', 'categories.parent_id'];
                if (Schema::hasColumn('categories', 'image')) {
                    $cols[] = 'categories.image';
                }

                // Load parents with direct counts + full tree (childrenRecursive adds withCount('products') per level)
                $parents = Category::query()
                    ->select($cols)
                    ->whereNull('parent_id')
                    ->withCount('productsActive')          // direct products on parent
                    ->with('childrenRecursive')      // children + grandchildren with their products_count
                    ->get();

                // Sort by total (accessor handles children OR childrenRecursive)
                return $parents
                    ->sortByDesc(fn ($c) => (int) ($c->total_products_count ?? $c->products_count ?? 0))
                    ->take(8)
                    ->values();
            });

            return view('home', [
                'recommendedProducts' => $recommendedProducts,
                'newProducts'         => $newProducts,
                'brands'              => $brands,
                'vendors'             => $vendors,
                'topCategories'       => $topCategories, // used by the home grid
            ]);
        } catch (\Throwable $e) {
            // Never 500 the whole page; log and show a minimal view
            Log::error('HomeController@index failed: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return view('home', [
                'recommendedProducts' => collect(),
                'newProducts'         => collect(),
                'brands'              => collect(),
                'vendors'             => collect(),
                'topCategories'       => collect(),
            ])->with('error', 'Some sections failed to load. Please try again later.');
        }
    }
}

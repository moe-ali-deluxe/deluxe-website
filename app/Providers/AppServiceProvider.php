<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Product;
use App\Models\Brand;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * Route-model binding for product images used by:
         *   /admin/products/images/{image}
         */
        if (class_exists(ProductImage::class)) {
            Route::model('image', ProductImage::class);
        }

        /**
         * NAV / HEADER CATEGORIES (scoped to the partial, with a unique var name)
         * Avoids clobbering $categories used by admin tables.
         */
        View::composer(['partials.categories-bar'], function ($view) {
            $navCategories = Cache::remember('nav_categories', now()->addHours(12), function () {
                try {
                    if (!class_exists(Category::class)) {
                        return collect();
                    }

                    return Category::query()
                        ->whereNull('parent_id') // only parents
                        ->select(['id','name','slug','parent_id','image'])
                        ->with([
                            'children' => fn($q) => $q->select(['id','name','slug','parent_id','image'])
                                                      ->orderBy('name')
                                                      ->with([
                                                          'children' => fn($qq) => $qq->select(['id','name','slug','parent_id','image'])
                                                                                       ->orderBy('name')
                                                      ]),
                        ])
                        ->orderBy('name')
                        ->get();
                } catch (\Throwable $e) {
                    Log::warning('Categories composer failed: '.$e->getMessage());
                    return collect();
                }
            });

            // Expose as $navCategories (not $categories)
            $view->with('navCategories', $navCategories);
        });

        /**
         * WISHLIST DATA (IDs + COUNT)
         * NOTE: fixed path to your card partial: products/partials/_card.blade.php
         */
        View::composer(
            [
                'layouts.app',
                'products.*',
                'products.show',
                'products.partials._card', // <-- corrected
                'wishlist.*',
            ],
            function ($view) {
                $ids = [];
                $count = 0;

                try {
                    if (Auth::check()) {
                        $user = Auth::user();
                        if ($user && method_exists($user, 'wishlist')) {
                            $ids = $user->wishlist()->pluck('product_id')->all();
                            $count = is_array($ids) ? count($ids) : 0;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('Wishlist composer failed: '.$e->getMessage());
                }

                $view->with('wishlistProducts', $ids)
                     ->with('wishlistCount', $count);
            }
        );

        /**
         * CACHE BUSTING
         * - When categories change: refresh nav + home top categories + sitemap
         * - When products change:  refresh home top categories + sitemap
         * - When brands change:    refresh sitemap (brand URLs in sitemap)
         */
        $forget = function (array $keys) {
            foreach ($keys as $k) Cache::forget($k);
        };

        if (class_exists(Category::class)) {
            try {
                Category::saved(function () use ($forget) {
                    $forget(['nav_categories', 'home_top_categories', 'sitemap.xml']);
                });
                Category::deleted(function () use ($forget) {
                    $forget(['nav_categories', 'home_top_categories', 'sitemap.xml']);
                });
            } catch (\Throwable $e) {
                Log::warning('Category cache-busting hooks failed: '.$e->getMessage());
            }
        }

        if (class_exists(Product::class)) {
            try {
                Product::saved(function () use ($forget) {
                    $forget(['home_top_categories', 'sitemap.xml']);
                });
                Product::deleted(function () use ($forget) {
                    $forget(['home_top_categories', 'sitemap.xml']);
                });
            } catch (\Throwable $e) {
                Log::warning('Product cache-busting hooks failed: '.$e->getMessage());
            }
        }

        if (class_exists(Brand::class)) {
            try {
                Brand::saved(function () use ($forget) {
                    $forget(['sitemap.xml']);
                });
                Brand::deleted(function () use ($forget) {
                    $forget(['sitemap.xml']);
                });
            } catch (\Throwable $e) {
                Log::warning('Brand cache-busting hooks failed: '.$e->getMessage());
            }
        }
    }
}

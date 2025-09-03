<?php
// app/Http/Controllers/SitemapController.php
namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

class SitemapController extends Controller
{
    public function sitemap()
    {
        // Cache for 1h; you can shorten/extend as you like
        $xml = Cache::remember('sitemap.xml', 3600, function () {
            // Static pages (add any other public pages you want indexed)
            $static = collect([
                ['loc' => url('/'),                  'lastmod' => now()],
                ['loc' => route('products.index'),   'lastmod' => now()],
                ['loc' => route('categories.index'), 'lastmod' => now()],
                ['loc' => route('contact.show'),     'lastmod' => now()],
                // ['loc' => route('about'),          'lastmod' => now()],
                // ['loc' => route('returns'),        'lastmod' => now()],
            ]);

            // Taxonomies
            $categories = Category::query()
                ->select(['slug','updated_at'])
                ->whereNotNull('slug')
                ->get();

            $brands = Brand::query()
                ->select(['slug','updated_at'])
                ->whereNotNull('slug')
                ->get();

            // Products (live only). Keep under 50k per single sitemap file.
            $products = Product::query()
                ->where('is_active', true)
                ->select(['id','slug','updated_at'])
                ->latest('updated_at')
                ->limit(50000)
                ->get();

            // Build URLs
            $catUrls = $categories->map(fn($c) => [
                'loc'     => route('products.byCategory', ['slug' => $c->slug]),
                'lastmod' => $c->updated_at ?? now(),
            ]);

            $brandUrls = $brands->map(fn($b) => [
                'loc'     => route('products.byBrand', ['brandSlug' => $b->slug]),
                'lastmod' => $b->updated_at ?? now(),
            ]);

            $productUrls = $products->map(function ($p) {
                // Default to ID route; prefer slug route if you’ve added it
                $loc = route('products.show', $p->id);
                if (Route::has('products.show.slug') && !empty($p->slug)) {
                    $loc = route('products.show.slug', ['slug' => $p->slug]);
                }
                return [
                    'loc'        => $loc,
                    'lastmod'    => $p->updated_at ?? now(),
                    // Optional hints (fine to keep)
                    'changefreq' => 'daily',
                    'priority'   => '0.9',
                ];
            });

            // Render Blade view (make sure you used the safe XML declaration output)
            return view('sitemap.xml', [
                'urls' => $static->concat($catUrls)->concat($brandUrls)->concat($productUrls),
            ])->render();
        });

        return Response::make($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}

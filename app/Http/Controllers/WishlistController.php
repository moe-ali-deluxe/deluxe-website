<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Eager-load images to avoid N+1, keep newest first
        $items = $user->wishlist()
            ->with('images')
            ->latest('wishlists.created_at')
            ->get();

        // Convenience array for views to mark items "hearted"
        $wishlistProducts = $items->pluck('id')->all();

        return view('wishlist.index', compact('items', 'wishlistProducts'));
    }

    public function add(Request $request, Product $product)
    {
        $user = $request->user();

        // Avoid duplicate rows safely
        $user->wishlist()->syncWithoutDetaching([$product->id]);

        $count = (int) $user->wishlist()->count();
        $msg   = 'Added to wishlist';

        if ($request->wantsJson()) {
            return response()->json([
                'ok'     => true,
                'action' => 'added',
                'count'  => $count,
                'id'     => $product->id,
                'msg'    => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    public function remove(Request $request, Product $product)
    {
        $user = $request->user();

        $user->wishlist()->detach($product->id);

        $count = (int) $user->wishlist()->count();
        $msg   = 'Removed from wishlist';

        if ($request->wantsJson()) {
            return response()->json([
                'ok'     => true,
                'action' => 'removed',
                'count'  => $count,
                'id'     => $product->id,
                'msg'    => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    // Single-button UX
    public function toggle(Request $request, Product $product)
    {
        $user = $request->user();

        $exists = $user->wishlist()->where('product_id', $product->id)->exists();

        if ($exists) {
            $user->wishlist()->detach($product->id);
            $inWishlist = false;
            $action = 'removed';
            $msg = 'Removed from wishlist';
        } else {
            $user->wishlist()->syncWithoutDetaching([$product->id]);
            $inWishlist = true;
            $action = 'added';
            $msg = 'Added to wishlist';
        }

        $count = (int) $user->wishlist()->count();

        if ($request->wantsJson()) {
            return response()->json([
                'ok'          => true,
                'action'      => $action,
                'in_wishlist' => $inWishlist,
                'count'       => $count,
                'id'          => $product->id,
                'msg'         => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }
}

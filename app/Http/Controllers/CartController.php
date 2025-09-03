<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;

class CartController extends Controller
{
    /* ----------------------------- Helpers (Generic) ----------------------------- */

    protected function priceFor(Product $p): float
    {
        return (float) (
            ($p->discount_price !== null && $p->discount_price > 0)
                ? $p->discount_price
                : $p->price
        );
    }

    /**
     * Build a map [product_id => qty] no matter the storage (DB for authed, session for guests).
     */
    protected function getCartMap(): array
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['total_amount' => 0]);
            $map = [];
            foreach ($cart->items as $it) {
                $map[$it->product_id] = (int) $it->quantity;
            }
            return $map;
        }

        return session()->get('cart', []); // guests
    }

    protected function setSessionMap(array $map): void
    {
        session()->put('cart', $map);
    }

    protected function cartCountFromMap(array $map): int
    {
        return array_sum($map);
    }

    protected function cartSubtotalFromMap(array $map): float
    {
        if (empty($map)) return 0.0;
        $products = Product::whereIn('id', array_keys($map))->get(['id','price','discount_price']);
        $priceById = $products->mapWithKeys(fn ($p) => [$p->id => $this->priceFor($p)]);
        $sum = 0.0;
        foreach ($map as $pid => $qty) {
            $sum += ($priceById[$pid] ?? 0.0) * (int) $qty;
        }
        return round($sum, 2);
    }

    protected function jsonCartOk(string $message, array $map): JsonResponse
    {
        return response()->json([
            'success' => $message,
            'cart' => [
                'count'    => $this->cartCountFromMap($map),
                'subtotal' => $this->cartSubtotalFromMap($map),
            ],
        ]);
    }

    protected function jsonCartError(string $message, int $status = 422): JsonResponse
    {
        return response()->json(['error' => $message], $status);
    }

    /* --------------------------------- Pages --------------------------------- */

    public function index()
    {
        // Always provide $products and a simple $cart map [id => qty] so the Blade stays the same.
        if (Auth::check()) {
            $dbCart = Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['total_amount' => 0]);
            $dbCart->load('items.product.images');

            $map = [];
            foreach ($dbCart->items as $it) {
                $map[$it->product_id] = (int) $it->quantity;
            }

            $products = $dbCart->items->pluck('product')->filter()->values();
            return view('cart.index', ['products' => $products, 'cart' => $map]);
        }

        // Guests (session)
        $map = session()->get('cart', []);
        $products = empty($map)
            ? collect()
            : Product::with('images')->whereIn('id', array_keys($map))->get();

        return view('cart.index', ['products' => $products, 'cart' => $map]);
    }

    /* -------------------------------- Actions -------------------------------- */

    // POST /cart/add/{product}
    public function add(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'nullable|integer|min:1|max:999',
        ]);
        $qty = (int) ($validated['quantity'] ?? 1);

        if ((int)($product->stock ?? 0) <= 0) {
            return $request->expectsJson()
                ? $this->jsonCartError('This product is out of stock.')
                : back()->with('error', 'This product is out of stock.');
        }

        if (Auth::check()) {
            // DB cart for authenticated users
            $cart = Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['total_amount' => 0]);
            /** @var CartItem $item */
            $item = $cart->items()->firstOrNew(['product_id' => $product->id]);
            $current = (int) $item->quantity;
            $item->quantity = min($current + $qty, (int) $product->stock);
            $item->save();

            // Build map for response
            $map = $this->getCartMap();
            return $request->expectsJson()
                ? $this->jsonCartOk('Product added to cart!', $map)
                : back()->with('success', 'Product added to cart!');
        }

        // Guests (session)
        $map = session()->get('cart', []);
        $map[$product->id] = min((int) ($map[$product->id] ?? 0) + $qty, (int) $product->stock);
        $this->setSessionMap($map);

        return $request->expectsJson()
            ? $this->jsonCartOk('Product added to cart!', $map)
            : back()->with('success', 'Product added to cart!');
    }

    // POST /cart/update/{product}
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:999',
        ]);
        $qty = (int) $validated['quantity'];

        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['total_amount' => 0]);

            /** @var CartItem|null $item */
            $item = $cart->items()->where('product_id', $product->id)->first();

            if ($qty === 0) {
                if ($item) $item->delete();
                $map = $this->getCartMap();
                return $request->expectsJson()
                    ? $this->jsonCartOk('Item removed from cart.', $map)
                    : back()->with('success', 'Item removed from cart.');
            }

            if ((int) ($product->stock ?? 0) < $qty) {
                return $request->expectsJson()
                    ? $this->jsonCartError('Requested quantity exceeds stock.')
                    : back()->with('error', 'Requested quantity exceeds stock.');
            }

            if (!$item) {
                $item = new CartItem(['product_id' => $product->id]);
                $item->cart_id = $cart->id;
            }
            $item->quantity = $qty;
            $item->save();

            $map = $this->getCartMap();
            return $request->expectsJson()
                ? $this->jsonCartOk('Quantity updated.', $map)
                : back()->with('success', 'Quantity updated.');
        }

        // Guests (session)
        $map = session()->get('cart', []);

        if ($qty === 0) {
            unset($map[$product->id]);
            $this->setSessionMap($map);
            return $request->expectsJson()
                ? $this->jsonCartOk('Item removed from cart.', $map)
                : back()->with('success', 'Item removed from cart.');
        }

        if ((int) ($product->stock ?? 0) < $qty) {
            return $request->expectsJson()
                ? $this->jsonCartError('Requested quantity exceeds stock.')
                : back()->with('error', 'Requested quantity exceeds stock.');
        }

        $map[$product->id] = $qty;
        $this->setSessionMap($map);

        return $request->expectsJson()
            ? $this->jsonCartOk('Quantity updated.', $map)
            : back()->with('success', 'Quantity updated.');
    }

    // DELETE /cart/remove/{product}
    public function remove(Request $request, Product $product)
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['total_amount' => 0]);
            $cart->items()->where('product_id', $product->id)->delete();

            $map = $this->getCartMap();
            return $request->expectsJson()
                ? $this->jsonCartOk('Item removed from cart.', $map)
                : back()->with('success', 'Item removed from cart.');
        }

        $map = session()->get('cart', []);
        if (isset($map[$product->id])) {
            unset($map[$product->id]);
            $this->setSessionMap($map);
        }

        return $request->expectsJson()
            ? $this->jsonCartOk('Item removed from cart.', $map)
            : back()->with('success', 'Item removed from cart.');
    }

    // POST /cart/clear
    public function clear(Request $request)
    {
        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id(), 'status' => 'active'], ['total_amount' => 0]);
            $cart->items()->delete();
            $map = []; // empty
        } else {
            session()->forget('cart');
            $map = [];
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => 'Cart cleared.',
                'cart'    => ['count' => 0, 'subtotal' => 0.0],
            ]);
        }

        return back()->with('success', 'Cart cleared.');
    }
}

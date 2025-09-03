<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use App\Mail\PurchaseConfirmation;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * Return unit price using discount_price when > 0, else price.
     */
    protected function unitPrice(Product $p): float
    {
        return (float) (
            ($p->discount_price !== null && $p->discount_price > 0)
                ? $p->discount_price
                : $p->price
        );
    }

    /**
     * Ensure any session cart items are migrated into the user's active DB cart.
     */
    protected function migrateSessionCartToDbCart(int $userId): ?Cart
    {
        $sessionCart = session()->get('cart', []); // [product_id => qty]

        // Get or create active cart
        /** @var Cart $cart */
        $cart = Cart::firstOrCreate(
            ['user_id' => $userId, 'status' => 'active'],
            ['total_amount' => 0]
        );

        if (!empty($sessionCart)) {
            $productIds = array_keys($sessionCart);
            $products = Product::whereIn('id', $productIds)->get(['id', 'stock']);
            $stocks = $products->pluck('stock', 'id');

            foreach ($sessionCart as $pid => $qty) {
                $qty = max(0, (int) $qty);
                if ($qty === 0) continue;

                $max = (int) ($stocks[$pid] ?? 0);
                if ($max <= 0) continue;

                $qty = min($qty, $max);

                /** @var CartItem $item */
                $item = $cart->items()->firstOrNew(['product_id' => $pid]);
                $item->quantity = (int) $item->quantity + $qty;
                // price stays null at this stage (we snapshot at order time)
                $item->save();
            }

            // Keep session cart until order is placed (badge stays in sync via JS).
        }

        return $cart->load('items.product.images');
    }

    /**
     * Show checkout page with computed prices/totals (without persisting).
     */
    public function index()
    {
        $userId = Auth::id();

        // Migrate any session cart into DB cart before showing checkout
        $cart = $this->migrateSessionCartToDbCart($userId);

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        // Build a computed view-model for display (unit price from Product)
        $computed = $cart->items->map(function (CartItem $item) {
            $p = $item->product;
            if (!$p) {
                return [
                    'item'    => $item,
                    'product' => null,
                    'qty'     => (int) $item->quantity,
                    'unit'    => 0.0,
                    'line'    => 0.0,
                ];
            }

            $unit = $this->unitPrice($p);
            $qty  = (int) $item->quantity;

            return [
                'item'    => $item,
                'product' => $p,
                'qty'     => $qty,
                'unit'    => $unit,
                'line'    => round($unit * $qty, 2),
            ];
        });

        $subtotal = round($computed->sum('line'), 2);

        return view('checkout.index', [
            'cart'     => $cart,
            'computed' => $computed,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * Process checkout: validate stock, snapshot prices, create order, decrement stock, clear carts.
     */
    public function store(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|string|max:1000',
            'payment_method'   => 'required|string', // e.g. restrict with: in:Cash,WishMoney,OMT
        ]);

        $userId = Auth::id();

        // Always work from DB cart at checkout time
        /** @var Cart|null $cart */
        $cart = Cart::where('user_id', $userId)
            ->where('status', 'active')
            ->with('items.product')
            ->first();

        // If no DB cart items, attempt a last-minute migration from session
        if (!$cart || $cart->items->isEmpty()) {
            $cart = $this->migrateSessionCartToDbCart($userId);
        }

        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        try {
            DB::beginTransaction();

            $total = 0.0;
            $outOfStock = [];

            // Validate stock & snapshot prices with row locks
            foreach ($cart->items as $item) {
                /** @var Product|null $p */
                $p = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if (!$p) {
                    $outOfStock[] = "Unknown product (#{$item->product_id})";
                    continue;
                }

                $price = $this->unitPrice($p);

                if ((int) $p->stock < (int) $item->quantity) {
                    $outOfStock[] = $p->name;
                    continue;
                }

                // Snapshot the unit price on the cart item (now it won’t be null)
                $item->price = $price;
                $item->save();

                $total += $price * (int) $item->quantity;
            }

            if (!empty($outOfStock)) {
                DB::rollBack();
                $message = 'Sorry, not enough stock for: ' . implode(', ', $outOfStock);
                return back()->with('error', $message)->with('out_of_stock', $outOfStock);
            }

            // Create order
            $voucherCode = strtoupper(Str::random(8));
            /** @var Order $order */
            $order = Order::create([
                'user_id'          => $userId,
                'total_amount'     => round($total, 2),
                'status'           => 'pending',
                'payment_method'   => $request->payment_method,
                'shipping_address' => $request->shipping_address,
                'voucher_code'     => $voucherCode,
            ]);

            // Create order items, decrement stock, log movements
            foreach ($cart->items as $item) {
                /** @var Product|null $p */
                $p = Product::where('id', $item->product_id)->lockForUpdate()->first();
                if (!$p) continue;

                $price = ($item->price !== null)
                    ? (float) $item->price
                    : $this->unitPrice($p);

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $p->id,
                    'quantity'   => (int) $item->quantity,
                    'price'      => $price,
                ]);

                // Decrement stock (guard race condition)
                if ((int) $p->stock >= (int) $item->quantity) {
                    $p->decrement('stock', (int) $item->quantity);
                } else {
                    DB::rollBack();
                    return back()->with('error', "Stock changed for {$p->name}. Please try again.");
                }

                // Optional stock movement log
                if (class_exists(StockMovement::class)) {
                    StockMovement::create([
                        'product_id'    => $p->id,
                        'quantity'      => -(int) $item->quantity,
                        'movement_type' => 'sale',
                        'reference_id'  => $order->id,
                        'description'   => 'Stock reduced for Order #' . $order->id,
                    ]);
                }
            }

            // Mark cart as completed and clear items
            $cart->status = 'ordered';
            $cart->total_amount = 0;
            $cart->items()->delete();
            $cart->save();

            // Also clear session cart to keep everything in sync
            session()->forget('cart');

            DB::commit();

            // Eager-load for notifications
            $order->load('items.product', 'user');

            // Email confirmation (best-effort)
            try {
                if ($order->user && $order->user->email) {
                    Mail::to($order->user->email)
                        ->cc('deluxeplusmohammad@gmail.com')
                        ->queue(new PurchaseConfirmation($order));
                }
            } catch (\Exception $e) {
                Log::error('Email send failed', ['error' => $e->getMessage()]);
            }

            // WhatsApp notifications (best-effort)
            try {
                $sid   = config('services.twilio.sid');
                $token = config('services.twilio.token');
                $from  = config('services.twilio.whatsapp_from'); // e.g. "whatsapp:+14155238886"
                $admin = config('services.twilio.whatsapp_to');   // e.g. "+96170xxxxxx" or "whatsapp:+96170xxxxxx"

                if ($sid && $token && $from) {
                    $client = new Client($sid, $token);

                    $productList = "";
                    foreach ($order->items as $it) {
                        $name = $it->product->name ?? ('#' . $it->product_id);
                        $productList .= "- {$name} (x{$it->quantity}) @ \${$it->price}\n";
                    }

                    // Ensure whatsapp: prefix for admin & client numbers
                    $adminTo = str_starts_with((string) $admin, 'whatsapp:') ? $admin : ('whatsapp:' . $admin);

                    $adminMsg = "🛒 *New Order Alert!*\n"
                        . "📦 Order ID: {$order->id}\n"
                        . "💰 Total: \${$order->total_amount}\n"
                        . "👤 Customer: " . ($order->user->name ?? 'N/A') . "\n"
                        . "📱 Phone: " . ($order->user->phone ?? 'N/A') . "\n"
                        . "🚚 Address: {$order->shipping_address}\n"
                        . "\n🧾 *Products Ordered:*\n{$productList}"
                        . "\n🎁 Voucher for client: *{$voucherCode}*";

                    if ($admin) {
                        $client->messages->create($adminTo, [
                            'from' => $from,
                            'body' => $adminMsg,
                        ]);
                    }

                    $clientNumber = $order->user->phone ?? null;
                    if ($clientNumber) {
                        $clientTo = str_starts_with($clientNumber, 'whatsapp:')
                            ? $clientNumber
                            : ('whatsapp:' . $clientNumber);

                        $clientMsg = "✅ Thank you for your purchase, " . ($order->user->name ?? 'Customer') . "!\n"
                            . "🧾 Order #{$order->id} total: \${$order->total_amount}\n"
                            . "🎁 Your voucher code for next time: *{$voucherCode}*";

                        $client->messages->create($clientTo, [
                            'from' => $from,
                            'body' => $clientMsg,
                        ]);
                    }

                    Log::info('WhatsApp messages sent successfully.');
                }
            } catch (\Exception $e) {
                Log::error('WhatsApp sending failed', ['error' => $e->getMessage()]);
            }

            // Redirect to thank-you page
            return redirect()->signedRoute('checkout.thankyou', ['oid' => $order->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order processing failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Order failed: ' . $e->getMessage());
        }
    }

    /**
     * Thank-you page.
     */
    public function thankYou(Request $request)
{
    $orderId = $request->route('oid') ?? session('order_id');
    if (!$orderId) {
        return redirect()->route('home')->with('error', 'No order found.');
    }

    $order = Order::with('items.product')->find($orderId);
    if (!$order) {
        return redirect()->route('home')->with('error', 'Order not found.');
    }

    $isSigned = $request->hasValidSignature();
    $isOwner  = auth()->check() && auth()->id() === (int) $order->user_id;

    if (! $isSigned && ! $isOwner) {
        return redirect()->route('home')->with('error', 'Unauthorized access to order.');
    }

    return view('checkout.thankyou', compact('order'));
}
}

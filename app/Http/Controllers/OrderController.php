<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StockMovement;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * Show the main orders page with only Active Orders loaded.
     */
    public function index()
    {
        $activeOrders = Order::where('user_id', Auth::id())
                             ->whereIn('status', ['pending', 'processing', 'shipped'])
                             ->orderBy('created_at', 'desc')
                             ->get();

        return view('orders.index', compact('activeOrders'));
    }

    /**
     * Cancel a specific order and restore stock.
     */
    public function cancel($id)
    {
        $order = Order::where('id', $id)
                      ->where('user_id', auth()->id())
                      ->firstOrFail();

        if (in_array($order->status, ['completed', 'cancelled'])) {
            return redirect()->back()->with('error', 'This order cannot be cancelled.');
        }

        // Restore stock for each product in the order
        foreach ($order->orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                // Restore stock in products table
                $product->stock += $item->quantity;
                $product->save();

                // Log stock movement
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'movement_type' => 'return',
                    'reference_id' => $order->id,
                    'description' => 'Order #' . $order->id . ' cancelled',
                ]);
            }
        }

        $order->status = 'cancelled';
        $order->save();

        return redirect()->back()->with('success', 'Your order has been cancelled successfully.');
    }

    /**
     * Load Completed Orders via AJAX.
     */
    public function completed()
    {
        $completedOrders = Order::where('user_id', Auth::id())
                                ->where('status', 'completed')
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('orders.partials.completed', compact('completedOrders'));
    }

    /**
     * Load Cancelled Orders via AJAX.
     */
    public function cancelled()
    {
        $cancelledOrders = Order::where('user_id', Auth::id())
                                ->where('status', 'cancelled')
                                ->orderBy('created_at', 'desc')
                                ->get();

        return view('orders.partials.cancelled', compact('cancelledOrders'));
    }

    /**
     * Reduce stock when order is confirmed.
     * Call this method after creating the order in checkout flow.
     */
    public function reduceStock(Order $order)
    {
        foreach ($order->orderItems as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                // Reduce stock in products table
                $product->stock -= $item->quantity;
                $product->save();

                // Log stock movement
                StockMovement::create([
                    'product_id' => $item->product_id,
                    'quantity' => -$item->quantity,
                    'movement_type' => 'sale',
                    'reference_id' => $order->id,
                    'description' => 'Order #' . $order->id . ' placed',
                ]);
            }
        }
    }
}

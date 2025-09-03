<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class MergeGuestCart
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
   public function handle(Login $event)
    {
        $cartId = Session::get('cart_id');

        if (!$cartId) {
            return;
        }

        $guestCart = Cart::where('id', $cartId)->whereNull('user_id')->first();

        if ($guestCart) {
            // Get or create user's active cart
            $userCart = Cart::firstOrCreate(
                ['user_id' => $event->user->id, 'status' => 'active'],
                ['total_amount' => 0]
            );

            foreach ($guestCart->items as $item) {
                $existingItem = $userCart->items()
                    ->where('product_id', $item->product_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->quantity += $item->quantity;
                    $existingItem->save();
                } else {
                    $userCart->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price
                    ]);
                }
            }

            // Delete old guest cart & session
            $guestCart->delete();
            Session::forget('cart_id');
        }
    }
}

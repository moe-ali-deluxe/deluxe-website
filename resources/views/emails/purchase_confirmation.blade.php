@component('mail::message')
# Thank you for your order, {{ $order->user->name ?? 'Customer' }} 🎉

We’ve received your order **#{{ $order->id }}**.

@component('mail::table')
| Product | Qty | Price |
|:--|:--:|--:|
@foreach($order->items as $item)
| {{ $item->product->name ?? ('#'.$item->product_id) }} | {{ $item->quantity }} | ${{ number_format($item->price, 2) }} |
@endforeach
@endcomponent

**Total:** ${{ number_format($order->total_amount, 2) }}

@component('mail::button', ['url' => route('orders.index')])
View My Orders
@endcomponent

Thanks for shopping with Deluxe Plus Medical & Dental Supplies!

@endcomponent

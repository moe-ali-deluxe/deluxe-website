{{-- resources/views/orders/partials/cancelled.blade.php --}}
<div class="cancelled-orders">
    @if($cancelledOrders->isEmpty())
        <p class="text-gray-600">No cancelled orders found.</p>
    @else
        @foreach($cancelledOrders as $order)
            <div class="mb-4 p-4 border rounded bg-red-50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold">
                            <span class="text-gray-600">Order #:</span>
                            {{ $order->id }}
                        </p>
                        <p class="mt-1">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-sm font-medium bg-red-100 text-red-700">
                                {{ ucfirst($order->status) }}
                            </span>
                        </p>
                        <p class="mt-1">
                            <span class="text-gray-600">Total:</span>
                            ${{ number_format((float) $order->total_amount, 2) }}
                        </p>
                        <p class="mt-1 text-gray-600">
                            <span>Date:</span>
                            {{ optional($order->created_at)->format('d M Y') }}
                        </p>
                    </div>
                    {{-- Optional: action area (leave empty or add a “View” if you have an orders.show route) --}}
                </div>
            </div>
        @endforeach

        {{-- Render pagination if the collection is paginated --}}
        @if(method_exists($cancelledOrders, 'links'))
            <div class="mt-4">
                {{ $cancelledOrders->links() }}
            </div>
        @endif
    @endif
</div>

{{-- resources/views/orders/partials/completed.blade.php --}}
<div class="completed-orders">
    @if($completedOrders->isEmpty())
        <p class="text-gray-600">No completed orders found.</p>
    @else
        @foreach($completedOrders as $order)
            <div class="mb-4 p-4 border rounded bg-green-50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-semibold">
                            <span class="text-gray-600">Order #:</span>
                            {{ $order->id }}
                        </p>
                        <p class="mt-1">
                            <span class="text-gray-600">Status:</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-sm font-medium bg-green-100 text-green-700">
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

                    {{-- Optional: link to details if you add a route like orders.show --}}
                    @if(Route::has('orders.show'))
                        <a href="{{ route('orders.show', $order) }}"
                           class="self-start inline-flex items-center px-3 py-1.5 text-sm rounded bg-white border hover:bg-gray-50">
                           View
                        </a>
                    @endif
                </div>
            </div>
        @endforeach

        {{-- Render pagination if the collection is paginated --}}
        @if(method_exists($completedOrders, 'links'))
            <div class="mt-4">
                {{ $completedOrders->links() }}
            </div>
        @endif
    @endif
</div>
